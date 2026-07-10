<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bns-viewport')
<title>Bantay Kalusugan - Vitamin A Distribution</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bns-shell-styles')
@include('bns.partials.record-list-styles')
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2", "secondary": "#50E3C2", "accent-orange": "#F5A623", "accent-red": "#D0021B",
                    "background-light": "#F4F4F4", "background-dark": "#1A1A1A", "text-light": "#4A4A4A", "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF", "card-dark": "#2C2C2C", "border-light": "#EAEAEA", "border-dark": "#3D3D3D",
                },
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.bns')

<div id="bnsContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bns-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-0 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="record-page-content">
            <div class="record-page-layout sm:py-0">
                @php
                    $records = collect($records ?? []);
                    $purokOptions = $records->pluck('purok')->unique()->sort()->values();
                    $totalRecords = $records->count();
                @endphp

                <div class="record-panel bg-card-light dark:bg-card-dark rounded-none sm:rounded-xl shadow-none sm:shadow-sm border-0 sm:border border-border-light dark:border-border-dark p-0 sm:p-4 flex-1 min-h-0">
                    @include('bns.partials.record-list-toolbar', [
                        'pageTitle' => 'Vitamin A distribution',
                        'exportAllRoute' => route('bns.vitamin-distribution.export'),
                        'searchPlaceholder' => 'Search child, family, or notes...',
                        'purokOptions' => $purokOptions,
                    ])

                    <div class="record-table-wrap mt-3 sm:mt-3">
                        <table class="w-full md:min-w-[800px]" id="recordsTable">
                            <thead class="hidden md:table-header-group">
                                <tr class="border-b border-border-light dark:border-border-dark">
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Child</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Current age</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Purok</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Date given</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Age when given</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-text-light/70 dark:text-text-dark/70">Notes</th>
                                </tr>
                            </thead>
                            <tbody id="recordsTableBody">
                                @forelse ($records as $row)
                                    @php
                                        $csvPayload = json_encode([
                                            'child_name' => $row['child_name'],
                                            'age_label' => $row['age_label'],
                                            'gender' => $row['gender'],
                                            'purok' => $row['purok'],
                                            'family_number' => $row['family_number'],
                                            'given_on_label' => $row['given_on_label'],
                                            'age_in_months_label' => $row['age_in_months_label'],
                                            'notes' => $row['notes'],
                                        ], JSON_HEX_APOS | JSON_HEX_QUOT);
                                    @endphp
                                    <tr
                                        class="record-row border-b border-border-light dark:border-border-dark hover:bg-background-light dark:hover:bg-background-dark"
                                        data-search="{{ strtolower(implode(' ', [$row['child_name'], $row['notes'], $row['family_number'], $row['purok']])) }}"
                                        data-purok="{{ $row['purok'] }}"
                                        data-date="{{ $row['given_on'] }}"
                                        data-csv="{{ $csvPayload }}"
                                    >
                                        @php
                                            $nameParts = preg_split('/\s+/', trim($row['child_name'])) ?: [];
                                            $initials = strtoupper(substr($nameParts[0] ?? 'C', 0, 1).substr($nameParts[count($nameParts) - 1] ?? 'C', 0, 1));
                                        @endphp

                                        <td class="record-mobile-cell md:hidden" colspan="6">
                                            <button type="button" class="record-mobile-toggle" aria-expanded="false" aria-label="Show details for {{ $row['child_name'] }}">
                                                <div class="size-9 rounded-full bg-primary/10 text-primary text-xs font-bold flex items-center justify-center shrink-0">{{ $initials }}</div>
                                                <span class="text-sm font-semibold text-text-light dark:text-text-dark truncate min-w-0 flex-1">{{ $row['child_name'] }}</span>
                                                <span class="record-mobile-status">{{ $row['given_on_label'] }}</span>
                                                <span class="material-symbols-outlined record-chevron" aria-hidden="true">expand_more</span>
                                            </button>
                                            <div class="record-mobile-extra">
                                                Family {{ $row['family_number'] }} · {{ $row['gender'] }}
                                            </div>
                                            <div class="record-row-details">
                                                <div class="record-detail-item">
                                                    <span class="record-detail-label">Current age</span>
                                                    <span class="record-detail-value font-medium text-text-light dark:text-text-dark">{{ $row['age_label'] }}</span>
                                                </div>
                                                <div class="record-detail-item">
                                                    <span class="record-detail-label">Purok</span>
                                                    <span class="record-detail-value">{{ $row['purok'] }}</span>
                                                </div>
                                                <div class="record-detail-item">
                                                    <span class="record-detail-label">Date given</span>
                                                    <span class="record-detail-value font-medium text-text-light dark:text-text-dark">{{ $row['given_on_label'] }}</span>
                                                </div>
                                                <div class="record-detail-item">
                                                    <span class="record-detail-label">Age when given</span>
                                                    <span class="record-detail-value">{{ $row['age_in_months_label'] }}</span>
                                                </div>
                                                <div class="record-detail-item">
                                                    <span class="record-detail-label">Notes</span>
                                                    <span class="record-detail-value text-text-light/70 dark:text-text-dark/70">{{ $row['notes'] ?: '—' }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="record-desktop-cell px-4 py-3 text-sm font-medium" data-label="Child">
                                            <div>
                                                <p class="font-semibold text-text-light dark:text-text-dark">{{ $row['child_name'] }}</p>
                                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Family {{ $row['family_number'] }} · {{ $row['gender'] }}</p>
                                            </div>
                                        </td>
                                        <td class="record-desktop-cell px-4 py-3 text-sm whitespace-nowrap" data-label="Current age">
                                            <span class="font-medium">{{ $row['age_label'] }}</span>
                                        </td>
                                        <td class="record-desktop-cell px-4 py-3 text-sm" data-label="Purok">{{ $row['purok'] }}</td>
                                        <td class="record-desktop-cell px-4 py-3 text-sm whitespace-nowrap" data-label="Date given">
                                            <span class="font-medium">{{ $row['given_on_label'] }}</span>
                                        </td>
                                        <td class="record-desktop-cell px-4 py-3 text-sm whitespace-nowrap" data-label="Age when given">{{ $row['age_in_months_label'] }}</td>
                                        <td class="record-desktop-cell px-4 py-3 text-sm text-text-light/70 dark:text-text-dark/70" data-label="Notes">
                                            <span class="break-words">{{ $row['notes'] }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr id="emptyRow">
                                        <td colspan="6" class="px-4 py-10 text-center">
                                            <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">nutrition</span>
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">No Vitamin A distribution records yet. Log Vitamin A from a child's profile in Monitoring.</p>
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="noResultsRow" class="hidden">
                                    <td colspan="6" class="px-4 py-10 text-center">
                                        <span class="material-symbols-outlined text-4xl text-text-light/50 dark:text-text-dark/50 mb-2 block">search_off</span>
                                        <p class="text-sm text-text-light/70 dark:text-text-dark/70">No records match your search or filters.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="record-list-footer sm:px-0">
                        <p class="text-xs sm:text-sm text-text-light/70 dark:text-text-dark/70">
                            Showing <span id="statVisible" class="font-semibold text-text-light dark:text-text-dark">{{ number_format($totalRecords) }}</span>
                            of <span class="font-semibold text-text-light dark:text-text-dark">{{ number_format($totalRecords) }}</span> records
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('partials.bns-mobile-nav')
</div>

@include('bns.partials.record-list-scripts', [
    'csvFilename' => 'vitamin-a-distribution-'.now()->format('Y-m-d').'.csv',
    'csvHeaders' => ['Child Name', 'Current Age', 'Gender', 'Purok', 'Family Number', 'Date Given', 'Age When Given', 'Notes'],
    'csvKeys' => ['child_name', 'age_label', 'gender', 'purok', 'family_number', 'given_on_label', 'age_in_months_label', 'notes'],
    'hasDateFilter' => true,
])
</body>
</html>
