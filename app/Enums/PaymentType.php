<?php

namespace App\Enums;

enum PaymentType: string
{
    case Cash = 'cash';
    case Credit = 'credit';
    case Installment = 'installment';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Ready cash',
            self::Credit => 'Credit',
            self::Installment => 'Installment',
        };
    }
}
