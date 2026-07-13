<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HouseholdMonitoringCase extends Model
{
    public const TYPE_PREGNANT = 'pregnant';

    public const TYPE_SENIOR = 'senior';

    public const TYPE_GENERAL = 'general';

    protected $fillable = [
        'resident_request_id',
        'user_id',
        'recorded_by',
        'member_type',
        'member_name',
        'member_source',
        'member_index',
        'issue_notes',
        'first_period_date',
        'last_period_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'member_index' => 'integer',
            'first_period_date' => 'date',
            'last_period_date' => 'date',
            'is_active' => 'boolean',
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(HouseholdMonitoringVisit::class);
    }

    public function isPregnant(): bool
    {
        return $this->member_type === self::TYPE_PREGNANT;
    }

    public function isSenior(): bool
    {
        return $this->member_type === self::TYPE_SENIOR;
    }

    public function typeLabel(): string
    {
        return match ($this->member_type) {
            self::TYPE_PREGNANT => 'Pregnant',
            self::TYPE_SENIOR => 'Senior',
            self::TYPE_GENERAL => 'Monitoring',
            default => ucfirst((string) $this->member_type),
        };
    }
}
