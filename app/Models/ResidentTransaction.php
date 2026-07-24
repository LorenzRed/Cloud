<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentTransaction extends Model
{
    public const MEDICATIONS = [
        'amlodipine_pcs' => 'Amlodipine',
        'losartan_pcs' => 'Losartan',
        'metoprolol_pcs' => 'Metoprolol',
        'simvastatin_pcs' => 'Simvastatin',
        'metformin_pcs' => 'Metformin',
        'gliclazide_pcs' => 'Gliclazide',
    ];

    /**
     * @return array<string, array{label: string, condition: string, hint: string, icon: string, tone: string}>
     */
    public static function medicationCatalog(): array
    {
        return [
            'amlodipine_pcs' => [
                'label' => 'Amlodipine',
                'condition' => 'Hypertension',
                'hint' => 'Used for high blood pressure',
                'icon' => 'cardiology',
                'tone' => 'rose',
            ],
            'losartan_pcs' => [
                'label' => 'Losartan',
                'condition' => 'Hypertension',
                'hint' => 'Used for high blood pressure',
                'icon' => 'cardiology',
                'tone' => 'rose',
            ],
            'metoprolol_pcs' => [
                'label' => 'Metoprolol',
                'condition' => 'Hypertension',
                'hint' => 'Used for high blood pressure and heart rate control',
                'icon' => 'monitor_heart',
                'tone' => 'rose',
            ],
            'simvastatin_pcs' => [
                'label' => 'Simvastatin',
                'condition' => 'Hyperlipidemia',
                'hint' => 'Used for high cholesterol',
                'icon' => 'bloodtype',
                'tone' => 'amber',
            ],
            'metformin_pcs' => [
                'label' => 'Metformin',
                'condition' => 'Diabetes',
                'hint' => 'Used for blood sugar control',
                'icon' => 'glucose',
                'tone' => 'sky',
            ],
            'gliclazide_pcs' => [
                'label' => 'Gliclazide',
                'condition' => 'Diabetes',
                'hint' => 'Used for blood sugar control',
                'icon' => 'glucose',
                'tone' => 'sky',
            ],
        ];
    }

    protected $fillable = [
        'resident_request_id',
        'user_id',
        'recorded_by',
        'age',
        'transaction_date',
        'gender',
        'philhealth_number',
        'member_name',
        'member_source',
        'member_index',
        'amlodipine_pcs',
        'losartan_pcs',
        'metoprolol_pcs',
        'simvastatin_pcs',
        'metformin_pcs',
        'gliclazide_pcs',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'transaction_date' => 'date',
            'amlodipine_pcs' => 'integer',
            'losartan_pcs' => 'integer',
            'metoprolol_pcs' => 'integer',
            'simvastatin_pcs' => 'integer',
            'metformin_pcs' => 'integer',
            'gliclazide_pcs' => 'integer',
            'member_index' => 'integer',
        ];
    }

    public function recipientLabel(): string
    {
        $name = trim((string) ($this->member_name ?? ''));

        return $name !== '' ? $name : 'Household member';
    }

    public function familyRoleLabel(): string
    {
        return match (strtolower(trim((string) ($this->member_source ?? '')))) {
            'resident' => 'Resident',
            'spouse' => 'Spouse',
            'household_child', 'child' => 'Child',
            'household_senior', 'senior' => 'Senior',
            default => filled($this->member_source) ? ucwords(str_replace('_', ' ', (string) $this->member_source)) : 'Family member',
        };
    }

    public function residentRequest(): BelongsTo
    {
        return $this->belongsTo(ResidentRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    /**
     * @return array<int, array{label: string, pcs: int}>
     */
    public function medicationEntries(): array
    {
        $entries = [];

        foreach (self::MEDICATIONS as $field => $label) {
            $pcs = $this->{$field};

            if ($pcs !== null && $pcs > 0) {
                $catalog = self::medicationCatalog()[$field] ?? [];
                $entries[] = [
                    'label' => $label,
                    'pcs' => $pcs,
                    'condition' => $catalog['condition'] ?? null,
                    'hint' => $catalog['hint'] ?? null,
                ];
            }
        }

        return $entries;
    }

    /**
     * @return array<int, string>
     */
    public function inferredConditions(): array
    {
        return collect($this->medicationEntries())
            ->pluck('condition')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function notificationSummary(): string
    {
        return 'Check the info in Transactions.';
    }
}
