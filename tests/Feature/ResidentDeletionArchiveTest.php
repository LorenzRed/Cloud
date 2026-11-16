<?php

namespace Tests\Feature;

use App\Models\ResidentDeletionLog;
use App\Models\ResidentRequest;
use App\Models\User;
use App\Support\BhwHouseholdEditor;
use App\Support\ResidentDeletionArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ResidentDeletionArchiveTest extends TestCase
{
    use RefreshDatabase;

    private function household(): ResidentRequest
    {
        $user = User::factory()->create();
        return ResidentRequest::create([
            'user_id' => $user->id, 'full_name' => 'Family Holder', 'status' => 'accepted',
            'age' => 30, 'contact' => '09123456789', 'barangay' => 'Test Barangay',
            'household_children' => [['full_name' => 'Child One', 'birthdate' => '2020-01-01']],
        ]);
    }

    public function test_deleted_child_is_logged_and_lgu_can_restore_it_once(): void
    {
        $resident = $this->household();
        $bhw = User::factory()->create(['role' => User::ROLE_BHW]);
        ResidentDeletionArchive::capture($resident, $bhw, 'remove_child', ['member_index' => 0],
            fn () => BhwHouseholdEditor::apply($resident, 'remove_child', ['member_index' => 0]));

        $this->assertNull($resident->fresh()->household_children);
        $log = ResidentDeletionLog::firstOrFail();
        $this->assertSame('Child One', $log->member_name);
        $this->assertSame($bhw->id, $log->deleted_by);
        $lgu = User::factory()->create(['role' => User::ROLE_LGU]);
        $this->actingAs($lgu)->get(route('lgu.deletion-log'))->assertOk()->assertSee('Child One');
        $this->post(route('lgu.deletion-log.restore', $log))->assertRedirect(route('lgu.deletion-log'));
        $this->assertSame('Child One', $resident->fresh()->household_children[0]['full_name']);
        $this->assertSame($lgu->id, $log->fresh()->restored_by);
        $this->expectException(ValidationException::class);
        ResidentDeletionArchive::restore($log, $lgu);
    }

    public function test_whole_account_is_retained_and_restored_with_its_original_ids(): void
    {
        $resident = $this->household();
        $userId = $resident->user_id;
        ResidentDeletionArchive::capture($resident, User::factory()->create(['role' => User::ROLE_BHW]), 'remove_resident', [], function () use ($resident) {
            $resident->user->delete();
            $resident->delete();
        });
        $this->assertSoftDeleted('resident_requests', ['id' => $resident->id]);
        $this->assertSoftDeleted('users', ['id' => $userId]);
        ResidentDeletionArchive::restore(ResidentDeletionLog::firstOrFail(), User::factory()->create(['role' => User::ROLE_LGU]));
        $this->assertNotNull(ResidentRequest::find($resident->id));
        $this->assertNotNull(User::find($userId));
    }

    public function test_restore_does_not_overwrite_later_household_edits(): void
    {
        $resident = $this->household();
        ResidentDeletionArchive::capture($resident, User::factory()->create(['role' => User::ROLE_BHW]), 'remove_child', ['member_index' => 0],
            fn () => BhwHouseholdEditor::apply($resident, 'remove_child', ['member_index' => 0]));
        $resident->update(['household_children' => [['full_name' => 'New Child']]]);
        $this->expectException(ValidationException::class);
        ResidentDeletionArchive::restore(ResidentDeletionLog::firstOrFail(), User::factory()->create(['role' => User::ROLE_LGU]));
    }

    public function test_bhw_cannot_access_lgu_deletion_log_or_restore(): void
    {
        $bhw = User::factory()->create(['role' => User::ROLE_BHW]);
        $resident = $this->household();
        ResidentDeletionArchive::capture($resident, $bhw, 'remove_child', ['member_index' => 0],
            fn () => BhwHouseholdEditor::apply($resident, 'remove_child', ['member_index' => 0]));
        $this->actingAs($bhw)->get(route('lgu.deletion-log'))->assertForbidden();
        $this->post(route('lgu.deletion-log.restore', ResidentDeletionLog::firstOrFail()))->assertForbidden();
    }

    public function test_holder_spouse_detection_tracks_removal_and_restore_and_user_fallback(): void
    {
        $resident = $this->household();
        $resident->update(['spouse_full_name' => 'Wife Name', 'spouse_details' => ['full_name' => 'Wife Name']]);
        $resident->user->update(['spouse_full_name' => 'Wife Name']);
        $this->assertSame('Wife Name', $resident->holderSpouseName());

        ResidentDeletionArchive::capture($resident, User::factory()->create(['role' => User::ROLE_BHW]), 'remove_spouse', [],
            fn () => BhwHouseholdEditor::apply($resident, 'remove_spouse', []));
        $this->assertSame('', $resident->fresh()->holderSpouseName());
        ResidentDeletionArchive::restore(ResidentDeletionLog::firstOrFail(), User::factory()->create(['role' => User::ROLE_LGU]));
        $this->assertSame('Wife Name', $resident->fresh()->holderSpouseName());

        $resident->update(['spouse_full_name' => null, 'spouse_details' => null]);
        $this->assertSame('Wife Name', $resident->fresh()->holderSpouseName());
    }
}
