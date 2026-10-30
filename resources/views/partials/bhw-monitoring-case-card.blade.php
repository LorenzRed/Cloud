@php
    $isActiveCase = (bool) $monitoringCase->is_active;
    $typeTone = $monitoringCase->isPregnant()
        ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/35 dark:text-pink-200'
        : ($monitoringCase->isSenior()
            ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/35 dark:text-amber-200'
            : 'bg-sky-100 text-sky-800 dark:bg-sky-900/35 dark:text-sky-200');
    $residentRequest = $monitoringCase->residentRequest;
    $residentUser = $residentRequest?->user;
    $residentAddressParts = array_values(array_filter([
        trim((string) ($residentRequest?->purok?->name ?? $residentUser?->purok?->name ?? '')),
        trim((string) ($residentRequest?->barangay ?? $residentUser?->barangay ?? '')),
        trim((string) ($residentUser?->province ?? 'Oriental Mindoro')),
    ], static fn (string $part): bool => $part !== '' && strcasecmp($part, 'N/A') !== 0 && strcasecmp($part, 'Non') !== 0));
    $residentInfo = implode(', ', $residentAddressParts);
    $residentAge = $residentRequest?->age ?? $residentUser?->age;
    $residentGender = trim((string) ($residentUser?->gender ?? ''));
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
                @if ($residentInfo !== '' || $residentAge !== null || $residentGender !== '')
                    <p class="mnt-case-meta">
                        {{ $residentInfo !== '' ? $residentInfo : 'Resident information' }}
                        @if ($residentAge !== null)
                            · Age {{ $residentAge }}
                        @endif
                        @if ($residentGender !== '')
                            · {{ ucfirst(strtolower($residentGender)) }}
                        @endif
                    </p>
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
                @if ($monitoringCase->isPregnant())
                    <div class="mnt-period-box">
                        <p>First period: <span class="font-bold">{{ $monitoringCase->first_period_date?->format('M d, Y') ?? 'Not recorded' }}</span></p>
                        <p>Last period: <span class="font-bold">{{ $monitoringCase->last_period_date?->format('M d, Y') ?? 'Not recorded' }}</span></p>
                    </div>
                @endif

                @php
                    $firstVisitDate = $monitoringCase->visits->first()?->visit_date?->format('M d, Y');
                    $lastVisitDate = $monitoringCase->visits->last()?->visit_date?->format('M d, Y');
                @endphp
                <details class="group">
                    <summary class="mnt-visit-heading flex cursor-pointer items-center justify-between gap-2 select-none">
                        <span>
                            <span class="block">{{ $isActiveCase ? 'Visit history' : 'Completed visit archive' }}</span>
                            <span class="block text-[11px] font-normal opacity-70">
                                {{ $firstVisitDate ?: 'No date' }} to {{ $lastVisitDate ?: 'No date' }}
                            </span>
                        </span>
                        <span class="material-symbols-outlined text-[20px] transition-transform duration-200 group-open:rotate-180">expand_more</span>
                    </summary>

                    <div class="mt-2">
                @foreach ($monitoringCase->visits as $visitIndex => $visit)
                    @php
                        $resolved = $visit->resolvedVitals($monitoringCase->visits);
                        $visitLabel = (! $isActiveCase && $loop->last)
                            ? 'Last Visit'
                            : $resolved['visit_label'];
                        $bmiCategory = \App\Models\HouseholdMonitoringVisit::bmiCategory($resolved['bmi_value']);
                        $bpAssessment = \App\Models\HouseholdMonitoringVisit::bloodPressureAssessment($resolved['blood_pressure']);
                        $visitNotes = $visit->notes === 'Initial monitoring baseline' && filled($monitoringCase->issue_notes)
                            ? $monitoringCase->issue_notes
                            : $visit->notes;
                        $visitNotes = str_replace(["\\r\\n", "\\n", "\\r"], "\n", (string) $visitNotes);
                        $vitals = collect([
                            filled($resolved['blood_pressure']) ? ['label' => 'BP', 'value' => $resolved['blood_pressure']] : null,
                            $resolved['weight_kg'] !== null ? ['label' => 'Weight', 'value' => $resolved['weight_kg'].' kg'] : null,
                            $resolved['height_cm'] !== null ? ['label' => 'Height', 'value' => $resolved['height_cm'].' cm'] : null,
                            $resolved['bmi_value'] !== null ? ['label' => 'BMI', 'value' => (string) $resolved['bmi_value'].($bmiCategory ? ' · '.$bmiCategory : '')] : null,
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

                        @if ($bmiCategory || $bpAssessment)
                            <div class="mt-2.5 grid gap-2 {{ $bmiCategory && $bpAssessment ? 'md:grid-cols-2' : '' }}">
                                @if ($bmiCategory)
                                    <div class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 dark:border-sky-800/50 dark:bg-sky-900/20">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide text-sky-700 dark:text-sky-300">BMI assessment</p>
                                        <p class="mt-0.5 text-xs text-sky-900 dark:text-sky-100">{{ $bmiCategory }}</p>
                                    </div>
                                @endif
                                @if ($bpAssessment)
                                    <div class="rounded-lg border px-3 py-2" style="border-color: {{ $bpAssessment['color'] }}66; background-color: {{ $bpAssessment['color'] }}12;">
                                        <p class="text-[10px] font-semibold uppercase tracking-wide" style="color: {{ $bpAssessment['color'] }}">{{ $bpAssessment['label'] }}</p>
                                        <p class="mt-0.5 text-xs" style="color: {{ $bpAssessment['color'] }}">{{ $bpAssessment['explanation'] }}</p>
                                        <p class="mt-1 text-xs" style="color: {{ $bpAssessment['color'] }}">{{ $bpAssessment['recommendation'] }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if (filled($visitNotes))
                            <details class="mt-2 border-t border-border-light dark:border-border-dark pt-2">
                                <summary class="cursor-pointer text-xs font-semibold text-primary">View notes</summary>
                                <p class="mt-2 mnt-visit-notes whitespace-pre-line">{{ $visitNotes }}</p>
                            </details>
                        @endif
                    </div>
                @endforeach
                    </div>
                </details>
            </div>
        @else
            <p class="mnt-empty-visits">No visits recorded yet{{ $isActiveCase ? ' — tap Add Visit to record vitals.' : '.' }}</p>
        @endif
    </div>
</article>
