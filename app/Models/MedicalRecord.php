<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $resident_request_id
 * @property int|null $user_id
 * @property string|null $record_type
 * @property string|null $record_value
 * @property string|null $note
 * @property int|null $recorded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'resident_request_id',
        'user_id',
        'record_type',
        'record_value',
        'note',
        'recorded_by',
    ];

    public function residentRequest(): BelongsTo
    {
        return $this->belongsTo(ResidentRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
