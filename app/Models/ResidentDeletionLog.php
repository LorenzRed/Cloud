<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResidentDeletionLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['changes' => 'encrypted:array', 'deleted_at' => 'datetime', 'restored_at' => 'datetime'];
    }
}
