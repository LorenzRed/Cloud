<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BnsChildImmunization extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_GIVEN = 'given';

    public const STATUS_MISSED = 'missed';

    protected $fillable = [
        'child_key',
        'resident_request_id',
        'child_index',
        'vaccine_code',
        'vaccine_name',
        'due_date',
        'given_date',
        'status',
        'notes',
        'recorded_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'given_date' => 'date',
        ];
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_user_id');
    }
}
