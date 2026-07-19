<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Bantay Kalusugan - Resident Requests</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-shell-styles')
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    @media (max-width: 1023px) {
        #requestDetailPanel:not(.mobile-hidden) {
            min-height: 0;
            max-height: calc(100dvh - 10rem);
        }
        #requestDetailPanel.mobile-hidden { display: none; }
        #requestListPanel.mobile-hidden { display: none; }
    }
    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content { height: 100%; overflow: hidden; display: flex; flex-direction: column; }
    .requests-page-title {
        font-size: 1.125rem;
        font-weight: 700;
        line-height: 1.25;
        letter-spacing: -0.01em;
    }
    @media (min-width: 768px) {
        .requests-page-title { font-size: 1.25rem; }
    }
    .request-list-scroll, .chat-scroll, .detail-body-scroll { scrollbar-width: thin; scrollbar-color: #c1c1c1 transparent; }
    .request-list-scroll::-webkit-scrollbar, .chat-scroll::-webkit-scrollbar, .detail-body-scroll::-webkit-scrollbar { width: 6px; }
    .request-list-scroll::-webkit-scrollbar-thumb, .chat-scroll::-webkit-scrollbar-thumb, .detail-body-scroll::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 10px; }
    .request-item.is-active { border-color: rgb(74 144 226 / 1); background: rgb(74 144 226 / 0.08); }
    #requestActionsPanel.is-open {
        display: flex;
    }
    @media (min-width: 1024px) {
        #requestActionsPanel.is-open {
            display: flex;
        }
    }
    .chat-bubble-out { background: linear-gradient(135deg, #4A90E2 0%, #357ABD 100%); }
    .toast-enter { animation: toastIn 0.2s ease-out; }
    .toast-exit { animation: toastOut 0.18s ease-in forwards; }
    @keyframes toastIn { from { opacity: 0; transform: translateY(-8px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes toastOut { from { opacity: 1; } to { opacity: 0; transform: translateY(-8px); } }
    #idViewerModal:not(.hidden) { display: block; }
    #idViewerPanel { width: fit-content; max-width: min(94vw, 680px); }
    #idViewerStage {
        background: linear-gradient(160deg, #1e293b 0%, #0f172a 100%);
        padding: 1.25rem 1.5rem 1.5rem;
        min-width: min(88vw, 560px);
        min-height: min(72vh, 420px);
    }
    #idViewerImage {
        display: block;
        width: auto;
        height: auto;
        max-width: min(88vw, 560px);
        max-height: 78vh;
        margin: 0 auto;
        border-radius: 0.75rem;
        border: 3px solid rgba(255, 255, 255, 0.92);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(255, 255, 255, 0.08);
        object-fit: contain;
    }
</style>
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
                    "accent-orange": "#F5A623",
                    "accent-red": "#D0021B",
                    "background-light": "#F4F4F4",
                    "background-dark": "#1A1A1A",
                    "text-light": "#4A4A4A",
                    "text-dark": "#E0E0E0",
                    "card-light": "#FFFFFF",
                    "card-dark": "#2C2C2C",
                    "border-light": "#EAEAEA",
                    "border-dark": "#3D3D3D",
                },
                fontFamily: { "display": ["Manrope", "sans-serif"] },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
    @include('sidebars.bhw')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 overflow-hidden p-4 md:p-6 lg:p-8 pb-24 lg:pb-6">
        <div class="page-content w-full max-w-7xl mx-auto">
            <div class="flex-1 min-h-0 grid grid-cols-1 lg:grid-cols-[minmax(280px,340px)_minmax(0,1fr)] gap-4">
                {{-- Request list --}}
                <section id="requestListPanel" class="flex flex-col min-h-0 rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-border-light dark:border-border-dark shrink-0 space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h1 class="requests-page-title text-text-light dark:text-text-dark">Resident Requests</h1>
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 dark:bg-amber-900/40 px-3 py-1.5 text-xs font-bold text-amber-800 dark:text-amber-200 shrink-0">
                                <span class="material-symbols-outlined text-sm">pending_actions</span>
                                {{ $pendingRequests->count() }} pending
                            </span>
                        </div>
                        <form method="GET" action="{{ route('bhw.requests') }}">
                        <div class="relative">
                                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-text-light/50 dark:text-text-dark/50 text-xl">search</span>
                                <input class="w-full h-11 pl-10 pr-4 rounded-xl bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" name="search" value="{{ $search }}" maxlength="100" placeholder="Search name or email..." type="search" autocomplete="off"/>
                            </div>
                        </form>
                    </div>
                    <div id="requestList" class="request-list-scroll flex-1 overflow-y-auto p-3 space-y-2">
                        @forelse ($pendingRequests as $pendingRequest)
                            @php
                                $initials = collect(explode(' ', $pendingRequest->full_name))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('');
                                $unreadCount = (int) ($unreadByResident[$pendingRequest->user_id] ?? 0);
                                $genderLabel = $pendingRequest->user?->gender
                                    ? ucfirst(strtolower(trim($pendingRequest->user->gender)))
                                    : 'N/A';
                                $parentRoleLabel = $pendingRequest->parent_role ?: 'N/A';
                                $civilStatusLabel = $pendingRequest->civil_status ?: ($pendingRequest->user?->civil_status ?: 'N/A');
                                $placeOfBirthLabel = $pendingRequest->place_of_birth ?: ($pendingRequest->user?->place_of_birth ?: 'N/A');
                                $educationalAttainmentLabel = $pendingRequest->educational_attainment ?: ($pendingRequest->user?->educational_attainment ?: 'N/A');
                                $spouseDetails = $pendingRequest->spouse_details ?? $pendingRequest->user?->spouse_details ?? [];
                                if (! is_array($spouseDetails)) {
                                    $spouseDetails = [];
                                }
                                $spouseNameLabel = $pendingRequest->spouse_full_name ?: ($pendingRequest->user?->spouse_full_name ?: ($spouseDetails['full_name'] ?? ''));
                                $spouseParentRoleLabel = filled($spouseDetails['parent_role'] ?? null)
                                    ? (string) $spouseDetails['parent_role']
                                    : '';
                                $spouseGenderLabel = filled($spouseDetails['gender'] ?? null)
                                    ? ucfirst(strtolower(trim((string) $spouseDetails['gender'])))
                                    : '';
                                $spouseBirthdateLabel = filled($spouseDetails['birthdate'] ?? null)
                                    ? \Illuminate\Support\Carbon::parse((string) $spouseDetails['birthdate'])->format('M d, Y')
                                    : '';
                                $spousePlaceOfBirthLabel = trim((string) ($spouseDetails['place_of_birth'] ?? ''));
                                $spouseEducationalAttainmentLabel = trim((string) ($spouseDetails['educational_attainment'] ?? ''));
                                $spouseRoleLabel = match ($parentRoleLabel) {
                                    'Father' => 'Wife',
                                    'Mother' => 'Husband',
                                    default => 'Spouse',
                                };
                                $childrenSummary = collect($pendingRequest->household_children ?? [])
                                    ->map(function ($child) {
                                        if (! is_array($child)) {
                                            return null;
                                        }

                                        $fullName = trim((string) ($child['full_name'] ?? $child['name'] ?? ''));
                                        if ($fullName === '') {
                                            return null;
                                        }

                                        $relationship = trim((string) ($child['relationship'] ?? ''));
                                        $gender = trim((string) ($child['gender'] ?? ''));
                                        $birthdate = trim((string) ($child['birthdate'] ?? ''));
                                        $birthdateLabel = $birthdate !== ''
                                            ? \Illuminate\Support\Carbon::parse($birthdate)->format('M d, Y')
                                            : 'N/A';
                                        $ageLabel = $birthdate !== '' ? \App\Support\PersonAge::labelFromBirthdate($birthdate) : null;
                                        $studying = match ($child['currently_studying'] ?? '') {
                                            'yes' => 'Studying',
                                            'infant' => 'Infant',
                                            default => 'Working',
                                        };
                                        $school = trim((string) ($child['school'] ?? ''));
                                        $attainment = trim((string) ($child['educational_attainment'] ?? ''));
                                        $occupation = trim((string) ($child['occupation'] ?? ''));

                                        $parts = array_filter([
                                            $relationship !== '' ? $relationship : null,
                                            $gender !== '' ? $gender : null,
                                            $birthdateLabel,
                                            $ageLabel,
                                            $studying,
                                            ($child['currently_studying'] ?? '') === 'yes'
                                                ? ($school !== '' ? $school : null)
                                                : null,
                                            ($child['currently_studying'] ?? '') === 'no' && $attainment !== '' ? $attainment : null,
                                            ($child['currently_studying'] ?? '') === 'no' && $occupation !== '' ? $occupation : null,
                                        ]);

                                        return $fullName.' — '.implode(' · ', $parts);
                                    })
                                    ->filter()
                                    ->values()
                                    ->all();
                                $seniorsSummary = collect($pendingRequest->household_seniors ?? [])
                                    ->map(function ($senior) {
                                        if (! is_array($senior)) {
                                            return null;
                                        }

                                        $fullName = trim((string) ($senior['full_name'] ?? $senior['name'] ?? ''));
                                        if ($fullName === '') {
                                            return null;
                                        }

                                        $relationship = trim((string) ($senior['relationship'] ?? ''));
                                        $gender = trim((string) ($senior['gender'] ?? ''));
                                        $birthdate = trim((string) ($senior['birthdate'] ?? ''));
                                        $birthdateLabel = $birthdate !== ''
                                            ? \Illuminate\Support\Carbon::parse($birthdate)->format('M d, Y')
                                            : 'N/A';
                                        $ageLabel = $birthdate !== '' ? \App\Support\PersonAge::labelFromBirthdate($birthdate) : null;
                                        $placeOfBirth = trim((string) ($senior['place_of_birth'] ?? ''));
                                        $educationalAttainment = trim((string) ($senior['educational_attainment'] ?? ''));
                                        $occupation = trim((string) ($senior['occupation'] ?? ''));

                                        $parts = array_filter([
                                            $relationship !== '' ? $relationship : null,
                                            $gender !== '' ? $gender : null,
                                            $birthdateLabel,
                                            $ageLabel,
                                            'Senior citizen',
                                            $placeOfBirth !== '' ? $placeOfBirth : null,
                                            $educationalAttainment !== '' ? $educationalAttainment : null,
                                            $occupation !== '' ? $occupation : null,
                                        ]);

                                        return $fullName.' — '.implode(' · ', $parts);
                                    })
                                    ->filter()
                                    ->values()
                                    ->all();
                                $childrenJson = json_encode($childrenSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                $seniorsJson = json_encode($seniorsSummary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
                                $birthdateLabel = $pendingRequest->user?->date_of_birth
                                    ? $pendingRequest->user->date_of_birth->format('M d, Y')
                                    : 'N/A';
                            @endphp
                            <button type="button"
                                class="request-item w-full text-left rounded-xl border border-border-light dark:border-border-dark p-3 transition hover:border-primary/40 hover:bg-primary/5 {{ $unreadCount > 0 ? 'bg-red-50/80 dark:bg-red-900/10 border-red-200/80 dark:border-red-900/30' : '' }}"
                                data-request-id="{{ $pendingRequest->id }}"
                                data-user-id="{{ $pendingRequest->user_id }}"
                                data-unread-count="{{ $unreadCount }}"
                                data-full-name="{{ $pendingRequest->full_name }}"
                                data-email="{{ $pendingRequest->user->email ?? 'No email' }}"
                                data-gender="{{ $genderLabel }}"
                                data-parent-role="{{ $parentRoleLabel }}"
                                data-civil-status="{{ $civilStatusLabel }}"
                                data-place-of-birth="{{ $placeOfBirthLabel }}"
                                data-educational-attainment="{{ $educationalAttainmentLabel }}"
                                data-spouse-name="{{ $spouseNameLabel }}"
                                data-spouse-role="{{ $spouseRoleLabel }}"
                                data-spouse-parent-role="{{ $spouseParentRoleLabel }}"
                                data-spouse-gender="{{ $spouseGenderLabel }}"
                                data-spouse-birthdate="{{ $spouseBirthdateLabel }}"
                                data-spouse-place-of-birth="{{ $spousePlaceOfBirthLabel }}"
                                data-spouse-educational-attainment="{{ $spouseEducationalAttainmentLabel }}"
                                data-children="{{ $childrenJson ?: '[]' }}"
                                data-seniors="{{ $seniorsJson ?: '[]' }}"
                                data-birthdate="{{ $birthdateLabel }}"
                                data-requested="{{ $pendingRequest->created_at->format('M d, Y h:i A') }}"
                                data-purok="{{ $pendingRequest->purok?->name ?? 'Non' }}"
                                data-id-type="{{ $pendingRequest->idTypeLabel() }}"
                                data-id-front="{{ $pendingRequest->id_front_path ? route('verification.id.photo', ['residentRequest' => $pendingRequest, 'side' => 'front']) : '' }}"
                                data-id-back="{{ $pendingRequest->id_back_path ? route('verification.id.photo', ['residentRequest' => $pendingRequest, 'side' => 'back']) : '' }}"
                                data-accept-url="{{ route('bhw.requests.accept', $pendingRequest) }}"
                                data-reject-url="{{ route('bhw.requests.reject', $pendingRequest) }}"
                                data-chat-url="{{ route('bhw.requests.chat', $pendingRequest) }}">
                                <div class="flex items-start gap-3">
                                    <div class="w-11 h-11 rounded-full bg-primary/15 text-primary flex items-center justify-center text-sm font-bold shrink-0">{{ $initials ?: 'R' }}</div>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-sm truncate">{{ $pendingRequest->full_name }}</p>
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 truncate">{{ $pendingRequest->user->email ?? 'No email' }}</p>
                                        <div class="mt-2 flex flex-wrap gap-1.5 items-center">
                                            <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/40 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800 dark:text-amber-200">Pending</span>
                                            <span class="inline-flex items-center rounded-full bg-background-light dark:bg-background-dark px-2 py-0.5 text-[10px] font-medium text-text-light/70 dark:text-text-dark/70">{{ $pendingRequest->purok?->name ?? 'Non' }}</span>
                                            @if ($unreadCount > 0)
                                                <span class="request-new-badge inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-200">New message</span>
                                            @endif
                                        </div>
                    </div>
                                    @if ($unreadCount > 0)
                                        <span class="request-new-dot w-2.5 h-2.5 rounded-full bg-red-500 shrink-0 mt-1" aria-hidden="true"></span>
                @endif
                                </div>
                            </button>
                        @empty
                            <div class="py-12 text-center px-4">
                                <span class="material-symbols-outlined text-5xl text-green-500 mb-3 block">check_circle</span>
                                <p class="font-semibold text-sm">All caught up</p>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1">No pending verification requests in your purok.</p>
                            </div>
                        @endforelse
                    </div>
                </section>

                {{-- Detail + chat --}}
                <section id="requestDetailPanel" class="flex flex-col min-h-0 rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-sm overflow-hidden mobile-hidden lg:flex">
                    <div id="detailEmptyState" class="flex-1 flex flex-col items-center justify-center p-8 text-center">
                        <div class="w-16 h-16 rounded-2xl bg-primary/10 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-3xl text-primary">forum</span>
                        </div>
                        <h2 class="text-lg font-bold">Select a request</h2>
                        <p class="text-sm text-text-light/60 dark:text-text-dark/60 mt-2 max-w-sm">Choose a resident to review their ID, open verification chat, and accept or reject.</p>
                    </div>

                    <div id="detailContent" class="hidden flex-1 min-h-0 flex flex-col overflow-hidden">
                        <div class="p-4 md:p-5 border-b border-border-light dark:border-border-dark shrink-0">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start">
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <button type="button" id="mobileBackBtn" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark shrink-0">
                                        <span class="material-symbols-outlined">arrow_back</span>
                                    </button>
                                    <div class="w-12 h-12 rounded-full bg-primary/15 text-primary flex items-center justify-center text-base font-bold shrink-0" id="detailAvatar">R</div>
                                    <div class="min-w-0 flex-1">
                                        <h2 class="text-base sm:text-lg font-bold break-words leading-snug" id="detailName">Resident Name</h2>
                                        <p class="text-sm text-text-light/60 dark:text-text-dark/60 break-words" id="detailEmail">email@example.com</p>
                                    </div>
                                    <button
                                        type="button"
                                        id="requestActionsToggle"
                                        class="lg:hidden inline-flex items-center justify-center h-10 w-10 rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark shrink-0"
                                        aria-expanded="false"
                                        aria-controls="requestActionsPanel"
                                        aria-label="Show accept and reject actions"
                                    >
                                        <span class="material-symbols-outlined" id="requestActionsToggleIcon">expand_more</span>
                                    </button>
                                </div>
                                <div id="requestActionsPanel" class="hidden lg:flex gap-2 w-full lg:w-auto shrink-0">
                                    <button type="button" id="openAcceptModalBtn" class="inline-flex flex-1 lg:flex-none items-center justify-center gap-1.5 h-10 lg:h-9 px-3 rounded-lg bg-primary text-white text-sm lg:text-xs font-bold hover:opacity-90">
                                        <span class="material-symbols-outlined text-base lg:text-sm">check</span> Accept
                                    </button>
                                    <form id="rejectForm" method="POST" action="" class="flex-1 lg:flex-none">
                                        @csrf
                                        <button type="button" id="openRejectModalBtn" class="inline-flex w-full items-center justify-center gap-1.5 h-10 lg:h-9 px-3 rounded-lg bg-accent-red text-white text-sm lg:text-xs font-bold hover:opacity-90">
                                            <span class="material-symbols-outlined text-base lg:text-sm">close</span> Reject
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="detail-body-scroll flex-1 min-h-0 overflow-y-auto overscroll-contain p-4 md:p-5 space-y-3">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Parent Role</p>
                                    <p class="text-sm font-semibold mt-0.5" id="detailParentRole">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Marital Status</p>
                                    <p class="text-sm font-semibold mt-0.5" id="detailCivilStatus">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Place of Birth</p>
                                    <p class="text-sm font-semibold mt-0.5 break-words" id="detailPlaceOfBirth">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Educational Attainment</p>
                                    <p class="text-sm font-semibold mt-0.5 break-words" id="detailEducationalAttainment">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Gender</p>
                                    <p class="text-sm font-semibold mt-0.5" id="detailGender">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Birthdate</p>
                                    <p class="text-sm font-semibold mt-0.5" id="detailBirthdate">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Purok</p>
                                    <p class="text-sm font-semibold mt-0.5 break-words" id="detailPurok">—</p>
                                </div>
                                <div class="rounded-xl bg-background-light dark:bg-background-dark p-3">
                                    <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Requested</p>
                                    <p class="text-sm font-semibold mt-0.5" id="detailRequested">—</p>
                                </div>
                            </div>

                            <div id="detailSpouseWrap" class="hidden rounded-xl border border-border-light dark:border-border-dark p-3">
                                <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50" id="detailSpouseRoleLabel">Spouse</p>
                                <p class="text-sm font-semibold mt-0.5 break-words" id="detailSpouseName">—</p>
                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words" id="detailSpouseMeta">—</p>
                            </div>

                            <div id="detailChildrenWrap" class="hidden rounded-xl border border-border-light dark:border-border-dark p-3">
                                <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Children</p>
                                <ul id="detailChildrenList" class="mt-2 space-y-2 text-sm font-medium"></ul>
                            </div>
                            <div id="detailSeniorsWrap" class="hidden rounded-xl border border-border-light dark:border-border-dark p-3">
                                <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Elderly in household</p>
                                <ul id="detailSeniorsList" class="mt-2 space-y-2 text-sm font-medium"></ul>
                            </div>

                            <div class="rounded-xl border border-border-light dark:border-border-dark p-3">
                                <p class="text-[10px] uppercase tracking-wide text-text-light/50 dark:text-text-dark/50">Valid ID</p>
                                <p class="text-sm font-semibold mt-0.5 break-words" id="detailIdType">—</p>
                                <div class="mt-2 flex flex-wrap gap-2" id="detailIdLinks"></div>
                            </div>

                            <a id="openChatLink" href="#" class="flex items-center justify-between gap-3 rounded-xl bg-primary text-white hover:opacity-95 p-4 transition shadow-md shadow-primary/20 group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                                        <span class="material-symbols-outlined text-white">forum</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold">Open Verification Chat</p>
                                        <p class="text-xs text-white/80 break-words" id="openChatSubtitle">Message this resident about their ID verification</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span id="openChatNewBadge" class="hidden inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-white text-red-600">New</span>
                                    <span class="material-symbols-outlined text-white">arrow_forward</span>
                                </div>
                            </a>
                                            </div>
                    </div>
                </section>
            </div>
        </div>
    </main>

    @include('partials.bhw-mobile-nav')
</div>

<div id="idViewerModal" class="fixed inset-0 z-[100] hidden" role="dialog" aria-modal="true" aria-labelledby="idViewerTitle">
    <div id="idViewerBackdrop" class="absolute inset-0 bg-black/70"></div>
    <div class="relative z-10 flex h-full w-full items-center justify-center p-4 pointer-events-none">
        <div id="idViewerPanel" class="pointer-events-auto flex flex-col rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 shrink-0">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <span class="material-symbols-outlined text-xl">badge</span>
                    </div>
                    <div class="min-w-0">
                        <h2 id="idViewerTitle" class="text-base font-bold truncate">Valid ID</h2>
                        <p id="idViewerSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 truncate mt-0.5">Front side</p>
                    </div>
                </div>
                <button type="button" id="closeIdViewerBtn" class="p-2 rounded-lg hover:bg-background-light dark:hover:bg-background-dark shrink-0" aria-label="Close">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>
            <div id="idViewerTabs" class="hidden shrink-0 gap-2 px-5 py-3 border-b border-border-light dark:border-border-dark bg-background-light/40 dark:bg-background-dark/40">
                <button type="button" data-id-side="front" class="id-viewer-tab inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold border border-border-light dark:border-border-dark">Front</button>
                <button type="button" data-id-side="back" class="id-viewer-tab inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold border border-border-light dark:border-border-dark">Back</button>
            </div>
            <div id="idViewerStage" class="flex items-center justify-center">
                <img id="idViewerImage" src="" alt="Valid ID photo"/>
            </div>
        </div>
    </div>
</div>

<div id="acceptConfirmModal" class="fixed inset-0 z-[110] hidden" role="dialog" aria-modal="true" aria-labelledby="acceptConfirmTitle">
    <div id="acceptConfirmBackdrop" class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 flex h-full w-full items-center justify-center p-4 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-md rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl overflow-hidden">
            <form id="acceptForm" method="POST" action="">
                @csrf
                <div class="flex items-center gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 bg-primary/5">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-primary/15 text-primary">
                        <span class="material-symbols-outlined text-xl">family_restroom</span>
                    </div>
                    <div>
                        <h2 id="acceptConfirmTitle" class="text-base font-bold">Accept request</h2>
                        <p id="acceptConfirmSubtitle" class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5">Assign a family number before approving.</p>
                    </div>
                </div>
                <div class="px-5 py-5 space-y-3">
                    <p id="acceptConfirmMessage" class="text-sm text-text-light/80 dark:text-text-dark/80 leading-relaxed">
                        Enter the family number for this household. This is required before the request can be accepted.
                    </p>
                    <div>
                        <label for="acceptFamilyNumber" class="block text-sm font-semibold mb-1.5">Family number <span class="text-accent-red">*</span></label>
                        <input
                            type="text"
                            name="family_number"
                            id="acceptFamilyNumber"
                            required
                            maxlength="50"
                            autocomplete="off"
                            placeholder="e.g. F-001"
                            class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3 text-sm focus:ring-primary focus:border-primary @error('family_number') border-accent-red @enderror"
                            value="{{ old('family_number') }}"
                        />
                        @error('family_number')
                            <p class="text-xs text-accent-red mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 px-5 py-4 border-t border-border-light dark:border-border-dark bg-background-light/40 dark:bg-background-dark/40">
                    <button type="button" id="acceptConfirmCancelBtn" class="h-10 px-4 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                        Cancel
                    </button>
                    <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-bold hover:opacity-90">
                        Accept request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="rejectConfirmModal" class="fixed inset-0 z-[110] hidden" role="dialog" aria-modal="true" aria-labelledby="rejectConfirmTitle">
    <div id="rejectConfirmBackdrop" class="absolute inset-0 bg-black/60"></div>
    <div class="relative z-10 flex h-full w-full items-center justify-center p-4 pointer-events-none">
        <div class="pointer-events-auto w-full max-w-sm rounded-2xl bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark shadow-2xl overflow-hidden">
            <div class="flex items-center gap-3 border-b border-border-light dark:border-border-dark px-5 py-4 bg-red-50 dark:bg-red-950/30">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-accent-red/15 text-accent-red">
                    <span class="material-symbols-outlined text-xl">warning</span>
                </div>
                <h2 id="rejectConfirmTitle" class="text-base font-bold">Reject request?</h2>
            </div>
            <div class="px-5 py-5">
                <p id="rejectConfirmMessage" class="text-sm text-text-light/80 dark:text-text-dark/80 leading-relaxed">
                    Are you sure you want to reject this verification request?
                </p>
            </div>
            <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2 px-5 py-4 border-t border-border-light dark:border-border-dark bg-background-light/40 dark:bg-background-dark/40">
                <button type="button" id="rejectConfirmCancelBtn" class="h-10 px-4 rounded-lg border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-background-light dark:hover:bg-background-dark">
                    Cancel
                </button>
                <button type="button" id="rejectConfirmSubmitBtn" class="h-10 px-4 rounded-lg bg-accent-red text-white text-sm font-bold hover:opacity-90">
                    Reject request
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const requestListPanel = document.getElementById('requestListPanel');
    const requestDetailPanel = document.getElementById('requestDetailPanel');
    const detailEmptyState = document.getElementById('detailEmptyState');
    const detailContent = document.getElementById('detailContent');
    const acceptForm = document.getElementById('acceptForm');
    const openAcceptModalBtn = document.getElementById('openAcceptModalBtn');
    const acceptConfirmModal = document.getElementById('acceptConfirmModal');
    const acceptConfirmBackdrop = document.getElementById('acceptConfirmBackdrop');
    const acceptConfirmMessage = document.getElementById('acceptConfirmMessage');
    const acceptConfirmSubtitle = document.getElementById('acceptConfirmSubtitle');
    const acceptConfirmCancelBtn = document.getElementById('acceptConfirmCancelBtn');
    const acceptFamilyNumber = document.getElementById('acceptFamilyNumber');
    const rejectForm = document.getElementById('rejectForm');
    const openRejectModalBtn = document.getElementById('openRejectModalBtn');
    const rejectConfirmModal = document.getElementById('rejectConfirmModal');
    const rejectConfirmBackdrop = document.getElementById('rejectConfirmBackdrop');
    const rejectConfirmMessage = document.getElementById('rejectConfirmMessage');
    const rejectConfirmCancelBtn = document.getElementById('rejectConfirmCancelBtn');
    const rejectConfirmSubmitBtn = document.getElementById('rejectConfirmSubmitBtn');
    const detailName = document.getElementById('detailName');
    const mobileBackBtn = document.getElementById('mobileBackBtn');
    const requestActionsToggle = document.getElementById('requestActionsToggle');
    const requestActionsPanel = document.getElementById('requestActionsPanel');
    const requestActionsToggleIcon = document.getElementById('requestActionsToggleIcon');
    const openChatLink = document.getElementById('openChatLink');
    const openChatNewBadge = document.getElementById('openChatNewBadge');
    const openChatSubtitle = document.getElementById('openChatSubtitle');
    const idViewerModal = document.getElementById('idViewerModal');
    const idViewerBackdrop = document.getElementById('idViewerBackdrop');
    const idViewerPanel = document.getElementById('idViewerPanel');
    const idViewerImage = document.getElementById('idViewerImage');
    const idViewerTitle = document.getElementById('idViewerTitle');
    const idViewerSubtitle = document.getElementById('idViewerSubtitle');
    const idViewerTabs = document.getElementById('idViewerTabs');
    const closeIdViewerBtn = document.getElementById('closeIdViewerBtn');

    function setRequestActionsOpen(open) {
        if (!requestActionsPanel || !requestActionsToggle) {
            return;
        }

        requestActionsPanel.classList.toggle('is-open', open);
        requestActionsToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        requestActionsToggle.setAttribute(
            'aria-label',
            open ? 'Hide accept and reject actions' : 'Show accept and reject actions'
        );

        if (requestActionsToggleIcon) {
            requestActionsToggleIcon.textContent = open ? 'expand_less' : 'expand_more';
        }
    }

    requestActionsToggle?.addEventListener('click', () => {
        const isOpen = requestActionsToggle.getAttribute('aria-expanded') === 'true';
        setRequestActionsOpen(!isOpen);
    });

    let activeIdPhotos = { front: '', back: '' };

    function setActiveIdTab(side) {
        document.querySelectorAll('.id-viewer-tab').forEach((tab) => {
            const isActive = tab.dataset.idSide === side;
            tab.classList.toggle('bg-primary', isActive);
            tab.classList.toggle('text-white', isActive);
            tab.classList.toggle('border-primary', isActive);
            tab.classList.toggle('bg-card-light', !isActive);
            tab.classList.toggle('dark:bg-card-dark', !isActive);
        });
        const url = activeIdPhotos[side] || '';
        idViewerImage.removeAttribute('style');
        idViewerImage.src = url;
        idViewerSubtitle.textContent = side === 'front' ? 'Front side' : 'Back side';
    }

    function fitIdViewerImage() {
        if (!idViewerImage?.naturalWidth) return;
        const maxW = Math.min(window.innerWidth * 0.88, 560);
        const maxH = window.innerHeight * 0.78;
        const scale = Math.min(maxW / idViewerImage.naturalWidth, maxH / idViewerImage.naturalHeight);
        idViewerImage.style.width = Math.round(idViewerImage.naturalWidth * scale) + 'px';
        idViewerImage.style.height = Math.round(idViewerImage.naturalHeight * scale) + 'px';
    }

    idViewerImage?.addEventListener('load', fitIdViewerImage);

    function openIdViewer(residentName, idType, frontUrl, backUrl, preferredSide = 'front') {
        activeIdPhotos = { front: frontUrl || '', back: backUrl || '' };
        const hasFront = Boolean(frontUrl);
        const hasBack = Boolean(backUrl);
        const startSide = preferredSide === 'back' && hasBack ? 'back' : (hasFront ? 'front' : 'back');

        if (idViewerTitle) idViewerTitle.textContent = `${residentName} — ${idType}`;
        if (idViewerTabs) {
            idViewerTabs.classList.toggle('hidden', !(hasFront && hasBack));
            idViewerTabs.classList.toggle('flex', hasFront && hasBack);
        }

        setActiveIdTab(startSide);
        idViewerModal?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeIdViewer() {
        idViewerModal?.classList.add('hidden');
        if (idViewerImage) {
            idViewerImage.removeAttribute('style');
            idViewerImage.src = '';
        }
        document.body.style.overflow = '';
    }

    closeIdViewerBtn?.addEventListener('click', closeIdViewer);

    function openAcceptConfirmModal() {
        if (!acceptForm?.action) {
            return;
        }

        const residentName = detailName?.textContent?.trim() || 'this resident';
        if (acceptConfirmMessage) {
            acceptConfirmMessage.textContent = `Enter the family number for ${residentName}'s household. This is required before the request can be accepted.`;
        }
        if (acceptConfirmSubtitle) {
            acceptConfirmSubtitle.textContent = residentName;
        }
        if (acceptFamilyNumber) {
            acceptFamilyNumber.value = acceptFamilyNumber.defaultValue || '';
        }

        acceptConfirmModal?.classList.remove('hidden');
        window.setTimeout(() => acceptFamilyNumber?.focus(), 50);
    }

    function closeAcceptConfirmModal() {
        acceptConfirmModal?.classList.add('hidden');
    }

    openAcceptModalBtn?.addEventListener('click', openAcceptConfirmModal);
    acceptConfirmCancelBtn?.addEventListener('click', closeAcceptConfirmModal);
    acceptConfirmBackdrop?.addEventListener('click', closeAcceptConfirmModal);
    acceptForm?.addEventListener('submit', closeAcceptConfirmModal);

    function openRejectConfirmModal() {
        if (!rejectForm?.action) return;
        const residentName = detailName?.textContent?.trim() || 'this resident';
        if (rejectConfirmMessage) {
            rejectConfirmMessage.textContent = `Are you sure you want to reject ${residentName}'s verification request? This will notify the resident and they will need to submit again.`;
        }
        rejectConfirmModal?.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        rejectConfirmSubmitBtn?.focus();
    }

    function closeRejectConfirmModal() {
        rejectConfirmModal?.classList.add('hidden');
        if (!idViewerModal || idViewerModal.classList.contains('hidden')) {
            document.body.style.overflow = '';
        }
    }

    openRejectModalBtn?.addEventListener('click', openRejectConfirmModal);
    rejectConfirmCancelBtn?.addEventListener('click', closeRejectConfirmModal);
    rejectConfirmBackdrop?.addEventListener('click', closeRejectConfirmModal);
    rejectConfirmSubmitBtn?.addEventListener('click', () => {
        if (rejectForm?.action) {
            rejectForm.submit();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && acceptConfirmModal && !acceptConfirmModal.classList.contains('hidden')) {
            closeAcceptConfirmModal();
            return;
        }
        if (event.key === 'Escape' && rejectConfirmModal && !rejectConfirmModal.classList.contains('hidden')) {
            closeRejectConfirmModal();
            return;
        }
        if (event.key === 'Escape' && idViewerModal && !idViewerModal.classList.contains('hidden')) {
            closeIdViewer();
        }
    });
    document.querySelectorAll('.id-viewer-tab').forEach((tab) => {
        tab.addEventListener('click', () => setActiveIdTab(tab.dataset.idSide || 'front'));
    });

    function bindIdViewButtons(container, residentName, idType, frontUrl, backUrl) {
        container.querySelectorAll('.view-id-btn').forEach((btn) => {
            btn.addEventListener('click', (event) => {
                event.preventDefault();
                openIdViewer(residentName, idType, frontUrl, backUrl, btn.dataset.side || 'front');
            });
        });
    }

    function initialsFromName(name) {
        return (name || '').split(' ').filter(Boolean).slice(0, 2).map(p => p[0]?.toUpperCase() || '').join('') || 'R';
    }

    function selectRequest(button) {
        document.querySelectorAll('.request-item').forEach(el => el.classList.remove('is-active'));
        button.classList.add('is-active');

        document.getElementById('detailAvatar').textContent = initialsFromName(button.dataset.fullName);
        document.getElementById('detailName').textContent = button.dataset.fullName;
        document.getElementById('detailEmail').textContent = button.dataset.email;
        document.getElementById('detailGender').textContent = button.dataset.gender;
        document.getElementById('detailParentRole').textContent = button.dataset.parentRole || '—';
        document.getElementById('detailCivilStatus').textContent = button.dataset.civilStatus || '—';
        document.getElementById('detailPlaceOfBirth').textContent = button.dataset.placeOfBirth || '—';
        const detailEducationalAttainment = document.getElementById('detailEducationalAttainment');
        if (detailEducationalAttainment) {
            detailEducationalAttainment.textContent = button.dataset.educationalAttainment || '—';
        }
        document.getElementById('detailBirthdate').textContent = button.dataset.birthdate;
        document.getElementById('detailPurok').textContent = button.dataset.purok;
        document.getElementById('detailRequested').textContent = button.dataset.requested;
        document.getElementById('detailIdType').textContent = button.dataset.idType;

        const detailSpouseWrap = document.getElementById('detailSpouseWrap');
        const spouseName = (button.dataset.spouseName || '').trim();
        const spouseRole = (button.dataset.spouseRole || 'Spouse').trim();
        const spouseParentRole = (button.dataset.spouseParentRole || '').trim();
        const spouseGender = (button.dataset.spouseGender || '').trim();
        const spouseBirthdate = (button.dataset.spouseBirthdate || '').trim();
        const spousePlaceOfBirth = (button.dataset.spousePlaceOfBirth || '').trim();
        const spouseEducationalAttainment = (button.dataset.spouseEducationalAttainment || '').trim();
        const detailSpouseRoleLabel = document.getElementById('detailSpouseRoleLabel');
        const detailSpouseName = document.getElementById('detailSpouseName');
        const detailSpouseMeta = document.getElementById('detailSpouseMeta');

        if (detailSpouseRoleLabel) {
            detailSpouseRoleLabel.textContent = spouseRole;
        }
        if (detailSpouseName) {
            detailSpouseName.textContent = spouseName || '—';
        }
        if (detailSpouseMeta) {
            const metaParts = [spouseParentRole, spouseGender, spouseBirthdate, spousePlaceOfBirth, spouseEducationalAttainment].filter(Boolean);
            detailSpouseMeta.textContent = metaParts.length > 0 ? metaParts.join(' · ') : '';
            detailSpouseMeta.classList.toggle('hidden', metaParts.length === 0);
        }
        detailSpouseWrap?.classList.toggle('hidden', spouseName === '');

        const detailChildrenWrap = document.getElementById('detailChildrenWrap');
        const detailChildrenList = document.getElementById('detailChildrenList');
        let childrenItems = [];

        try {
            childrenItems = JSON.parse(button.dataset.children || '[]');
        } catch (error) {
            childrenItems = [];
        }

        if (detailChildrenList) {
            detailChildrenList.innerHTML = '';
            childrenItems.forEach((item) => {
                const li = document.createElement('li');
                li.className = 'break-words leading-snug';
                li.textContent = item;
                detailChildrenList.appendChild(li);
            });
        }

        detailChildrenWrap?.classList.toggle('hidden', !Array.isArray(childrenItems) || childrenItems.length === 0);

        const detailSeniorsWrap = document.getElementById('detailSeniorsWrap');
        const detailSeniorsList = document.getElementById('detailSeniorsList');
        let seniorsItems = [];

        try {
            seniorsItems = JSON.parse(button.dataset.seniors || '[]');
        } catch (error) {
            seniorsItems = [];
        }

        if (detailSeniorsList) {
            detailSeniorsList.innerHTML = '';
            seniorsItems.forEach((item) => {
                const li = document.createElement('li');
                li.className = 'break-words leading-snug';
                li.textContent = item;
                detailSeniorsList.appendChild(li);
            });
        }

        detailSeniorsWrap?.classList.toggle('hidden', !Array.isArray(seniorsItems) || seniorsItems.length === 0);

        const idLinks = document.getElementById('detailIdLinks');
        idLinks.innerHTML = '';
        const residentName = button.dataset.fullName;
        const idType = button.dataset.idType;
        const frontUrl = button.dataset.idFront || '';
        const backUrl = button.dataset.idBack || '';

        if (frontUrl) {
            idLinks.innerHTML += `<button type="button" class="view-id-btn inline-flex items-center gap-1 rounded-lg border border-primary/30 bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary hover:bg-primary/15" data-side="front"><span class="material-symbols-outlined text-sm">image</span> Front ID</button>`;
        }
        if (backUrl) {
            idLinks.innerHTML += `<button type="button" class="view-id-btn inline-flex items-center gap-1 rounded-lg border border-primary/30 bg-primary/10 px-3 py-1.5 text-xs font-semibold text-primary hover:bg-primary/15" data-side="back"><span class="material-symbols-outlined text-sm">image</span> Back ID</button>`;
        }
        if (!frontUrl && !backUrl) {
            idLinks.innerHTML = '<span class="text-xs text-text-light/60 dark:text-text-dark/60">No ID photos uploaded</span>';
        } else {
            bindIdViewButtons(idLinks, residentName, idType, frontUrl, backUrl);
        }

        acceptForm.action = button.dataset.acceptUrl;
        if (acceptFamilyNumber) {
            acceptFamilyNumber.value = '';
        }
        rejectForm.action = button.dataset.rejectUrl;
        if (openChatLink) openChatLink.href = button.dataset.chatUrl;

        const unreadCount = Number(button.dataset.unreadCount || 0);
        if (openChatNewBadge) {
            openChatNewBadge.classList.toggle('hidden', unreadCount <= 0);
        }
        if (openChatSubtitle) {
            openChatSubtitle.textContent = unreadCount > 0
                ? `${unreadCount} new message${unreadCount === 1 ? '' : 's'} from this resident`
                : 'Message this resident about their ID verification';
        }

        detailEmptyState.classList.add('hidden');
        detailContent.classList.remove('hidden');
        detailContent.classList.add('flex');
        setRequestActionsOpen(false);

        if (window.innerWidth < 1024) {
            requestListPanel.classList.add('mobile-hidden');
            requestDetailPanel.classList.remove('mobile-hidden');
        }
    }

    document.querySelectorAll('.request-item').forEach(button => {
        button.addEventListener('click', () => selectRequest(button));
    });

    mobileBackBtn?.addEventListener('click', () => {
        setRequestActionsOpen(false);
        requestDetailPanel.classList.add('mobile-hidden');
        requestListPanel.classList.remove('mobile-hidden');
    });

    const firstRequest = document.querySelector('.request-item');
    if (firstRequest && window.innerWidth >= 1024) {
        selectRequest(firstRequest);
    } else if (window.innerWidth >= 1024) {
        requestDetailPanel.classList.remove('mobile-hidden');
    }

    @if (session('reopen_accept_request_id') || $errors->has('family_number'))
    (function reopenAcceptModalAfterValidation() {
        const reopenId = @json(session('reopen_accept_request_id'));
        const reopenButton = reopenId
            ? document.querySelector(`.request-item[data-request-id="${reopenId}"]`)
            : document.querySelector('.request-item.is-active');

        if (!reopenButton) {
            return;
        }

        selectRequest(reopenButton);
        openAcceptConfirmModal();
    })();
    @endif
</script>
@if (session('status'))
<div id="flashStatus" data-auto-dismiss="3500" class="fixed top-4 right-4 z-[60] w-[min(92vw,24rem)] rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800 shadow-lg toast-enter dark:bg-green-900/30 dark:border-green-700 dark:text-green-200" role="status" aria-live="polite">
    {{ session('status') }}
</div>
<script>
    document.querySelectorAll('[data-auto-dismiss]').forEach((toast) => {
        const timeout = Number(toast.getAttribute('data-auto-dismiss')) || 3500;

        window.setTimeout(() => {
            toast.classList.remove('toast-enter');
            toast.classList.add('toast-exit');
            window.setTimeout(() => toast.remove(), 180);
        }, timeout);
    });
</script>
@endif
</body>
</html>
