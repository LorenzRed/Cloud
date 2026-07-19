<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $duplicatePurokIds = DB::table('bhw_purok')
            ->select('purok_id')
            ->groupBy('purok_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('purok_id');

        foreach ($duplicatePurokIds as $purokId) {
            $keepId = DB::table('bhw_purok')
                ->where('purok_id', $purokId)
                ->orderBy('id')
                ->value('id');

            $removedUserIds = DB::table('bhw_purok')
                ->where('purok_id', $purokId)
                ->where('id', '!=', $keepId)
                ->pluck('user_id');

            DB::table('bhw_purok')
                ->where('purok_id', $purokId)
                ->where('id', '!=', $keepId)
                ->delete();

            foreach ($removedUserIds as $userId) {
                $primaryPurokId = DB::table('bhw_purok')
                    ->where('user_id', $userId)
                    ->orderBy('purok_id')
                    ->value('purok_id');

                $primaryName = $primaryPurokId
                    ? DB::table('puroks')->where('id', $primaryPurokId)->value('name')
                    : null;

                DB::table('users')
                    ->where('id', $userId)
                    ->where('role', 'BHW')
                    ->update([
                        'purok_id' => $primaryPurokId,
                        'household_no' => $primaryName,
                        'updated_at' => now(),
                    ]);
            }
        }

        Schema::table('bhw_purok', function (Blueprint $table) {
            $table->unique('purok_id');
        });
    }

    public function down(): void
    {
        Schema::table('bhw_purok', function (Blueprint $table) {
            $table->dropUnique(['purok_id']);
        });
    }
};
