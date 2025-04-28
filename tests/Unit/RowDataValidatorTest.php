<?php

namespace Tests\Unit;

use App\Validators\RowDataValidator;
use Tests\TestCase;

class RowDataValidatorTest extends TestCase
{
    private RowDataValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RowDataValidator();
    }

    public function test_valid_row_passes_validation(): void
    {
        $row = [
            '123',
            'John',
            '25.04.2025',
        ];

        $errors = $this->validator->validate($row, 1);

        $this->assertCount(0, $errors);
    }

    public function test_missing_fields_fail_validation(): void
    {
        $row = [
            'uid' => '',
            'name' => '',
            'date' => '',
        ];

        $errors = $this->validator->validate($row, 1);

        $this->assertNotEmpty($errors);
        $this->assertCount(3, $errors);
    }

    public function test_invalid_date_format_fails_validation(): void
    {
        $row = [
            'uid' => '123',
            'name' => 'John Doe',
            'date' => '2025-04-25',
        ];

        $errors = $this->validator->validate($row, 1);

        $this->assertNotEmpty($errors);
    }
}
