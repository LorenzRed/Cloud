<?php

namespace Tests\Feature;

use App\Models\ResidentDeletionLog;
use App\Models\ResidentRequest;
use App\Models\SystemActivityLog;
use App\Models\SystemError;
use App\Models\User;
use App\Support\BhwHouseholdEditor;
use App\Support\ResidentDeletionArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private function activity(array $attributes = []): SystemActivityLog
    {
        return SystemActivityLog::create($attributes + ['actor_name' => 'Test Actor', 'actor_role' => 'LGU',
            'action' => 'Login successful', 'event_type' => 'login_success', 'method' => 'POST',
            'status_code' => 302, 'outcome' => 'completed', 'created_at' => now()]);
    }

    public function test_lgu_sees_latest_login_and_error_events_without_transactions_or_polling(): void
    {
        $lgu = User::factory()->create(['role' => User::ROLE_LGU]);
        $this->activity(['action' => 'Earlier login']);
        $this->activity(['action' => 'Latest login']);
        $this->activity(['event_type' => null, 'action' => 'Old transaction event']);
        $this->activity(['event_type' => 'system_error', 'action' => 'Application error', 'outcome' => 'error']);
        $this->actingAs($lgu)->get(route('lgu.system-logs'))->assertOk()
            ->assertSeeInOrder(['Application error', 'Latest login', 'Earlier login'])
            ->assertDontSee('Old transaction event')->assertDontSee('setInterval', false)->assertSee('30 days');
        $this->getJson(route('lgu.system-logs'))->assertOk()->assertJsonStructure(['html', 'pagination']);
        $this->assertDatabaseCount('system_activity_logs', 4);
        $this->get(route('lgu.system-logs', ['search' => 'Latest']))->assertOk()->assertSee('Latest login')->assertDontSee('Earlier login');
        $this->get(route('lgu.system-logs', ['outcome' => 'error']))->assertOk()->assertSee('Application error')->assertDontSee('Latest login');
    }

    public function test_non_lgu_access_is_denied_without_logging_routine_requests(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_BHW]))
            ->get(route('lgu.barangays'))->assertForbidden();
        $this->get(route('lgu.system-logs'))->assertForbidden();
        $this->assertDatabaseCount('system_activity_logs', 0);
    }

    public function test_successful_login_records_who_ip_and_device_without_password(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_LGU, 'email' => 'admin@example.com', 'password' => 'ValidPassword123!']);
        $this->withHeaders(['User-Agent' => 'Test Browser'])->post(route('login.perform'), [
            'email' => $user->email, 'password' => 'ValidPassword123!',
        ])->assertRedirect(route('dashboard'));
        $log = SystemActivityLog::firstOrFail();
        $this->assertSame('login_success', $log->event_type);
        $this->assertSame($user->id, $log->user_id);
        $this->assertSame('127.0.0.1', $log->ip_address);
        $this->assertSame('Test Browser', $log->user_agent);
        $this->assertSame($user->email, $log->attempted_email);
        $raw = DB::table('system_activity_logs')->first();
        $this->assertStringNotContainsString($user->email, $raw->attempted_email);
        $this->assertStringNotContainsString('ValidPassword123!', json_encode($raw));
        $this->get(route('lgu.main'))->assertOk();
        $this->assertDatabaseCount('system_activity_logs', 1);
    }

    public function test_unsuccessful_login_is_logged_without_attributing_it_to_the_account_owner(): void
    {
        $user = User::factory()->create(['email' => 'resident@example.com', 'password' => 'RealPassword123!']);
        $this->post(route('login.perform'), ['email' => $user->email, 'password' => 'IncorrectPassword'])
            ->assertSessionHasErrors('email');
        $log = SystemActivityLog::firstOrFail();
        $this->assertSame('login_failed', $log->event_type);
        $this->assertSame('rejected', $log->outcome);
        $this->assertNull($log->user_id);
        $this->assertSame($user->email, $log->attempted_email);
        $this->assertStringNotContainsString('IncorrectPassword', $log->toJson());
    }

    public function test_api_logins_are_monitored(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USERS, 'password' => 'RealPassword123!']);
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'RealPassword123!'])->assertOk();
        $this->assertDatabaseHas('system_activity_logs', ['event_type' => 'login_success', 'user_id' => $user->id]);
    }

    public function test_restore_is_not_logged_as_a_system_event(): void
    {
        $residentUser = User::factory()->create();
        $resident = ResidentRequest::create(['user_id' => $residentUser->id, 'full_name' => 'Holder',
            'age' => 30, 'contact' => '09123456789', 'barangay' => 'Test', 'status' => 'accepted',
            'household_children' => [['full_name' => 'Child']]]);
        ResidentDeletionArchive::capture($resident, User::factory()->create(['role' => User::ROLE_BHW]), 'remove_child', ['member_index' => 0],
            fn () => BhwHouseholdEditor::apply($resident, 'remove_child', ['member_index' => 0]));
        $this->actingAs(User::factory()->create(['role' => User::ROLE_LGU]))
            ->post(route('lgu.deletion-log.restore', ResidentDeletionLog::firstOrFail()))->assertRedirect(route('lgu.deletion-log'));
        $this->assertDatabaseCount('system_activity_logs', 0);
    }

    public function test_error_is_logged_once_and_retention_removes_old_logs_without_deleting_restore_archives(): void
    {
        $this->freezeTime();
        $old = $this->activity(['created_at' => now()->subDays(31)]);
        $recent = $this->activity(['created_at' => now()->subDays(29)]);
        $boundary = $this->activity(['created_at' => now()->subDays(30)]);
        foreach ([31, 29] as $age) {
            SystemError::create(['category' => 'System', 'cause' => 'Test cause', 'suggested_action' => 'Check logs',
                'actor_name' => 'System', 'created_at' => now()->subDays($age)]);
        }
        $archive = ResidentDeletionLog::create(['resident_request_id' => 999, 'resident_name' => 'Resident', 'member_name' => 'Resident',
            'action' => 'remove_resident', 'deleted_by' => 1, 'deleted_by_name' => 'BHW', 'changes' => [], 'deleted_at' => now()->subDays(31)]);
        $this->artisan('security-logs:purge-expired')->assertSuccessful();
        $this->assertNull($old->fresh());
        $this->assertNotNull($recent->fresh());
        $this->assertNotNull($boundary->fresh());
        $this->assertDatabaseCount('system_errors', 1);
        $this->assertNotNull($archive->fresh());

        Route::middleware('web')->get('/test-login-monitor-error', fn () => response('Unavailable', 503))->name('test.monitor-error');
        $this->get('/test-login-monitor-error')->assertStatus(503);
        $this->assertDatabaseHas('system_activity_logs', ['event_type' => 'system_error', 'route_name' => 'test.monitor-error']);
        $this->assertSame(1, SystemActivityLog::where('route_name', 'test.monitor-error')->count());
    }
}
