@php
    $bnsReport = $bnsReport ?? [];
    $overview = $bnsReport['overview'] ?? [];
    $periodActivity = $bnsReport['period_activity'] ?? [];
    $immunization = $bnsReport['immunization'] ?? [];
    $nutrition = $bnsReport['nutrition'] ?? [];
    $nutritionChildren = $bnsReport['nutrition_children'] ?? [];
    $monthlyActivity = $bnsReport['activity_chart'] ?? ['labels' => [], 'weight' => [], 'deworming' => [], 'vitaminA' => [], 'immunization' => []];
    $recentActivities = $bnsReport['recent_activities'] ?? [];
    $childrenByPurok = $bnsReport['children_by_purok'] ?? [];
    $chartGranularity = $bnsReport['period']['chart_granularity'] ?? 'monthly';
@endphp

<div id="bnsReportContent" class="space-y-6">
    <section>
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">child_care</span> Child masterlist overview
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            @foreach ([
                ['label' => 'Children (0–5)', 'value' => $overview['total_children'] ?? 0, 'icon' => 'child_care', 'icon_class' => 'text-primary'],
                ['label' => 'Households', 'value' => $overview['households'] ?? 0, 'icon' => 'home', 'icon_class' => 'text-primary'],
                ['label' => 'Puroks', 'value' => $overview['puroks'] ?? 0, 'icon' => 'map', 'icon_class' => 'text-primary'],
                ['label' => 'With weight records', 'value' => $overview['children_with_weight'] ?? 0, 'icon' => 'monitor_weight', 'icon_class' => 'text-secondary'],
                ['label' => 'Fully immunized', 'value' => $overview['fully_immunized'] ?? 0, 'icon' => 'vaccines', 'icon_class' => 'text-secondary'],
                ['label' => 'Missed doses', 'value' => $overview['children_with_missed_doses'] ?? 0, 'icon' => 'warning', 'icon_class' => 'text-accent-red'],
            ] as $card)
                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $card['label'] }}</p>
                        <span class="material-symbols-outlined {{ $card['icon_class'] }} text-lg">{{ $card['icon'] }}</span>
                    </div>
                    <p class="text-2xl font-bold">{{ number_format((int) $card['value']) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section>
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">monitor_weight</span> Nutritional status (latest weight)
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
            @foreach ([
                ['label' => 'Malnourished', 'value' => $nutrition['malnutrition_cases'] ?? 0, 'icon' => 'warning', 'icon_class' => 'text-accent-red'],
                ['label' => 'Severely malnourished', 'value' => $nutrition['severely_malnourished'] ?? 0, 'icon' => 'error', 'icon_class' => 'text-accent-red'],
                ['label' => 'Underweight / stunted / wasted', 'value' => $nutrition['malnourished'] ?? 0, 'icon' => 'trending_down', 'icon_class' => 'text-accent-orange'],
                ['label' => 'Normal', 'value' => $nutrition['normal'] ?? 0, 'icon' => 'check_circle', 'icon_class' => 'text-secondary'],
                ['label' => 'Overweight / obese', 'value' => $nutrition['overweight'] ?? 0, 'icon' => 'trending_up', 'icon_class' => 'text-accent-orange'],
                ['label' => 'No weight record', 'value' => $nutrition['no_weight_record'] ?? 0, 'icon' => 'help', 'icon_class' => 'text-text-light/50'],
            ] as $card)
                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $card['label'] }}</p>
                        <span class="material-symbols-outlined {{ $card['icon_class'] }} text-lg">{{ $card['icon'] }}</span>
                    </div>
                    <p class="text-2xl font-bold">{{ number_format((int) $card['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                <h3 class="text-base font-bold mb-4 shrink-0">Nutrition status distribution</h3>
                <div class="h-72 max-w-md mx-auto w-full"><canvas id="bnsNutritionChart"></canvas></div>
            </div>
            <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
                <h3 class="text-base font-bold mb-4 shrink-0">Children needing attention</h3>
                <div class="report-scroll-panel">
                    <table class="w-full min-w-[480px]">
                        <thead>
                            <tr class="border-b border-border-light dark:border-border-dark">
                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Child</th>
                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Age</th>
                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Purok</th>
                                <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $attentionChildren = collect($nutritionChildren)->filter(
                                    fn ($row) => in_array($row['status_group'] ?? '', ['severely_malnourished', 'malnourished', 'overweight'], true)
                                );
                            @endphp
                            @forelse ($attentionChildren as $row)
                                @php
                                    $badgeClass = match ($row['status_group'] ?? '') {
                                        'severely_malnourished' => 'bg-red-100 dark:bg-red-900/40 text-red-700 dark:text-red-200',
                                        'malnourished' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200',
                                        'overweight' => 'bg-orange-100 dark:bg-orange-900/40 text-orange-800 dark:text-orange-200',
                                        default => 'bg-background-light dark:bg-background-dark text-text-light/60',
                                    };
                                @endphp
                                <tr class="border-b border-border-light dark:border-border-dark">
                                    <td class="px-2 py-2 text-sm font-medium">{{ $row['child_name'] }}</td>
                                    <td class="px-2 py-2 text-sm whitespace-nowrap">{{ $row['age_label'] }}</td>
                                    <td class="px-2 py-2 text-sm">{{ $row['purok'] }}</td>
                                    <td class="px-2 py-2 text-sm">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">{{ $row['status_label'] }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-2 py-6 text-sm text-center text-text-light/60">No malnourished or overweight children based on latest weight records.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <section>
        <h2 class="text-lg font-bold mb-3 flex items-center gap-2">
            <span class="material-symbols-outlined text-primary">event_note</span>
            Activity in selected {{ $chartGranularity === 'daily' ? 'week' : ($chartGranularity === 'weekly' ? 'month' : 'period') }}
        </h2>
        <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            @foreach ([
                ['label' => 'Weight entries', 'value' => $periodActivity['weight_entries'] ?? 0],
                ['label' => 'Children weighed', 'value' => $periodActivity['children_weighed'] ?? 0],
                ['label' => 'Avg weight (kg)', 'value' => $periodActivity['avg_weight_kg'] ?? '—'],
                ['label' => 'Deworming given', 'value' => $periodActivity['deworming_given'] ?? 0],
                ['label' => 'Vitamin A given', 'value' => $periodActivity['vitamin_a_given'] ?? 0],
                ['label' => 'Vaccines given', 'value' => $periodActivity['immunizations_given'] ?? 0],
            ] as $card)
                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-4">
                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mb-1">{{ $card['label'] }}</p>
                    <p class="text-2xl font-bold">{{ is_numeric($card['value']) ? number_format((int) $card['value']) : $card['value'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5">
            <h2 class="text-lg font-bold mb-4">
                @if ($chartGranularity === 'daily')
                    Daily activity
                @elseif ($chartGranularity === 'weekly')
                    Weekly activity
                @else
                    Monthly activity
                @endif
            </h2>
            <div class="h-72"><canvas id="bnsActivityChart"></canvas></div>
        </section>
        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5">
            <h2 class="text-lg font-bold mb-4">Immunization dose status</h2>
            <div class="h-72 max-w-sm mx-auto"><canvas id="bnsImmunizationChart"></canvas></div>
            <div class="grid grid-cols-3 gap-2 mt-4 text-center text-sm">
                <div><span class="block font-bold text-secondary">{{ number_format((int) ($immunization['given'] ?? 0)) }}</span>Given</div>
                <div><span class="block font-bold text-accent-red">{{ number_format((int) ($immunization['missed'] ?? 0)) }}</span>Missed</div>
                <div><span class="block font-bold text-accent-orange">{{ number_format((int) ($immunization['pending'] ?? 0)) }}</span>Pending</div>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 items-stretch">
        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
            <h2 class="text-lg font-bold mb-4 shrink-0">Children by purok</h2>
            <div class="h-72"><canvas id="bnsPurokChart"></canvas></div>
        </section>
        <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-5 flex flex-col min-h-0">
            <h2 class="text-lg font-bold mb-4 shrink-0">Recent child activities</h2>
            <div class="report-scroll-panel">
                <table class="w-full min-w-[420px]">
                    <thead>
                        <tr class="border-b border-border-light dark:border-border-dark">
                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Date</th>
                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Type</th>
                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Child</th>
                            <th class="px-2 py-2 text-left text-xs font-semibold uppercase text-text-light/60">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentActivities as $activity)
                            <tr class="border-b border-border-light dark:border-border-dark">
                                <td class="px-2 py-2 text-sm whitespace-nowrap">{{ $activity['date'] }}</td>
                                <td class="px-2 py-2 text-sm">{{ $activity['type'] }}</td>
                                <td class="px-2 py-2 text-sm font-medium">{{ $activity['child_name'] }}</td>
                                <td class="px-2 py-2 text-sm text-text-light/70">{{ $activity['detail'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-2 py-6 text-sm text-center text-text-light/60">No activities recorded for this period yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>
