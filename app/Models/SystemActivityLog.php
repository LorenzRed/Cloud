<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemActivityLog extends Model
{
    public $timestamps = false;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['targets' => 'array', 'created_at' => 'datetime'];
    }
}
