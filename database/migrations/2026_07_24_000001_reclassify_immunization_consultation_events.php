<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('events')
            ->where('category', 'announcement')
            ->where('title', 'like', 'Immunization consultation%')
            ->update(['category' => 'immunization_consultation']);
    }

    public function down(): void
    {
        DB::table('events')
            ->where('category', 'immunization_consultation')
            ->update(['category' => 'announcement']);
    }
};
