<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bns_child_weight_logs', function (Blueprint $table) {
            $table->decimal('muac_cm', 4, 1)->nullable()->after('height_cm');
        });
    }

    public function down(): void
    {
        Schema::table('bns_child_weight_logs', function (Blueprint $table) {
            $table->dropColumn('muac_cm');
        });
    }
};
