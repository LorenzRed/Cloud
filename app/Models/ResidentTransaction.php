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

    protected $fillable = [
        'resident_request_id',
        'user_id',
        'recorded_by',
        'age',
        'transaction_date',
        'gender',
        'philhealth_number',
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
        ];
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
                $entries[] = [
                    'label' => $label,
                    'pcs' => $pcs,
                ];
            }
        }

        return $entries;
    }

    public function notificationSummary(): string
    {
        $parts = [
            'Age '.$this->age,
            'Date '.$this->transaction_date?->format('M d, Y'),
            'Gender '.$this->gender,
            'PhilHealth '.$this->philhealth_number,
        ];

        $medicationParts = collect($this->medicationEntries())
            ->map(fn (array $entry) => $entry['label'].' '.$entry['pcs'].' pc'.($entry['pcs'] === 1 ? '' : 's'))
            ->all();

        if ($medicationParts !== []) {
            $parts[] = 'Medications: '.implode(', ', $medicationParts);
        }

        return implode('. ', $parts).'.';
    }
}
