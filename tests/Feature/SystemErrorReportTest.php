<?php

namespace Tests\Feature;

use App\Models\SystemError;
use App\Models\User;
use App\Support\SystemErrorRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SystemErrorReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_records_safe_causes_and_avoids_duplicate_entries(): void
    {
        $request = \Illuminate\Http\Request::create('/example', 'POST', ['password' => 'PrivatePassword']);
        SystemErrorRecorder::record(new \TypeError('PrivatePassword patient data'), $request);
        SystemErrorRecorder::record(new \TypeError('Duplicate'), $request);
        $this->assertDatabaseCount('system_errors', 1);
        $error = SystemError::firstOrFail();
        $this->assertSame('Application', $error->category);
        $this->assertStringNotContainsString('PrivatePassword', $error->toJson());
        $this->assertStringNotContainsString('patient data', $error->toJson());
        SystemErrorRecorder::record(new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException(), \Illuminate\Http\Request::create('/other'));
        $this->assertDatabaseCount('system_errors', 1);
    }

    public function test_report_summarizes_selected_dates_and_print_includes_all_rows(): void
    {
        $lgu = User::factory()->create(['role' => User::ROLE_LGU]);
        foreach (range(1, 27) as $index) {
            SystemError::create(['category' => 'Database', 'cause' => 'Database unavailable', 'suggested_action' => 'Check database',
                'route_name' => 'bhw.residents.profile', 'actor_name' => 'Worker', 'user_id' => $lgu->id, 'status_code' => 500,
                'created_at' => '2026-11-15 10:00:00']);
        }
        $this->actingAs($lgu)->get(route('lgu.reports', ['from' => '2026-11-15', 'to' => '2026-11-15']))
            ->assertOk()->assertViewHas('total', 27)->assertViewHas('affectedUsers', 1)->assertSee('Generated Summary');
        $this->get(route('lgu.reports', ['from' => '2026-11-15', 'to' => '2026-11-15', 'print' => 1]))
            ->assertOk()->assertViewHas('incidents', fn ($rows) => $rows->count() === 27);
        $this->get(route('lgu.reports', ['from' => '2026-11-16', 'to' => '2026-11-16']))
            ->assertOk()->assertViewHas('total', 0);
        $this->get(route('lgu.reports', ['from' => '2026-11-16', 'to' => '2026-11-01']))->assertSessionHasErrors('to');
    }

    public function test_server_error_responses_are_recorded_and_report_is_lgu_only(): void
    {
        Route::middleware(['web'])->get('/test-system-error', fn () => response('Service unavailable', 503))->name('test.system-error');
        $this->get('/test-system-error')->assertStatus(503);
        $this->assertDatabaseHas('system_errors', ['status_code' => 503, 'route_name' => 'test.system-error']);
        $this->actingAs(User::factory()->create(['role' => User::ROLE_BHW]))->get(route('lgu.reports'))->assertForbidden();
    }

    public function test_reported_exception_is_saved_with_cause_once(): void
    {
        Route::middleware('web')->get('/test-thrown-error', function () {
            throw new \TypeError('Sensitive patient information');
        })->name('test.thrown-error');
        $this->get('/test-thrown-error')->assertStatus(500);
        $this->assertDatabaseCount('system_errors', 1);
        $this->assertDatabaseHas('system_errors', ['category' => 'Application', 'route_name' => 'test.thrown-error']);
    }
}
