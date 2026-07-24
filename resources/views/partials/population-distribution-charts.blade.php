@php
    $populationDistribution = $populationDistribution ?? [
        'age_labels' => ['0–17', '18–35', '36–59', '60+'],
        'age_by_gender' => ['all' => [0, 0, 0, 0], 'male' => [0, 0, 0, 0], 'female' => [0, 0, 0, 0]],
        'gender' => ['labels' => ['Male', 'Female'], 'values' => [0, 0], 'colors' => ['#4A90E2', '#EC4899']],
    ];
@endphp

<section class="grid grid-cols-1 xl:grid-cols-2 gap-4">
    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col gap-3 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="text-base font-bold">Age distribution</h2>
                    <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">All household members by age group</p>
                </div>
                <div class="flex flex-wrap gap-1.5 shrink-0" role="group" aria-label="Filter age distribution by gender">
                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-age-gender="all" aria-pressed="true">All</button>
                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-age-gender="male" aria-pressed="false">Male</button>
                    <button type="button" class="dash-period-btn text-xs font-semibold px-3 py-1.5 rounded-full border border-border-light dark:border-border-dark bg-background-light/80 dark:bg-background-dark/80" data-age-gender="female" aria-pressed="false">Female</button>
                </div>
            </div>
            <p id="ageDistributionSubtitle" class="text-[11px] font-medium text-primary/80 dark:text-primary/90">Showing all genders</p>
        </div>
        <div class="dash-chart-wrap">
            <canvas id="ageDistributionChart" aria-label="Age distribution bar chart"></canvas>
        </div>
    </div>

    <div class="dash-card rounded-xl border border-border-light dark:border-border-dark p-4 sm:p-5 shadow-sm">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
            <div>
                <h2 class="text-base font-bold">Gender distribution</h2>
                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Share of household members by gender</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-center">
            <div class="h-56 sm:h-64 max-w-xs mx-auto w-full">
                <canvas id="genderDistributionChart" aria-label="Gender distribution doughnut chart"></canvas>
            </div>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-1">
                @foreach ([
                    ['label' => 'Male', 'tone' => 'bg-primary'],
                    ['label' => 'Female', 'tone' => 'bg-pink-500'],
                ] as $index => $item)
                    @php
                        $value = (int) (($populationDistribution['gender']['values'] ?? [])[$index] ?? 0);
                    @endphp
                    <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light/50 dark:bg-background-dark/50 px-3 py-2.5">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="size-2.5 rounded-full {{ $item['tone'] }}"></span>
                            <span class="text-xs text-text-light/60 dark:text-text-dark/60">{{ $item['label'] }}</span>
                        </div>
                        <p class="text-lg font-bold tabular-nums">{{ number_format($value) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
