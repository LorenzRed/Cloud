<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Purok extends Model
{
    protected $fillable = [
        'barangay_id',
        'name',
        'created_by',
        'boundary_geojson',
    ];

    protected $casts = [
        'boundary_geojson' => 'array',
    ];

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function bhwWorkers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bhw_purok')
            ->where('role', 'BHW')
            ->withTimestamps();
    }
}
