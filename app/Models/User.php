<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $full_name
 * @property string $email
 * @property string $role
 * @property string|null $gender
 * @property Carbon|null $date_of_birth
 * @property int|null $age
 * @property string|null $civil_status
 * @property string|null $contact_number
 * @property string|null $barangay
 * @property string|null $household_no
 * @property string|null $municipality
 * @property string|null $province
 * @property string|null $profile_picture_path
 */
class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasFactory, Notifiable;

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
        'gender',
        'date_of_birth',
        'age',
        'civil_status',
        'contact_number',
        'barangay',
        'household_no',
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
}
