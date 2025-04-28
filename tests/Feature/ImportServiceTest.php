<?php

namespace Tests\Feature;

use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_service_creates_rows_and_stores_errors(): void
    {
        $data = [
            ['uid' => '1', 'name' => 'John', 'date' => '25.04.2025'],
            ['uid' => '', 'name' => 'No ID', 'date' => '25.04.2025'], // ошибка
        ];

        // Симулируем файл Excel как CSV для простоты теста
        $csvContent = "id,name,date\n";
        foreach ($data as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        $path = 'imports/test.csv';
        Storage::disk('local')->put($path, $csvContent);

        $importService = app(ImportService::class);

        Redis::shouldReceive('set')->times(2);

        $importService->import($path);

        $this->assertDatabaseHas('rows', [
            'name' => 'John',
        ]);

        Storage::disk('storage')->assertExists('result.txt');
    }
}
