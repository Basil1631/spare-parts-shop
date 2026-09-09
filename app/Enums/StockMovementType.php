<?php

namespace App\Enums;

enum StockMovementType: string
{
    case Receive = 'receive';
    case Sale = 'sale';
    case VoidRestore = 'void_restore';
    case ReturnIn = 'return';
    case AdjustIn = 'adjust_in';
    case AdjustOut = 'adjust_out';

    public function increasesStock(): bool
    {
        return in_array($this, [self::Receive, self::VoidRestore, self::ReturnIn, self::AdjustIn], true);
    }
}
