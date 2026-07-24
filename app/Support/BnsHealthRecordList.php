<?php

namespace App\Support;

use App\Models\BnsChildDewormingLog;
use App\Models\BnsChildVitaminALog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BnsHealthRecordList
{
    /**
     * @return Collection<string, array<string, mixed>>
     */
    public static function childLookup(User $bns): Collection
    {
        return BnsChildMonitoring::childrenUnderFiveFor($bns)->keyBy('id');
    }

    /**
     * One row per child, with nested Vitamin A givings (newest first).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function vitaminDistributionRows(User $bns): Collection
    {
        return self::groupGivingsByChild(self::vitaminDistributionLogRows($bns));
    }

    /**
     * Flat Vitamin A logs (one row per giving) for CSV export.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function vitaminDistributionLogRows(User $bns): Collection
    {
        $children = self::childLookup($bns);
        $childKeys = $children->keys()->map(fn ($id) => (string) $id)->all();

        if ($childKeys === []) {
            return collect();
        }

        return BnsChildVitaminALog::query()
            ->whereIn('child_key', $childKeys)
            ->orderByDesc('given_on')
            ->orderByDesc('id')
            ->get()
            ->map(function (BnsChildVitaminALog $log) use ($children) {
                $child = $children[(string) $log->child_key] ?? null;
                if ($child === null) {
                    return null;
                }

                return [
                    'child_key' => (string) $log->child_key,
                    'child_name' => (string) ($child['full_name'] ?? 'Child'),
                    'age_label' => (string) ($child['age_label'] ?? '—'),
                    'gender' => (string) ($child['gender'] ?? '—'),
                    'sex' => self::sexCode($child['gender'] ?? null),
                    'contact' => self::representativeContact($child),
                    'purok' => (string) ($child['purok'] ?? '—'),
                    'family_number' => (string) ($child['family_number'] ?? '—'),
                    'given_on' => $log->given_on?->toDateString(),
                    'given_on_label' => $log->given_on?->format('M d, Y') ?? '—',
                    'age_in_months' => $log->age_in_months !== null ? (int) $log->age_in_months : null,
                    'age_in_months_label' => $log->age_in_months !== null ? $log->age_in_months.' months' : '—',
                    'dose_received' => trim((string) ($log->notes ?? '')) ?: '—',
                    'notes' => trim((string) ($log->notes ?? '')) ?: '—',
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * One row per child, with nested deworming givings (newest first).
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function dewormingRows(User $bns): Collection
    {
        return self::groupGivingsByChild(self::dewormingLogRows($bns));
    }

    /**
     * Flat deworming logs (one row per giving) for CSV export.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public static function dewormingLogRows(User $bns): Collection
    {
        $children = self::childLookup($bns);
        $childKeys = $children->keys()->map(fn ($id) => (string) $id)->all();

        if ($childKeys === []) {
            return collect();
        }

        return BnsChildDewormingLog::query()
            ->whereIn('child_key', $childKeys)
            ->orderByDesc('given_on')
            ->orderByDesc('id')
            ->get()
            ->map(function (BnsChildDewormingLog $log) use ($children) {
                $child = $children[(string) $log->child_key] ?? null;
                if ($child === null) {
                    return null;
                }

                return [
                    'child_key' => (string) $log->child_key,
                    'child_name' => (string) ($child['full_name'] ?? 'Child'),
                    'age_label' => (string) ($child['age_label'] ?? '—'),
                    'gender' => (string) ($child['gender'] ?? '—'),
                    'sex' => self::sexCode($child['gender'] ?? null),
                    'contact' => self::representativeContact($child),
                    'purok' => (string) ($child['purok'] ?? '—'),
                    'family_number' => (string) ($child['family_number'] ?? '—'),
                    'given_on' => $log->given_on?->toDateString(),
                    'given_on_label' => $log->given_on?->format('M d, Y') ?? '—',
                    'dose_received' => trim((string) ($log->medicine ?? '')) ?: '—',
                    'medicine' => trim((string) ($log->medicine ?? '')) ?: '—',
                    'notes' => trim((string) ($log->notes ?? '')) ?: '—',
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $logRows
     * @return Collection<int, array<string, mixed>>
     */
    private static function groupGivingsByChild(Collection $logRows): Collection
    {
        return $logRows
            ->groupBy('child_key')
            ->map(function (Collection $givings) {
                $latest = $givings->first();
                $sorted = $givings->values()->all();

                return [
                    'child_key' => (string) ($latest['child_key'] ?? ''),
                    'child_name' => (string) ($latest['child_name'] ?? 'Child'),
                    'age_label' => (string) ($latest['age_label'] ?? '—'),
                    'gender' => (string) ($latest['gender'] ?? '—'),
                    'sex' => (string) ($latest['sex'] ?? self::sexCode($latest['gender'] ?? null)),
                    'contact' => (string) ($latest['contact'] ?? '—'),
                    'purok' => (string) ($latest['purok'] ?? '—'),
                    'family_number' => (string) ($latest['family_number'] ?? '—'),
                    'given_on' => $latest['given_on'] ?? null,
                    'given_on_label' => (string) ($latest['given_on_label'] ?? '—'),
                    'times_given' => $givings->count(),
                    'given_dates' => $givings
                        ->pluck('given_on')
                        ->filter()
                        ->values()
                        ->all(),
                    'medicine' => (string) ($latest['medicine'] ?? $latest['dose_received'] ?? '—'),
                    'dose_received' => (string) ($latest['dose_received'] ?? $latest['medicine'] ?? $latest['notes'] ?? '—'),
                    'age_in_months_label' => (string) ($latest['age_in_months_label'] ?? '—'),
                    'notes' => (string) ($latest['notes'] ?? '—'),
                    'givings' => $sorted,
                ];
            })
            ->sortBy(fn (array $row) => strtolower((string) ($row['child_name'] ?? '')).'|'.(string) ($row['child_key'] ?? ''))
            ->values();
    }

    private static function sexCode(mixed $gender): string
    {
        $value = strtolower(trim((string) $gender));

        return match (true) {
            in_array($value, ['male', 'm'], true) => 'M',
            in_array($value, ['female', 'f'], true) => 'F',
            default => '—',
        };
    }

    /**
     * @param  array<string, mixed>  $child
     */
    private static function representativeContact(array $child): string
    {
        $contact = trim((string) ($child['contact'] ?? ''));
        if ($contact !== '' && $contact !== '—') {
            return $contact;
        }

        return '—';
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string>  $keys
     * @param  Collection<int, array<string, mixed>>|list<array<string, mixed>>  $rows
     */
    public static function csvDownload(
        string $filename,
        array $headers,
        array $keys,
        Collection|array $rows,
        ?string $title = null,
    ): StreamedResponse {
        $collection = $rows instanceof Collection ? $rows : collect($rows);

        return Response::streamDownload(function () use ($collection, $headers, $keys, $title) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if (filled($title)) {
                fputcsv($handle, [$title]);
                fputcsv($handle, []);
            }

            fputcsv($handle, $headers);

            foreach ($collection as $row) {
                $line = [];
                foreach ($keys as $key) {
                    $line[] = (string) ($row[$key] ?? '');
                }
                fputcsv($handle, $line);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
