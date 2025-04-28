<?php

namespace App\Services;

use App\Models\Row;
use App\Events\RowCreatedEvent;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;
use App\Validators\RowDataValidator;

class ImportService implements ToCollection
{
    use Importable;
    protected array $errors = [];
    protected int $processedRows = 0;

    /**
     * Обработка коллекции строк.
     * @throws \Exception
     */
    public function collection(Collection $rows): void
    {
        $progressKey = uniqid('import_', true);
        Redis::set("import:progress:{$progressKey}", 0);

        $batchSize = 1000;
        $batch = [];
        $processed = 0;

        // Пропускаем заголовок
        $rows = array_slice($rows->all(), 1);

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $lineNumber = $index + 2;

                $validationErrors = RowDataValidator::validate($row->toArray());

                if (!empty($validationErrors)) {
                    $this->errors[] = "На строке {$lineNumber} - " . implode(', ', $validationErrors);
                    $processed++;
                    continue;
                }

                $batch[] = [
                    'uid' => $row[0],
                    'name' => $row[1],
                    'date' => \Carbon\Carbon::createFromFormat('d.m.Y', $row[2])->format('Y-m-d'),
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                $processed++;

                if (count($batch) >= $batchSize) {
                    Row::insert($batch);
                    $batch = [];
                    Redis::set($progressKey, $processed);
                    event(new RowCreatedEvent($processed));
                }
            }

            // Вставляем оставшиеся строки
            if (!empty($batch)) {
                Row::insert($batch);
                Redis::set($progressKey, $processed);
                event(new RowCreatedEvent($processed));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Выполняет запуск импорта.
     */
    public function import(string $filePath): void
    {
        Row::truncate();
        Excel::import($this, $filePath);
        Storage::delete($filePath);

        $this->SaveErrorsAndPushToGit();
    }

    /**
     * Отправляет отчёт об ошибках в Git.
     */
    private function SaveErrorsAndPushToGit(): void
    {
        if (empty($this->errors)) {
            return;
        }

        $gitReportsDir = base_path('storage/app');
        if (!file_exists($gitReportsDir)) {
            mkdir($gitReportsDir, 0755, true);
        }

        $reportPath = $gitReportsDir . '/result.txt';
        file_put_contents($reportPath, implode("\n", $this->errors));

        $commands = [
            "cd " . escapeshellarg(base_path()),
            "git add " . escapeshellarg($reportPath),
            "git commit -m " . escapeshellarg("Auto: Импорт данных от " . date('Y-m-d H:i:s')),
            "git push origin master"
        ];

        foreach ($commands as $cmd) {
            exec($cmd." 2>&1", $output, $returnCode);
            Log::info("Command: {$cmd}", ['output' => $output]);

            if ($returnCode !== 0) {
                Log::error("Ошибка: {$cmd}", ['output' => $output]);
                break;
            }
        }
    }
}
