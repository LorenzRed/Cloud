<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BnsChildWeightLog extends Model
{
    protected $fillable = [
        'child_key',
        'resident_request_id',
        'child_index',
        'weight_kg',
        'height_cm',
        'muac_cm',
        'age_in_months',
        'recorded_on',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:1',
            'muac_cm' => 'decimal:1',
            'recorded_on' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
