<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $barangay_id
 * @property array<int, int|string>|null $target_purok_ids
 * @property int|null $created_by
 * @property string|null $title
 * @property string|null $description
 * @property Carbon|null $event_date
 * @property string|null $start_time
 * @property string|null $end_time
 * @property string|null $location
 * @property string|null $category
 * @property string|null $image_url
 * @property Carbon|null $disabled_at
 */
class Event extends Model
{
    public const CATEGORY_ANNOUNCEMENT = 'announcement';

    public const CATEGORY_IMMUNIZATION_CONSULTATION = 'immunization_consultation';

    protected $fillable = [
        'barangay_id',
        'target_purok_ids',
        'created_by',
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'location',
        'category',
        'image_url',
        'disabled_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'disabled_at' => 'datetime',
        'target_purok_ids' => 'array',
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function canBeManagedBy(User $user): bool
    {
        if ((int) $this->created_by === (int) $user->id) {
            return true;
        }

        $creatorRole = $this->createdBy?->role;

        return match ($creatorRole) {
            User::ROLE_BHW => in_array($user->role, [User::ROLE_BHW_HEAD, User::ROLE_BRG_CAPTAIN], true),
            User::ROLE_BHW_HEAD => $user->role === User::ROLE_BRG_CAPTAIN,
            User::ROLE_BRG_CAPTAIN => $user->role === User::ROLE_BHW_HEAD,
            default => false,
        };
    }

    /**
     * Barangay-wide when target_purok_ids is null; otherwise limited to those puroks.
     */
    public function scopeVisibleToPurok(Builder $query, ?int $purokId): Builder
    {
        return $query->where(function (Builder $builder) use ($purokId) {
            $builder->whereNull('target_purok_ids');

            if ($purokId) {
                $builder->orWhere(function (Builder $scoped) use ($purokId) {
                    $scoped
                        ->whereJsonContains('target_purok_ids', $purokId)
                        ->orWhereJsonContains('target_purok_ids', (string) $purokId);
                });
            }
        });
    }

    public function isBarangayWide(): bool
    {
        return $this->target_purok_ids === null;
    }

    public function expiresAt(): ?Carbon
    {
        if (! $this->event_date) {
            return null;
        }

        $date = $this->event_date->format('Y-m-d');
        $time = filled($this->end_time)
            ? Carbon::parse((string) $this->end_time)->format('H:i:s')
            : '23:59:59';

        return Carbon::parse($date.' '.$time);
    }

    public function isExpired(?Carbon $now = null): bool
    {
        $expiresAt = $this->expiresAt();

        if (! $expiresAt) {
            return false;
        }

        return $expiresAt->lte($now ?? now());
    }

    public function scopeActiveAnnouncements(Builder $query): Builder
    {
        $now = now();

        return $query
            ->where('category', self::CATEGORY_ANNOUNCEMENT)
            ->where(function (Builder $builder) use ($now) {
                $builder
                    ->whereDate('event_date', '>', $now->toDateString())
                    ->orWhere(function (Builder $sameDay) use ($now) {
                        $sameDay
                            ->whereDate('event_date', $now->toDateString())
                            ->where(function (Builder $timeCheck) use ($now) {
                                $timeCheck
                                    ->whereNull('end_time')
                                    ->orWhereTime('end_time', '>', $now->format('H:i:s'));
                            });
                    });
            });
    }

    /**
     * Resident feed: announcements that have not reached their end time.
     */
    public function scopeVisibleToResidents(Builder $query): Builder
    {
        return $query->activeAnnouncements();
    }

    /**
     * Staff archive: all announcements for a barangay/creator.
     */
    public function scopeStaffAnnouncements(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_ANNOUNCEMENT);
    }

    /**
     * BNS immunization consultation notices (date-only schedule items, not announcements).
     */
    public function scopeImmunizationConsultations(Builder $query): Builder
    {
        return $query->where('category', self::CATEGORY_IMMUNIZATION_CONSULTATION);
    }

    public static function purgeExpired(): int
    {
        $now = now();
        $deleted = 0;

        self::query()
            ->where('category', self::CATEGORY_ANNOUNCEMENT)
            ->whereDate('event_date', '<=', $now->toDateString())
            ->chunkById(100, function ($events) use ($now, &$deleted): void {
                foreach ($events as $event) {
                    if ($event->isExpired($now) && $event->delete()) {
                        $deleted++;
                    }
                }
            });

        return $deleted;
    }
}
