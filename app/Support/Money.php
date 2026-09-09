<?php

namespace App\Support;

class Money
{
    public static function toFils(string|float|int|null $aed): int
    {
        if ($aed === null || $aed === '') {
            return 0;
        }

        $normalized = str_replace(',', '', (string) $aed);

        return (int) round(((float) $normalized) * 100);
    }

    public static function fromFils(int $fils): string
    {
        $negative = $fils < 0;
        $fils = abs($fils);
        $formatted = number_format($fils / 100, 2, '.', ',');

        return ($negative ? '-' : '').$formatted;
    }

    public static function vatFils(int $exclusiveFils, string|float $vatPercent): int
    {
        return (int) round($exclusiveFils * ((float) $vatPercent) / 100);
    }
}
