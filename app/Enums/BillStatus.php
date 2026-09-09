<?php

namespace App\Enums;

enum BillStatus: string
{
    case Issued = 'issued';
    case Voided = 'voided';
}
