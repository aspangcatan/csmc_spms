<?php

namespace App\Support;

// Rating periods stored in the `semester` column of ipcrs/spcrs:
// 1-2 are semesters, 3 is a 3 month period whose dates are picked on creation.
class Period
{
    const CUSTOM = 3;

    const LABELS = [
        1 => '1st Semester',
        2 => '2nd Semester',
        3 => '3 Month Period',
    ];

    const SHORT_LABELS = [
        1 => '1st Sem',
        2 => '2nd Sem',
        3 => '3 Mo',
    ];

    // [first month, last month] of each period. For the 3 month period this is only the
    // default suggested in the creation modal; its real dates are stored in period_from/period_to.
    const MONTHS = [
        1 => [1, 6],
        2 => [7, 12],
        3 => [1, 3],
    ];

    public static function codes(): array
    {
        return array_keys(self::LABELS);
    }

    // For `in:` validation rules.
    public static function rule(): string
    {
        return 'in:' . implode(',', self::codes());
    }

    public static function label($code): string
    {
        return self::LABELS[(int) $code] ?? 'Unknown Period';
    }

    public static function shortLabel($code): string
    {
        return self::SHORT_LABELS[(int) $code] ?? '?';
    }

    // True when the user picks the period's dates instead of deriving them from the code.
    public static function isCustom($code): bool
    {
        return (int) $code === self::CUSTOM;
    }

    // ['Y-m-d', 'Y-m-d'] date range the period covers in $year.
    public static function range($year, $code): array
    {
        [$start, $end] = self::MONTHS[(int) $code] ?? self::MONTHS[1];
        $from = sprintf('%04d-%02d-01', $year, $start);
        $to = date('Y-m-t', strtotime(sprintf('%04d-%02d-01', $year, $end)));

        return [$from, $to];
    }

    // Default period on page load: the current semester.
    public static function current(): int
    {
        return date('n') <= 6 ? 1 : 2;
    }

    // Data handed to the frontend so JS can label periods and compute their dates.
    public static function forJs(): array
    {
        return collect(self::codes())->mapWithKeys(fn ($code) => [$code => [
            'label' => self::LABELS[$code],
            'short' => self::SHORT_LABELS[$code],
            'months' => self::MONTHS[$code],
            'custom' => self::isCustom($code),
        ]])->all();
    }
}
