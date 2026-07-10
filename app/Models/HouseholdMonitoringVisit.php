<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HouseholdMonitoringVisit extends Model
{
    protected $fillable = [
        'household_monitoring_case_id',
        'recorded_by',
        'visit_date',
        'weight_kg',
        'height_cm',
        'bmi_value',
        'muac_cm',
        'anti_tetanus',
        'blood_pressure',
        'maintenance_medicines',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'weight_kg' => 'decimal:2',
            'height_cm' => 'decimal:2',
            'bmi_value' => 'decimal:1',
            'muac_cm' => 'decimal:1',
        ];
    }

    public function monitoringCase(): BelongsTo
    {
        return $this->belongsTo(HouseholdMonitoringCase::class, 'household_monitoring_case_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function notificationSummary(HouseholdMonitoringCase $case): string
    {
        $parts = [
            $case->typeLabel().' monitoring for '.$case->member_name,
            'Visit '.$this->visit_date?->format('M d, Y'),
        ];

        if ($case->isPregnant()) {
            if ($this->weight_kg !== null) {
                $parts[] = 'Weight '.$this->weight_kg.' kg';
            }

            if ($this->height_cm !== null) {
                $parts[] = 'Height '.$this->height_cm.' cm';
            }

            if ($this->bmi_value !== null) {
                $parts[] = 'BMI '.$this->bmi_value;
            }

            if ($this->muac_cm !== null) {
                $parts[] = 'MUAC '.$this->muac_cm.' cm';
            }

            if (filled($this->anti_tetanus)) {
                $parts[] = 'Anti-tetanus '.$this->anti_tetanus;
            }
        }

        if ($case->isSenior()) {
            if (filled($this->blood_pressure)) {
                $parts[] = 'BP '.$this->blood_pressure;
            }

            if ($this->weight_kg !== null) {
                $parts[] = 'Weight '.$this->weight_kg.' kg';
            }

            if ($this->height_cm !== null) {
                $parts[] = 'Height '.$this->height_cm.' cm';
            }

            if ($this->bmi_value !== null) {
                $parts[] = 'BMI '.$this->bmi_value;
            }

            if (filled($this->maintenance_medicines)) {
                $parts[] = 'Maintenance: '.$this->maintenance_medicines;
            }
        }

        return implode('. ', $parts).'.';
    }
}
