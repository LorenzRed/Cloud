@php
    $isActiveCase = (bool) $monitoringCase->is_active;
    $typeTone = $monitoringCase->isPregnant()
        ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/35 dark:text-pink-200'
        : ($monitoringCase->isSenior()
            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/35 dark:text-amber-200'
            : 'bg-sky-100 text-sky-800 dark:bg-sky-900/35 dark:text-sky-200');
@endphp

<article class="mnt-case-card {{ $isActiveCase ? 'mnt-case-card--active' : 'mnt-case-card--completed' }}">
    <div class="mnt-case-banner">
        <span class="material-symbols-outlined" aria-hidden="true">{{ $isActiveCase ? 'monitor_heart' : 'task_alt' }}</span>
        @if ($isActiveCase)
            <span>Active monitoring</span>
        @else
            <span>
                Completed
                @if ($monitoringCase->completed_at)
                    · {{ $monitoringCase->completed_at->format('M d, Y') }}
                @endif
            </span>
        @endif
    </div>

    <div class="mnt-case-body">
        <div class="mnt-case-head">
            <div class="min-w-0 flex-1">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $typeTone }}">
                        {{ $monitoringCase->typeLabel() }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-bold {{ $isActiveCase ? 'bg-primary/15 text-primary' : 'bg-green-100 text-green-800 dark:bg-green-900/35 dark:text-green-200' }}">
                        {{ $isActiveCase ? 'Ongoing' : 'Done' }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-semibold bg-background-light dark:bg-background-dark text-text-light/70 dark:text-text-dark/70">
                        {{ $monitoringCase->visits->count() }} visit{{ $monitoringCase->visits->count() === 1 ? '' : 's' }}
                    </span>
                </div>
                <h5 class="mnt-case-title">{{ $monitoringCase->member_name }}</h5>
                @if ($monitoringCase->isPregnant() && ($monitoringCase->first_period_date || $monitoringCase->last_period_date))
                    <p class="mnt-case-meta">
                        Period {{ $monitoringCase->first_period_date?->format('M d, Y') ?? 'N/A' }}
                        → {{ $monitoringCase->last_period_date?->format('M d, Y') ?? 'Present' }}
                    </p>
                @endif
                @if ($monitoringCase->issue_notes)
                    <p class="mnt-case-meta">{{ $monitoringCase->issue_notes }}</p>
                @endif
            </div>

            @if ($isActiveCase)
                <div class="mnt-case-actions">
                    <button
                        type="button"
                        class="open-add-visit-modal mnt-btn mnt-btn--visit"
                        data-case-id="{{ $monitoringCase->id }}"
                        data-action="{{ route('bhw.monitoring-cases.visits.store', $monitoringCase) }}"
                        data-member-name="{{ $monitoringCase->member_name }}"
                        data-is-pregnant="{{ $monitoringCase->isPregnant() ? '1' : '0' }}"
                        data-is-senior="{{ $monitoringCase->isSenior() ? '1' : '0' }}"
                        data-first-period-date="{{ $monitoringCase->first_period_date?->toDateString() }}"
                        data-last-period-date="{{ $monitoringCase->last_period_date?->toDateString() }}"
                    >
                        <span class="material-symbols-outlined text-[18px]">add</span>
                        Add Visit
                    </button>
                    <button
                        type="button"
                        class="open-complete-monitoring-modal mnt-btn mnt-btn--complete"
                        data-complete-url="{{ route('bhw.monitoring-cases.complete', $monitoringCase) }}"
                        data-member-name="{{ $monitoringCase->member_name }}"
                        data-type-label="{{ $monitoringCase->typeLabel() }}"
                    >
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        Complete
                    </button>
                </div>
            @endif
        </div>

        @if ($monitoringCase->visits->isNotEmpty())
            <div class="mnt-visit-list">
                <div class="mnt-visit-heading">
                    <p>{{ $isActiveCase ? 'Visit history' : 'Completed visit archive' }}</p>
                </div>

                @if ($monitoringCase->isPregnant())
                    <div class="mnt-period-box">
                        <p>First period: <span class="font-bold">{{ $monitoringCase->first_period_date?->format('M d, Y') ?? 'Not recorded' }}</span></p>
                        <p>Last period: <span class="font-bold">{{ $monitoringCase->last_period_date?->format('M d, Y') ?? 'Not recorded' }}</span></p>
                    </div>
                @endif

                @foreach ($monitoringCase->visits as $visitIndex => $visit)
                    @php
                        $resolved = $visit->resolvedVitals($monitoringCase->visits);
                        $visitLabel = (! $isActiveCase && $loop->last)
                            ? 'Last Visit'
                            : $resolved['visit_label'];
                        $vitals = collect([
                            filled($resolved['blood_pressure']) ? ['label' => 'BP', 'value' => $resolved['blood_pressure']] : null,
                            $resolved['weight_kg'] !== null ? ['label' => 'Weight', 'value' => $resolved['weight_kg'].' kg'] : null,
                            $resolved['height_cm'] !== null ? ['label' => 'Height', 'value' => $resolved['height_cm'].' cm'] : null,
                            $resolved['bmi_value'] !== null ? ['label' => 'BMI', 'value' => (string) $resolved['bmi_value']] : null,
                            $resolved['muac_cm'] !== null ? ['label' => 'MUAC', 'value' => $resolved['muac_cm'].' cm'] : null,
                            filled($resolved['anti_tetanus']) ? ['label' => 'Anti-TT', 'value' => (string) $resolved['anti_tetanus']] : null,
                        ])->filter()->values();
                    @endphp
                    <div class="mnt-visit-card">
                        <div class="mnt-visit-top">
                            <div class="min-w-0">
                                <p class="mnt-visit-date">{{ $visit->visit_date?->format('M d, Y') ?: 'No date' }}</p>
                                <p class="mnt-visit-ago">{{ $visit->created_at?->diffForHumans() }}</p>
                            </div>
                            <span class="mnt-visit-chip">{{ $visitLabel }}</span>
                        </div>

                        @if ($vitals->isNotEmpty())
                            <div class="mnt-vital-grid">
                                @foreach ($vitals as $vital)
                                    <div class="mnt-vital-pill">
                                        <span class="mnt-vital-pill__label">{{ $vital['label'] }}</span>
                                        <span class="mnt-vital-pill__value">{{ $vital['value'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($visit->notes)
                            <p class="mnt-visit-notes">{{ $visit->notes }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <p class="mnt-empty-visits">No visits recorded yet{{ $isActiveCase ? ' — tap Add Visit to record vitals.' : '.' }}</p>
        @endif
    </div>
</article>
