<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Event extends Model
{
    protected $fillable = [
        'barangay_id',
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
    ];

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
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
            ->where('category', 'announcement')
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
     * Permanently delete past announcements to free database space.
     */
    public static function purgeExpired(): int
    {
        $now = now();

        return static::query()
            ->where('category', 'announcement')
            ->where(function (Builder $builder) use ($now) {
                $builder
                    ->whereDate('event_date', '<', $now->toDateString())
                    ->orWhere(function (Builder $sameDay) use ($now) {
                        $sameDay
                            ->whereDate('event_date', $now->toDateString())
                            ->whereNotNull('end_time')
                            ->whereTime('end_time', '<=', $now->format('H:i:s'));
                    });
            })
            ->delete();
    }
}
