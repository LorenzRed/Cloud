<?php

namespace App\Models;

use App\Support\BhwResidentScope;
use App\Support\PrivateStorage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $full_name
 * @property string|null $parent_role
 * @property string $barangay
 * @property int|null $purok_id
 * @property string|null $contact
 * @property string|null $address
 * @property string|null $family_number
 * @property int|null $age
 * @property string|null $place_of_birth
 * @property string|null $civil_status
 * @property string|null $educational_attainment
 * @property string|null $spouse_full_name
 * @property array{
 *     first_name?: string,
 *     last_name?: string,
 *     middle_initial?: string|null,
 *     full_name?: string,
 *     birthdate?: string,
 *     parent_role?: string,
 *     gender?: string,
 *     place_of_birth?: string,
 *     educational_attainment?: string
 * }|null $spouse_details
 * @property array<int, array{
 *     full_name: string,
 *     birthdate: string,
 *     gender: string,
 *     relationship: string,
 *     currently_studying: string,
 *     school?: string|null,
 *     educational_attainment: string,
 *     occupation: string
 * }>|null $household_children
 * @property array<int, array{
 *     full_name: string,
 *     birthdate: string,
 *     age_in_years?: int,
 *     gender: string,
 *     relationship: string,
 *     place_of_birth: string,
 *     educational_attainment: string,
 *     occupation?: string|null
 * }>|null $household_seniors
 * @property string $status
 * @property string|null $id_type
 * @property string|null $id_other_label
 * @property string|null $id_front_path
 * @property string|null $id_back_path
 * @property \Illuminate\Support\Carbon|null $reviewed_at
 */
class ResidentRequest extends Model
{
    use HasFactory, \Illuminate\Database\Eloquent\SoftDeletes;

    public const ID_TYPE_NATIONAL_ID = 'national_id';

    public const ID_TYPE_PHILHEALTH_ID = 'philhealth_id';

    public const ID_TYPE_DRIVERS_LICENSE = 'drivers_license';

    public const ID_TYPE_OTHER = 'other';

    /** @var array<string, string> */
    public const ID_TYPES = [
        self::ID_TYPE_NATIONAL_ID => 'National ID',
        self::ID_TYPE_PHILHEALTH_ID => 'PhilHealth ID',
        self::ID_TYPE_DRIVERS_LICENSE => "Driver's License",
        self::ID_TYPE_OTHER => 'Other',
    ];

    public const PARENT_ROLE_SELF = 'Self';

    public const PARENT_ROLE_FATHER = 'Father';

    public const PARENT_ROLE_MOTHER = 'Mother';

    public const PARENT_ROLE_GRANDFATHER = 'Grandfather';

    public const PARENT_ROLE_GRANDMOTHER = 'Grandmother';

    /** @var list<string> */
    public const PARENT_ROLES = [
        self::PARENT_ROLE_SELF,
        self::PARENT_ROLE_FATHER,
        self::PARENT_ROLE_MOTHER,
        self::PARENT_ROLE_GRANDFATHER,
        self::PARENT_ROLE_GRANDMOTHER,
    ];

    /**
     * Spouse guardian roles (Self is only for the registrant).
     *
     * @var list<string>
     */
    public const SPOUSE_PARENT_ROLES = [
        self::PARENT_ROLE_FATHER,
        self::PARENT_ROLE_MOTHER,
        self::PARENT_ROLE_GRANDFATHER,
        self::PARENT_ROLE_GRANDMOTHER,
    ];

    public static function genderFromParentRole(?string $role): ?string
    {
        return match (trim((string) $role)) {
            self::PARENT_ROLE_FATHER, self::PARENT_ROLE_GRANDFATHER => 'Male',
            self::PARENT_ROLE_MOTHER, self::PARENT_ROLE_GRANDMOTHER => 'Female',
            default => null,
        };
    }

    public const CHILD_RELATIONSHIP_SON = 'Son';

    public const CHILD_RELATIONSHIP_DAUGHTER = 'Daughter';

    public const CHILD_RELATIONSHIP_ADOPTED = 'Adopted';

    public const CHILD_RELATIONSHIP_GRANDSON = 'Grandson';

    public const CHILD_RELATIONSHIP_GRANDDAUGHTER = 'Granddaughter';

    public const CHILD_RELATIONSHIP_RELATIVE = 'Relative';

    public const SENIOR_RELATIONSHIP_GRANDFATHER = 'Grandfather';

    public const SENIOR_RELATIONSHIP_GRANDMOTHER = 'Grandmother';

    /** @var list<string> */
    public const CHILD_RELATIONSHIPS = [
        self::CHILD_RELATIONSHIP_SON,
        self::CHILD_RELATIONSHIP_DAUGHTER,
        self::CHILD_RELATIONSHIP_ADOPTED,
        self::CHILD_RELATIONSHIP_GRANDSON,
        self::CHILD_RELATIONSHIP_GRANDDAUGHTER,
        self::CHILD_RELATIONSHIP_RELATIVE,
    ];

    /**
     * Other household members (not guardian, spouse, or children).
     *
     * @var list<string>
     */
    public const HOUSEHOLD_SENIOR_RELATIONSHIPS = [
        self::PARENT_ROLE_FATHER,
        self::PARENT_ROLE_MOTHER,
        'Brother',
        'Sister',
        self::SENIOR_RELATIONSHIP_GRANDFATHER,
        self::SENIOR_RELATIONSHIP_GRANDMOTHER,
        'Uncle',
        'Aunt',
        'Cousin',
        'Nephew',
        'Niece',
        'Father-in-law',
        'Mother-in-law',
        'Brother-in-law',
        'Sister-in-law',
    ];

    /** @var list<string> */
    public const EDUCATIONAL_ATTAINMENTS = [
        'No Formal Education',
        'Elementary Level',
        'Elementary Graduate',
        'High School Level',
        'High School Graduate',
        'Senior High School',
        'Vocational',
        'College Level',
        'College Graduate',
        'Post Graduate',
    ];

    /** @var array<string, string> */
    private const LEGACY_ID_TYPE_LABELS = [
        'student_id' => 'Student ID',
    ];

    protected $fillable = [
        'user_id',
        'full_name',
        'parent_role',
        'barangay',
        'purok_id',
        'contact',
        'address',
        'family_number',
        'age',
        'place_of_birth',
        'years_of_residency',
        'civil_status',
        'educational_attainment',
        'spouse_full_name',
        'spouse_details',
        'household_children',
        'household_seniors',
        'status',
        'id_type',
        'id_other_label',
        'id_front_path',
        'id_back_path',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
            'household_children' => 'array',
            'household_seniors' => 'array',
            'spouse_details' => 'array',
            'years_of_residency' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function holderSpouseDetails(): array
    {
        $details = $this->spouse_details;
        if (! is_array($details) || $details === []) {
            $details = $this->user?->spouse_details;
        }
        return is_array($details) ? $details : [];
    }

    public function holderSpouseName(): string
    {
        foreach ([$this->spouse_full_name, $this->user?->spouse_full_name, $this->holderSpouseDetails()['full_name'] ?? null] as $name) {
            if (trim((string) $name) !== '') {
                return trim((string) $name);
            }
        }
        return '';
    }

    public function purok(): BelongsTo
    {
        return $this->belongsTo(Purok::class);
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(ResidentTransaction::class);
    }

    public function monitoringCases(): HasMany
    {
        return $this->hasMany(HouseholdMonitoringCase::class);
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    public function scopeForBhw(Builder $query, User $bhw): Builder
    {
        return BhwResidentScope::apply($query, $bhw);
    }

    public function isAccessibleByBhw(User $bhw): bool
    {
        return BhwResidentScope::canAccess($bhw, $this);
    }

    /**
     * @param  array<string, mixed>  $member
     */
    public static function memberIsElderly(array $member): bool
    {
        if (array_key_exists('is_elderly', $member)) {
            return (bool) $member['is_elderly'];
        }

        $age = isset($member['age_in_years']) ? (int) $member['age_in_years'] : null;
        if ($age === null || $age < 0) {
            $age = \App\Support\PersonAge::ageInYears(
                isset($member['birthdate']) ? (string) $member['birthdate'] : null
            );
        }

        return $age !== null && $age >= 60;
    }

    /**
     * Other-family entries entered by the resident (excludes auto-saved guardian/spouse elderly rows).
     *
     * @return list<array<string, mixed>>
     */
    public function otherFamilyMembers(): array
    {
        $rows = [];
        foreach (is_array($this->household_seniors) ? $this->household_seniors : [] as $member) {
            if (! is_array($member)) {
                continue;
            }

            $source = trim((string) ($member['source'] ?? 'other'));
            if (in_array($source, ['guardian', 'spouse'], true)) {
                continue;
            }

            $name = trim((string) ($member['full_name'] ?? $member['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $rows[] = $member;
        }

        return $rows;
    }

    /**
     * Delete verification ID files from accepted/rejected requests after 7 days.
     * Pending requests keep their IDs until reviewed.
     */
    public static function purgeExpiredVerificationIds(): int
    {
        $cutoff = now()->subDays(7);
        $purged = 0;

        $requests = static::query()
            ->whereIn('status', ['accepted', 'rejected'])
            ->where(function ($query) {
                $query->whereNotNull('id_front_path')
                    ->orWhereNotNull('id_back_path');
            })
            ->where(function ($query) use ($cutoff) {
                $query->where(function ($reviewed) use ($cutoff) {
                    $reviewed->whereNotNull('reviewed_at')
                        ->where('reviewed_at', '<=', $cutoff);
                })->orWhere(function ($fallback) use ($cutoff) {
                    $fallback->whereNull('reviewed_at')
                        ->where('updated_at', '<=', $cutoff);
                });
            })
            ->get();

        foreach ($requests as $request) {
            PrivateStorage::delete($request->id_front_path);
            PrivateStorage::delete($request->id_back_path);

            $request->forceFill([
                'id_front_path' => null,
                'id_back_path' => null,
            ])->saveQuietly();

            $purged++;
        }

        return $purged;
    }

    public function idTypeLabel(): string
    {
        if ($this->id_type === self::ID_TYPE_OTHER) {
            $custom = trim((string) ($this->id_other_label ?? ''));

            return $custom !== '' ? $custom : self::ID_TYPES[self::ID_TYPE_OTHER];
        }

        return self::ID_TYPES[$this->id_type]
            ?? self::LEGACY_ID_TYPE_LABELS[$this->id_type]
            ?? 'Valid ID';
    }
}
