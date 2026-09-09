<?php

namespace App\Support;

class Sku
{
    public static function normalize(?string $sku): string
    {
        $sku = strtoupper((string) $sku);

        return preg_replace('/[\s\-]+/', '', $sku) ?? '';
    }
}
