<?php

namespace Tests\Feature;

use App\Events\RowCreatedEvent;
use App\Services\ImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileProcessedEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_row_created_event_is_dispatched(): void
    {
        Redis::shouldReceive('set')->times(2);
        Event::fake();

        $data = [
            ['uid' => '1', 'name' => 'John', 'date' => '25.04.2025'],
            ['uid' => '2', 'name' => 'Jack', 'date' => '28.03.2021']
        ];

        // Симулируем файл Excel как CSV для простоты теста
        $csvContent = "id,name,date\n";

        foreach ($data as $row) {
            $csvContent .= implode(',', $row) . "\n";
        }

        $path = 'imports/test.csv';
        Storage::disk('local')->put($path, $csvContent);

        $service = new ImportService();
        $service->import($path);

        Event::assertDispatched(RowCreatedEvent::class);
    }
}
