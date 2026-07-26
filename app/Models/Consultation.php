<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $resident_request_id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property string|null $medical_checkup
 * @property string|null $note
 * @property int|null $set_by
 */
class Consultation extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_request_id',
        'user_id',
        'scheduled_at',
        'medical_checkup',
        'note',
        'set_by',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function residentRequest(): BelongsTo
    {
        return $this->belongsTo(ResidentRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function setter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'set_by');
    }
}
