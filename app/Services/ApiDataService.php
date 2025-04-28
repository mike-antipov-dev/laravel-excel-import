<?php

namespace App\Services;

use App\Models\Row;

class ApiDataService
{
    public static function getRows()
    {
        return Row::all()
            ->groupBy(function ($item) {
                return $item->date;
            })
            ->map(function ($items, $date) {
                return [
                    'date' => $date,
                    'items' => $items->map(function ($item) {
                        return [
                            'id' => $item->uid,
                            'name' => $item->name,
                            'date' => $item->date,
                        ];
                    })->values(),
                ];
            })
            ->values()
            ->toJson();
    }
}
