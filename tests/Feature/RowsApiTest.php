<?php

namespace Tests\Feature;

use App\Models\Row;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RowsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_returns_grouped_rows(): void
    {
        Row::factory()->create([
            'uid' => '1',
            'name' => 'John',
            'date' => '25.03.1978',
        ]);

        Row::factory()->create([
            'uid' => '2',
            'name' => 'Jack',
            'date' => '25.03.1978',
        ]);

        $response = $this->getJson('/api/data');

        $response->assertOk()
            ->assertJsonStructure([
                '*' => [
                    'date',
                    'items' => [
                        '*' => ['id', 'name', 'date']
                    ]
                ]
            ]);
    }
}
