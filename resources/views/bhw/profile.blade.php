<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - Resident Profile</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<style>
    .material-symbols-outlined {
        font-variation-settings:
            'FILL' 0,
            'wght' 400,
            'GRAD' 0,
            'opsz' 24;
        vertical-align: middle;
    }

    .tab-content {
        display: none;
    }

    .tab-content.active {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .profile-page-layout {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        height: 100%;
    }

    .profile-page-header {
        flex-shrink: 0;
    }

    .profile-layout {
        flex: 1;
        min-height: 0;
        align-items: stretch;
    }

    @media (min-width: 1024px) {
        .profile-layout {
            display: grid;
            grid-template-columns: minmax(260px, 300px) minmax(0, 1fr);
            grid-template-rows: minmax(0, 1fr);
        }
    }

    @media (max-width: 1023px) {
        .profile-layout {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
        }

        .profile-layout > .min-w-0.flex.flex-col {
            flex: 1;
            min-height: 0;
        }
    }

    .profile-sidebar-card,
    .profile-main-panel {
        min-width: 0;
    }

    .profile-main-panel {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        overflow: hidden;
    }

    .profile-tab-shell {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .profile-tab-body {
        flex: 1;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .profile-tab-nav {
        display: flex;
        gap: 0.375rem;
        padding: 0.375rem;
        margin: 0.75rem 1rem 0;
        border-radius: 0.75rem;
        background: rgba(244, 244, 244, 0.9);
        overflow-x: auto;
        scrollbar-width: none;
        flex-shrink: 0;
    }

    .dark .profile-tab-nav {
        background: rgba(26, 26, 26, 0.9);
    }

    .profile-tab-nav::-webkit-scrollbar {
        display: none;
    }

    .profile-tab-link {
        flex: 1;
        min-width: max-content;
        text-align: center;
        padding: 0.5rem 0.875rem;
        border-radius: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 600;
        color: rgba(74, 74, 74, 0.7);
        border: 1px solid transparent;
        transition: background 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
        white-space: nowrap;
    }

    .dark .profile-tab-link {
        color: rgba(224, 224, 224, 0.7);
    }

    .profile-tab-link.active {
        color: #4A90E2;
        background: #FFFFFF;
        border-color: #EAEAEA;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

    .dark .profile-tab-link.active {
        background: #2C2C2C;
        border-color: #3D3D3D;
    }

    .profile-tab-toolbar {
        flex-shrink: 0;
        padding-bottom: 0.75rem;
    }

    .profile-tab-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        overflow-x: hidden;
        -webkit-overflow-scrolling: touch;
        padding-bottom: max(1.5rem, env(safe-area-inset-bottom, 0px));
        scroll-padding-bottom: max(1.5rem, env(safe-area-inset-bottom, 0px));
    }

    .profile-tab-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .profile-tab-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .profile-tab-scroll::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .summary-card {
        border-radius: 0.875rem;
        border: 1px solid #EAEAEA;
        background: #F9FAFB;
        padding: 0.875rem 1rem;
    }

    .dark .summary-card {
        border-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.5);
    }

    .summary-card-label {
        font-size: 0.625rem;
        font-weight: 700;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: rgba(74, 74, 74, 0.55);
    }

    .dark .summary-card-label {
        color: rgba(224, 224, 224, 0.55);
    }

    .summary-meta-chip {
        display: inline-flex;
        align-items: center;
        padding: 0.15rem 0.5rem;
        border-radius: 9999px;
        font-size: 0.6875rem;
        font-weight: 600;
        background: rgba(74, 144, 226, 0.1);
        color: #4A90E2;
    }

    .household-member-row {
        display: flex;
        gap: 0.75rem;
        padding: 0.625rem 0;
        border-bottom: 1px solid #EAEAEA;
    }

    .dark .household-member-row {
        border-bottom-color: #3D3D3D;
    }

    .household-member-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .sidebar-info-grid {
        display: grid;
        gap: 0.625rem;
    }

    @media (min-width: 640px) and (max-width: 1023px) {
        .sidebar-info-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    .sidebar-info-item {
        border-radius: 0.75rem;
        border: 1px solid #EAEAEA;
        background: #F9FAFB;
        padding: 0.75rem;
    }

    .dark .sidebar-info-item {
        border-color: #3D3D3D;
        background: rgba(26, 26, 26, 0.45);
    }

    .profile-detail-row {
        width: 100%;
        text-align: left;
    }

    @media (max-width: 768px) {
        #sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease-in-out;
            position: fixed;
            z-index: 40;
            height: 100vh;
        }

        #sidebar.active {
            transform: translateX(0);
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 30;
        }

        #overlay.active {
            display: block;
        }

        main {
            width: 100%;
        }
    }

    .page-content {
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }

    .profile-chat-glow {
        box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.35), 0 4px 14px rgba(74, 144, 226, 0.24);
        animation: profile-chat-pulse 2.4s ease-in-out infinite;
    }

    @keyframes profile-chat-pulse {
        0%, 100% {
            box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.35), 0 4px 14px rgba(74, 144, 226, 0.24);
        }

        50% {
            box-shadow: 0 0 0 1px rgba(74, 144, 226, 0.5), 0 4px 18px rgba(74, 144, 226, 0.38);
        }
    }

    .profile-chat-compact .material-symbols-outlined {
        font-size: 1rem;
        line-height: 1;
    }

    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
        overflow: hidden;
    }

    .main-container {
        height: 100vh;
        display: flex;
    }

    main {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        min-height: 0;
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
                fontFamily: {
                    "display": ["Manrope", "sans-serif"]
                },
            },
        },
    }
</script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@php
    $residentUser = $resident->user;
    $residentName = $resident->full_name ?: ($resident->user->name ?? 'Resident');
    $residentGender = $resident->user->gender ?? 'N/A';
    $residentAge = $resident->age ?? ($resident->user->age ?? null);

    $residentMunicipality = trim((string) ($residentUser?->municipality ?? '')) ?: 'Victoria';
    $residentProvince = trim((string) ($residentUser?->province ?? '')) ?: 'Oriental Mindoro';
    $residentPurok = trim((string) ($resident->purok?->name ?? $residentUser?->purok?->name ?? ''));
    if (strcasecmp($residentPurok, 'Non') === 0) {
        $residentPurok = '';
    }
    $residentBarangay = trim((string) ($resident->barangay ?? $residentUser?->barangay ?? ''));
    if (strcasecmp($residentBarangay, 'N/A') === 0) {
        $residentBarangay = '';
    }
    $residentMapAddress = trim((string) ($resident->address ?? ''));

    $residentAddressParts = [];
    if ($residentMapAddress !== '') {
        $residentAddressParts[] = $residentMapAddress;
    }
    if ($residentPurok !== '') {
        $residentAddressParts[] = $residentPurok;
    }
    if ($residentBarangay !== '') {
        $residentAddressParts[] = $residentBarangay;
    }
    $residentAddressParts[] = $residentMunicipality;
    $residentAddressParts[] = $residentProvince;

    $residentAddress = implode(', ', $residentAddressParts);

    $residentContact = $resident->contact ?: ($resident->user->contact_number ?? null);
    $familyNumber = trim((string) ($resident->family_number ?? $residentUser?->family_number ?? ''));
    $familyNumberLabel = $familyNumber !== '' ? $familyNumber : 'Not assigned';
    $residentProfilePicturePath = $residentUser?->profile_picture_path;

    $parentRoleLabel = trim((string) ($resident->parent_role ?? ''));
    $civilStatusLabel = trim((string) ($resident->civil_status ?? $residentUser?->civil_status ?? ''));
    $placeOfBirthLabel = trim((string) ($resident->place_of_birth ?? $residentUser?->place_of_birth ?? ''));
    $educationalAttainmentLabel = trim((string) ($resident->educational_attainment ?? $residentUser?->educational_attainment ?? ''));
    $residentMetaParts = array_values(array_filter([
        $residentAge !== null ? $residentAge.' yrs old' : null,
        $residentGender !== 'N/A' ? $residentGender : null,
        $parentRoleLabel !== '' ? $parentRoleLabel : null,
        $civilStatusLabel !== '' ? $civilStatusLabel : null,
        $placeOfBirthLabel !== '' ? $placeOfBirthLabel : null,
        $educationalAttainmentLabel !== '' ? $educationalAttainmentLabel : null,
    ]));

    $spouseDetails = $resident->spouse_details ?? $residentUser?->spouse_details ?? [];
    if (! is_array($spouseDetails)) {
        $spouseDetails = [];
    }

    $spouseName = trim((string) (
        $resident->spouse_full_name
        ?: ($residentUser?->spouse_full_name ?? '')
        ?: ($spouseDetails['full_name'] ?? '')
    ));

    $spouseRoleLabel = match ($parentRoleLabel) {
        'Father' => 'Wife',
        'Mother' => 'Husband',
        default => 'Spouse',
    };

    $spouseParentRoleLabel = trim((string) ($spouseDetails['parent_role'] ?? ''));
    $spouseGenderLabel = filled($spouseDetails['gender'] ?? null)
        ? ucfirst(strtolower(trim((string) $spouseDetails['gender'])))
        : '';
    $spouseBirthdateLabel = filled($spouseDetails['birthdate'] ?? null)
        ? \Illuminate\Support\Carbon::parse((string) $spouseDetails['birthdate'])->format('M d, Y')
        : '';
    $spousePlaceOfBirthLabel = trim((string) ($spouseDetails['place_of_birth'] ?? ''));
    $spouseEducationalAttainmentLabel = trim((string) ($spouseDetails['educational_attainment'] ?? ''));
    $spouseMetaParts = array_values(array_filter([
        $spouseParentRoleLabel !== '' ? $spouseParentRoleLabel : null,
        $spouseGenderLabel !== '' ? $spouseGenderLabel : null,
        $spouseBirthdateLabel !== '' ? $spouseBirthdateLabel : null,
        $spousePlaceOfBirthLabel !== '' ? $spousePlaceOfBirthLabel : null,
        $spouseEducationalAttainmentLabel !== '' ? $spouseEducationalAttainmentLabel : null,
    ]));

    $householdChildren = collect($resident->household_children ?? [])
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

            $metaParts = array_values(array_filter([
                $relationship !== '' ? $relationship : null,
                $gender !== '' ? $gender : null,
                $birthdateLabel,
                $ageLabel,
                $studying,
                ($child['currently_studying'] ?? '') === 'yes' ? ($school !== '' ? $school : null) : null,
                ($child['currently_studying'] ?? '') === 'no' && $attainment !== '' ? $attainment : null,
                ($child['currently_studying'] ?? '') === 'no' && $occupation !== '' ? $occupation : null,
            ]));

            return [
                'full_name' => $fullName,
                'meta' => implode(' · ', $metaParts),
            ];
        })
        ->filter()
        ->values();

    $householdSeniors = collect($resident->household_seniors ?? [])
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

            $metaParts = array_values(array_filter([
                $relationship !== '' ? $relationship : null,
                $gender !== '' ? $gender : null,
                $birthdateLabel,
                $ageLabel,
                'Senior citizen',
                $placeOfBirth !== '' ? $placeOfBirth : null,
                $educationalAttainment !== '' ? $educationalAttainment : null,
                $occupation !== '' ? $occupation : null,
            ]));

            return [
                'full_name' => $fullName,
                'meta' => implode(' · ', $metaParts),
            ];
        })
        ->filter()
        ->values();

    $monitoringMemberOptions = collect([
        [
            'name' => $residentName,
            'source' => 'resident',
            'index' => null,
            'label' => $residentName.' (Resident)',
        ],
    ]);

    if ($spouseName !== '') {
        $monitoringMemberOptions->push([
            'name' => $spouseName,
            'source' => 'spouse',
            'index' => null,
            'label' => $spouseName.' (Spouse)',
        ]);
    }

    foreach (($resident->household_seniors ?? []) as $seniorIndex => $seniorRow) {
        if (! is_array($seniorRow)) {
            continue;
        }

        $seniorOptionName = trim((string) ($seniorRow['full_name'] ?? $seniorRow['name'] ?? ''));
        if ($seniorOptionName === '') {
            continue;
        }

        $monitoringMemberOptions->push([
            'name' => $seniorOptionName,
            'source' => 'household_senior',
            'index' => $seniorIndex,
            'label' => $seniorOptionName.' (Senior in household)',
        ]);
    }

    $monitoringMemberOptions = $monitoringMemberOptions->values();
@endphp

<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>

    <div id="overlay" class=""></div>

    @include('sidebars.bhw')

    <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-hidden md:ml-0 min-w-0 max-md:pt-16 min-h-0">
        <div class="page-content">
            <div class="profile-page-layout max-w-7xl mx-auto w-full min-w-0">
                <div class="profile-page-header flex flex-col md:flex-row justify-between items-start md:items-center mb-3 lg:mb-5 gap-3 shrink-0">
                    <div class="min-w-0">
                        <nav class="text-sm text-text-light/70 dark:text-text-dark/70 mb-2">
                            <a class="hover:underline" href="{{ route('bhw.residents') }}">Residents</a>
                            <span class="mx-2">/</span>
                            <span class="break-words">{{ $residentName }}</span>
                        </nav>
                        <h1 class="text-2xl lg:text-3xl font-bold text-text-light dark:text-text-dark">Resident Profile</h1>
                    </div>
                </div>

                <div class="profile-layout flex-1 min-h-0">
                    <div class="min-w-0 shrink-0 lg:min-h-0 lg:flex lg:flex-col">
                        <div class="profile-sidebar-card bg-card-light dark:bg-card-dark rounded-xl p-4 sm:p-5 shadow-sm border border-border-light dark:border-border-dark lg:h-full flex flex-col">
                            <div class="flex flex-col sm:flex-row lg:flex-col items-center gap-2 sm:gap-2.5 lg:text-center">
                                @if ($residentProfilePicturePath && $residentUser)
                                    <img
                                        src="{{ route('profile.picture', ['user' => $residentUser->id, 'v' => optional($residentUser->updated_at)->timestamp]) }}"
                                        alt="Resident profile picture"
                                        class="w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] lg:w-20 lg:h-20 rounded-full object-cover border-2 border-primary/20 shrink-0"
                                    />
                                @else
                                    <div class="w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] lg:w-20 lg:h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center text-xl lg:text-2xl font-bold shrink-0">
                                        {{ strtoupper(substr($residentName, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1 text-center sm:text-left lg:text-center">
                                    <p class="text-base lg:text-lg font-bold leading-tight break-words text-text-light dark:text-text-dark">{{ $residentName }}</p>
                                    @if (! empty($residentMetaParts))
                                        <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-0.5 line-clamp-2">{{ implode(' · ', array_slice($residentMetaParts, 0, 3)) }}</p>
                                    @endif
                                    <a href="{{ route('bhw.residents.chat', $resident) }}" class="profile-chat-compact profile-chat-glow inline-flex items-center justify-center gap-1.5 mt-2 px-3.5 py-1.5 rounded-full bg-primary text-white text-sm font-semibold hover:opacity-95 transition">
                                        <span class="material-symbols-outlined text-base">forum</span>
                                        <span>Chat</span>
                                        @if (($residentChatUnreadCount ?? 0) > 0)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-white text-red-600 leading-none">{{ $residentChatUnreadCount }}</span>
                                        @endif
                                    </a>
                                </div>
                            </div>
                            <div class="sidebar-info-grid mt-3 lg:mt-4 w-full">
                                <div class="sidebar-info-item">
                                    <p class="summary-card-label">Family Number</p>
                                    <p class="text-sm font-semibold mt-1 break-words text-text-light dark:text-text-dark">{{ $familyNumberLabel }}</p>
                                </div>
                                <div class="sidebar-info-item">
                                    <p class="summary-card-label">Contact</p>
                                    <p class="text-sm font-semibold mt-1 break-words text-text-light dark:text-text-dark">{{ $residentContact ?: 'N/A' }}</p>
                                </div>
                                <div class="sidebar-info-item sm:col-span-3 lg:col-span-1">
                                    <p class="summary-card-label">Address</p>
                                    <p class="text-sm mt-1 break-words leading-relaxed text-text-light dark:text-text-dark">{{ $residentAddress ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="min-w-0 flex flex-col min-h-0 flex-1 lg:min-h-0">
                        <div class="profile-main-panel bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark profile-tab-shell">
                            <nav aria-label="Tabs" class="profile-tab-nav">
                                <a aria-current="page" class="profile-tab-link tab-link active" data-tab="overview" href="#">Overview</a>
                                <a class="profile-tab-link tab-link" data-tab="medical-history" href="#">Transaction</a>
                                <a class="profile-tab-link tab-link" data-tab="maintenance" href="#">Maintenance</a>
                                <a class="profile-tab-link tab-link" data-tab="consultations" href="#">Consultations</a>
                            </nav>

                            <div class="p-4 sm:p-5 flex-1 min-h-0 profile-tab-body">
                                <div id="overview" class="tab-content active min-h-0 flex-1">
                                    <div class="profile-tab-scroll space-y-4">
                                        <div class="summary-card">
                                            <div class="flex items-start gap-3">
                                                <span class="material-symbols-outlined text-primary text-xl shrink-0 mt-0.5">person</span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="summary-card-label">Resident</p>
                                                    <p class="text-sm font-semibold mt-1 break-words text-text-light dark:text-text-dark">{{ $residentName }}</p>
                                                    @if (! empty($residentMetaParts))
                                                        <div class="flex flex-wrap gap-1.5 mt-2">
                                                            @foreach ($residentMetaParts as $metaPart)
                                                                <span class="summary-meta-chip">{{ $metaPart }}</span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                    @if ($spouseName !== '' || $householdChildren->isNotEmpty() || $householdSeniors->isNotEmpty())
                                        <div class="summary-card">
                                            <div class="flex items-center gap-2 mb-3">
                                                <span class="material-symbols-outlined text-primary text-lg">groups</span>
                                                <h5 class="text-sm font-bold text-text-light dark:text-text-dark">Household Members</h5>
                                            </div>
                                            <div class="space-y-0">

                                            @if ($spouseName !== '')
                                                <div class="household-member-row">
                                                    <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50 text-lg shrink-0">favorite</span>
                                                    <div class="min-w-0">
                                                        <p class="summary-card-label">{{ $spouseRoleLabel }}</p>
                                                        <p class="text-sm font-semibold mt-0.5 break-words text-text-light dark:text-text-dark">{{ $spouseName }}</p>
                                                        @if (! empty($spouseMetaParts))
                                                            <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words leading-relaxed">{{ implode(' · ', $spouseMetaParts) }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif

                                            @if ($householdChildren->isNotEmpty())
                                                @foreach ($householdChildren as $child)
                                                    <div class="household-member-row">
                                                        <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50 text-lg shrink-0">child_care</span>
                                                        <div class="min-w-0">
                                                            <p class="summary-card-label">Child</p>
                                                            <p class="text-sm font-semibold mt-0.5 break-words text-text-light dark:text-text-dark">{{ $child['full_name'] }}</p>
                                                            @if ($child['meta'] !== '')
                                                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words leading-relaxed">{{ $child['meta'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif

                                            @if ($householdSeniors->isNotEmpty())
                                                @foreach ($householdSeniors as $senior)
                                                    <div class="household-member-row">
                                                        <span class="material-symbols-outlined text-text-light/50 dark:text-text-dark/50 text-lg shrink-0">elderly</span>
                                                        <div class="min-w-0">
                                                            <p class="summary-card-label">Elderly in household</p>
                                                            <p class="text-sm font-semibold mt-0.5 break-words text-text-light dark:text-text-dark">{{ $senior['full_name'] }}</p>
                                                            @if ($senior['meta'] !== '')
                                                                <p class="text-xs text-text-light/60 dark:text-text-dark/60 mt-1 break-words leading-relaxed">{{ $senior['meta'] }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                            </div>
                                        </div>
                                    @endif
                                    </div>
                                </div>

                                <div id="medical-history" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Transaction</h4>
                                            <button type="button" id="toggleMedicalHistoryForm" class="inline-flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap shrink-0">
                                                <span class="material-symbols-outlined text-base">add</span>
                                                <span>Add Transaction</span>
                                            </button>
                                        </div>

                                        @if (session('status'))
                                            <div class="mt-3 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                                {{ session('status') }}
                                            </div>
                                        @endif

                                        @if ($errors->any())
                                            <div class="mt-3 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                                                <ul class="list-disc pl-5 space-y-1">
                                                    @foreach ($errors->all() as $error)
                                                        <li>{{ $error }}</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </div>

                                    <div id="medicalHistoryFormWrapper" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
                                        <div class="w-full max-w-2xl rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 shadow-xl max-h-[90vh] overflow-y-auto">
                                            <div class="flex items-center justify-between gap-3 mb-4">
                                                <div>
                                                    <h5 class="text-lg font-bold">Add Transaction</h5>
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Record resident details and medicine pieces dispensed.</p>
                                                </div>
                                                <button type="button" id="closeMedicalHistoryForm" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" aria-label="Close transaction form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('bhw.residents.medical-history.store', $resident) }}" class="space-y-4">
                                            @csrf
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="age">Age</label>
                                                    <input id="age" name="age" value="{{ old('age') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="e.g., 58" type="number" min="0" max="150" required />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="transaction_date">Date</label>
                                                    <input id="transaction_date" name="transaction_date" value="{{ old('transaction_date', now()->toDateString()) }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" type="date" required />
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="gender">Gender</label>
                                                    <select id="gender" name="gender" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" required>
                                                        <option value="" disabled {{ old('gender') ? '' : 'selected' }}>Select gender</option>
                                                        <option value="M" {{ old('gender') === 'M' ? 'selected' : '' }}>M</option>
                                                        <option value="F" {{ old('gender') === 'F' ? 'selected' : '' }}>F</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="philhealth_number">PhilHealth Number</label>
                                                    <input id="philhealth_number" name="philhealth_number" value="{{ old('philhealth_number') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="PhilHealth number" type="text" required />
                                                </div>
                                            </div>

                                            <div class="rounded-lg border border-border-light dark:border-border-dark p-3 sm:p-4 bg-card-light dark:bg-card-dark space-y-3">
                                                <p class="text-sm font-semibold text-text-light dark:text-text-dark">Medicines (pcs taken)</p>
                                                @foreach (\App\Models\ResidentTransaction::MEDICATIONS as $field => $label)
                                                    <div>
                                                        <label class="block text-sm font-medium mb-1" for="{{ $field }}">{{ $label }}</label>
                                                        <input
                                                            id="{{ $field }}"
                                                            name="{{ $field }}"
                                                            value="{{ old($field) }}"
                                                            class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                            placeholder="How many pcs"
                                                            type="number"
                                                            min="0"
                                                            max="999"
                                                        />
                                                    </div>
                                                @endforeach
                                            </div>

                                            <div class="flex justify-end">
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                                    <span class="material-symbols-outlined text-base">save</span>
                                                    <span>Save and Notify User</span>
                                                </button>
                                            </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="profile-tab-scroll">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @forelse ($resident->transactions as $transaction)
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Transaction</p>
                                                        <p class="font-semibold mt-1 text-text-light dark:text-text-dark">{{ $transaction->transaction_date?->format('M d, Y') }}</p>
                                                    </div>
                                                    <span class="inline-flex items-center rounded-full bg-primary/10 px-2.5 py-1 text-xs font-semibold text-primary shrink-0">
                                                        {{ $transaction->gender }}
                                                    </span>
                                                </div>

                                                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                                                    <div>
                                                        <p class="text-text-light/60 dark:text-text-dark/60">Age</p>
                                                        <p class="font-semibold">{{ $transaction->age }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-text-light/60 dark:text-text-dark/60">PhilHealth</p>
                                                        <p class="font-semibold break-words">{{ $transaction->philhealth_number }}</p>
                                                    </div>
                                                </div>

                                                @if ($transaction->medicationEntries() !== [])
                                                    <div class="mt-3 pt-3 border-t border-border-light dark:border-border-dark">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 mb-2">Medicines</p>
                                                        <div class="space-y-1.5 text-sm">
                                                            @foreach ($transaction->medicationEntries() as $medication)
                                                                <div class="flex items-center justify-between gap-2">
                                                                    <span class="text-text-light dark:text-text-dark">{{ $medication['label'] }}</span>
                                                                    <span class="font-semibold">{{ $medication['pcs'] }} pc{{ $medication['pcs'] === 1 ? '' : 's' }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif

                                                <p class="mt-3 text-xs text-text-light/50 dark:text-text-dark/50">
                                                    Recorded {{ $transaction->created_at?->diffForHumans() }}
                                                </p>
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70 md:col-span-2">
                                                No transactions posted yet.
                                            </div>
                                        @endforelse
                                    </div>
                                    </div>
                                </div>

                                <div id="maintenance" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <div>
                                                <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Household Maintenance</h4>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">Monitor pregnant family members and seniors with health issues.</p>
                                            </div>
                                            <button type="button" id="toggleMonitoringCaseForm" class="inline-flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap shrink-0">
                                                <span class="material-symbols-outlined text-base">add</span>
                                                <span>Add Monitoring</span>
                                            </button>
                                        </div>

                                        @if (session('status') && (request('tab') === 'maintenance' || str_contains(session('status'), 'monitoring')))
                                            <div class="mt-3 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                                {{ session('status') }}
                                            </div>
                                        @endif
                                    </div>

                                    <div id="monitoringCaseFormWrapper" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
                                        <div class="w-full max-w-2xl rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 shadow-xl max-h-[90vh] overflow-y-auto">
                                            <div class="flex items-center justify-between gap-3 mb-4">
                                                <div>
                                                    <h5 class="text-lg font-bold">Add Household Monitoring</h5>
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Register a pregnant member or senior with health concerns for follow-up.</p>
                                                </div>
                                                <button type="button" id="closeMonitoringCaseForm" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" aria-label="Close monitoring form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('bhw.residents.monitoring-cases.store', $resident) }}" class="space-y-4" id="monitoringCaseForm">
                                                @csrf
                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1" for="member_type">Monitoring Type</label>
                                                        <select id="member_type" name="member_type" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" required>
                                                            <option value="" disabled {{ old('member_type') ? '' : 'selected' }}>Select type</option>
                                                            <option value="pregnant" {{ old('member_type') === 'pregnant' ? 'selected' : '' }}>Pregnant</option>
                                                            <option value="senior" {{ old('member_type') === 'senior' ? 'selected' : '' }}>Senior with issue</option>
                                                        </select>
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1" for="monitoring_member_select">Household Member</label>
                                                        <select id="monitoring_member_select" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm">
                                                            <option value="">Select household member</option>
                                                            @foreach ($monitoringMemberOptions as $option)
                                                                <option
                                                                    value="{{ $option['name'] }}"
                                                                    data-source="{{ $option['source'] }}"
                                                                    data-index="{{ $option['index'] ?? '' }}"
                                                                >{{ $option['label'] }}</option>
                                                            @endforeach
                                                            <option value="__custom__">Other / custom name</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <input type="hidden" name="member_source" id="member_source" value="{{ old('member_source') }}">
                                                <input type="hidden" name="member_index" id="member_index" value="{{ old('member_index') }}">

                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="member_name">Member Name</label>
                                                    <input id="member_name" name="member_name" value="{{ old('member_name') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" placeholder="Name of person to monitor" type="text" required />
                                                </div>

                                                <div id="pregnantPeriodFields" class="hidden grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1" for="first_period_date">First Period Date</label>
                                                        <input id="first_period_date" name="first_period_date" value="{{ old('first_period_date') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="date" />
                                                    </div>
                                                    <div>
                                                        <label class="block text-sm font-semibold mb-1" for="last_period_date">Last Period Date</label>
                                                        <input id="last_period_date" name="last_period_date" value="{{ old('last_period_date') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="date" />
                                                    </div>
                                                </div>

                                                <div class="rounded-lg border border-border-light dark:border-border-dark p-3 bg-card-light dark:bg-card-dark">
                                                    <p class="text-sm font-bold text-text-light dark:text-text-dark mb-3">Height, Weight &amp; BMI</p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1" for="monitoring_height_cm">Height (cm)</label>
                                                            <input id="monitoring_height_cm" name="height_cm" value="{{ old('height_cm') }}" data-visit-height="new" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="e.g., 165" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1" for="monitoring_weight_kg">Weight (kg)</label>
                                                            <input id="monitoring_weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" data-visit-weight="new" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="e.g., 60" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1">BMI (auto)</label>
                                                            <input data-visit-bmi="new" type="text" readonly class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" placeholder="Auto-calculated" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="rounded-lg border border-border-light dark:border-border-dark p-3 bg-card-light dark:bg-card-dark">
                                                    <p class="text-sm font-bold text-text-light dark:text-text-dark mb-3">Blood Pressure (BP)</p>
                                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1" for="monitoring_bp_systolic">Systolic</label>
                                                            <input id="monitoring_bp_systolic" name="bp_systolic" value="{{ old('bp_systolic') }}" data-visit-bp-sys="new" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="number" min="50" max="300" placeholder="e.g., 120" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1" for="monitoring_bp_diastolic">Diastolic</label>
                                                            <input id="monitoring_bp_diastolic" name="bp_diastolic" value="{{ old('bp_diastolic') }}" data-visit-bp-dia="new" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="number" min="30" max="200" placeholder="e.g., 80" />
                                                        </div>
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1">BP Reading</label>
                                                            <input data-visit-bp-preview="new" type="text" readonly class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm font-semibold" placeholder="Auto: 120/80" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="issue_notes">Issue / Notes (optional)</label>
                                                    <textarea id="issue_notes" name="issue_notes" rows="2" maxlength="1000" class="w-full rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" placeholder="Describe health concern or monitoring reason">{{ old('issue_notes') }}</textarea>
                                                </div>

                                                <div class="flex justify-end">
                                                    <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold">
                                                        <span class="material-symbols-outlined text-base">save</span>
                                                        <span>Save Monitoring</span>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="profile-tab-scroll space-y-4">
                                        @forelse ($resident->monitoringCases as $monitoringCase)
                                            <article class="rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex flex-wrap items-start justify-between gap-3">
                                                    <div class="min-w-0">
                                                        <div class="flex flex-wrap items-center gap-2">
                                                            <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold {{ $monitoringCase->isPregnant() ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-200' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200' }}">
                                                                {{ $monitoringCase->typeLabel() }}
                                                            </span>
                                                            @if ($monitoringCase->is_active)
                                                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800 dark:bg-green-900/30 dark:text-green-200">Active</span>
                                                            @endif
                                                        </div>
                                                        <h5 class="text-lg font-bold mt-2 text-text-light dark:text-text-dark">{{ $monitoringCase->member_name }}</h5>
                                                        @if ($monitoringCase->isPregnant() && ($monitoringCase->first_period_date || $monitoringCase->last_period_date))
                                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">
                                                                Period:
                                                                {{ $monitoringCase->first_period_date?->format('M d, Y') ?? 'N/A' }}
                                                                to
                                                                {{ $monitoringCase->last_period_date?->format('M d, Y') ?? 'Present' }}
                                                            </p>
                                                        @endif
                                                        @if ($monitoringCase->issue_notes)
                                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">{{ $monitoringCase->issue_notes }}</p>
                                                        @endif
                                                    </div>
                                                    <button type="button" class="toggle-visit-form inline-flex items-center gap-1 rounded-lg h-9 px-3 border border-border-light dark:border-border-dark text-sm font-semibold hover:bg-card-light dark:hover:bg-card-dark" data-case-id="{{ $monitoringCase->id }}">
                                                        <span class="material-symbols-outlined text-base">add</span>
                                                        Add Visit
                                                    </button>
                                                </div>

                                                <div id="visitForm-{{ $monitoringCase->id }}" class="hidden mt-4 rounded-lg border border-border-light dark:border-border-dark p-3 bg-card-light dark:bg-card-dark">
                                                    <form method="POST" action="{{ route('bhw.monitoring-cases.visits.store', $monitoringCase) }}" class="space-y-4 monitoring-visit-form" data-case-id="{{ $monitoringCase->id }}">
                                                        @csrf
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1">Visit Date</label>
                                                            <input name="visit_date" value="{{ now()->toDateString() }}" class="w-full sm:max-w-xs h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="date" required />
                                                        </div>

                                                        <div class="rounded-lg border border-border-light dark:border-border-dark p-3 bg-background-light dark:bg-background-dark">
                                                            <p class="text-sm font-bold text-text-light dark:text-text-dark mb-3">Height, Weight &amp; BMI</p>
                                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">Height (cm)</label>
                                                                    <input name="height_cm" data-visit-height="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="e.g., 165" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">Weight (kg)</label>
                                                                    <input name="weight_kg" data-visit-weight="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="e.g., 60" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">BMI (auto)</label>
                                                                    <input data-visit-bmi="{{ $monitoringCase->id }}" type="text" readonly class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" placeholder="Auto-calculated" />
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if ($monitoringCase->isSenior())
                                                            <div class="rounded-lg border border-border-light dark:border-border-dark p-3 bg-background-light dark:bg-background-dark">
                                                                <p class="text-sm font-bold text-text-light dark:text-text-dark mb-3">Blood Pressure (BP)</p>
                                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1">Systolic</label>
                                                                        <input name="bp_systolic" data-visit-bp-sys="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="50" max="300" placeholder="e.g., 120" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1">Diastolic</label>
                                                                        <input name="bp_diastolic" data-visit-bp-dia="{{ $monitoringCase->id }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm" type="number" min="30" max="200" placeholder="e.g., 80" />
                                                                    </div>
                                                                    <div>
                                                                        <label class="block text-sm font-semibold mb-1">BP Reading</label>
                                                                        <input data-visit-bp-preview="{{ $monitoringCase->id }}" type="text" readonly class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark text-sm font-semibold" placeholder="Auto: 120/80" />
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($monitoringCase->isPregnant())
                                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">MUAC (cm)</label>
                                                                    <input name="muac_cm" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" type="number" min="0" step="0.1" placeholder="Mid-upper arm circumference" />
                                                                </div>
                                                                <div>
                                                                    <label class="block text-sm font-semibold mb-1">Anti-Tetanus</label>
                                                                    <select name="anti_tetanus" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm">
                                                                        <option value="">Not given</option>
                                                                        <option value="TT1">TT1</option>
                                                                        <option value="TT2">TT2</option>
                                                                        <option value="TT3">TT3</option>
                                                                        <option value="TT4">TT4</option>
                                                                        <option value="TT5">TT5</option>
                                                                        <option value="Booster">Booster</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if ($monitoringCase->isSenior())
                                                            <div>
                                                                <label class="block text-sm font-semibold mb-1">Maintenance Medicines (if needed)</label>
                                                                <textarea name="maintenance_medicines" rows="2" maxlength="1000" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" placeholder="List maintenance medicines and dosage"></textarea>
                                                            </div>
                                                        @endif
                                                        <div>
                                                            <label class="block text-sm font-semibold mb-1">Notes (optional)</label>
                                                            <textarea name="notes" rows="2" maxlength="500" class="w-full rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" placeholder="Additional visit notes"></textarea>
                                                        </div>
                                                        <div class="flex justify-end">
                                                            <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-9 px-3 bg-primary text-white text-sm font-semibold">
                                                                <span class="material-symbols-outlined text-base">save</span>
                                                                Save Visit
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>

                                                @if ($monitoringCase->visits->isNotEmpty())
                                                    <div class="mt-4 space-y-2">
                                                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Visit History</p>
                                                        @foreach ($monitoringCase->visits as $visit)
                                                            <div class="rounded-lg border border-border-light dark:border-border-dark p-3 text-sm">
                                                                <div class="flex items-center justify-between gap-2">
                                                                    <p class="font-semibold">{{ $visit->visit_date?->format('M d, Y') }}</p>
                                                                    <p class="text-xs text-text-light/50 dark:text-text-dark/50">{{ $visit->created_at?->diffForHumans() }}</p>
                                                                </div>
                                                                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2 text-text-light/80 dark:text-text-dark/80">
                                                                    @if ($visit->height_cm !== null)
                                                                        <span>Height: {{ $visit->height_cm }} cm</span>
                                                                    @endif
                                                                    @if ($visit->weight_kg !== null)
                                                                        <span>Weight: {{ $visit->weight_kg }} kg</span>
                                                                    @endif
                                                                    @if ($visit->bmi_value !== null)
                                                                        <span>BMI: {{ $visit->bmi_value }}</span>
                                                                    @endif
                                                                    @if ($visit->muac_cm !== null)
                                                                        <span>MUAC: {{ $visit->muac_cm }} cm</span>
                                                                    @endif
                                                                    @if ($visit->anti_tetanus)
                                                                        <span>Anti-tetanus: {{ $visit->anti_tetanus }}</span>
                                                                    @endif
                                                                    @if ($visit->blood_pressure)
                                                                        <span>BP: {{ $visit->blood_pressure }}</span>
                                                                    @endif
                                                                </div>
                                                                @if ($visit->maintenance_medicines)
                                                                    <p class="mt-2 text-text-light/70 dark:text-text-dark/70">Maintenance: {{ $visit->maintenance_medicines }}</p>
                                                                @endif
                                                                @if ($visit->notes)
                                                                    <p class="mt-2 text-text-light/70 dark:text-text-dark/70">{{ $visit->notes }}</p>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="mt-4 text-sm text-text-light/60 dark:text-text-dark/60">No visits recorded yet.</p>
                                                @endif
                                            </article>
                                        @empty
                                            <div class="rounded-lg border border-dashed border-border-light dark:border-border-dark p-8 text-center text-sm text-text-light/70 dark:text-text-dark/70">
                                                <span class="material-symbols-outlined text-4xl text-primary/70">health_and_safety</span>
                                                <p class="mt-3 font-semibold">No household monitoring yet.</p>
                                                <p class="mt-1">Add monitoring for a pregnant family member or a senior with health concerns.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div id="consultations" class="tab-content min-w-0">
                                    <div class="profile-tab-toolbar">
                                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                            <h4 class="text-base sm:text-lg font-bold text-text-light dark:text-text-dark">Consultations</h4>
                                            <button type="button" id="toggleConsultationForm" class="inline-flex items-center justify-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap shrink-0">
                                                <span class="material-symbols-outlined text-base">event</span>
                                                <span>Add Appointment</span>
                                            </button>
                                        </div>

                                        <div id="consultationFormWrapper" class="hidden rounded-xl border border-border-light dark:border-border-dark p-4 bg-background-light dark:bg-background-dark mt-3">
                                        <form method="POST" action="{{ route('bhw.residents.consultations.store', $resident) }}" class="space-y-3">
                                            @csrf
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="consultation_date">Date</label>
                                                    <input
                                                        id="consultation_date"
                                                        name="consultation_date"
                                                        value="{{ old('consultation_date') }}"
                                                        class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                        type="date"
                                                        required
                                                    />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="consultation_time">Time</label>
                                                    <input
                                                        id="consultation_time"
                                                        name="consultation_time"
                                                        value="{{ old('consultation_time') }}"
                                                        class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                        type="time"
                                                        required
                                                    />
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold mb-1" for="consultation_checkup">Medical Checkup</label>
                                                <input
                                                    id="consultation_checkup"
                                                    name="consultation_checkup"
                                                    value="{{ old('consultation_checkup') }}"
                                                    class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                    type="text"
                                                    placeholder="e.g., Prenatal checkup, BP monitoring, follow-up"
                                                    required
                                                />
                                            </div>
                                            <div>
                                                <label class="block text-sm font-semibold mb-1" for="consultation_note">Note (optional)</label>
                                                <textarea id="consultation_note" name="consultation_note" rows="2" maxlength="500" class="w-full rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="Optional instructions for resident">{{ old('consultation_note') }}</textarea>
                                            </div>
                                            <div class="flex justify-end">
                                                <button type="submit" class="inline-flex items-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                                    <span class="material-symbols-outlined text-base">send</span>
                                                    <span>Save and Notify User</span>
                                                </button>
                                            </div>
                                        </form>
                                        </div>
                                    </div>

                                    <div class="profile-tab-scroll">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @forelse ($resident->consultations as $consultation)
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70">Date</p>
                                                <p class="font-semibold mt-1">{{ $consultation->scheduled_at?->format('M d, Y') }}</p>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">Time</p>
                                                <p class="font-semibold mt-1">{{ $consultation->scheduled_at?->format('h:i A') }}</p>
                                                <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">Medical Checkup</p>
                                                <p class="font-semibold mt-1">{{ $consultation->medical_checkup ?: 'General consultation' }}</p>
                                                @if ($consultation->note)
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-2">{{ $consultation->note }}</p>
                                                @endif
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70 md:col-span-2">
                                                No consultations set yet.
                                            </div>
                                        @endforelse
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    menuToggle?.addEventListener('click', () => {
        sidebar?.classList.toggle('active');
        overlay?.classList.toggle('active');
    });

    overlay?.addEventListener('click', () => {
        sidebar?.classList.remove('active');
        overlay.classList.remove('active');
    });

    document.querySelectorAll('.tab-link').forEach((tab) => {
        tab.addEventListener('click', (event) => {
            event.preventDefault();

            const targetTab = tab.getAttribute('data-tab');
            const tabs = document.querySelectorAll('.tab-link');
            const tabContents = document.querySelectorAll('.tab-content');

            tabs.forEach((item) => {
                item.classList.remove('active');
                item.setAttribute('aria-current', 'false');
            });

            tab.classList.add('active');
            tab.setAttribute('aria-current', 'page');

            tabContents.forEach((content) => content.classList.remove('active'));
            document.getElementById(targetTab).classList.add('active');
        });
    });

    document.getElementById('printRecordBtn')?.addEventListener('click', () => {
        window.print();
    });

    const toggleMedicalHistoryFormBtn = document.getElementById('toggleMedicalHistoryForm');
    const closeMedicalHistoryFormBtn = document.getElementById('closeMedicalHistoryForm');
    const medicalHistoryFormWrapper = document.getElementById('medicalHistoryFormWrapper');
    const medicalHistoryModalPanel = medicalHistoryFormWrapper?.querySelector('div');
    const ageInput = document.getElementById('age');
    const toggleConsultationFormBtn = document.getElementById('toggleConsultationForm');
    const consultationFormWrapper = document.getElementById('consultationFormWrapper');
    const toggleMonitoringCaseFormBtn = document.getElementById('toggleMonitoringCaseForm');
    const closeMonitoringCaseFormBtn = document.getElementById('closeMonitoringCaseForm');
    const monitoringCaseFormWrapper = document.getElementById('monitoringCaseFormWrapper');
    const monitoringCaseModalPanel = monitoringCaseFormWrapper?.querySelector('div');
    const memberTypeSelect = document.getElementById('member_type');
    const pregnantPeriodFields = document.getElementById('pregnantPeriodFields');
    const firstPeriodDateInput = document.getElementById('first_period_date');
    const monitoringMemberSelect = document.getElementById('monitoring_member_select');
    const memberNameInput = document.getElementById('member_name');
    const memberSourceInput = document.getElementById('member_source');
    const memberIndexInput = document.getElementById('member_index');

    toggleMedicalHistoryFormBtn?.addEventListener('click', () => {
        medicalHistoryFormWrapper?.classList.remove('hidden');
        ageInput?.focus();
    });

    closeMedicalHistoryFormBtn?.addEventListener('click', () => {
        medicalHistoryFormWrapper?.classList.add('hidden');
    });

    medicalHistoryFormWrapper?.addEventListener('click', (event) => {
        if (event.target === medicalHistoryFormWrapper) {
            medicalHistoryFormWrapper.classList.add('hidden');
        }
    });

    medicalHistoryModalPanel?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    toggleConsultationFormBtn?.addEventListener('click', () => {
        consultationFormWrapper?.classList.toggle('hidden');
    });

    const syncPregnantPeriodFields = () => {
        const isPregnant = memberTypeSelect?.value === 'pregnant';
        pregnantPeriodFields?.classList.toggle('hidden', !isPregnant);
        if (firstPeriodDateInput) {
            firstPeriodDateInput.required = Boolean(isPregnant);
        }
    };

    memberTypeSelect?.addEventListener('change', syncPregnantPeriodFields);

    monitoringMemberSelect?.addEventListener('change', () => {
        const selectedOption = monitoringMemberSelect.options[monitoringMemberSelect.selectedIndex];
        const selectedValue = selectedOption?.value || '';

        if (selectedValue === '__custom__') {
            if (memberNameInput) {
                memberNameInput.value = '';
                memberNameInput.readOnly = false;
                memberNameInput.focus();
            }
            if (memberSourceInput) {
                memberSourceInput.value = 'custom';
            }
            if (memberIndexInput) {
                memberIndexInput.value = '';
            }
            return;
        }

        if (memberNameInput) {
            memberNameInput.value = selectedValue;
            memberNameInput.readOnly = selectedValue !== '';
        }

        if (memberSourceInput) {
            memberSourceInput.value = selectedOption?.dataset.source || '';
        }

        if (memberIndexInput) {
            memberIndexInput.value = selectedOption?.dataset.index || '';
        }
    });

    toggleMonitoringCaseFormBtn?.addEventListener('click', () => {
        monitoringCaseFormWrapper?.classList.remove('hidden');
        syncPregnantPeriodFields();
        calculateVisitBmi('new');
        updateVisitBpPreview('new');
        memberTypeSelect?.focus();
    });

    closeMonitoringCaseFormBtn?.addEventListener('click', () => {
        monitoringCaseFormWrapper?.classList.add('hidden');
    });

    monitoringCaseFormWrapper?.addEventListener('click', (event) => {
        if (event.target === monitoringCaseFormWrapper) {
            monitoringCaseFormWrapper.classList.add('hidden');
        }
    });

    monitoringCaseModalPanel?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    document.querySelectorAll('.toggle-visit-form').forEach((button) => {
        button.addEventListener('click', () => {
            const caseId = button.getAttribute('data-case-id');
            const form = document.getElementById(`visitForm-${caseId}`);
            form?.classList.toggle('hidden');
        });
    });

    const calculateVisitBmi = (caseId) => {
        const heightInput = document.querySelector(`[data-visit-height="${caseId}"]`);
        const weightInput = document.querySelector(`[data-visit-weight="${caseId}"]`);
        const bmiInput = document.querySelector(`[data-visit-bmi="${caseId}"]`);

        if (!heightInput || !weightInput || !bmiInput) {
            return;
        }

        const heightCm = parseFloat(heightInput.value);
        const weightKg = parseFloat(weightInput.value);

        if (!Number.isFinite(heightCm) || !Number.isFinite(weightKg) || heightCm <= 0 || weightKg <= 0) {
            bmiInput.value = '';
            return;
        }

        bmiInput.value = (weightKg / Math.pow(heightCm / 100, 2)).toFixed(1);
    };

    const updateVisitBpPreview = (caseId) => {
        const systolicInput = document.querySelector(`[data-visit-bp-sys="${caseId}"]`);
        const diastolicInput = document.querySelector(`[data-visit-bp-dia="${caseId}"]`);
        const previewInput = document.querySelector(`[data-visit-bp-preview="${caseId}"]`);

        if (!previewInput) {
            return;
        }

        const systolic = systolicInput?.value?.trim();
        const diastolic = diastolicInput?.value?.trim();

        previewInput.value = systolic && diastolic ? `${systolic}/${diastolic}` : '';
    };

    document.querySelectorAll('[data-visit-height], [data-visit-weight]').forEach((input) => {
        input.addEventListener('input', () => {
            const caseId = input.getAttribute('data-visit-height') || input.getAttribute('data-visit-weight');
            if (caseId) {
                calculateVisitBmi(caseId);
            }
        });
    });

    document.querySelectorAll('[data-visit-bp-sys], [data-visit-bp-dia]').forEach((input) => {
        input.addEventListener('input', () => {
            const caseId = input.getAttribute('data-visit-bp-sys') || input.getAttribute('data-visit-bp-dia');
            if (caseId) {
                updateVisitBpPreview(caseId);
            }
        });
    });

    const pageTab = new URLSearchParams(window.location.search).get('tab');
    if (pageTab) {
        const tabLink = document.querySelector(`.tab-link[data-tab="${pageTab}"]`);
        tabLink?.click();
    }

    const hasValidationErrors = {{ $errors->any() ? 'true' : 'false' }};
    const hasStatusMessage = {{ session('status') ? 'true' : 'false' }};

    if ((hasValidationErrors || hasStatusMessage) && !pageTab) {
        const medicalHistoryTabLink = document.querySelector('.tab-link[data-tab="medical-history"]');
        medicalHistoryTabLink?.click();
    }

    if (hasValidationErrors && pageTab === 'maintenance') {
        monitoringCaseFormWrapper?.classList.remove('hidden');
        syncPregnantPeriodFields();
        calculateVisitBmi('new');
        updateVisitBpPreview('new');
    }

    if (hasValidationErrors && (!pageTab || pageTab === 'medical-history')) {
        medicalHistoryFormWrapper?.classList.remove('hidden');
    }

    if (pageTab === 'maintenance') {
        const maintenanceTabLink = document.querySelector('.tab-link[data-tab="maintenance"]');
        maintenanceTabLink?.click();
    }

    if (pageTab === 'consultations') {
        consultationFormWrapper?.classList.remove('hidden');
    }

    syncPregnantPeriodFields();
</script>
</body>
</html>
