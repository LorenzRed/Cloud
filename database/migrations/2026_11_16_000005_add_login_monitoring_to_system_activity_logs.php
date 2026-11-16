<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('system_activity_logs', function (Blueprint $table) {
            $table->string('event_type', 30)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('attempted_email')->nullable();
            $table->string('user_agent')->nullable();
        });
        DB::table('system_activity_logs')->where('outcome', 'error')->update(['event_type' => 'system_error']);
        DB::table('system_activity_logs')->whereIn('route_name', ['login.perform', 'api/auth/login'])
            ->where('outcome', 'completed')->update(['event_type' => 'login_success', 'action' => 'Login successful']);
        DB::table('system_activity_logs')->whereIn('route_name', ['login.perform', 'api/auth/login'])
            ->where('outcome', 'rejected')->update(['event_type' => 'login_failed', 'action' => 'Login unsuccessful']);
    }

    public function down(): void
    {
        Schema::table('system_activity_logs', fn (Blueprint $table) => $table->dropColumn(['event_type', 'ip_address', 'attempted_email', 'user_agent']));
    }
};
