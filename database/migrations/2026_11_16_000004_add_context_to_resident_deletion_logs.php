<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('resident_deletion_logs', fn (Blueprint $table) => $table->text('context')->nullable());
    }

    public function down(): void
    {
        Schema::table('resident_deletion_logs', fn (Blueprint $table) => $table->dropColumn('context'));
    }
};
