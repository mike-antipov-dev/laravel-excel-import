<?php

namespace App\Validators;

class RowDataValidator
{
    /**
     * Валидация строки.
     */
    public static function validate(array $row): array
    {
        $errors = [];

        if (empty($row[0])) {
            $errors[] = "отсутствует ID";
        }

        if (!empty($row[0]) && !is_numeric($row[0])) {
            $errors[] = "некорректный ID";
        }

        if (empty(trim($row[1] ?? ''))) {
            $errors[] = "отсутствует имя";
        }

        if (empty(trim($row[2] ?? ''))) {
            $errors[] = "отсутствует дата";
        } elseif (!self::validateDate($row[2] ?? '')) {
            $errors[] = "некорректный формат даты";
        }

        return $errors;
    }

    /**
     * Проверка формата даты.
     */
    private static function validateDate($date): bool
    {
        $d = \DateTime::createFromFormat('d.m.Y', $date);
        return $d && $d->format('d.m.Y') === $date;
    }
}
