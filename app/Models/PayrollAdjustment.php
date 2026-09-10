<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    protected $fillable = [
        'user_id',
        'period_start',
        'cuttings_fils',
        'notes',
        'set_by',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'cuttings_fils' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
