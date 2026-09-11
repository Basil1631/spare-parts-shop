<?php

namespace App\Enums;

enum PurchaseStatus: string
{
    case AwaitingGodown = 'awaiting_godown';
    case Received = 'received';
    case Paid = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::AwaitingGodown => 'Awaiting godown',
            self::Received => 'Received · unpaid',
            self::Paid => 'Paid',
        };
    }
}
