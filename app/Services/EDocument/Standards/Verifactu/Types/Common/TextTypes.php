<?php

namespace App\Services\EDocument\Standards\Verifactu\Types\Common;

trait TextTypes
{
    protected function validateMaxLength(string $value, int $maxLength, string $fieldName): void
    {
        if (strlen($value) > $maxLength) {
            throw new \InvalidArgumentException("$fieldName must not exceed $maxLength characters");
        }
    }

    protected function validateExactLength(string $value, int $length, string $fieldName): void
    {
        if (strlen($value) !== $length) {
            throw new \InvalidArgumentException("$fieldName must be exactly $length characters long");
        }
    }

    protected function validateNIF(string $nif): void
    {
        $this->validateExactLength($nif, 9, 'NIF');
        // TODO: Add more specific NIF validation rules
    }

    protected function validateDate(string $date): void
    {
        if (!preg_match('/^\d{2}-\d{2}-\d{4}$/', $date)) {
            throw new \InvalidArgumentException('Date must be in DD-MM-YYYY format');
        }
        
        list($day, $month, $year) = explode('-', $date);
        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new \InvalidArgumentException('Invalid date');
        }
    }

    protected function validateTimestamp(string $timestamp): void
    {
        if (!preg_match('/^\d{2}-\d{2}-\d{4} \d{2}:\d{2}:\d{2}$/', $timestamp)) {
            throw new \InvalidArgumentException('Timestamp must be in DD-MM-YYYY HH:mm:ss format');
        }

        list($date, $time) = explode(' ', $timestamp);
        list($day, $month, $year) = explode('-', $date);
        list($hour, $minute, $second) = explode(':', $time);

        if (!checkdate((int)$month, (int)$day, (int)$year)) {
            throw new \InvalidArgumentException('Invalid date in timestamp');
        }

        if ($hour > 23 || $minute > 59 || $second > 59) {
            throw new \InvalidArgumentException('Invalid time in timestamp');
        }
    }

    protected function validateNumericString(string $value, int $maxIntegerDigits, int $maxDecimalDigits, string $fieldName): void
    {
        if (!preg_match('/^[+-]?\d{1,' . $maxIntegerDigits . '}(\.\d{0,' . $maxDecimalDigits . '})?$/', $value)) {
            throw new \InvalidArgumentException("$fieldName must have at most $maxIntegerDigits digits before decimal point and $maxDecimalDigits after");
        }
    }
} 