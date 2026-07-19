<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property string $name
 * @property string|null $full_name
 * @property string $email
 * @property string $role
 * @property int|null $managed_by_user_id
 * @property string|null $gender
 * @property string|null $parent_role
 * @property Carbon|null $date_of_birth
 * @property int|null $age
 * @property string|null $civil_status
 * @property string|null $educational_attainment
 * @property string|null $spouse_full_name
 * @property array<string, mixed>|null $spouse_details
 * @property string|null $place_of_birth
 * @property string|null $contact_number
 * @property string|null $barangay
 * @property int|null $purok_id
 * @property string|null $household_no
 * @property string|null $municipality
 * @property string|null $province
 * @property string|null $profile_picture_path
 */
class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasApiTokens, HasFactory, Notifiable;

    public const ROLE_USERS = 'users';

    public const ROLE_LGU = 'LGU';

    public const ROLE_BRG_CAPTAIN = 'BrgCaptain';

    public const ROLE_BNS = 'BNS';

    public const ROLE_BHW = 'BHW';

    public const ROLE_BHW_HEAD = 'BHWHead';

    public const CIVIL_STATUSES = [
        'Single',
        'Married',
        'Widowed',
        'Separated',
        'Divorced',
        'Annulled',
    ];

    /** @var list<string> Civil statuses that show the spouse section. */
    public const SPOUSE_SECTION_STATUSES = [
        'Married',
        'Separated',
    ];

    /** @var list<string> Civil statuses that require complete spouse details. */
    public const REQUIRED_SPOUSE_STATUSES = [
        'Married',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'full_name',
        'email',
        'role',
        'managed_by_user_id',
        'gender',
        'parent_role',
        'date_of_birth',
        'age',
        'civil_status',
        'educational_attainment',
        'spouse_full_name',
        'spouse_details',
        'place_of_birth',
        'contact_number',
        'barangay',
        'purok_id',
        'household_no',
        'family_number',
        'municipality',
        'province',
        'profile_picture_path',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'date_of_birth' => 'date',
            'password' => 'hashed',
            'spouse_details' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->date_of_birth) {
                $user->age = Carbon::parse($user->date_of_birth)->age;
            } else {
                $user->age = null;
            }
        });
    }

    public function residentRequest(): HasOne
    {
        return $this->hasOne(ResidentRequest::class);
    }

    public function ledBarangay(): HasOne
    {
        return $this->hasOne(Barangay::class, 'captain_user_id');
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    public function managedBy(): BelongsTo
    {
        return $this->belongsTo(self::class, 'managed_by_user_id');
    }

    public function managedBhwWorkers(): HasMany
    {
        return $this->hasMany(self::class, 'managed_by_user_id')
            ->where('role', self::ROLE_BHW);
    }

    public function isBhwHead(): bool
    {
        return $this->role === self::ROLE_BHW_HEAD;
    }

    public function isBhw(): bool
    {
        return $this->role === self::ROLE_BHW;
    }

    public function assignedPuroks(): BelongsToMany
    {
        return $this->belongsToMany(Purok::class, 'bhw_purok')->withTimestamps();
    }

    /**
     * Purok IDs already assigned to a different BHW.
     *
     * @param  list<int>  $purokIds
     * @return list<int>
     */
    public static function purokIdsTakenByOtherBhws(array $purokIds, int $exceptUserId): array
    {
        if ($purokIds === []) {
            return [];
        }

        return static::query()
            ->where('role', self::ROLE_BHW)
            ->whereKeyNot($exceptUserId)
            ->whereHas('assignedPuroks', fn ($query) => $query->whereIn('puroks.id', $purokIds))
            ->with(['assignedPuroks' => fn ($query) => $query->whereIn('puroks.id', $purokIds)])
            ->get()
            ->flatMap(fn (self $worker) => $worker->assignedPuroks->pluck('id'))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $purokIds
     */
    public function syncBhwPurokAssignments(array $purokIds, ?string $barangayName = null): void
    {
        $taken = self::purokIdsTakenByOtherBhws($purokIds, (int) $this->id);

        if ($taken !== []) {
            $names = Purok::query()
                ->whereIn('id', $taken)
                ->orderBy('name')
                ->pluck('name')
                ->implode(', ');

            throw \Illuminate\Validation\ValidationException::withMessages([
                'purok_ids' => 'Each purok can only have one BHW. Already assigned: '.$names.'.',
            ]);
        }

        $this->assignedPuroks()->sync($purokIds);

        $update = [];

        if ($barangayName !== null) {
            $update['barangay'] = $barangayName;
        }

        $primary = $this->assignedPuroks()->orderBy('puroks.name')->first();

        $update['purok_id'] = $primary?->id;
        $update['household_no'] = $primary?->name;

        $this->update($update);
    }

    public function refreshBhwPrimaryPurok(): void
    {
        if ($this->role !== 'BHW') {
            return;
        }

        $primary = $this->assignedPuroks()->orderBy('puroks.name')->first();

        $this->update([
            'purok_id' => $primary?->id,
            'household_no' => $primary?->name,
        ]);
    }

    public function householdPurokLabel(): ?string
    {
        $household = trim((string) ($this->household_no ?? ''));

        if ($household !== '') {
            return $household;
        }

        $purokName = trim((string) ($this->purok?->name ?? ''));

        return $purokName !== '' ? $purokName : null;
    }

    public function syncHouseholdNoFromPurok(): void
    {
        if (! $this->purok_id) {
            $residentRequest = $this->relationLoaded('residentRequest')
                ? $this->residentRequest
                : $this->residentRequest()->with('purok')->first();

            if ($residentRequest?->purok_id) {
                $this->forceFill([
                    'purok_id' => $residentRequest->purok_id,
                    'barangay' => $this->barangay ?: $residentRequest->barangay,
                    'household_no' => $residentRequest->purok?->name,
                ])->saveQuietly();

                $this->load('purok');
            }

            return;
        }

        if (filled($this->household_no)) {
            return;
        }

        $purokName = trim((string) ($this->purok?->name ?? ''));

        if ($purokName === '') {
            return;
        }

        $this->forceFill(['household_no' => $purokName])->saveQuietly();
    }
}
