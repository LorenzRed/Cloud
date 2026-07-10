<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResidentBhwMessage extends Model
{
    protected $fillable = [
        'resident_user_id',
        'bhw_user_id',
        'sender_id',
        'message',
        'attachment_path',
        'read_at',
        'edited_at',
        'unsent_at',
        'hidden_for_sender_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'edited_at' => 'datetime',
            'unsent_at' => 'datetime',
            'hidden_for_sender_at' => 'datetime',
        ];
    }

    public function resident(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resident_user_id');
    }

    public function bhw(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bhw_user_id');
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(ResidentBhwMessageReaction::class, 'resident_bhw_message_id');
    }
}
