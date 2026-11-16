<?php

namespace Tests\Feature;

use App\Models\Barangay;
use App\Models\Purok;
use App\Models\ResidentDeletionLog;
use App\Models\ResidentRequest;
use App\Models\User;
use App\Support\ResidentDeletionArchive;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BhwResidentAccountRetentionTest extends TestCase
{
    use RefreshDatabase;

    public function test_removal_only_removes_purok_membership_and_lgu_can_restore_it(): void
    {
        $barangay = Barangay::create(['name' => 'Test Barangay']);
        $purok = Purok::create(['barangay_id' => $barangay->id, 'name' => 'Purok One']);
        $bhw = User::factory()->create(['role' => User::ROLE_BHW, 'barangay' => $barangay->name, 'purok_id' => $purok->id]);
        $user = User::factory()->create(['role' => User::ROLE_USERS, 'barangay' => $barangay->name,
            'purok_id' => $purok->id, 'household_no' => 'Purok One', 'password' => 'ResidentPass123!']);
        $resident = ResidentRequest::create(['user_id' => $user->id, 'full_name' => 'Resident', 'age' => 30,
            'barangay' => $barangay->name, 'purok_id' => $purok->id, 'status' => 'accepted', 'contact' => '09123456789']);

        $this->actingAs($bhw)->delete(route('bhw.residents.destroy', $resident))->assertRedirect(route('bhw.residents'));
        $this->assertSoftDeleted('resident_requests', ['id' => $resident->id]);
        $this->assertNotNull(User::find($user->id));
        $this->assertNull($user->fresh()->purok_id);
        $this->assertNull($user->fresh()->household_no);

        $this->post(route('logout'));
        $this->post(route('login.perform'), ['email' => $user->email, 'password' => 'ResidentPass123!'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        ResidentDeletionArchive::restore(ResidentDeletionLog::firstOrFail(), User::factory()->create(['role' => User::ROLE_LGU]));
        $this->assertNotNull(ResidentRequest::find($resident->id));
        $this->assertSame($purok->id, $user->fresh()->purok_id);
    }

    public function test_registration_rejects_email_belonging_to_previously_archived_account(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'existing@example.com', 'role' => User::ROLE_USERS]);
        $user->delete();
        $this->post(route('register.email'), ['email' => 'EXISTING@example.com'])
            ->assertSessionHasErrors(['email' => 'This email is already registered. Try logging in instead.']);
        Mail::assertNothingSent();
        $this->assertSame(1, User::withTrashed()->where('email', 'existing@example.com')->count());
    }

    public function test_registration_completion_also_rejects_archived_email(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com', 'role' => User::ROLE_USERS]);
        $user->delete();
        $this->withSession(['register_email' => $user->email, 'register_email_verified' => $user->email])
            ->post('/register/complete', ['email' => $user->email, 'password' => 'ResidentPass123!',
                'password_confirmation' => 'ResidentPass123!', 'terms_accepted' => '1'])
            ->assertRedirect(route('register'))->assertSessionHasErrors('email');
        $this->assertSame(1, User::withTrashed()->where('email', $user->email)->count());
    }

    public function test_otp_verification_rejects_archived_email_with_existing_account_message(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com', 'role' => User::ROLE_USERS]);
        $user->delete();
        $this->withSession(['register_email' => $user->email])
            ->post(route('register.verify'), ['email' => $user->email, 'otp' => '123456'])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email' => 'This email is already registered. Try logging in instead.']);
    }

    public function test_removed_resident_sees_verify_again_on_home_and_prefilled_form(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USERS]);
        $resident = ResidentRequest::create(['user_id' => $user->id, 'full_name' => 'Saved Resident', 'age' => 30,
            'contact' => '09123456789', 'barangay' => 'Test Barangay', 'status' => 'accepted']);
        $resident->delete();
        $this->actingAs($user)->get(route('users.main'))->assertOk()
            ->assertViewHas('verificationStatus', 'removed')->assertSee('Verify again')
            ->assertSee('Verify your residency again');
        $this->get(route('users.verification'))->assertOk()->assertViewHas('isRemovedResubmit', true)
            ->assertViewHas('residentRequest', fn ($row) => $row->id === $resident->id)
            ->assertSee('Update Your Verification');
    }

    public function test_removed_resident_can_resubmit_same_household_for_review_without_duplicate(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $barangay = Barangay::create(['name' => 'Test Barangay']);
        $purok = Purok::create(['barangay_id' => $barangay->id, 'name' => 'Purok One']);
        $user = User::factory()->create(['role' => User::ROLE_USERS]);
        $resident = ResidentRequest::create(['user_id' => $user->id, 'full_name' => 'Old Name', 'age' => 30,
            'contact' => '09123456789', 'barangay' => $barangay->name, 'status' => 'accepted', 'reviewed_at' => now()]);
        $resident->delete();
        $this->actingAs($user)->post(route('users.verification.submit'), [
            'first_name' => 'Updated', 'last_name' => 'Resident', 'location_mode' => 'manual',
            'barangay' => $barangay->name, 'purok' => $purok->name, 'contact' => '09123456789',
            'birthdate_display' => '1995-01-01', 'parent_role' => 'Self', 'gender_display' => 'Female',
            'civil_status' => 'Single', 'place_of_birth' => 'Victoria', 'educational_attainment' => 'College Graduate',
            'privacy_consent' => '1', 'location_consent' => '1', 'id_type' => ResidentRequest::ID_TYPE_NATIONAL_ID,
            'id_photo_front' => UploadedFile::fake()->image('front.jpg'),
            'id_photo_back' => UploadedFile::fake()->image('back.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('users.verification').'#goHomeLink');
        $this->assertSame(1, ResidentRequest::withTrashed()->where('user_id', $user->id)->count());
        $resubmitted = ResidentRequest::findOrFail($resident->id);
        $this->assertSame('pending', $resubmitted->status);
        $this->assertSame('Updated Resident', $resubmitted->full_name);
        $this->assertSame($purok->id, $resubmitted->purok_id);
        $this->assertNull($resubmitted->reviewed_at);
        $this->get(route('users.main'))->assertOk()->assertViewHas('verificationStatus', 'pending')->assertDontSee('Verify again');
    }
}
