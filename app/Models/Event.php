<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'registration_limit',
        'category',
        'image_url',
        'disabled_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'registration_limit' => 'integer',
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
}
