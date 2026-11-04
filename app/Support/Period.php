<?php

namespace App\Support;

// Rating periods stored in the `semester` column of ipcrs/spcrs:
// 1-2 are semesters, 3-6 are the fixed quarters Q1-Q4.
class Period
{
    const LABELS = [
        1 => '1st Semester',
        2 => '2nd Semester',
        3 => '1st Quarter (Jan-Mar)',
        4 => '2nd Quarter (Apr-Jun)',
        5 => '3rd Quarter (Jul-Sep)',
        6 => '4th Quarter (Oct-Dec)',
    ];

    const SHORT_LABELS = [
        1 => '1st Sem',
        2 => '2nd Sem',
        3 => 'Q1',
        4 => 'Q2',
        5 => 'Q3',
        6 => 'Q4',
    ];

    // [first month, last month] of each period.
    const MONTHS = [
        1 => [1, 6],
        2 => [7, 12],
        3 => [1, 3],
        4 => [4, 6],
        5 => [7, 9],
        6 => [10, 12],
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

    public static function isQuarter($code): bool
    {
        return (int) $code >= 3;
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
        ]])->all();
    }
}
