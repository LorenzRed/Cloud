<?php

namespace App\Support;

use App\Models\BnsChildDewormingLog;
use App\Models\BnsChildImmunization;
use App\Models\BnsChildVitaminALog;
use App\Models\BnsChildWeightLog;
use App\Models\Barangay;
use App\Models\Event;
use App\Models\InboxMessage;
use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BnsChildHealth
{
    public static function makeChildKey(int $residentRequestId, int $childIndex): string
    {
        return $residentRequestId.'-'.$childIndex;
    }

    /**
     * @return array{0: int, 1: int}
     */
    public static function parseChildKey(string $childKey): array
    {
        if (! preg_match('/^(\d+)-(\d+)$/', trim($childKey), $matches)) {
            abort(404, 'Invalid child record.');
        }

        return [(int) $matches[1], (int) $matches[2]];
    }

    public static function findChildForBns(User $bns, string $childKey): array
    {
        [$requestId, $childIndex] = self::parseChildKey($childKey);

        $child = BnsChildMonitoring::childrenUnderFiveFor($bns)
            ->first(fn (array $row) => (int) $row['resident_request_id'] === $requestId
                && (int) ($row['child_index'] ?? -1) === $childIndex);

        if ($child === null) {
            // Fallback match by id field
            $child = BnsChildMonitoring::childrenUnderFiveFor($bns)
                ->first(fn (array $row) => ($row['id'] ?? '') === $childKey);
        }

        if ($child === null) {
            abort(404, 'Child not found in your barangay masterlist.');
        }

        return $child;
    }

    public static function ensureImmunizationRows(string $childKey, int $residentRequestId, int $childIndex, string $birthdate): void
    {
        self::migrateLegacyImmunizationCodes($childKey);

        $schedule = BnsImmunizationSchedule::scheduleForBirthdate($birthdate);

        foreach ($schedule as $item) {
            $record = BnsChildImmunization::query()->firstOrCreate(
                [
                    'child_key' => $childKey,
                    'vaccine_code' => $item['code'],
                ],
                [
                    'resident_request_id' => $residentRequestId,
                    'child_index' => $childIndex,
                    'vaccine_name' => $item['name'],
                    'due_date' => $item['due_date'],
                    'status' => BnsChildImmunization::STATUS_PENDING,
                ]
            );

            $updates = [];
            if ($record->vaccine_name !== $item['name']) {
                $updates['vaccine_name'] = $item['name'];
            }
            if ($record->due_date?->toDateString() !== $item['due_date']) {
                $updates['due_date'] = $item['due_date'];
            }
            if ($updates !== []) {
                $record->update($updates);
            }
        }

        self::refreshImmunizationStatuses($childKey);
    }

    private static function migrateLegacyImmunizationCodes(string $childKey): void
    {
        foreach (['mr_1' => 'mmr_1', 'mr_2' => 'mmr_2'] as $legacyCode => $newCode) {
            $legacy = BnsChildImmunization::query()
                ->where('child_key', $childKey)
                ->where('vaccine_code', $legacyCode)
                ->first();

            if ($legacy === null) {
                continue;
            }

            $target = BnsChildImmunization::query()
                ->where('child_key', $childKey)
                ->where('vaccine_code', $newCode)
                ->first();

            if ($target === null) {
                $legacy->update(['vaccine_code' => $newCode]);

                continue;
            }

            if ($target->status === BnsChildImmunization::STATUS_PENDING && $legacy->status !== BnsChildImmunization::STATUS_PENDING) {
                $target->update([
                    'status' => $legacy->status,
                    'given_date' => $legacy->given_date,
                    'notes' => $legacy->notes,
                    'recorded_by_user_id' => $legacy->recorded_by_user_id,
                ]);
            }

            $legacy->delete();
        }
    }

    public static function refreshImmunizationStatuses(string $childKey): void
    {
        $today = now()->startOfDay();

        BnsChildImmunization::query()
            ->where('child_key', $childKey)
            ->where('status', '!=', BnsChildImmunization::STATUS_GIVEN)
            ->each(function (BnsChildImmunization $record) use ($today) {
                if ($record->given_date !== null) {
                    $record->update(['status' => BnsChildImmunization::STATUS_GIVEN]);

                    return;
                }

                if ($record->status === BnsChildImmunization::STATUS_MISSED) {
                    return;
                }

                $dueDate = $record->due_date?->copy()->startOfDay();
                if ($dueDate && $today->gt($dueDate)) {
                    $record->update(['status' => BnsChildImmunization::STATUS_MISSED]);
                } else {
                    $record->update(['status' => BnsChildImmunization::STATUS_PENDING]);
                }
            });
    }

    /**
     * @return array<string, mixed>
     */
    public static function healthPayload(User $bns, string $childKey): array
    {
        $child = self::findChildForBns($bns, $childKey);
        [$requestId, $childIndex] = self::parseChildKey($childKey);
        $birthdate = (string) ($child['birthdate'] ?? '');
        $ageInMonths = BnsChildMonitoring::ageInMonths($birthdate) ?? 0;

        self::ensureImmunizationRows($childKey, $requestId, $childIndex, $birthdate);

        $weightLogs = BnsChildWeightLog::query()
            ->where('child_key', $childKey)
            ->orderByDesc('recorded_on')
            ->orderByDesc('id')
            ->get()
            ->map(function (BnsChildWeightLog $log) use ($child) {
                $ageMonths = $log->age_in_months ?? BnsChildMonitoring::ageInMonths($child['birthdate'] ?? null);
                $nutrition = BnsChildNutrition::assess(
                    (float) $log->weight_kg,
                    $log->height_cm !== null ? (float) $log->height_cm : null,
                    $ageMonths,
                    (string) ($child['gender'] ?? '')
                );

                return [
                    'id' => $log->id,
                    'weight_kg' => (float) $log->weight_kg,
                    'height_cm' => $log->height_cm !== null ? (float) $log->height_cm : null,
                    'muac_cm' => $log->muac_cm !== null ? (float) $log->muac_cm : null,
                    'age_in_months' => $ageMonths,
                    'recorded_on' => $log->recorded_on?->format('M d, Y'),
                    'recorded_on_raw' => $log->recorded_on?->toDateString(),
                    'nutrition' => $nutrition,
                    'muac' => BnsChildNutrition::assessMuac(
                        $log->muac_cm !== null ? (float) $log->muac_cm : null,
                        $ageMonths
                    ),
                ];
            });

        $firstWeightLog = $weightLogs->first();
        $latestNutrition = is_array($firstWeightLog)
            ? ($firstWeightLog['nutrition'] ?? BnsChildNutrition::assess(null, null, $ageInMonths, (string) ($child['gender'] ?? '')))
            : BnsChildNutrition::assess(null, null, $ageInMonths, (string) ($child['gender'] ?? ''));
        $latestMuac = is_array($firstWeightLog) ? ($firstWeightLog['muac'] ?? null) : null;

        $dewormingLogs = BnsChildDewormingLog::query()
            ->where('child_key', $childKey)
            ->orderByDesc('given_on')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BnsChildDewormingLog $log) => [
                'id' => $log->id,
                'given_on' => $log->given_on?->format('M d, Y'),
                'given_on_raw' => $log->given_on?->toDateString(),
                'medicine' => $log->medicine,
                'notes' => $log->notes,
            ]);

        $vitaminALogs = BnsChildVitaminALog::query()
            ->where('child_key', $childKey)
            ->orderByDesc('given_on')
            ->orderByDesc('id')
            ->get()
            ->map(fn (BnsChildVitaminALog $log) => [
                'id' => $log->id,
                'given_on' => $log->given_on?->format('M d, Y'),
                'given_on_raw' => $log->given_on?->toDateString(),
                'age_in_months' => $log->age_in_months,
                'notes' => $log->notes,
            ]);

        $today = now()->startOfDay();

        $immunizations = BnsChildImmunization::query()
            ->where('child_key', $childKey)
            ->whereIn('vaccine_code', BnsImmunizationSchedule::vaccineCodes())
            ->orderBy('due_date')
            ->get()
            ->map(function (BnsChildImmunization $record) use ($today) {
                $dueMonths = BnsImmunizationSchedule::dueMonthsForCode((string) $record->vaccine_code);
                $dueDate = $record->due_date?->copy()->startOfDay();
                $isOverdue = $dueDate && $today->gt($dueDate) && $record->status !== BnsChildImmunization::STATUS_GIVEN;

                return [
                    'id' => $record->id,
                    'vaccine_code' => $record->vaccine_code,
                    'vaccine_name' => $record->vaccine_name,
                    'due_age_label' => $dueMonths !== null
                        ? BnsImmunizationSchedule::dueAgeLabel($dueMonths)
                        : null,
                    'due_date' => $record->due_date?->format('M d, Y'),
                    'due_date_raw' => $record->due_date?->toDateString(),
                    'given_date' => $record->given_date?->format('M d, Y'),
                    'given_date_raw' => $record->given_date?->toDateString(),
                    'status' => $record->status,
                    'status_label' => match ($record->status) {
                        BnsChildImmunization::STATUS_GIVEN => 'Given',
                        BnsChildImmunization::STATUS_MISSED => 'Missed',
                        default => $isOverdue ? 'Overdue' : 'Pending',
                    },
                    'is_overdue' => $isOverdue,
                    'missed_date' => $record->status === BnsChildImmunization::STATUS_MISSED
                        ? $record->due_date?->format('M d, Y')
                        : null,
                    'notes' => $record->notes,
                ];
            });

        $immunizationSummary = self::immunizationSummary($immunizations);

        $vitaminADueMonths = BnsImmunizationSchedule::vitaminADueMonths();
        $usedVitaminLogIds = [];

        $vitaminASchedule = collect($vitaminADueMonths)
            ->map(function (int $dueMonth, int $index) use ($birthdate, $vitaminALogs, $ageInMonths, $vitaminADueMonths, &$usedVitaminLogIds) {
                $dueDate = BnsImmunizationSchedule::dueDateFor($birthdate, (float) $dueMonth);
                $nextDueMonth = $vitaminADueMonths[$index + 1] ?? 60;
                $given = $vitaminALogs->first(function ($log) use ($dueMonth, $nextDueMonth, $usedVitaminLogIds) {
                    if (in_array((int) ($log['id'] ?? 0), $usedVitaminLogIds, true)) {
                        return false;
                    }

                    $age = (int) ($log['age_in_months'] ?? -1);

                    return $age >= $dueMonth && $age < $nextDueMonth;
                });

                if ($given) {
                    $usedVitaminLogIds[] = (int) $given['id'];
                }

                $status = 'upcoming';
                if ($given) {
                    $status = 'given';
                } elseif ($ageInMonths >= $dueMonth && now()->startOfDay()->gt($dueDate)) {
                    $status = 'missed';
                } elseif ($ageInMonths >= $dueMonth) {
                    $status = 'due';
                }

                return [
                    'due_month' => $dueMonth,
                    'due_date' => $dueDate->format('M d, Y'),
                    'due_date_raw' => $dueDate->toDateString(),
                    'status' => $status,
                    'log_id' => $given['id'] ?? null,
                    'given_on' => $given['given_on'] ?? null,
                    'given_on_raw' => $given['given_on_raw'] ?? null,
                    'notes' => $given['notes'] ?? null,
                ];
            })
            ->values();

        return [
            'child' => $child,
            'child_key' => $childKey,
            'age_in_months' => $ageInMonths,
            'eligible_vitamin_a' => BnsImmunizationSchedule::isEligibleForVitaminA($ageInMonths),
            'latest_weight_kg' => $weightLogs->first()['weight_kg'] ?? null,
            'latest_muac_cm' => $weightLogs->first()['muac_cm'] ?? null,
            'nutrition' => $latestNutrition,
            'muac' => $latestMuac,
            'weight_logs' => $weightLogs->values(),
            'deworming_logs' => $dewormingLogs->values(),
            'vitamin_a_logs' => $vitaminALogs->values(),
            'vitamin_a_schedule' => $vitaminASchedule,
            'immunizations' => $immunizations->values(),
            'immunization_summary' => $immunizationSummary,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $immunizations
     * @return array{total: int, given: int, missed: int, pending: int, fully_immunized: bool}
     */
    public static function immunizationSummary(Collection $immunizations): array
    {
        $total = $immunizations->count();
        $given = $immunizations->where('status', BnsChildImmunization::STATUS_GIVEN)->count();
        $missed = $immunizations->where('status', BnsChildImmunization::STATUS_MISSED)->count();
        $pending = $immunizations->where('status', BnsChildImmunization::STATUS_PENDING)->count();

        return [
            'total' => $total,
            'given' => $given,
            'missed' => $missed,
            'pending' => $pending,
            'fully_immunized' => $total > 0 && $given === $total,
        ];
    }

    public static function storeWeight(User $bns, string $childKey, array $data): BnsChildWeightLog
    {
        $child = self::findChildForBns($bns, $childKey);
        [$requestId, $childIndex] = self::parseChildKey($childKey);
        $ageInMonths = BnsChildMonitoring::ageInMonths($child['birthdate'] ?? null);

        if ($ageInMonths !== null && $ageInMonths > 59) {
            throw ValidationException::withMessages([
                'weight_kg' => 'Weight monitoring in this form is for children 0–59 months.',
            ]);
        }

        return BnsChildWeightLog::query()->create([
            'child_key' => $childKey,
            'resident_request_id' => $requestId,
            'child_index' => $childIndex,
            'weight_kg' => $data['weight_kg'],
            'height_cm' => $data['height_cm'] ?? null,
            'muac_cm' => $data['muac_cm'] ?? null,
            'age_in_months' => $ageInMonths,
            'recorded_on' => $data['recorded_on'],
            'notes' => null,
            'recorded_by_user_id' => $bns->id,
        ]);
    }

    public static function updateWeight(User $bns, string $childKey, int $logId, array $data): BnsChildWeightLog
    {
        $child = self::findChildForBns($bns, $childKey);

        $log = BnsChildWeightLog::query()
            ->where('child_key', $childKey)
            ->whereKey($logId)
            ->firstOrFail();

        try {
            $recordedOn = Carbon::parse((string) $data['recorded_on'])->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'recorded_on' => 'Enter a valid date recorded.',
            ]);
        }

        if ($recordedOn->gt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'recorded_on' => 'Date recorded cannot be in the future.',
            ]);
        }

        $born = PersonAge::parseBirthdate($child['birthdate'] ?? null);
        if ($born && $recordedOn->lt($born)) {
            throw ValidationException::withMessages([
                'recorded_on' => 'Date recorded cannot be before the child’s birthdate.',
            ]);
        }

        $ageInMonths = PersonAge::ageInWholeMonths($child['birthdate'] ?? null, $recordedOn);

        if ($ageInMonths !== null && $ageInMonths > 59) {
            throw ValidationException::withMessages([
                'weight_kg' => 'Weight monitoring in this form is for children 0–59 months.',
            ]);
        }

        $log->update([
            'weight_kg' => $data['weight_kg'],
            'height_cm' => $data['height_cm'] ?? null,
            'muac_cm' => $data['muac_cm'] ?? null,
            'age_in_months' => $ageInMonths,
            'recorded_on' => $recordedOn->toDateString(),
            'recorded_by_user_id' => $bns->id,
        ]);

        return $log->fresh();
    }

    public static function storeDeworming(User $bns, string $childKey, array $data): BnsChildDewormingLog
    {
        $child = self::findChildForBns($bns, $childKey);
        [$requestId, $childIndex] = self::parseChildKey($childKey);

        return BnsChildDewormingLog::query()->create([
            'child_key' => $childKey,
            'resident_request_id' => $requestId,
            'child_index' => $childIndex,
            'given_on' => $data['given_on'],
            'medicine' => $data['medicine'] ?? null,
            'notes' => null,
            'recorded_by_user_id' => $bns->id,
        ]);
    }

    public static function updateDeworming(User $bns, string $childKey, int $logId, array $data): BnsChildDewormingLog
    {
        self::findChildForBns($bns, $childKey);

        $log = BnsChildDewormingLog::query()
            ->where('child_key', $childKey)
            ->whereKey($logId)
            ->firstOrFail();

        $log->update([
            'given_on' => $data['given_on'],
            'medicine' => $data['medicine'] ?? null,
            'notes' => null,
            'recorded_by_user_id' => $bns->id,
        ]);

        return $log->fresh();
    }

    public static function deleteDeworming(User $bns, string $childKey, int $logId): void
    {
        self::findChildForBns($bns, $childKey);

        $log = BnsChildDewormingLog::query()
            ->where('child_key', $childKey)
            ->whereKey($logId)
            ->firstOrFail();

        $log->delete();
    }

    public static function storeVitaminA(User $bns, string $childKey, array $data): BnsChildVitaminALog
    {
        $child = self::findChildForBns($bns, $childKey);
        [$requestId, $childIndex] = self::parseChildKey($childKey);
        $givenOn = self::validatedVitaminAGivenOn($child['birthdate'] ?? null, (string) $data['given_on']);
        $ageInMonths = PersonAge::ageInWholeMonths($child['birthdate'] ?? null, $givenOn) ?? 0;

        if (! BnsImmunizationSchedule::isEligibleForVitaminA($ageInMonths)) {
            throw ValidationException::withMessages([
                'given_on' => 'Vitamin A is given to children 6–59 months old. Check the date given.',
            ]);
        }

        return BnsChildVitaminALog::query()->create([
            'child_key' => $childKey,
            'resident_request_id' => $requestId,
            'child_index' => $childIndex,
            'given_on' => $givenOn->toDateString(),
            'age_in_months' => $ageInMonths,
            'notes' => $data['medicine'] ?? $data['notes'] ?? null,
            'recorded_by_user_id' => $bns->id,
        ]);
    }

    public static function updateVitaminA(User $bns, string $childKey, int $logId, array $data): BnsChildVitaminALog
    {
        $child = self::findChildForBns($bns, $childKey);

        $log = BnsChildVitaminALog::query()
            ->where('child_key', $childKey)
            ->whereKey($logId)
            ->firstOrFail();

        $givenOn = self::validatedVitaminAGivenOn($child['birthdate'] ?? null, (string) $data['given_on']);
        $ageInMonths = PersonAge::ageInWholeMonths($child['birthdate'] ?? null, $givenOn) ?? 0;

        if (! BnsImmunizationSchedule::isEligibleForVitaminA($ageInMonths)) {
            throw ValidationException::withMessages([
                'given_on' => 'Vitamin A is given to children 6–59 months old. Check the date given.',
            ]);
        }

        $log->update([
            'given_on' => $givenOn->toDateString(),
            'age_in_months' => $ageInMonths,
            'notes' => array_key_exists('medicine', $data)
                ? ($data['medicine'] ?? null)
                : (array_key_exists('notes', $data) ? ($data['notes'] ?? null) : $log->notes),
            'recorded_by_user_id' => $bns->id,
        ]);

        return $log->fresh();
    }

    private static function validatedVitaminAGivenOn(?string $birthdate, string $givenOnInput): Carbon
    {
        try {
            $givenOn = Carbon::parse($givenOnInput)->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'given_on' => 'Enter a valid date given.',
            ]);
        }

        if ($givenOn->gt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'given_on' => 'Date given cannot be in the future.',
            ]);
        }

        $born = PersonAge::parseBirthdate($birthdate);
        if ($born && $givenOn->lt($born)) {
            throw ValidationException::withMessages([
                'given_on' => 'Date given cannot be before the child’s birthdate.',
            ]);
        }

        return $givenOn;
    }

    public static function updateImmunization(User $bns, string $childKey, int $immunizationId, array $data): BnsChildImmunization
    {
        self::findChildForBns($bns, $childKey);

        $record = BnsChildImmunization::query()
            ->where('child_key', $childKey)
            ->whereKey($immunizationId)
            ->firstOrFail();

        $status = $data['status'];
        $givenDate = $data['given_date'] ?? null;

        if ($status === BnsChildImmunization::STATUS_GIVEN && ! $givenDate) {
            throw ValidationException::withMessages([
                'given_date' => 'Date given is required when marking a vaccine as given.',
            ]);
        }

        $record->update([
            'status' => $status,
            'given_date' => $status === BnsChildImmunization::STATUS_GIVEN ? $givenDate : null,
            'notes' => $data['notes'] ?? $record->notes,
            'recorded_by_user_id' => $bns->id,
        ]);

        self::refreshImmunizationStatuses($childKey);

        return $record->fresh();
    }

    /**
     * Create a date-only announcement post and notify users about an immunization consultation.
     *
     * @param  array{consultation_date: string}  $data
     * @return array{post_id: int, message: string}
     */
    public static function notifyImmunizationConsultation(User $bns, string $childKey, int $immunizationId, array $data): array
    {
        $child = self::findChildForBns($bns, $childKey);

        $record = BnsChildImmunization::query()
            ->where('child_key', $childKey)
            ->whereKey($immunizationId)
            ->firstOrFail();

        if ($record->status === BnsChildImmunization::STATUS_GIVEN) {
            throw ValidationException::withMessages([
                'consultation_date' => 'This vaccine is already marked as given.',
            ]);
        }

        try {
            $consultationDate = Carbon::parse((string) $data['consultation_date'])->startOfDay();
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'consultation_date' => 'Enter a valid consultation date.',
            ]);
        }

        if ($consultationDate->lt(now()->startOfDay())) {
            throw ValidationException::withMessages([
                'consultation_date' => 'Consultation date cannot be in the past.',
            ]);
        }

        $barangayName = BnsChildMonitoring::barangayNameFor($bns);
        $barangay = filled($barangayName)
            ? Barangay::query()->where('name', $barangayName)->first()
            : null;

        if ($barangay === null) {
            throw ValidationException::withMessages([
                'consultation_date' => 'Your barangay assignment was not found.',
            ]);
        }

        $residentRequest = ResidentRequest::query()->find((int) ($child['resident_request_id'] ?? 0));
        $purokId = $residentRequest?->purok_id ? (int) $residentRequest->purok_id : null;
        $householdUserId = $residentRequest?->user_id ? (int) $residentRequest->user_id : null;

        $childName = trim((string) ($child['full_name'] ?? 'your child')) ?: 'your child';
        $vaccineName = trim((string) ($record->vaccine_name ?? 'immunization')) ?: 'immunization';
        $dateLabel = $consultationDate->format('M d, Y');

        $title = SafeInput::plainText("Immunization consultation: {$vaccineName}");
        $description = SafeInput::plainText(
            "Please bring {$childName} for {$vaccineName} immunization consultation on {$dateLabel}.",
            true
        );

        $post = Event::query()->create([
            'barangay_id' => $barangay->id,
            'target_purok_ids' => $purokId ? [$purokId] : null,
            'created_by' => $bns->id,
            'title' => $title !== '' ? $title : 'Immunization consultation',
            'description' => $description !== '' ? $description : null,
            'event_date' => $consultationDate->toDateString(),
            'start_time' => null,
            'end_time' => '23:59:59',
            'location' => null,
            'category' => Event::CATEGORY_IMMUNIZATION_CONSULTATION,
            'image_url' => null,
        ]);

        $now = now();

        if ($householdUserId) {
            InboxMessage::query()->create([
                'user_id' => $householdUserId,
                'title' => 'Immunization consultation',
                'message' => "Consultation for {$childName} ({$vaccineName}) is on {$dateLabel}. Check Upcoming Schedule on Home for details.",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $purokQuery = ResidentRequest::query()
            ->where('barangay', $barangay->name)
            ->where('status', 'accepted')
            ->whereNotNull('user_id');

        if ($purokId) {
            $purokQuery->where('purok_id', $purokId);
        }

        $residentUserIds = $purokQuery
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn (int $id) => $id > 0 && $id !== (int) $householdUserId)
            ->values();

        foreach ($residentUserIds as $residentUserId) {
            InboxMessage::query()->create([
                'user_id' => $residentUserId,
                'title' => 'Immunization schedule',
                'message' => "Immunization consultation for {$vaccineName} is on {$dateLabel}. Check Upcoming Schedule on Home.",
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        return [
            'post_id' => (int) $post->id,
            'message' => $householdUserId
                ? 'Consultation notice posted and parent/guardian notified.'
                : 'Consultation notice posted. No linked user account was found to notify.',
        ];
    }

    /**
     * @return array{total: int, given: int, missed: int, pending: int, fully_immunized: bool}
     */
    public static function computedImmunizationSummary(string $birthdate): array
    {
        $today = now()->startOfDay();
        $schedule = BnsImmunizationSchedule::scheduleForBirthdate($birthdate);

        $missed = 0;
        $pending = 0;

        foreach ($schedule as $item) {
            $dueDate = Carbon::parse($item['due_date'])->startOfDay();
            if ($today->gt($dueDate)) {
                $missed++;
            } else {
                $pending++;
            }
        }

        $total = count($schedule);

        return [
            'total' => $total,
            'given' => 0,
            'missed' => $missed,
            'pending' => $pending,
            'fully_immunized' => false,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $children
     * @return array<string, array<string, mixed|null>>
     */
    public static function summariesForChildren(Collection $children): array
    {
        if ($children->isEmpty()) {
            return [];
        }

        $childKeys = $children->pluck('id')->all();

        $latestWeights = BnsChildWeightLog::query()
            ->whereIn('child_key', $childKeys)
            ->orderByDesc('recorded_on')
            ->get()
            ->unique('child_key')
            ->keyBy('child_key');

        $immunizationRows = BnsChildImmunization::query()
            ->whereIn('child_key', $childKeys)
            ->get()
            ->groupBy('child_key');

        $summaries = [];
        foreach ($children as $child) {
            $key = (string) $child['id'];
            $rows = $immunizationRows->get($key);

            if ($rows && $rows->isNotEmpty()) {
                self::refreshImmunizationStatuses($key);
                $rows = BnsChildImmunization::query()
                    ->where('child_key', $key)
                    ->whereIn('vaccine_code', BnsImmunizationSchedule::vaccineCodes())
                    ->get();
                $imm = self::immunizationSummary($rows->map(fn ($r) => ['status' => $r->status]));
            } elseif (filled($child['birthdate'] ?? null)) {
                $imm = self::computedImmunizationSummary((string) $child['birthdate']);
            } else {
                $imm = ['total' => 0, 'given' => 0, 'missed' => 0, 'pending' => 0, 'fully_immunized' => false];
            }

            $latestWeight = $latestWeights[$key] ?? null;
            $nutrition = BnsChildNutrition::assess(
                $latestWeight ? (float) $latestWeight->weight_kg : null,
                $latestWeight?->height_cm !== null ? (float) $latestWeight->height_cm : null,
                (int) ($child['age_in_months'] ?? BnsChildMonitoring::ageInMonths($child['birthdate'] ?? null) ?? 0),
                (string) ($child['gender'] ?? '')
            );

            $summaries[$key] = [
                'latest_weight_kg' => $latestWeight ? (float) $latestWeight->weight_kg : null,
                'nutrition' => $nutrition,
                'immunization_given' => $imm['given'] ?? 0,
                'immunization_total' => $imm['total'] ?? 0,
                'immunization_missed' => $imm['missed'] ?? 0,
                'fully_immunized' => $imm['fully_immunized'] ?? false,
            ];
        }

        return $summaries;
    }
}
