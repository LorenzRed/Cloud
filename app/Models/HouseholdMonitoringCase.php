<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int|null $resident_request_id
 * @property int|null $user_id
 * @property int|null $recorded_by
 * @property string|null $member_type
 * @property string|null $member_name
 * @property string|null $member_source
 * @property int|null $member_index
 * @property string|null $issue_notes
 * @property \Illuminate\Support\Carbon|null $first_period_date
 * @property \Illuminate\Support\Carbon|null $last_period_date
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $completed_at
 */
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
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'member_index' => 'integer',
            'first_period_date' => 'date',
            'last_period_date' => 'date',
            'is_active' => 'boolean',
            'completed_at' => 'datetime',
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

    public static function hasActiveForMember(
        int $residentRequestId,
        ?string $memberSource,
        ?int $memberIndex,
        string $memberName
    ): bool {
        $query = self::query()
            ->where('resident_request_id', $residentRequestId)
            ->where('is_active', true)
            ->where(function ($identityQuery) use ($memberSource, $memberIndex, $memberName): void {
                $identityQuery
                    ->whereRaw('LOWER(TRIM(member_name)) = ?', [mb_strtolower(trim($memberName))]);

                if ($memberIndex !== null && $memberSource !== null) {
                    $identityQuery->orWhere(function ($indexedQuery) use ($memberSource, $memberIndex): void {
                        $indexedQuery
                            ->where('member_source', $memberSource)
                            ->where('member_index', $memberIndex);
                    });
                }
            });

        return $query->exists();
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
