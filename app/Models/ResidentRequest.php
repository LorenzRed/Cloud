<?php

namespace App\Models;

use App\Support\BhwResidentScope;
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
    use HasFactory;

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

    public const PARENT_ROLE_FATHER = 'Father';

    public const PARENT_ROLE_MOTHER = 'Mother';

    /** @var list<string> */
    public const PARENT_ROLES = [
        self::PARENT_ROLE_FATHER,
        self::PARENT_ROLE_MOTHER,
    ];

    public const CHILD_RELATIONSHIP_SON = 'Son';

    public const CHILD_RELATIONSHIP_DAUGHTER = 'Daughter';

    public const CHILD_RELATIONSHIP_ADOPTED = 'Adopted';

    public const SENIOR_RELATIONSHIP_GRANDFATHER = 'Grandfather';

    public const SENIOR_RELATIONSHIP_GRANDMOTHER = 'Grandmother';

    /** @var list<string> */
    public const CHILD_RELATIONSHIPS = [
        self::CHILD_RELATIONSHIP_SON,
        self::CHILD_RELATIONSHIP_DAUGHTER,
        self::CHILD_RELATIONSHIP_ADOPTED,
    ];

    /** @var list<string> */
    public const HOUSEHOLD_SENIOR_RELATIONSHIPS = [
        self::SENIOR_RELATIONSHIP_GRANDFATHER,
        self::SENIOR_RELATIONSHIP_GRANDMOTHER,
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
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
