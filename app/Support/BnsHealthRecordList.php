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
     * @return Collection<int, array<string, mixed>>
     */
    public static function vitaminDistributionRows(User $bns): Collection
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
                    'purok' => (string) ($child['purok'] ?? '—'),
                    'family_number' => (string) ($child['family_number'] ?? '—'),
                    'given_on' => $log->given_on?->toDateString(),
                    'given_on_label' => $log->given_on?->format('M d, Y') ?? '—',
                    'age_in_months' => $log->age_in_months !== null ? (int) $log->age_in_months : null,
                    'age_in_months_label' => $log->age_in_months !== null ? $log->age_in_months.' months' : '—',
                    'notes' => trim((string) ($log->notes ?? '')) ?: '—',
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function dewormingRows(User $bns): Collection
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
                    'purok' => (string) ($child['purok'] ?? '—'),
                    'family_number' => (string) ($child['family_number'] ?? '—'),
                    'given_on' => $log->given_on?->toDateString(),
                    'given_on_label' => $log->given_on?->format('M d, Y') ?? '—',
                    'medicine' => trim((string) ($log->medicine ?? '')) ?: '—',
                    'notes' => trim((string) ($log->notes ?? '')) ?: '—',
                ];
            })
            ->filter()
            ->values();
    }

    /**
     * @param  list<string>  $headers
     * @param  list<string>  $keys
     * @param  Collection<int, array<string, mixed>>|list<array<string, mixed>>  $rows
     */
    public static function csvDownload(string $filename, array $headers, array $keys, Collection|array $rows): StreamedResponse
    {
        $collection = $rows instanceof Collection ? $rows : collect($rows);

        return Response::streamDownload(function () use ($collection, $headers, $keys) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
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
