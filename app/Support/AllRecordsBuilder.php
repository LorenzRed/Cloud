<?php

namespace App\Support;

use App\Models\Barangay;
use App\Models\HouseholdMonitoringVisit;
use App\Models\Purok;
use App\Models\ResidentRequest;
use App\Models\ResidentTransaction;
use App\Models\User;
use Illuminate\Support\Collection;

class AllRecordsBuilder
{
    /**
     * @param  Collection<int, int>|list<int>  $residentRequestIds
     * @return array{
     *     records: Collection<int, array<string, mixed>>,
     *     transactionCount: int,
     *     maintenanceCount: int
     * }
     */
    public static function build(
        iterable $residentRequestIds,
        string $type,
        ?string $fromDate,
        ?string $toDate
    ): array {
        $residentIds = collect($residentRequestIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $records = collect();
        $transactionCount = 0;
        $maintenanceCount = 0;
        $transactions = collect();
        $maintenanceVisits = collect();
        $visitsByCase = collect();

        if ($residentIds->isNotEmpty()) {
            $txQuery = ResidentTransaction::query()
                ->whereIn('resident_request_id', $residentIds)
                ->with(['residentRequest.purok']);

            if (filled($fromDate)) {
                $txQuery->whereDate('transaction_date', '>=', $fromDate);
            }
            if (filled($toDate)) {
                $txQuery->whereDate('transaction_date', '<=', $toDate);
            }

            $transactions = $txQuery
                ->orderByDesc('transaction_date')
                ->orderByDesc('id')
                ->get();
            $transactionCount = $transactions->count();

            $visitQuery = HouseholdMonitoringVisit::query()
                ->whereHas('monitoringCase', fn ($query) => $query->whereIn('resident_request_id', $residentIds))
                ->with(['monitoringCase.residentRequest.purok']);

            if (filled($fromDate)) {
                $visitQuery->whereDate('visit_date', '>=', $fromDate);
            }
            if (filled($toDate)) {
                $visitQuery->whereDate('visit_date', '<=', $toDate);
            }

            $maintenanceVisits = $visitQuery
                ->orderByDesc('visit_date')
                ->orderByDesc('id')
                ->get();
            $maintenanceCount = $maintenanceVisits->count();

            $caseIds = $maintenanceVisits
                ->pluck('household_monitoring_case_id')
                ->filter()
                ->unique()
                ->values()
                ->all();

            $visitsByCase = $caseIds === []
                ? collect()
                : HouseholdMonitoringVisit::query()
                    ->whereIn('household_monitoring_case_id', $caseIds)
                    ->orderBy('visit_date')
                    ->orderBy('id')
                    ->get()
                    ->groupBy('household_monitoring_case_id');

            foreach ($visitsByCase as $caseId => $caseVisits) {
                HouseholdMonitoringVisit::backfillCarriedVitalsForCase((int) $caseId, $caseVisits);
            }

            if ($visitsByCase->isNotEmpty()) {
                $maintenanceVisits = HouseholdMonitoringVisit::query()
                    ->whereIn('id', $maintenanceVisits->pluck('id'))
                    ->with(['monitoringCase.residentRequest.purok'])
                    ->orderByDesc('visit_date')
                    ->orderByDesc('id')
                    ->get();

                $visitsByCase = HouseholdMonitoringVisit::query()
                    ->whereIn('household_monitoring_case_id', $caseIds)
                    ->orderBy('visit_date')
                    ->orderBy('id')
                    ->get()
                    ->groupBy('household_monitoring_case_id');
            }
        }

        if (in_array($type, ['all', 'transactions'], true)) {
            $records = $records->merge($transactions->map(fn (ResidentTransaction $tx) => self::mapTransaction($tx)));
        }

        if (in_array($type, ['all', 'maintenance'], true)) {
            $records = $records->merge(
                $maintenanceVisits->map(fn (HouseholdMonitoringVisit $visit) => self::mapMaintenanceVisit($visit, $visitsByCase))
            );
        }

        return [
            'records' => $records->sortByDesc('sort_at')->values(),
            'transactionCount' => $transactionCount,
            'maintenanceCount' => $maintenanceCount,
        ];
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public static function purokOptionsForHead(User $head): Collection
    {
        $purokIds = BhwHeadScope::assignedPurokIds($head);
        if ($purokIds === []) {
            return collect();
        }

        return Purok::query()
            ->whereIn('id', $purokIds)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($purok) => [
                'id' => (int) $purok->id,
                'name' => (string) $purok->name,
            ])
            ->values();
    }

    /**
     * @return Collection<int, array{id: int, name: string}>
     */
    public static function purokOptionsForCaptain(?Barangay $barangay): Collection
    {
        if (! $barangay) {
            return collect();
        }

        PurokLocator::ensureNonPurok($barangay);

        return Purok::query()
            ->where('barangay_id', $barangay->id)
            ->where('name', '!=', PurokLocator::NON_NAME)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn ($purok) => [
                'id' => (int) $purok->id,
                'name' => (string) $purok->name,
            ])
            ->values();
    }

    /**
     * @return list<int>
     */
    public static function acceptedResidentIdsForBhw(User $bhw): array
    {
        return ResidentRequest::query()
            ->forBhw($bhw)
            ->where('status', 'accepted')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function acceptedResidentIdsForHead(User $head, string|int|null $purokId = 'all'): array
    {
        $query = ResidentRequest::query()
            ->tap(fn ($builder) => BhwHeadScope::apply($builder, $head))
            ->where('status', 'accepted');

        if ($purokId !== null && (string) $purokId !== 'all' && ctype_digit((string) $purokId)) {
            $query->where('purok_id', (int) $purokId);
        }

        return $query
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function acceptedResidentIdsForCaptain(?Barangay $barangay, string|int|null $purokId = 'all'): array
    {
        if (! $barangay) {
            return [];
        }

        $barangayName = trim((string) $barangay->name);
        if ($barangayName === '') {
            return [];
        }

        $query = ResidentRequest::query()
            ->where('status', 'accepted')
            ->where('barangay', $barangayName);

        if ($purokId !== null && (string) $purokId !== 'all' && ctype_digit((string) $purokId)) {
            $query->where('purok_id', (int) $purokId);
        }

        return $query
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private static function mapTransaction(ResidentTransaction $tx): array
    {
        $medEntries = $tx->medicationEntries();
        $meds = collect($medEntries)
            ->map(fn (array $entry) => $entry['label'].' '.$entry['pcs'].' pcs')
            ->implode(', ');
        $conditions = $tx->inferredConditions();
        $detail = $meds !== ''
            ? $meds
            : ($conditions !== [] ? implode(', ', $conditions) : 'Medicine dispensed');
        $purok = trim((string) ($tx->residentRequest?->purok?->name ?? ''));
        $household = trim((string) ($tx->residentRequest?->full_name ?? 'Resident'));
        $philhealth = trim((string) ($tx->philhealth_number ?? ''));
        $gender = trim((string) ($tx->gender ?? ''));

        $details = [
            ['label' => 'Household', 'value' => $household],
            ['label' => 'Recipient', 'value' => $tx->recipientLabel()],
            ['label' => 'Date', 'value' => $tx->transaction_date?->format('M d, Y') ?: '—'],
            ['label' => 'Age', 'value' => $tx->age !== null ? (string) $tx->age : '—'],
            ['label' => 'Gender', 'value' => $gender !== '' ? $gender : '—'],
            ['label' => 'Purok', 'value' => $purok !== '' ? $purok : '—'],
            ['label' => 'PhilHealth', 'value' => $philhealth !== '' ? $philhealth : '—'],
            ['label' => 'Medications', 'value' => $meds !== '' ? $meds : 'None recorded'],
            ['label' => 'Health concerns', 'value' => $conditions !== [] ? implode(', ', $conditions) : 'None'],
        ];

        return [
            'sort_at' => optional($tx->transaction_date)->timestamp ?? 0,
            'type' => 'transaction',
            'type_label' => 'Transaction',
            'icon' => 'medication',
            'tone' => 'primary',
            'title' => $tx->recipientLabel(),
            'subtitle' => $household.($purok !== '' ? ' · '.$purok : ''),
            'detail' => $detail,
            'date_label' => $tx->transaction_date?->format('M d, Y') ?: '—',
            'time_label' => null,
            'preview_chips' => collect($medEntries)
                ->take(4)
                ->map(fn (array $entry) => [
                    'label' => $entry['label'],
                    'value' => $entry['pcs'].' pcs',
                ])
                ->values()
                ->whenEmpty(fn () => collect($conditions)->take(4)->map(fn ($c) => [
                    'label' => 'Concern',
                    'value' => $c,
                ])->values())
                ->pad(4, ['label' => 'Med', 'value' => '—', 'empty' => true])
                ->all(),
            'details' => $details,
        ];
    }

    /**
     * @param  Collection<int|string, Collection<int, HouseholdMonitoringVisit>>  $visitsByCase
     * @return array<string, mixed>
     */
    private static function mapMaintenanceVisit(HouseholdMonitoringVisit $visit, Collection $visitsByCase): array
    {
        $case = $visit->monitoringCase;
        $household = trim((string) ($case?->residentRequest?->full_name ?? 'Resident'));
        $purok = trim((string) ($case?->residentRequest?->purok?->name ?? ''));
        $memberName = trim((string) ($case?->member_name ?? 'Member'));
        $typeLabel = $case?->typeLabel() ?: 'Monitoring';
        $caseVisits = $visitsByCase->get($visit->household_monitoring_case_id, collect());
        $resolved = $visit->resolvedVitals($caseVisits);
        $isCompleted = ! (bool) ($case?->is_active ?? true);
        $lastCaseVisit = $caseVisits->sortBy([
            fn ($item) => optional($item->visit_date)->timestamp ?? 0,
            fn ($item) => (int) $item->id,
        ])->last();
        $visitLabel = ($isCompleted && $lastCaseVisit && (int) $lastCaseVisit->id === (int) $visit->id)
            ? 'Last Visit'
            : $resolved['visit_label'];

        $previewChips = collect([
            filled($resolved['anti_tetanus']) ? ['label' => 'Anti-TT', 'value' => (string) $resolved['anti_tetanus']] : null,
            $resolved['blood_pressure'] ? ['label' => 'BP', 'value' => (string) $resolved['blood_pressure']] : null,
            $resolved['weight_kg'] !== null ? ['label' => 'Weight', 'value' => $resolved['weight_kg'].' kg'] : null,
            $resolved['height_cm'] !== null ? ['label' => 'Height', 'value' => $resolved['height_cm'].' cm'] : null,
            $resolved['bmi_value'] !== null ? ['label' => 'BMI', 'value' => (string) $resolved['bmi_value']] : null,
            $resolved['muac_cm'] !== null ? ['label' => 'MUAC', 'value' => $resolved['muac_cm'].' cm'] : null,
        ])->filter()->take(4)->pad(4, ['label' => 'Vital', 'value' => '—', 'empty' => true])->values()->all();

        $detail = collect($previewChips)->map(fn ($chip) => $chip['label'].' '.$chip['value'])->implode(' · ');
        if ($detail === '') {
            $detail = filled($visit->notes) ? (string) $visit->notes : 'Maintenance visit recorded';
        }

        $status = $case?->is_active
            ? 'Active'
            : ($case?->completed_at ? 'Completed '.$case->completed_at->format('M d, Y') : 'Inactive');

        $details = [
            ['label' => 'Visit', 'value' => $visitLabel],
            ['label' => 'Household', 'value' => $household],
            ['label' => 'Member', 'value' => $memberName],
            ['label' => 'Monitoring type', 'value' => $typeLabel],
            ['label' => 'Visit date', 'value' => $visit->visit_date?->format('M d, Y') ?: '—'],
            ['label' => 'Case status', 'value' => $status],
            ['label' => 'Purok', 'value' => $purok !== '' ? $purok : '—'],
            ['label' => 'Blood pressure', 'value' => filled($resolved['blood_pressure']) ? (string) $resolved['blood_pressure'] : '—'],
            ['label' => 'Weight', 'value' => $resolved['weight_kg'] !== null ? $resolved['weight_kg'].' kg' : '—'],
            ['label' => 'Height', 'value' => $resolved['height_cm'] !== null ? $resolved['height_cm'].' cm' : '—'],
            ['label' => 'BMI', 'value' => $resolved['bmi_value'] !== null ? (string) $resolved['bmi_value'] : '—'],
            ['label' => 'MUAC', 'value' => $resolved['muac_cm'] !== null ? $resolved['muac_cm'].' cm' : '—'],
            ['label' => 'Anti-tetanus', 'value' => filled($resolved['anti_tetanus']) ? (string) $resolved['anti_tetanus'] : '—'],
            ['label' => 'Notes', 'value' => filled($visit->notes) ? (string) $visit->notes : '—'],
        ];

        if ($case?->isPregnant()) {
            array_splice($details, 6, 0, [
                ['label' => 'First period', 'value' => $case->first_period_date?->format('M d, Y') ?: '—'],
                ['label' => 'Last period', 'value' => $case->last_period_date?->format('M d, Y') ?: '—'],
            ]);
        }

        if (filled($case?->issue_notes)) {
            $details[] = ['label' => 'Issue notes', 'value' => (string) $case->issue_notes];
        }

        return [
            'sort_at' => optional($visit->visit_date)->timestamp ?? 0,
            'type' => 'maintenance',
            'type_label' => 'Maintenance',
            'icon' => $isCompleted ? 'task_alt' : 'monitor_heart',
            'tone' => 'secondary',
            'status_label' => $visitLabel.($isCompleted ? ' · Completed' : ' · Active'),
            'is_completed' => $isCompleted,
            'visit_label' => $visitLabel,
            'title' => $memberName,
            'subtitle' => $visitLabel.' · '.$typeLabel.' · '.$household.($purok !== '' ? ' · '.$purok : ''),
            'detail' => $detail,
            'date_label' => $visit->visit_date?->format('M d, Y') ?: '—',
            'time_label' => null,
            'preview_chips' => $previewChips,
            'details' => $details,
        ];
    }
}
