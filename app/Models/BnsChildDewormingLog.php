<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BnsChildDewormingLog extends Model
{
    protected $fillable = [
        'child_key',
        'resident_request_id',
        'child_index',
        'given_on',
        'medicine',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'given_on' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
