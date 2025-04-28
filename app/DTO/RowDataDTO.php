<?php

namespace App\DTO;

class RowDataDTO
{
    public function __construct(
        public readonly int $uid,
        public readonly string $name,
        public readonly \Carbon\Carbon $date
    ) {}
}
