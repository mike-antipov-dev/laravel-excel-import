<?php

namespace App\Repositories;

use App\DTO\RowDataDTO;
use App\Models\Row;

class RowRepository
{
    public function create(RowDataDTO $dto): Row
    {
        return Row::create([
            'name' => $dto->name,
            'date' => $dto->date->toDateString(),
        ]);
    }
}
