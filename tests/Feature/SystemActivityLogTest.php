<?php

namespace Tests\Feature;

use App\Models\ResidentDeletionLog;
use App\Models\ResidentRequest;
use App\Models\SystemActivityLog;
use App\Models\User;
use App\Support\BhwHouseholdEditor;
use App\Support\ResidentDeletionArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_lgu_sees_latest_activity_first_and_live_refresh_does_not_log_itself(): void
    {
        $lgu = User::factory()->create(['role' => User::ROLE_LGU]);
        foreach (['Earlier action', 'Latest action'] as $action) {
            SystemActivityLog::create(['user_id' => $lgu->id, 'actor_name' => 'Test Actor', 'actor_role' => 'LGU',
                'action' => $action, 'method' => 'POST', 'status_code' => 302, 'outcome' => 'completed', 'created_at' => now()]);
        }
        $this->actingAs($lgu)->get(route('lgu.system-logs'))->assertOk()
            ->assertSeeInOrder(['Latest action', 'Earlier action']);
        $this->getJson(route('lgu.system-logs'))->assertOk()->assertJsonStructure(['html', 'pagination', 'checked_at']);
        $this->assertDatabaseCount('system_activity_logs', 2);
        $this->get(route('lgu.system-logs', ['search' => 'Latest']))->assertOk()->assertSee('Latest action')->assertDontSee('Earlier action');
    }

    public function test_non_lgu_access_is_denied_and_recorded(): void
    {
        $bhw = User::factory()->create(['role' => User::ROLE_BHW]);
        $this->actingAs($bhw)->get(route('lgu.barangays'))->assertForbidden();
        $this->assertDatabaseHas('system_activity_logs', [
            'user_id' => $bhw->id, 'route_name' => 'lgu.barangays', 'outcome' => 'rejected', 'status_code' => 403,
        ]);
        $this->get(route('lgu.system-logs'))->assertForbidden();
    }

    public function test_restore_activity_records_actor_and_record_id_without_request_secrets(): void
    {
        $residentUser = User::factory()->create();
        $resident = ResidentRequest::create(['user_id' => $residentUser->id, 'full_name' => 'Holder',
            'age' => 30, 'contact' => '09123456789', 'barangay' => 'Test', 'status' => 'accepted',
            'household_children' => [['full_name' => 'Child']]]);
        ResidentDeletionArchive::capture($resident, User::factory()->create(['role' => User::ROLE_BHW]), 'remove_child', ['member_index' => 0],
            fn () => BhwHouseholdEditor::apply($resident, 'remove_child', ['member_index' => 0]));
        $log = ResidentDeletionLog::firstOrFail();
        $lgu = User::factory()->create(['role' => User::ROLE_LGU]);
        $this->actingAs($lgu)->post(route('lgu.deletion-log.restore', $log), ['password' => 'NeverLogThisPassword', 'message' => 'PrivateMedicalMessage'])
            ->assertRedirect(route('lgu.deletion-log'));
        $activity = SystemActivityLog::firstOrFail();
        $this->assertSame($lgu->id, $activity->user_id);
        $this->assertSame('completed', $activity->outcome);
        $this->assertSame(['log' => (string) $log->id], $activity->targets);
        $this->assertStringNotContainsString('NeverLogThisPassword', $activity->toJson());
        $this->assertStringNotContainsString('PrivateMedicalMessage', $activity->toJson());
        $this->get(route('lgu.system-logs', ['outcome' => 'rejected']))->assertOk()->assertDontSee('Lgu Deletion Log Restore');
    }
}
