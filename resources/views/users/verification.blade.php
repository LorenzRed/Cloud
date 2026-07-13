<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, interactive-widget=resizes-content" name="viewport" />
    @include('partials.submit-guard')
    <title>Resident Verification</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <style>
        .consent-banner {
            position: fixed;
            inset: 0;
            z-index: 80;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding: max(1rem, env(safe-area-inset-bottom, 0px)) max(1rem, env(safe-area-inset-right, 0px)) max(1rem, env(safe-area-inset-bottom, 0px)) max(1rem, env(safe-area-inset-left, 0px));
            pointer-events: none;
        }

        .consent-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(15, 23, 42, 0.35);
            pointer-events: auto;
        }

        .consent-banner.is-visible {
            pointer-events: auto;
        }

        .consent-banner-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 42rem;
            margin: 0 auto;
            border-radius: 1rem;
            border: 1px solid #dbeafe;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
            padding: 1rem 1.1rem;
            transform: translateY(120%);
            opacity: 0;
            transition: transform 0.35s cubic-bezier(0.22, 1, 0.36, 1), opacity 0.25s ease;
        }

        .consent-banner.is-visible .consent-banner-panel {
            transform: translateY(0);
            opacity: 1;
        }

        @media (min-width: 640px) {
            .consent-banner {
                padding: 1.25rem 1.5rem 1.5rem;
            }

            .consent-banner-panel {
                padding: 1.15rem 1.25rem;
            }
        }

        #locationMap .leaflet-control-attribution {
            display: none !important;
        }

        .verification-modal-overlay {
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-y: auto;
            padding: max(1rem, env(safe-area-inset-top, 0px)) max(1rem, env(safe-area-inset-right, 0px)) max(1rem, env(safe-area-inset-bottom, 0px)) max(1rem, env(safe-area-inset-left, 0px));
        }

        .verification-modal-panel {
            width: 100%;
            margin: auto;
            max-height: min(90dvh, 36rem);
            overflow-y: auto;
        }

        #verificationForm input,
        #verificationForm select {
            font-size: 16px;
        }

        @media (min-width: 640px) {
            #verificationForm input,
            #verificationForm select {
                font-size: 0.875rem;
            }
        }

        .child-form-input.has-birthdate-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.25);
        }

        #birthdateClientError:not(.hidden),
        #spouseBirthdateClientError:not(.hidden) {
            animation: birthdate-error-in 0.2s ease;
        }

        @keyframes birthdate-error-in {
            from {
                opacity: 0;
                transform: translateY(-4px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        #idCameraModal .id-camera-viewport {
            position: relative;
            background: #0f172a;
            aspect-ratio: 4 / 3;
            max-height: min(52dvh, 22rem);
            overflow: hidden;
        }

        #idCameraModal video,
        #idCameraModal .id-camera-preview-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        #idCameraModal .id-camera-frame {
            position: absolute;
            inset: 10% 7%;
            border: 2px dashed rgba(255, 255, 255, 0.85);
            border-radius: 0.75rem;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.45);
            pointer-events: none;
        }

        #idCameraStabilityFill {
            transition: width 0.15s ease, background-color 0.2s ease;
        }

        #idCameraStabilityFill.is-ready {
            background-color: #22c55e;
        }

        #idCameraStabilityFill.is-warning {
            background-color: #f59e0b;
        }

        #idCameraStabilityFill.is-error {
            background-color: #ef4444;
        }
        #childMemberModal .verification-modal-panel {
            display: flex;
            flex-direction: column;
            max-height: min(92dvh, 40rem);
            overflow: hidden;
        }

        #childMemberModal .child-modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        #childMemberModal .child-modal-footer {
            flex-shrink: 0;
        }

        #childMemberModal input,
        #childMemberModal select {
            font-size: 16px;
        }

        @media (min-width: 640px) {
            #childMemberModal input,
            #childMemberModal select {
                font-size: 0.875rem;
            }
        }

        #childrenSection {
            background: linear-gradient(180deg, rgba(239, 246, 255, 0.72) 0%, #ffffff 100%);
        }

        #seniorsSection {
            background: linear-gradient(180deg, rgba(255, 251, 235, 0.72) 0%, #ffffff 100%);
        }

        #seniorMemberModal .verification-modal-panel {
            display: flex;
            flex-direction: column;
            max-height: min(92dvh, 40rem);
            overflow: hidden;
        }

        #seniorMemberModal .senior-modal-body {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
        }

        #seniorMemberModal .senior-modal-footer {
            flex-shrink: 0;
        }

        #seniorMemberModal input,
        #seniorMemberModal select {
            font-size: 16px;
        }

        @media (min-width: 640px) {
            #seniorMemberModal input,
            #seniorMemberModal select {
                font-size: 0.875rem;
            }
        }

        #seniorMemberModal .child-form-input:focus {
            border-color: #d97706;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
        }

        @media (max-width: 639px) {
            #seniorMemberModal.verification-modal-overlay {
                align-items: flex-end;
                justify-content: center;
                overflow: hidden;
                padding: 0;
            }

            #seniorMemberModal .verification-modal-panel {
                max-height: min(92dvh, 40rem);
                border-radius: 1.25rem 1.25rem 0 0;
                border-bottom: 0;
            }

            #seniorMemberModal .senior-modal-body {
                scroll-padding-bottom: 7rem;
            }
        }

        @media (min-width: 640px) {
            #seniorMemberModal .verification-modal-panel {
                border-radius: 1.25rem;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            }
        }

        .senior-summary-avatar {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            color: #b45309;
            background: linear-gradient(135deg, #fef3c7 0%, #fffbeb 100%);
            border: 1px solid #fcd34d;
            flex-shrink: 0;
        }

        .child-summary-card {
            display: flex;
            align-items: flex-start;
            gap: 0.875rem;
            padding: 0.875rem;
            border-radius: 1rem;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        }

        @media (min-width: 640px) {
            .child-summary-card {
                padding: 1rem 1.125rem;
            }

            .child-summary-card:hover {
                border-color: #bfdbfe;
                box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
            }
        }

        .child-summary-avatar {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            font-weight: 700;
            color: #1d4ed8;
            background: linear-gradient(135deg, #dbeafe 0%, #eff6ff 100%);
            border: 1px solid #bfdbfe;
            flex-shrink: 0;
        }

        .child-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.2rem;
            padding: 0.2rem 0.55rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
        }

        .child-chip-neutral {
            color: #374151;
            background: #f3f4f6;
        }

        .child-chip-blue {
            color: #1d4ed8;
            background: #eff6ff;
        }

        .child-chip-green {
            color: #047857;
            background: #ecfdf5;
        }

        .child-chip-amber {
            color: #b45309;
            background: #fffbeb;
        }

        .child-chip-violet {
            color: #6d28d9;
            background: #f5f3ff;
        }

        .child-summary-actions {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.125rem;
            border-radius: 0.75rem;
            background: #f9fafb;
            border: 1px solid #f3f4f6;
            flex-shrink: 0;
        }

        .child-action-btn {
            width: 2.25rem;
            height: 2.25rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.625rem;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .child-empty-state {
            border: 1.5px dashed #cbd5e1;
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.72);
            padding: 1.25rem 1rem;
            text-align: center;
        }

        .child-segmented-control {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.375rem;
            padding: 0.25rem;
            border-radius: 0.875rem;
            background: #f3f4f6;
            border: 1px solid #e5e7eb;
        }

        .child-segment-option {
            position: relative;
            display: inline-flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 0.3rem;
            min-height: 2.5rem;
            padding: 0.5rem 0.35rem;
            border-radius: 0.625rem;
            font-size: 0.6875rem;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: background-color 0.15s ease, color 0.15s ease, box-shadow 0.15s ease;
            text-align: center;
            line-height: 1.2;
            white-space: nowrap;
        }

        .child-segment-option .material-symbols-outlined {
            flex-shrink: 0;
            line-height: 1;
            font-size: 1.125rem;
        }

        @media (min-width: 640px) {
            .child-segment-option {
                min-height: 2.75rem;
                padding: 0.625rem 0.5rem;
                font-size: 0.8125rem;
                gap: 0.375rem;
            }
        }

        .child-segment-option input {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .child-segment-option:has(input:checked) {
            color: #1d4ed8;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        }

        .child-form-field {
            display: flex;
            flex-direction: column;
            gap: 0.375rem;
        }

        .child-form-label {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.02em;
            color: #374151;
        }

        .child-form-input {
            width: 100%;
            min-height: 2.75rem;
            border-radius: 0.75rem;
            border: 1px solid #d1d5db;
            padding-inline: 0.875rem;
            color: #111827;
            background: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .child-form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        }

        .child-form-field.is-invalid .child-form-label {
            color: #dc2626;
        }

        .child-form-field.is-invalid .child-form-input,
        #childEmploymentWrap.is-invalid .child-form-input {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }

        .child-form-input.is-invalid:focus {
            border-color: #ef4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
        }

        @media (max-width: 639px) {
            #childMemberModal.verification-modal-overlay {
                align-items: flex-end;
                justify-content: center;
                overflow: hidden;
                padding: 0;
            }

            #childMemberModal .verification-modal-panel {
                max-height: min(92dvh, 40rem);
                border-radius: 1.25rem 1.25rem 0 0;
                border-bottom: 0;
            }

            #childMemberModal .child-modal-body {
                scroll-padding-bottom: 7rem;
            }
        }

        @media (min-width: 640px) {
            #childMemberModal .verification-modal-panel {
                border-radius: 1.25rem;
                box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
            }
        }
    </style>
</head>

<body class="bg-gray-100 min-h-screen">
    @php
        $residentFullName = trim((string) ($residentRequest?->full_name ?? ''));
        $sourceName = trim((string) old('full_name', $residentFullName));
        $nameParts = collect(preg_split('/\s+/', $sourceName ?: '', -1, PREG_SPLIT_NO_EMPTY));
        $defaultFirstName = old('first_name', $residentFullName !== '' ? ($nameParts->first() ?? '') : '');
        $defaultLastName = old('last_name', $residentFullName !== '' ? ($nameParts->count() > 1 ? $nameParts->last() : '') : '');
        $middleToken = $residentFullName !== '' && $nameParts->count() > 2 ? (string) $nameParts->slice(1, -1)->first() : '';
        $defaultMiddleInitial = old('middle_initial', $middleToken ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $middleToken), 0, 1)) : '');
        $trackedBarangay = old('barangay', $residentRequest?->barangay ?? '');
        $trackedPurok = old('purok', $residentRequest?->purok?->name ?? '');
        if ($trackedPurok === \App\Support\PurokLocator::NON_NAME) {
            $trackedPurok = '';
        }
        $storedAddress = old('address', $residentRequest?->address ?? '');
        $trackedLocationMode = old('location_mode', preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*,\s*(-?\d+(?:\.\d+)?)\s*$/', (string) $storedAddress) ? 'map' : ($trackedBarangay !== '' ? 'manual' : 'map'));

        $storedGenderRaw = old('gender_display', Auth::user()->gender);
        $storedGender = in_array($storedGenderRaw, ['Male', 'Female'], true) ? $storedGenderRaw : '';
        $storedParentRole = old('parent_role', $residentRequest?->parent_role ?? Auth::user()->parent_role ?? '');
        if (! in_array($storedParentRole, \App\Models\ResidentRequest::PARENT_ROLES, true)) {
            $storedParentRole = $storedParentRole === 'Male' ? 'Father' : ($storedParentRole === 'Female' ? 'Mother' : '');
        }
        $storedCivilStatus = old('civil_status', $residentRequest?->civil_status ?? Auth::user()->civil_status ?? '');
        $storedPlaceOfBirth = old('place_of_birth', $residentRequest?->place_of_birth ?? Auth::user()->place_of_birth ?? '');
        $storedEducationalAttainment = old('educational_attainment', $residentRequest?->educational_attainment ?? Auth::user()->educational_attainment ?? '');
        $spouseSectionStatuses = \App\Models\User::SPOUSE_SECTION_STATUSES;
        $requiredSpouseStatuses = \App\Models\User::REQUIRED_SPOUSE_STATUSES;
        $storedSpouseDetails = old('spouse_details') ?? $residentRequest?->spouse_details ?? Auth::user()->spouse_details ?? [];
        if (! is_array($storedSpouseDetails)) {
            $storedSpouseDetails = [];
        }
        $spouseSourceName = trim((string) old('spouse_full_name', $storedSpouseDetails['full_name'] ?? ($residentRequest?->spouse_full_name ?? Auth::user()->spouse_full_name ?? '')));
        $spouseNameParts = collect(preg_split('/\s+/', $spouseSourceName ?: '', -1, PREG_SPLIT_NO_EMPTY));
        $defaultSpouseFirstName = old('spouse_first_name', $storedSpouseDetails['first_name'] ?? ($spouseSourceName !== '' ? ($spouseNameParts->first() ?? '') : ''));
        $defaultSpouseLastName = old('spouse_last_name', $storedSpouseDetails['last_name'] ?? ($spouseSourceName !== '' && $spouseNameParts->count() > 1 ? $spouseNameParts->last() : ''));
        $spouseMiddleToken = $spouseSourceName !== '' && $spouseNameParts->count() > 2 ? (string) $spouseNameParts->slice(1, -1)->first() : '';
        $defaultSpouseMiddleInitial = old('spouse_middle_initial', $storedSpouseDetails['middle_initial'] ?? ($spouseMiddleToken ? strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $spouseMiddleToken), 0, 1)) : ''));
        $defaultSpouseBirthdate = old('spouse_birthdate_display', $storedSpouseDetails['birthdate'] ?? '');
        $defaultSpouseParentRole = old('spouse_parent_role', $storedSpouseDetails['parent_role'] ?? '');
        if (! in_array($defaultSpouseParentRole, \App\Models\ResidentRequest::PARENT_ROLES, true)) {
            $defaultSpouseParentRole = '';
        }
        $defaultSpouseGender = old('spouse_gender_display', $storedSpouseDetails['gender'] ?? '');
        if ($defaultSpouseParentRole === '' && $defaultSpouseGender === 'Male') {
            $defaultSpouseParentRole = 'Father';
        } elseif ($defaultSpouseParentRole === '' && $defaultSpouseGender === 'Female') {
            $defaultSpouseParentRole = 'Mother';
        } elseif ($defaultSpouseParentRole === '' && $storedParentRole === 'Father') {
            $defaultSpouseParentRole = 'Mother';
        } elseif ($defaultSpouseParentRole === '' && $storedParentRole === 'Mother') {
            $defaultSpouseParentRole = 'Father';
        }
        if ($defaultSpouseGender === '' && $defaultSpouseParentRole === 'Father') {
            $defaultSpouseGender = 'Male';
        } elseif ($defaultSpouseGender === '' && $defaultSpouseParentRole === 'Mother') {
            $defaultSpouseGender = 'Female';
        }
        $defaultSpousePlaceOfBirth = old('spouse_place_of_birth', $storedSpouseDetails['place_of_birth'] ?? '');
        $defaultSpouseEducationalAttainment = old('spouse_educational_attainment', $storedSpouseDetails['educational_attainment'] ?? '');
        $showSpouseSection = in_array($storedCivilStatus, $spouseSectionStatuses, true);
        $civilStatusOptions = \App\Models\User::CIVIL_STATUSES;
        $parentRoleOptions = \App\Models\ResidentRequest::PARENT_ROLES;
        $storedChildren = collect(old('children', $residentRequest?->household_children ?? []))
            ->filter(fn ($child) => is_array($child))
            ->map(function ($child) {
                $fullName = trim((string) ($child['full_name'] ?? $child['name'] ?? ''));

                return [
                    'full_name' => $fullName,
                    'birthdate' => (string) ($child['birthdate'] ?? ''),
                    'age_in_months' => isset($child['age_in_months'])
                        ? (int) $child['age_in_months']
                        : \App\Support\PersonAge::ageInWholeMonths((string) ($child['birthdate'] ?? '')),
                    'gender' => (string) ($child['gender'] ?? ''),
                    'relationship' => (string) ($child['relationship'] ?? ''),
                    'currently_studying' => in_array($child['currently_studying'] ?? '', ['yes', 'no', 'infant'], true)
                        ? $child['currently_studying']
                        : (filled($child['school'] ?? null) ? 'yes' : 'no'),
                    'school' => (string) ($child['school'] ?? ''),
                    'educational_attainment' => (string) ($child['educational_attainment'] ?? ''),
                    'occupation' => (string) ($child['occupation'] ?? ''),
                ];
            })
            ->filter(fn ($child) => $child['full_name'] !== '' || $child['birthdate'] !== '')
            ->values()
            ->all();
        $storedSeniors = collect(old('seniors', $residentRequest?->household_seniors ?? []))
            ->filter(fn ($senior) => is_array($senior))
            ->map(function ($senior) {
                $fullName = trim((string) ($senior['full_name'] ?? $senior['name'] ?? ''));

                return [
                    'full_name' => $fullName,
                    'birthdate' => (string) ($senior['birthdate'] ?? ''),
                    'age_in_years' => isset($senior['age_in_years'])
                        ? (int) $senior['age_in_years']
                        : \App\Support\PersonAge::ageInYears((string) ($senior['birthdate'] ?? '')),
                    'gender' => (string) ($senior['gender'] ?? ''),
                    'relationship' => (string) ($senior['relationship'] ?? ''),
                    'place_of_birth' => (string) ($senior['place_of_birth'] ?? ''),
                    'educational_attainment' => (string) ($senior['educational_attainment'] ?? ''),
                    'occupation' => (string) ($senior['occupation'] ?? ''),
                ];
            })
            ->filter(fn ($senior) => $senior['full_name'] !== '' || $senior['birthdate'] !== '')
            ->values()
            ->all();
        $childRelationshipOptions = \App\Models\ResidentRequest::CHILD_RELATIONSHIPS;
        $seniorRelationshipOptions = \App\Models\ResidentRequest::HOUSEHOLD_SENIOR_RELATIONSHIPS;
        $educationalAttainmentOptions = \App\Models\ResidentRequest::EDUCATIONAL_ATTAINMENTS;
        $selectedIdType = old('id_type', $residentRequest?->id_type ?? '');
        $idTypes = \App\Models\ResidentRequest::ID_TYPES;
        $defaultBirthdate = old('birthdate_display', optional(Auth::user()->date_of_birth)->format('Y-m-d') ?: '');
    @endphp

    <div class="max-w-2xl mx-auto py-6 sm:py-10 px-4">
        <div class="bg-white rounded-xl shadow p-4 sm:p-6 md:p-8">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Resident Verification</h1>
            <p class="text-sm text-gray-600 mt-2">One account represents your whole household. Complete the <span class="font-semibold">Representative</span>, then your <span class="font-semibold">husband or wife</span> (if applicable), children, and any <span class="font-semibold">elderly family members</span> living with you.</p>

            @if ($errors->any())
                <div class="mt-4 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if ($isRejectedResubmit ?? false)
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                    <p class="font-semibold">Your previous request was not approved.</p>
                    <p class="mt-1 text-amber-800/90">Review each field below, update anything that needs fixing, capture new ID photos if needed, then send your request again.</p>
                </div>
            @endif

            <form method="POST" action="{{ route('users.verification.submit') }}" enctype="multipart/form-data" class="mt-6 space-y-4" id="verificationForm">
                @csrf
                <input type="hidden" name="location_consent" id="locationConsentInput" value="{{ old('location_consent') }}" />
                <input type="hidden" name="privacy_consent" id="privacyConsentInput" value="{{ old('privacy_consent') }}" />

                <div id="representativeSection" class="rounded-xl border border-blue-100 bg-gradient-to-b from-blue-50/60 to-white p-4 sm:p-5 space-y-4">
                    <div>
                        <h2 class="text-sm font-bold text-blue-900">Representative</h2>
                        <p class="text-xs text-gray-600 mt-0.5">Parent (father or mother) registering for the family account.</p>
                    </div>

                <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                    <div class="sm:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="first_name">First Name</label>
                        <input id="first_name" name="first_name" type="text" value="{{ $defaultFirstName }}" placeholder="Enter first name" required @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div class="sm:col-span-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="last_name">Last Name</label>
                        <input id="last_name" name="last_name" type="text" value="{{ $defaultLastName }}" placeholder="Enter last name" required @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                    <div class="sm:col-span-2 sm:max-w-[92px]">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="middle_initial">M.I. <span class="font-normal text-gray-500">(optional)</span></label>
                        <input id="middle_initial" name="middle_initial" type="text" maxlength="1" value="{{ $defaultMiddleInitial }}" placeholder="M" @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div id="birthdateField" class="child-form-field">
                        <label class="child-form-label" for="birthdate_display">Birthdate</label>
                        <input id="birthdate_display" name="birthdate_display" type="date" value="{{ $defaultBirthdate }}" @disabled($requestSent ?? false)
                            class="child-form-input" />
                        <p id="birthdateClientError" class="hidden mt-1.5 text-xs font-medium text-red-600" role="alert" aria-live="polite"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="parent_role">Parent Role</label>
                        <select id="parent_role" name="parent_role" required @disabled($requestSent ?? false)
                            class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select parent role</option>
                            @foreach ($parentRoleOptions as $roleOption)
                                <option value="{{ $roleOption }}" @selected($storedParentRole === $roleOption)>{{ $roleOption }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="civil_status">Marital Status</label>
                        <select id="civil_status" name="civil_status" required @disabled($requestSent ?? false)
                            class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select marital status</option>
                            @foreach ($civilStatusOptions as $statusOption)
                                <option value="{{ $statusOption }}" @selected($storedCivilStatus === $statusOption)>{{ $statusOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="place_of_birth">Place of Birth</label>
                        <input id="place_of_birth" name="place_of_birth" type="text" value="{{ $storedPlaceOfBirth }}" placeholder="Full Address" required @disabled($requestSent ?? false)
                            class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="educational_attainment">Educational Attainment</label>
                    <select id="educational_attainment" name="educational_attainment" required @disabled($requestSent ?? false)
                        class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Select educational attainment</option>
                        @foreach ($educationalAttainmentOptions as $attainmentOption)
                            <option value="{{ $attainmentOption }}" @selected($storedEducationalAttainment === $attainmentOption)>{{ $attainmentOption }}</option>
                        @endforeach
                    </select>
                </div>
                </div>

                <div id="spouseSection" class="rounded-xl border border-blue-100 bg-gradient-to-b from-blue-50/60 to-white p-4 sm:p-5 space-y-4 @if(!$showSpouseSection) hidden @endif">
                    <div>
                        <h2 class="text-sm font-bold text-blue-900" id="spouseSectionTitle">Husband / Wife</h2>
                        <p class="text-xs text-gray-600 mt-0.5" id="spouseSectionHint">Your spouse's details, same fields as the representative.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-end">
                        <div class="sm:col-span-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_first_name">First Name</label>
                            <input id="spouse_first_name" name="spouse_first_name" type="text" value="{{ $defaultSpouseFirstName }}" placeholder="Enter first name" @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>
                        <div class="sm:col-span-5">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_last_name">Last Name</label>
                            <input id="spouse_last_name" name="spouse_last_name" type="text" value="{{ $defaultSpouseLastName }}" placeholder="Enter last name" @disabled($requestSent ?? false)
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                        <div class="sm:col-span-2 sm:max-w-[92px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_middle_initial">M.I. <span class="font-normal text-gray-500">(optional)</span></label>
                            <input id="spouse_middle_initial" name="spouse_middle_initial" type="text" maxlength="1" value="{{ $defaultSpouseMiddleInitial }}" placeholder="M" @disabled($requestSent ?? false)
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div id="spouseBirthdateField" class="child-form-field">
                            <label class="child-form-label" for="spouse_birthdate_display">Birthdate</label>
                            <input id="spouse_birthdate_display" name="spouse_birthdate_display" type="date" value="{{ $defaultSpouseBirthdate }}" @disabled($requestSent ?? false)
                                class="child-form-input" />
                            <p id="spouseBirthdateClientError" class="hidden mt-1.5 text-xs font-medium text-red-600" role="alert" aria-live="polite"></p>
                        </div>
                    <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_parent_role">Parent Role</label>
                            <select id="spouse_parent_role" name="spouse_parent_role" @disabled($requestSent ?? false)
                                class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select parent role</option>
                                @foreach ($parentRoleOptions as $roleOption)
                                    <option value="{{ $roleOption }}" @selected($defaultSpouseParentRole === $roleOption)>{{ $roleOption }}</option>
                                @endforeach
                        </select>
                    </div>
                </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_civil_status_display">Marital Status</label>
                            <input id="spouse_civil_status_display" type="text" value="{{ $storedCivilStatus }}" readonly tabindex="-1"
                                class="w-full h-11 rounded-lg border-gray-200 bg-gray-50 text-gray-700" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_place_of_birth">Place of Birth</label>
                            <input id="spouse_place_of_birth" name="spouse_place_of_birth" type="text" value="{{ $defaultSpousePlaceOfBirth }}" placeholder="Full Address" @disabled($requestSent ?? false)
                                class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="spouse_educational_attainment">Educational Attainment</label>
                        <select id="spouse_educational_attainment" name="spouse_educational_attainment" @disabled($requestSent ?? false)
                            class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select educational attainment</option>
                            @foreach ($educationalAttainmentOptions as $attainmentOption)
                                <option value="{{ $attainmentOption }}" @selected($defaultSpouseEducationalAttainment === $attainmentOption)>{{ $attainmentOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="spouse_full_name" id="spouse_full_name" value="{{ $spouseSourceName }}" />
                    <input type="hidden" id="spouse_gender_display" name="spouse_gender_display" value="{{ $defaultSpouseGender }}" />
                </div>

                <div id="childrenSection" class="rounded-xl border border-blue-100 p-4 sm:p-5 space-y-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20">
                                    <span class="material-symbols-outlined text-[20px] leading-none">family_restroom</span>
                                </span>
                                <div class="min-w-0 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <h2 class="text-sm font-bold text-blue-900 shrink-0">Children</h2>
                                    <span id="childrenCountBadge" class="hidden inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-blue-700 shrink-0">0 added</span>
                                    <span class="text-xs text-gray-600">Add children in your household.</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="openChildModalBtn" @disabled($requestSent ?? false)
                            class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 h-11 px-5 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-blue-600/20 transition">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Add child
                        </button>
                    </div>

                    <div id="childrenEmptyHint" class="child-empty-state">
                        <span class="material-symbols-outlined text-4xl text-blue-300">child_care</span>
                        <p class="mt-2 text-sm font-semibold text-gray-800">No children added yet</p>
                        <p class="mt-1 text-xs text-gray-500">Tap <span class="font-semibold text-blue-700">Add child</span> to include household members.</p>
                    </div>

                    <div id="childrenSummaryList" class="grid grid-cols-1 lg:grid-cols-2 gap-3"></div>
                    <div id="childrenHiddenFields" class="sr-only" aria-hidden="true"></div>
                </div>

                <div id="seniorsSection" class="rounded-xl border border-amber-100 p-4 sm:p-5 space-y-4">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow-sm shadow-amber-600/20">
                                    <span class="material-symbols-outlined text-[20px] leading-none">elderly</span>
                                </span>
                                <div class="min-w-0 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                    <h2 class="text-sm font-bold text-amber-900 shrink-0">Elderly in household</h2>
                                    <span id="seniorsCountBadge" class="hidden inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-amber-800 shrink-0">0 added</span>
                                    <span class="text-xs text-gray-600">Add lolo or lola if they still live with you (60+).</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" id="openSeniorModalBtn" @disabled($requestSent ?? false)
                            class="w-full sm:w-auto shrink-0 inline-flex items-center justify-center gap-2 h-11 px-5 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-amber-600/20 transition">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            Add elderly
                        </button>
                    </div>

                    <div id="seniorsEmptyHint" class="child-empty-state">
                        <span class="material-symbols-outlined text-4xl text-amber-300">elderly</span>
                        <p class="mt-2 text-sm font-semibold text-gray-800">No elderly members added</p>
                        <p class="mt-1 text-xs text-gray-500">Optional — add grandfather or grandmother for senior citizen monitoring.</p>
                    </div>

                    <div id="seniorsSummaryList" class="grid grid-cols-1 lg:grid-cols-2 gap-3"></div>
                    <div id="seniorsHiddenFields" class="sr-only" aria-hidden="true"></div>
                </div>

                <input type="hidden" id="gender_display" name="gender_display" value="{{ $storedGender }}" />

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1" for="contact">Contact Number</label>
                    <input id="contact" name="contact" type="tel" inputmode="numeric" maxlength="11" pattern="[0-9]{11}"
                        value="{{ old('contact', $residentRequest?->contact ?? Auth::user()->contact_number) }}" placeholder="09123456789" required @disabled($requestSent ?? false)
                        class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                </div>

                <div class="rounded-xl border border-blue-100 bg-gradient-to-b from-blue-50/70 to-white p-4">
                    <label class="block text-sm font-semibold tracking-wide text-blue-900 mb-3 text-center">Barangay &amp; Purok</label>

                    <p class="mb-3 text-center text-xs text-gray-600">
                        Tap <span class="font-semibold text-blue-700">Set location</span> and choose <span class="font-semibold text-blue-700">map-based</span> (GPS area) or <span class="font-semibold text-blue-700">manual</span> (select barangay and purok).
                    </p>

                    <div id="savedBarangayText" class="mb-3 flex flex-col items-center gap-2 @if(!$trackedBarangay) hidden @endif">
                        <span id="savedLocationModeBadge" class="inline-flex items-center gap-1 rounded-full bg-gray-100 text-gray-700 text-[11px] font-semibold px-3 py-1 border border-gray-200 @if(!$trackedBarangay) hidden @endif">
                            {{ $trackedLocationMode === 'manual' ? 'Manual selection' : 'Map-based area' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 text-blue-800 text-sm font-semibold px-4 py-1.5 border border-blue-200">
                            <span class="material-symbols-outlined text-[16px]">place</span>
                            <span id="savedBarangayTextValue">{{ $trackedBarangay }}</span>
                        </span>
                        <span id="savedPurokWrap" class="inline-flex items-center gap-1.5 rounded-full bg-indigo-100 text-indigo-800 text-sm font-semibold px-4 py-1.5 border border-indigo-200 @if(!$trackedPurok) hidden @endif">
                            <span class="material-symbols-outlined text-[16px]">map</span>
                            <span id="savedPurokTextValue">{{ $trackedPurok }}</span>
                        </span>
                    </div>

                    <div class="flex justify-center">
                        <button
                            id="openTrackModalBtn"
                            type="button"
                            @disabled($requestSent ?? false)
                            class="inline-flex items-center gap-2 px-6 h-11 rounded-full text-sm font-semibold shadow-sm transition disabled:bg-gray-400 disabled:cursor-not-allowed {{ $trackedBarangay ? 'bg-white text-blue-700 border border-blue-300 hover:bg-blue-50' : 'bg-blue-600 text-white hover:bg-blue-700 hover:shadow' }}"
                        >
                            <span class="material-symbols-outlined text-[18px]">my_location</span>
                            <span id="openTrackModalBtnLabel">{{ $trackedBarangay ? 'Change location' : 'Set location' }}</span>
                        </button>
                    </div>

                    <input id="location_mode" name="location_mode" type="hidden" value="{{ $trackedLocationMode }}" />
                    <input id="barangay" name="barangay" type="hidden" value="{{ $trackedBarangay }}" required />
                    <input id="purok" name="purok" type="hidden" value="{{ $trackedPurok }}" />
                    <input id="address" name="address" type="hidden" value="{{ $storedAddress }}" />
                    <input id="full_name" name="full_name" type="hidden" value="{{ old('full_name', $sourceName) }}" required />
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-4 space-y-4">
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900">Valid ID Verification</h2>
                        <p class="text-xs text-gray-600 mt-1">Capture a clear live photo of your valid ID (front, then back).</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="id_type">ID Type</label>
                        <select id="id_type" name="id_type" required @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select valid ID type</option>
                            @foreach ($idTypes as $value => $label)
                                <option value="{{ $value }}" @selected($selectedIdType === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="idOtherWrap" class="@if($selectedIdType !== 'other') hidden @endif">
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="id_other_label">Specify ID (Other)</label>
                        <input id="id_other_label" name="id_other_label" type="text" value="{{ old('id_other_label', $residentRequest?->id_other_label ?? '') }}" maxlength="255" placeholder="e.g., Postal ID, PRC ID" @disabled($requestSent ?? false)
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" />
                    </div>

                    <div id="idPhotoWrap" class="@if(!$selectedIdType) hidden @endif space-y-3">
                        <p class="text-xs text-gray-500">Hold your ID inside the frame. The capture button enables when the image is steady and sharp.</p>
                        <button type="button" id="startIdCaptureBtn" @disabled($requestSent ?? false)
                            class="w-full h-11 rounded-lg bg-blue-600 text-white text-sm font-semibold inline-flex items-center justify-center gap-2 hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed @if($requestSent ?? false) pointer-events-none @endif">
                            <span class="material-symbols-outlined text-[20px]">photo_camera</span>
                            <span id="startIdCaptureBtnLabel">Capture ID Photos</span>
                        </button>
                        <input id="id_photo_front" name="id_photo_front" type="file" accept="image/jpeg" class="sr-only" tabindex="-1" aria-hidden="true" />
                        <input id="id_photo_back" name="id_photo_back" type="file" accept="image/jpeg" class="sr-only" tabindex="-1" aria-hidden="true" />
                    </div>
                </div>

                <div class="pt-4 mt-2 border-t border-gray-200">
                    @if ($requestSent ?? false)
                        <button type="button" disabled class="w-full bg-gray-400 text-white font-semibold py-2.5 rounded-lg cursor-not-allowed">
                            Request Sent
                        </button>
                    @else
                        <button type="submit" class="w-full h-11 sm:h-auto sm:py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-sm sm:text-base">
                            Send Request
                        </button>
                    @endif
                </div>
            </form>

            <div id="verificationFooterLinks" class="mt-6 pt-4 border-t border-gray-200 text-center">
                <div id="backToLoginLink" @class(['hidden' => $requestSent ?? false])>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-gray-900 hover:underline text-sm font-medium">
                            Back to login
                        </button>
                    </form>
                </div>
                <div id="goHomeLink" @class(['hidden' => ! ($requestSent ?? false)])>
                    <a href="{{ route('users.main') }}" class="text-blue-600 hover:underline text-sm font-medium">Go Home</a>
                </div>
            </div>
        </div>
    </div>

    <div id="trackModal" class="hidden fixed inset-0 z-50 bg-black/50 verification-modal-overlay">
        <div class="verification-modal-panel w-full max-w-4xl rounded-xl bg-white shadow border border-gray-200 overflow-hidden flex flex-col">
            <div class="px-4 py-3 border-b border-gray-200 flex items-start justify-between gap-3 shrink-0">
                <div class="min-w-0 pr-2">
                    <h2 class="text-base font-bold text-gray-900">Barangay &amp; Purok</h2>
                    <p class="text-xs text-gray-600 mt-0.5">Choose map-based GPS area or manual barangay and purok selection.</p>
                </div>
                <button id="closeTrackModalBtn" type="button" class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-md hover:bg-gray-100" aria-label="Close track location">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="px-4 pt-3 border-b border-gray-200 shrink-0">
                <div class="grid grid-cols-2 gap-2 rounded-lg bg-gray-100 p-1">
                    <button type="button" id="trackModeMapBtn" class="track-mode-btn h-10 rounded-md text-sm font-semibold bg-white text-blue-700 shadow-sm">Map-based</button>
                    <button type="button" id="trackModeManualBtn" class="track-mode-btn h-10 rounded-md text-sm font-semibold text-gray-600 hover:text-gray-800">Manual</button>
                </div>
            </div>

            <div id="trackModalBody" class="flex-1 min-h-0 overflow-y-auto">
                <div id="trackMapPanel" class="p-4">
                    <p class="text-xs text-gray-500 leading-relaxed mb-3">Map-based uses your GPS to match the barangay and purok. Inaccurate result may happen sometimes.</p>
                    <div class="relative h-[min(42dvh,360px)] sm:h-[380px] rounded-lg overflow-hidden border border-gray-200">
                        <div id="locationMap" class="h-full w-full min-h-[220px]"></div>
                        <div id="mapLoadingOverlay" class="hidden absolute inset-0 z-10 bg-black/35 backdrop-blur-[1px] flex items-center justify-center">
                            <div class="rounded-lg bg-white/95 px-4 py-3 border border-gray-200 shadow flex items-center gap-3">
                                <span class="inline-block h-5 w-5 rounded-full border-2 border-blue-600 border-t-transparent animate-spin"></span>
                                <p id="mapLoadingText" class="text-sm font-medium text-gray-700">Getting current location...</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="trackManualPanel" class="hidden p-4 space-y-4">
                    <p class="text-xs text-gray-500 leading-relaxed">Manual selection assigns you to a barangay and purok without saving GPS coordinates. Use this if the map cannot find your exact spot.</p>
                    <div>
                        <label for="manualBarangaySelect" class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                        <select id="manualBarangaySelect" class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm" @disabled($requestSent ?? false)>
                            <option value="">Select barangay</option>
                            @foreach ($barangayOptions as $barangayOption)
                                <option value="{{ $barangayOption->name }}" @selected($trackedBarangay === $barangayOption->name)>{{ $barangayOption->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="manualPurokSelect" class="block text-sm font-medium text-gray-700 mb-1">Purok</label>
                        <select id="manualPurokSelect" class="w-full h-11 rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 text-sm" @disabled($requestSent ?? false)>
                            <option value="">Select purok</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 bg-white shrink-0 space-y-2">
                <button id="useCurrentPositionBtn" type="button" @disabled($requestSent ?? false)
                    class="w-full h-11 rounded-lg bg-blue-600 text-white text-sm font-semibold inline-flex items-center justify-center gap-2 hover:bg-blue-700 disabled:bg-gray-400 disabled:text-gray-200 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">my_location</span>
                    Use Current Position
                </button>
                <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-2">
                    <button id="cancelTrackModalBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg border border-gray-300 text-sm font-semibold">Cancel</button>
                <button id="saveTrackModalBtn" type="button" @disabled($requestSent ?? false)
                        class="w-full sm:w-auto h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Save Location
                </button>
                </div>
            </div>
        </div>
    </div>

    <div id="locationConsentBanner" class="consent-banner hidden" aria-live="polite">
        <div class="consent-banner-panel">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:gap-4">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-100 text-blue-700">
                    <span class="material-symbols-outlined text-[22px]">my_location</span>
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <p class="text-sm font-bold text-gray-900">Location Terms & Agreement</p>
                        <button id="locationConsentCloseBtn" type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Close location terms">
                            <span class="material-symbols-outlined text-[20px]">close</span>
                        </button>
                    </div>
                    <p class="mt-1 text-xs sm:text-sm text-gray-600 leading-relaxed">
                        This app uses your GPS location only to verify your barangay for resident verification.
                        Your coordinates are used for barangay matching and are not shared outside authorized health workers.
                        Clicking <span class="font-semibold text-gray-800">Use Current Position</span> agrees to this and opens your browser&apos;s location permission (if not already allowed).
                    </p>
                </div>
                <div class="flex flex-col-reverse sm:flex-row gap-2 w-full sm:w-auto sm:shrink-0">
                    <button id="locationConsentDeclineBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Decline
                    </button>
                    <button id="locationConsentAcceptBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-sm">
                        Allow Location
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="seniorMemberModal" class="hidden fixed inset-0 z-[91] bg-black/50 verification-modal-overlay">
        <div class="verification-modal-panel w-full max-w-lg bg-white border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 flex items-center justify-between gap-3 shrink-0">
                <div class="min-w-0 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-600 text-white shadow-sm shadow-amber-600/20 shrink-0">
                        <span class="material-symbols-outlined text-[20px]">elderly</span>
                    </span>
                    <div class="min-w-0">
                        <h3 id="seniorMemberModalTitle" class="text-base font-bold text-gray-900">Add Elderly Member</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Grandfather or grandmother living in your home</p>
                    </div>
                </div>
                <button id="seniorMemberCloseBtn" type="button" class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-xl hover:bg-white/80" aria-label="Close elderly form">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="senior-modal-body px-4 sm:px-5 py-4 space-y-4">
                <div class="child-form-field">
                    <label class="child-form-label" for="seniorFullName">Full Name</label>
                    <input id="seniorFullName" type="text" placeholder="Juan D. Cruz" class="child-form-input" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="child-form-field">
                        <label class="child-form-label" for="seniorBirthdate">Birthdate</label>
                        <input id="seniorBirthdate" type="date" class="child-form-input" />
                        <p id="seniorAgeHint" class="hidden text-xs text-amber-700 font-medium"></p>
                    </div>
                    <div class="child-form-field">
                        <label class="child-form-label" for="seniorGender">Gender</label>
                        <select id="seniorGender" class="child-form-input">
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="child-form-field">
                    <label class="child-form-label" for="seniorRelationship">Relationship</label>
                    <select id="seniorRelationship" class="child-form-input">
                        <option value="">Select relationship</option>
                        @foreach ($seniorRelationshipOptions as $relationshipOption)
                            <option value="{{ $relationshipOption }}">{{ $relationshipOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="child-form-field">
                    <label class="child-form-label" for="seniorPlaceOfBirth">Place of Birth</label>
                    <input id="seniorPlaceOfBirth" type="text" placeholder="Full Address" class="child-form-input" />
                </div>

                <div class="child-form-field">
                    <label class="child-form-label" for="seniorEducationalAttainment">Educational Attainment</label>
                    <select id="seniorEducationalAttainment" class="child-form-input">
                        <option value="">Select educational attainment</option>
                        @foreach ($educationalAttainmentOptions as $attainmentOption)
                            <option value="{{ $attainmentOption }}">{{ $attainmentOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="child-form-field">
                    <label class="child-form-label" for="seniorOccupation">Occupation <span class="font-normal text-gray-500">(optional)</span></label>
                    <input id="seniorOccupation" type="text" placeholder="e.g., Retired, Farmer" class="child-form-input" />
                </div>

                <p class="text-[11px] text-gray-500 leading-snug">Only add <span class="font-semibold text-amber-700">grandfather or grandmother</span> who still live in your home and are at least 60 years old.</p>

                <div class="senior-modal-footer-mobile sm:hidden pt-1 space-y-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))]">
                    <button type="button" class="senior-member-save-btn w-full h-11 px-4 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 shadow-md shadow-amber-600/20">Save elderly member</button>
                    <button type="button" class="senior-member-cancel-btn w-full h-11 px-4 rounded-full border border-gray-300 bg-white text-sm font-semibold">Cancel</button>
                </div>
            </div>

            <div class="senior-modal-footer hidden sm:flex px-4 sm:px-5 py-4 border-t border-gray-100 flex-col-reverse sm:flex-row sm:justify-end gap-2 bg-white/95 backdrop-blur">
                <button type="button" class="senior-member-cancel-btn w-full sm:w-auto h-11 px-5 rounded-full border border-gray-300 bg-white text-sm font-semibold hover:bg-gray-50">Cancel</button>
                <button type="button" class="senior-member-save-btn w-full sm:w-auto h-11 px-5 rounded-full bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700 shadow-md shadow-amber-600/20">Save elderly member</button>
            </div>
        </div>
    </div>

    <div id="privacyConsentBanner" class="consent-banner hidden" aria-live="polite">
        <div class="consent-banner-panel">
            <div class="flex flex-col gap-3">
                <div class="flex items-start gap-3">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-indigo-700">
                        <span class="material-symbols-outlined text-[22px]">shield_person</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-bold text-gray-900">Data Privacy Terms & Agreement</p>
                            <button id="privacyConsentCloseBtn" type="button" class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-md text-gray-500 hover:bg-gray-100 hover:text-gray-700" aria-label="Close privacy terms">
                                <span class="material-symbols-outlined text-[20px]">close</span>
                            </button>
                        </div>
                        <p class="mt-1 text-xs sm:text-sm text-gray-600 leading-relaxed">
                            Under the <span class="font-semibold text-gray-800">Data Privacy Act of 2012 (RA 10173)</span>, you will provide personal information including live-captured valid ID photos,
                            name, contact details, and barangay data. These files are collected solely for identity verification, stored securely,
                            and accessed only by authorized BHW and barangay officials for resident approval.
                        </p>
                    </div>
                </div>
                <label class="flex items-start gap-2 rounded-lg border border-blue-100 bg-blue-50/70 px-3 py-2.5 text-xs sm:text-sm text-gray-700">
                    <input id="privacyConsentCheckbox" type="checkbox" class="mt-0.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                    <span>I have read and agree to the collection and processing of my personal data and captured ID photos for verification purposes.</span>
                </label>
                <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                    <button id="privacyConsentDeclineBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg border border-gray-300 bg-white text-sm font-semibold text-gray-700 hover:bg-gray-50">
                        Decline
                    </button>
                    <button id="privacyConsentAcceptBtn" type="button" disabled class="w-full sm:w-auto h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-sm disabled:bg-gray-300 disabled:cursor-not-allowed">
                        Accept & Send Request
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="idCameraModal" class="hidden fixed inset-0 z-[96] bg-black/80 verification-modal-overlay">
        <div class="verification-modal-panel w-full max-w-lg rounded-xl bg-white border border-gray-200 shadow-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between gap-3 shrink-0">
                <div class="min-w-0">
                    <h3 id="idCameraModalTitle" class="text-base font-bold text-gray-900">Capture ID — Front</h3>
                    <p id="idCameraModalSubtitle" class="text-xs text-gray-600 mt-0.5">Step 1 of 2</p>
                </div>
                <button id="idCameraCloseBtn" type="button" class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-md hover:bg-gray-100" aria-label="Close camera">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="p-4 space-y-3">
                <div id="idCameraLiveWrap" class="id-camera-viewport rounded-lg">
                    <video id="idCameraVideo" autoplay playsinline muted class="hidden"></video>
                    <img id="idCameraPreviewImg" alt="Captured ID preview" class="id-camera-preview-img hidden" />
                    <div class="id-camera-frame" aria-hidden="true"></div>
                </div>

                <div id="idCameraLiveControls" class="space-y-2">
                    <p id="idCameraStatusText" class="text-sm font-medium text-gray-700 text-center">Starting camera...</p>
                    <div class="h-2 w-full rounded-full bg-gray-200 overflow-hidden">
                        <div id="idCameraStabilityFill" class="h-full w-0 rounded-full bg-amber-500 is-warning"></div>
                    </div>
                    <p id="idCameraHintText" class="text-xs text-gray-500 text-center">Align your ID inside the frame and hold still.</p>
                </div>
            </div>

            <div class="px-4 py-3 border-t border-gray-200 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 bg-white shrink-0">
                <button id="idCameraCancelBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg border border-gray-300 text-sm font-semibold">Cancel</button>
                <button id="idCameraRetakeBtn" type="button" class="hidden w-full sm:w-auto h-11 px-4 rounded-lg border border-gray-300 text-sm font-semibold">Retake</button>
                <button id="idCameraCaptureBtn" type="button" disabled
                    class="w-full sm:w-auto h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed">
                    Capture
                </button>
                <button id="idCameraUsePhotoBtn" type="button" class="hidden w-full sm:w-auto h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">
                    Complete
                </button>
            </div>
        </div>
    </div>

    <div id="childMemberModal" class="hidden fixed inset-0 z-[91] bg-black/50 verification-modal-overlay">
        <div class="verification-modal-panel w-full max-w-lg bg-white border border-gray-200 overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center justify-between gap-3 shrink-0">
                <div class="min-w-0 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-blue-600 text-white shadow-sm shadow-blue-600/20 shrink-0">
                        <span class="material-symbols-outlined text-[20px]">child_care</span>
                    </span>
                    <div class="min-w-0">
                        <h3 id="childMemberModalTitle" class="text-base font-bold text-gray-900">Add Child</h3>
                        <p class="text-xs text-gray-600 mt-0.5">Household member profile</p>
                    </div>
                </div>
                <button id="childMemberCloseBtn" type="button" class="h-10 w-10 shrink-0 inline-flex items-center justify-center rounded-xl hover:bg-white/80" aria-label="Close child form">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>
            </div>

            <div class="child-modal-body px-4 sm:px-5 py-4 space-y-4">
                <div class="child-form-field">
                    <label class="child-form-label" for="childFullName">Full Name</label>
                    <input id="childFullName" type="text" placeholder="Juan D. Cruz" class="child-form-input" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="child-form-field">
                        <label class="child-form-label" for="childBirthdate">Birthdate</label>
                        <input id="childBirthdate" type="date" class="child-form-input" />
                        <p id="childAgeHint" class="hidden text-xs text-violet-700 font-medium"></p>
                    </div>
                    <div class="child-form-field">
                        <label class="child-form-label" for="childGender">Gender</label>
                        <select id="childGender" class="child-form-input">
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>

                <div class="child-form-field">
                    <label class="child-form-label" for="childRelationship">Relationship</label>
                    <select id="childRelationship" class="child-form-input">
                        <option value="">Select relationship</option>
                        @foreach ($childRelationshipOptions as $relationshipOption)
                            <option value="{{ $relationshipOption }}">{{ $relationshipOption }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="child-form-field">
                    <span class="child-form-label">School / work status</span>
                    <div class="child-segmented-control" role="radiogroup" aria-label="School or work status">
                        <label class="child-segment-option">
                            <input type="radio" name="childCurrentlyStudying" value="infant" />
                            <span class="material-symbols-outlined text-[18px]">child_care</span>
                            Infant
                        </label>
                        <label class="child-segment-option">
                            <input type="radio" name="childCurrentlyStudying" value="yes" checked />
                            <span class="material-symbols-outlined text-[18px]">school</span>
                            Studying
                        </label>
                        <label class="child-segment-option">
                            <input type="radio" name="childCurrentlyStudying" value="no" />
                            <span class="material-symbols-outlined text-[18px]">work</span>
                            Working
                        </label>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-snug">Choose <span class="font-semibold text-violet-700">Infant</span> for newborns and babies not yet in school.</p>
                </div>

                <div id="childSchoolWrap" class="child-form-field">
                    <label class="child-form-label" for="childSchool">School</label>
                    <input id="childSchool" type="text" placeholder="School name" class="child-form-input" />
                </div>

                <div id="childEmploymentWrap" class="hidden space-y-4">
                    <div class="child-form-field">
                        <label class="child-form-label" for="childEducationalAttainment">Educational Attainment</label>
                        <select id="childEducationalAttainment" class="child-form-input">
                            <option value="">Select educational attainment</option>
                            @foreach ($educationalAttainmentOptions as $attainmentOption)
                                <option value="{{ $attainmentOption }}">{{ $attainmentOption }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="child-form-field">
                        <label class="child-form-label" for="childOccupation">Occupation</label>
                        <input id="childOccupation" type="text" placeholder="e.g., Farmer, Vendor, None" class="child-form-input" />
                    </div>
                </div>

                <div class="child-modal-footer-mobile sm:hidden pt-1 space-y-2 pb-[max(0.5rem,env(safe-area-inset-bottom,0px))]">
                    <button type="button" class="child-member-save-btn w-full h-11 px-4 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-md shadow-blue-600/20">Save child</button>
                    <button type="button" class="child-member-cancel-btn w-full h-11 px-4 rounded-full border border-gray-300 bg-white text-sm font-semibold">Cancel</button>
                </div>
            </div>

            <div class="child-modal-footer hidden sm:flex px-4 sm:px-5 py-4 border-t border-gray-100 flex-col-reverse sm:flex-row sm:justify-end gap-2 bg-white/95 backdrop-blur">
                <button type="button" class="child-member-cancel-btn w-full sm:w-auto h-11 px-5 rounded-full border border-gray-300 bg-white text-sm font-semibold hover:bg-gray-50">Cancel</button>
                <button type="button" class="child-member-save-btn w-full sm:w-auto h-11 px-5 rounded-full bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-md shadow-blue-600/20">Save child</button>
            </div>
        </div>
    </div>

    <div id="noticeModal" class="hidden fixed inset-0 z-[90] bg-black/50 verification-modal-overlay">
        <div class="verification-modal-panel w-full max-w-sm rounded-xl bg-white border border-gray-200 shadow-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200">
                <h3 id="noticeTitle" class="text-base font-bold text-gray-900 pr-2">Message</h3>
            </div>
            <div class="px-4 py-4">
                <p id="noticeMessage" class="text-sm text-gray-700 leading-relaxed break-words"></p>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 bg-white">
                <button id="noticeOkBtn" type="button" class="w-full h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700">OK</button>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="hidden fixed inset-0 z-[90] bg-black/50 verification-modal-overlay">
        <div class="verification-modal-panel w-full max-w-sm rounded-2xl bg-white border border-blue-100 shadow-2xl overflow-hidden">
            <div class="px-4 sm:px-5 py-4 border-b border-blue-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">verified</span>
                <h3 id="confirmTitle" class="text-base font-bold text-gray-900">Confirm</h3>
            </div>
            <div class="px-4 sm:px-5 py-5">
                <p id="confirmMessage" class="text-sm text-gray-700 leading-relaxed break-words"></p>
            </div>
            <div class="px-4 sm:px-5 py-4 border-t border-blue-100 bg-white flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button id="confirmCancelBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg border border-gray-300 text-sm font-semibold hover:bg-gray-50">Cancel</button>
                <button id="confirmOkBtn" type="button" class="w-full sm:w-auto h-11 px-4 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 shadow-sm">Save</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const requestSent = @json((bool) ($requestSent ?? false));

        function showVerificationSentFooterLinks() {
            document.getElementById('backToLoginLink')?.classList.add('hidden');
            document.getElementById('goHomeLink')?.classList.remove('hidden');
        }

        const firstNameInput = document.getElementById('first_name');
        const lastNameInput = document.getElementById('last_name');
        const middleInitialInput = document.getElementById('middle_initial');
        const fullNameInput = document.getElementById('full_name');

        const parentRoleSelect = document.getElementById('parent_role');
        const civilStatusSelect = document.getElementById('civil_status');
        const placeOfBirthInput = document.getElementById('place_of_birth');
        const educationalAttainmentSelect = document.getElementById('educational_attainment');
        const genderInput = document.getElementById('gender_display');
        const spouseSectionStatuses = @json($spouseSectionStatuses);
        const requiredSpouseStatuses = @json($requiredSpouseStatuses);
        const spouseSection = document.getElementById('spouseSection');
        const spouseSectionTitle = document.getElementById('spouseSectionTitle');
        const spouseSectionHint = document.getElementById('spouseSectionHint');
        const spouseFirstNameInput = document.getElementById('spouse_first_name');
        const spouseLastNameInput = document.getElementById('spouse_last_name');
        const spouseMiddleInitialInput = document.getElementById('spouse_middle_initial');
        const spouseBirthdateInput = document.getElementById('spouse_birthdate_display');
        const spouseBirthdateField = document.getElementById('spouseBirthdateField');
        const spouseBirthdateClientError = document.getElementById('spouseBirthdateClientError');
        const spouseParentRoleSelect = document.getElementById('spouse_parent_role');
        const spouseGenderInput = document.getElementById('spouse_gender_display');
        const spouseCivilStatusDisplay = document.getElementById('spouse_civil_status_display');
        const spousePlaceOfBirthInput = document.getElementById('spouse_place_of_birth');
        const spouseEducationalAttainmentSelect = document.getElementById('spouse_educational_attainment');
        const spouseFullNameInput = document.getElementById('spouse_full_name');
        const childrenSection = document.getElementById('childrenSection');
        const childrenSummaryList = document.getElementById('childrenSummaryList');
        const childrenEmptyHint = document.getElementById('childrenEmptyHint');
        const childrenCountBadge = document.getElementById('childrenCountBadge');
        const childrenHiddenFields = document.getElementById('childrenHiddenFields');
        const openChildModalBtn = document.getElementById('openChildModalBtn');
        const childMemberModal = document.getElementById('childMemberModal');
        const childModalPanel = childMemberModal?.querySelector('.verification-modal-panel');
        const childModalBody = childMemberModal?.querySelector('.child-modal-body');
        const childMemberModalTitle = document.getElementById('childMemberModalTitle');
        const childMemberCloseBtn = document.getElementById('childMemberCloseBtn');
        const childFullNameInput = document.getElementById('childFullName');
        const childBirthdateInput = document.getElementById('childBirthdate');
        const childAgeHint = document.getElementById('childAgeHint');
        const childGenderSelect = document.getElementById('childGender');
        const childRelationshipSelect = document.getElementById('childRelationship');
        const childSchoolWrap = document.getElementById('childSchoolWrap');
        const childSchoolInput = document.getElementById('childSchool');
        const childEmploymentWrap = document.getElementById('childEmploymentWrap');
        const childEducationalAttainmentSelect = document.getElementById('childEducationalAttainment');
        const childOccupationInput = document.getElementById('childOccupation');
        const childStudyingRadios = document.querySelectorAll('input[name="childCurrentlyStudying"]');
        const seniorsSection = document.getElementById('seniorsSection');
        const seniorsSummaryList = document.getElementById('seniorsSummaryList');
        const seniorsEmptyHint = document.getElementById('seniorsEmptyHint');
        const seniorsCountBadge = document.getElementById('seniorsCountBadge');
        const seniorsHiddenFields = document.getElementById('seniorsHiddenFields');
        const openSeniorModalBtn = document.getElementById('openSeniorModalBtn');
        const seniorMemberModal = document.getElementById('seniorMemberModal');
        const seniorModalPanel = seniorMemberModal?.querySelector('.verification-modal-panel');
        const seniorModalBody = seniorMemberModal?.querySelector('.senior-modal-body');
        const seniorMemberModalTitle = document.getElementById('seniorMemberModalTitle');
        const seniorMemberCloseBtn = document.getElementById('seniorMemberCloseBtn');
        const seniorFullNameInput = document.getElementById('seniorFullName');
        const seniorBirthdateInput = document.getElementById('seniorBirthdate');
        const seniorAgeHint = document.getElementById('seniorAgeHint');
        const seniorGenderSelect = document.getElementById('seniorGender');
        const seniorRelationshipSelect = document.getElementById('seniorRelationship');
        const seniorPlaceOfBirthInput = document.getElementById('seniorPlaceOfBirth');
        const seniorEducationalAttainmentSelect = document.getElementById('seniorEducationalAttainment');
        const seniorOccupationInput = document.getElementById('seniorOccupation');
        const SENIOR_MIN_AGE = 60;
        const CONTACT_MAX_DIGITS = 11;
        const PERSON_NAME_PATTERN = /^[A-Za-z,]+(?: [A-Za-z,]+)*$/;
        const CHILD_FULL_NAME_PATTERN = /^[A-Za-z,.]+(?: [A-Za-z,.]+)*$/;
        const TEXT_FIELD_PATTERN = /^[A-Za-z0-9,]+(?: [A-Za-z0-9,]+)*$/;
        const STRING_SPECIAL_CHAR_MESSAGE = 'Only comma (,) is allowed as a special character.';
        const CHILD_FULL_NAME_SPECIAL_CHAR_MESSAGE = 'Only comma (,) and period (.) are allowed as special characters.';

        function capitalizeWords(value) {
            return (value || '')
                .toLowerCase()
                .replace(/(^|\s)\S/g, (segment) => segment.toUpperCase());
        }

        function sanitizePersonName(value) {
            return (value || '').replace(/[^A-Za-z,\s]/g, '').replace(/\s+/g, ' ');
        }

        function sanitizeChildFullName(value) {
            return (value || '').replace(/[^A-Za-z,.\s]/g, '').replace(/\s+/g, ' ');
        }

        function sanitizeTextField(value) {
            return (value || '').replace(/[^A-Za-z0-9,\s]/g, '').replace(/\s+/g, ' ');
        }

        function formatChildFullName(value) {
            return capitalizeWords(sanitizeChildFullName(value));
        }

        function formatTextField(value) {
            return capitalizeWords(sanitizeTextField(value));
        }

        function formatPersonName(value) {
            return capitalizeWords(sanitizePersonName(value));
        }

        function sanitizeContactNumber(value) {
            return (value || '').replace(/\D/g, '').slice(0, CONTACT_MAX_DIGITS);
        }

        function formatMiddleInitialValue(value) {
            return (value || '').replace(/[^A-Za-z]/g, '').slice(0, 1).toUpperCase();
        }

        function applyFormattedValueToInput(input, formatted) {
            if (!input || formatted === input.value) {
                return;
            }

            const cursor = input.selectionStart;
            const diff = input.value.length - formatted.length;
            input.value = formatted;
            const nextCursor = Math.min(Math.max(0, (cursor ?? formatted.length) - diff), formatted.length);
            input.setSelectionRange(nextCursor, nextCursor);
        }

        function applyCapitalizeWordsToInput(input) {
            if (!input) {
                return;
            }

            applyFormattedValueToInput(input, capitalizeWords(input.value));
        }

        function applyPersonNameToInput(input) {
            if (!input) {
                return;
            }

            applyFormattedValueToInput(input, formatPersonName(input.value));
        }

        function bindCapitalizeWordsInput(input, onChange = null) {
            if (!input) {
                return;
            }

            const apply = () => {
                applyCapitalizeWordsToInput(input);
                onChange?.();
            };

            input.addEventListener('input', apply);
            input.addEventListener('blur', apply);

            if (input.value) {
                input.value = capitalizeWords(input.value);
            }
        }

        function applyTextFieldToInput(input) {
            if (!input) {
                return;
            }

            applyFormattedValueToInput(input, formatTextField(input.value));
        }

        function bindTextFieldInput(input, onChange = null) {
            if (!input) {
                return;
            }

            const apply = () => {
                applyTextFieldToInput(input);
                onChange?.();
            };

            input.addEventListener('input', apply);
            input.addEventListener('blur', apply);

            if (input.value) {
                input.value = formatTextField(input.value);
            }
        }

        function bindChildFullNameInput(input, onChange = null) {
            if (!input) {
                return;
            }

            const apply = () => {
                applyFormattedValueToInput(input, formatChildFullName(input.value));
                onChange?.();
            };

            input.addEventListener('input', apply);
            input.addEventListener('blur', apply);

            if (input.value) {
                input.value = formatChildFullName(input.value);
            }
        }

        function bindPersonNameInput(input, onChange = null) {
            if (!input) {
                return;
            }

            const apply = () => {
                applyPersonNameToInput(input);
                onChange?.();
            };

            input.addEventListener('input', apply);
            input.addEventListener('blur', apply);

            if (input.value) {
                input.value = formatPersonName(input.value);
            }
        }

        function bindContactInput(input) {
            if (!input) {
                return;
            }

            const apply = () => {
                applyFormattedValueToInput(input, sanitizeContactNumber(input.value));
            };

            input.addEventListener('input', apply);
            input.addEventListener('blur', apply);

            if (input.value) {
                input.value = sanitizeContactNumber(input.value);
            }
        }

        function bindMiddleInitialInput(input, onChange = null) {
            if (!input) {
                return;
            }

            const apply = () => {
                input.value = formatMiddleInitialValue(input.value);
                onChange?.();
            };

            input.addEventListener('input', apply);
            input.addEventListener('blur', apply);

            if (input.value) {
                apply();
            }
        }

        function normalizeCapitalizedFieldValues() {
            [
                firstNameInput,
                lastNameInput,
                spouseFirstNameInput,
                spouseLastNameInput,
            ].forEach((input) => {
                if (input?.value) {
                    input.value = formatPersonName(input.value);
                }
            });

            if (childFullNameInput?.value) {
                childFullNameInput.value = formatChildFullName(childFullNameInput.value);
            }

            if (seniorFullNameInput?.value) {
                seniorFullNameInput.value = formatChildFullName(seniorFullNameInput.value);
            }

            [
                placeOfBirthInput,
                spousePlaceOfBirthInput,
                childSchoolInput,
                childOccupationInput,
                seniorPlaceOfBirthInput,
                seniorOccupationInput,
                idOtherLabelInput,
            ].forEach((input) => {
                if (input?.value) {
                    input.value = formatTextField(input.value);
                }
            });

            if (middleInitialInput?.value) {
                middleInitialInput.value = formatMiddleInitialValue(middleInitialInput.value);
            }

            if (spouseMiddleInitialInput?.value) {
                spouseMiddleInitialInput.value = formatMiddleInitialValue(spouseMiddleInitialInput.value);
            }

            if (contactInput?.value) {
                contactInput.value = sanitizeContactNumber(contactInput.value);
            }
        }

        function isValidPersonName(value) {
            const trimmed = (value || '').trim();
            return trimmed !== '' && PERSON_NAME_PATTERN.test(trimmed);
        }

        function isValidChildFullName(value) {
            const trimmed = (value || '').trim();
            return trimmed !== '' && CHILD_FULL_NAME_PATTERN.test(trimmed);
        }

        function isValidTextField(value) {
            const trimmed = (value || '').trim();
            return trimmed !== '' && TEXT_FIELD_PATTERN.test(trimmed);
        }

        const initialHouseholdChildren = @json($storedChildren);
        let householdChildren = Array.isArray(initialHouseholdChildren) ? [...initialHouseholdChildren] : [];
            householdChildren = householdChildren.map((child) => ({
            ...child,
            full_name: formatChildFullName(child.full_name || ''),
            school: formatTextField(child.school || ''),
            occupation: formatTextField(child.occupation || ''),
        }));
        const initialHouseholdSeniors = @json($storedSeniors);
        let householdSeniors = Array.isArray(initialHouseholdSeniors) ? [...initialHouseholdSeniors] : [];
        householdSeniors = householdSeniors.map((senior) => ({
            ...senior,
            full_name: formatChildFullName(senior.full_name || ''),
            place_of_birth: formatTextField(senior.place_of_birth || ''),
            occupation: formatTextField(senior.occupation || ''),
        }));
        let editingSeniorIndex = null;
        let editingChildIndex = null;
        const birthdateInput = document.getElementById('birthdate_display');
        const birthdateField = document.getElementById('birthdateField');
        const birthdateClientError = document.getElementById('birthdateClientError');
        const contactInput = document.getElementById('contact');

        const openTrackModalBtn = document.getElementById('openTrackModalBtn');
        const openTrackModalBtnLabel = document.getElementById('openTrackModalBtnLabel');
        const trackModal = document.getElementById('trackModal');
        const closeTrackModalBtn = document.getElementById('closeTrackModalBtn');
        const cancelTrackModalBtn = document.getElementById('cancelTrackModalBtn');
        const saveTrackModalBtn = document.getElementById('saveTrackModalBtn');
        const trackModalBody = document.getElementById('trackModalBody');
        const useCurrentPositionBtn = document.getElementById('useCurrentPositionBtn');
        const barangayInput = document.getElementById('barangay');
        const purokInput = document.getElementById('purok');
        const locationModeInput = document.getElementById('location_mode');
        const savedLocationModeBadge = document.getElementById('savedLocationModeBadge');
        const trackModeMapBtn = document.getElementById('trackModeMapBtn');
        const trackModeManualBtn = document.getElementById('trackModeManualBtn');
        const trackMapPanel = document.getElementById('trackMapPanel');
        const trackManualPanel = document.getElementById('trackManualPanel');
        const manualBarangaySelect = document.getElementById('manualBarangaySelect');
        const manualPurokSelect = document.getElementById('manualPurokSelect');
        const savedBarangayText = document.getElementById('savedBarangayText');
        const savedBarangayTextValue = document.getElementById('savedBarangayTextValue');
        const savedPurokWrap = document.getElementById('savedPurokWrap');
        const savedPurokTextValue = document.getElementById('savedPurokTextValue');
        const addressInput = document.getElementById('address');
        const noticeModal = document.getElementById('noticeModal');
        const noticeTitle = document.getElementById('noticeTitle');
        const noticeMessage = document.getElementById('noticeMessage');
        const noticeOkBtn = document.getElementById('noticeOkBtn');
        const confirmModal = document.getElementById('confirmModal');
        const confirmTitle = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        const confirmOkBtn = document.getElementById('confirmOkBtn');
        const mapLoadingOverlay = document.getElementById('mapLoadingOverlay');
        const mapLoadingText = document.getElementById('mapLoadingText');
        const barangayBoundaries = @json($barangayBoundaries ?? []);
        const purokBoundaries = @json($purokBoundaries ?? []);
        const purokOptions = @json($purokOptions ?? []);
        const NON_PUROK_NAME = 'Non'; // excluded from user-facing purok options

        function isRealPurokName(name) {
            return (name || '').trim() !== '' && (name || '').trim() !== NON_PUROK_NAME;
        }
        let activeTrackMode = (locationModeInput?.value || 'map') === 'manual' ? 'manual' : 'map';

        function getLocationMode() {
            return activeTrackMode === 'manual' ? 'manual' : 'map';
        }

        function setTrackMode(mode) {
            activeTrackMode = mode === 'manual' ? 'manual' : 'map';

            if (locationModeInput) {
                locationModeInput.value = activeTrackMode;
            }

            trackMapPanel?.classList.toggle('hidden', activeTrackMode !== 'map');
            trackManualPanel?.classList.toggle('hidden', activeTrackMode !== 'manual');
            useCurrentPositionBtn?.classList.toggle('hidden', activeTrackMode !== 'map');

            trackModeMapBtn?.classList.toggle('bg-white', activeTrackMode === 'map');
            trackModeMapBtn?.classList.toggle('text-blue-700', activeTrackMode === 'map');
            trackModeMapBtn?.classList.toggle('shadow-sm', activeTrackMode === 'map');
            trackModeMapBtn?.classList.toggle('text-gray-600', activeTrackMode !== 'map');

            trackModeManualBtn?.classList.toggle('bg-white', activeTrackMode === 'manual');
            trackModeManualBtn?.classList.toggle('text-blue-700', activeTrackMode === 'manual');
            trackModeManualBtn?.classList.toggle('shadow-sm', activeTrackMode === 'manual');
            trackModeManualBtn?.classList.toggle('text-gray-600', activeTrackMode !== 'manual');

            if (saveTrackModalBtn) {
                saveTrackModalBtn.disabled = requestSent;
            }
        }

        function populateManualPurokOptions(barangayName, selectedPurok = '') {
            if (!manualPurokSelect) {
                return;
            }

            const normalizedBarangay = (barangayName || '').trim().toLowerCase();
            const options = Array.isArray(purokOptions)
                ? purokOptions.filter((purok) => {
                    const sameBarangay = (purok.barangay_name || '').trim().toLowerCase() === normalizedBarangay;
                    return sameBarangay && isRealPurokName(purok.name);
                })
                : [];

            manualPurokSelect.innerHTML = '<option value="">Select purok</option>';

            options.forEach((purok) => {
                const option = document.createElement('option');
                option.value = purok.name;
                option.textContent = purok.name;
                if (selectedPurok && selectedPurok === purok.name) {
                    option.selected = true;
                }
                manualPurokSelect.appendChild(option);
            });

            if (selectedPurok && isRealPurokName(selectedPurok) && !options.some((purok) => purok.name === selectedPurok)) {
                const fallback = document.createElement('option');
                fallback.value = selectedPurok;
                fallback.textContent = selectedPurok;
                fallback.selected = true;
                manualPurokSelect.appendChild(fallback);
            }
        }

        function applySavedLocationDisplay(barangayValue, purokValue, mode = getLocationMode()) {
            if (barangayInput) {
                barangayInput.value = barangayValue;
            }

            if (purokInput) {
                purokInput.value = purokValue;
            }

            if (locationModeInput) {
                locationModeInput.value = mode;
            }

            if (savedBarangayTextValue) {
                savedBarangayTextValue.textContent = barangayValue;
            }

            if (savedPurokTextValue) {
                savedPurokTextValue.textContent = purokValue;
            }

            if (savedPurokWrap) {
                savedPurokWrap.classList.toggle('hidden', !isRealPurokName(purokValue));
            }

            if (savedLocationModeBadge) {
                savedLocationModeBadge.textContent = mode === 'manual' ? 'Manual selection' : 'Map-based area';
                savedLocationModeBadge.classList.toggle('hidden', !barangayValue);
            }

            if (savedBarangayText) {
                savedBarangayText.classList.toggle('hidden', !barangayValue);
            }

            updateTrackButtonAppearance();
        }

        const verificationForm = document.getElementById('verificationForm');
        const idTypeSelect = document.getElementById('id_type');
        const idOtherWrap = document.getElementById('idOtherWrap');
        const idPhotoWrap = document.getElementById('idPhotoWrap');
        const idOtherLabelInput = document.getElementById('id_other_label');
        const idPhotoFrontInput = document.getElementById('id_photo_front');
        const idPhotoBackInput = document.getElementById('id_photo_back');
        const startIdCaptureBtn = document.getElementById('startIdCaptureBtn');
        const startIdCaptureBtnLabel = document.getElementById('startIdCaptureBtnLabel');
        const idCameraModal = document.getElementById('idCameraModal');
        const idCameraModalTitle = document.getElementById('idCameraModalTitle');
        const idCameraModalSubtitle = document.getElementById('idCameraModalSubtitle');
        const idCameraVideo = document.getElementById('idCameraVideo');
        const idCameraPreviewImg = document.getElementById('idCameraPreviewImg');
        const idCameraLiveWrap = document.getElementById('idCameraLiveWrap');
        const idCameraLiveControls = document.getElementById('idCameraLiveControls');
        const idCameraStatusText = document.getElementById('idCameraStatusText');
        const idCameraHintText = document.getElementById('idCameraHintText');
        const ID_CAMERA_READY_MESSAGE = 'Make sure your whole ID is inside the box and clearly visible before you capture.';
        const idCameraStabilityFill = document.getElementById('idCameraStabilityFill');
        const idCameraCaptureBtn = document.getElementById('idCameraCaptureBtn');
        const idCameraUsePhotoBtn = document.getElementById('idCameraUsePhotoBtn');
        const idCameraRetakeBtn = document.getElementById('idCameraRetakeBtn');
        const idCameraCancelBtn = document.getElementById('idCameraCancelBtn');
        const idCameraCloseBtn = document.getElementById('idCameraCloseBtn');
        const maxIdPhotoBytes = 5 * 1024 * 1024;
        const allowedIdPhotoTypes = ['image/jpeg', 'image/jpg'];
        let frontPhotoObjectUrl = null;
        let backPhotoObjectUrl = null;
        let idCameraStream = null;
        let idCameraAnalysisTimer = null;
        let idCameraStep = 'front';
        let idCameraPreviewBlob = null;
        let idCameraLastGrayFrame = null;
        let idCameraSteadyFrames = 0;
        let idCameraCaptureReady = false;
        const ID_CAMERA_ANALYSIS_MS = 100;
        const ID_CAMERA_STEADY_FRAMES = 8;
        const ID_CAMERA_MOTION_THRESHOLD = 10;
        const ID_CAMERA_SHARPNESS_THRESHOLD = 70;
        const idCameraAnalysisCanvas = document.createElement('canvas');
        const LOCATION_CONSENT_STORAGE_KEY = 'verification_location_consent';
        const locationConsentBanner = document.getElementById('locationConsentBanner');
        const locationConsentAcceptBtn = document.getElementById('locationConsentAcceptBtn');
        const locationConsentDeclineBtn = document.getElementById('locationConsentDeclineBtn');
        const locationConsentCloseBtn = document.getElementById('locationConsentCloseBtn');
        const locationConsentInput = document.getElementById('locationConsentInput');
        const privacyConsentBanner = document.getElementById('privacyConsentBanner');
        const privacyConsentCheckbox = document.getElementById('privacyConsentCheckbox');
        const privacyConsentAcceptBtn = document.getElementById('privacyConsentAcceptBtn');
        const privacyConsentDeclineBtn = document.getElementById('privacyConsentDeclineBtn');
        const privacyConsentCloseBtn = document.getElementById('privacyConsentCloseBtn');
        const privacyConsentInput = document.getElementById('privacyConsentInput');
        let locationConsentAccepted = sessionStorage.getItem(LOCATION_CONSENT_STORAGE_KEY) === '1'
            || locationConsentInput?.value === '1';
        let privacyConsentAccepted = privacyConsentInput?.value === '1';
        let pendingFormSubmit = false;

        function showConsentBanner(banner) {
            if (!banner) {
                return;
            }

            banner.classList.remove('hidden');
            requestAnimationFrame(() => {
                banner.classList.add('is-visible');
            });
        }

        function hideConsentBanner(banner) {
            if (!banner) {
                return;
            }

            banner.classList.remove('is-visible');
            setTimeout(() => {
                banner.classList.add('hidden');
            }, 280);
        }

        function setLocationConsent(accepted) {
            locationConsentAccepted = accepted;
            if (locationConsentInput) {
                locationConsentInput.value = accepted ? '1' : '';
            }
            if (accepted) {
                sessionStorage.setItem(LOCATION_CONSENT_STORAGE_KEY, '1');
            } else {
                sessionStorage.removeItem(LOCATION_CONSENT_STORAGE_KEY);
            }
        }

        function setPrivacyConsent(accepted) {
            privacyConsentAccepted = accepted;
            if (privacyConsentInput) {
                privacyConsentInput.value = accepted ? '1' : '';
            }
        }

        function setProfileBirthdateMaxBeforeToday(input) {
            if (!input) {
                return;
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            input.max = yesterday.toISOString().slice(0, 10);
        }

        function clearProfileBirthdateError(input, errorEl) {
            input?.classList.remove('has-birthdate-error');

            if (errorEl) {
                errorEl.textContent = '';
                errorEl.classList.add('hidden');
            }
        }

        function showProfileBirthdateError(input, errorEl, fieldEl, message) {
            clearProfileBirthdateError(input, errorEl);
            input?.classList.add('has-birthdate-error');

            if (errorEl) {
                errorEl.textContent = message;
                errorEl.classList.remove('hidden');
            }

            fieldEl?.scrollIntoView({ behavior: 'smooth', block: 'center' });

            window.setTimeout(() => {
                input?.focus();
            }, 280);
        }

        function validateProfileBirthdate(input, errorEl, fieldEl, subjectLabel) {
            const value = (input?.value || '').trim();

            if (!value) {
                showProfileBirthdateError(input, errorEl, fieldEl, `Please enter ${subjectLabel} birthdate.`);
                return false;
            }

            const parsed = new Date(`${value}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (Number.isNaN(parsed.getTime()) || parsed >= today) {
                showProfileBirthdateError(input, errorEl, fieldEl, `Please choose a valid birthdate for ${subjectLabel} before today.`);
                return false;
            }

            clearProfileBirthdateError(input, errorEl);
            return true;
        }

        function bindProfileBirthdateInput(input, errorEl) {
            input?.addEventListener('change', () => clearProfileBirthdateError(input, errorEl));
        }

        setProfileBirthdateMaxBeforeToday(birthdateInput);
        setProfileBirthdateMaxBeforeToday(spouseBirthdateInput);
        bindProfileBirthdateInput(birthdateInput, birthdateClientError);
        bindProfileBirthdateInput(spouseBirthdateInput, spouseBirthdateClientError);

        function syncGenderFromParentRole() {
            if (!genderInput || !parentRoleSelect) {
                return;
            }

            const role = (parentRoleSelect.value || '').trim();
            genderInput.value = role === 'Father' ? 'Male' : (role === 'Mother' ? 'Female' : '');
            updateSpouseSectionCopy();
            suggestSpouseParentRole();
        }

        function syncSpouseGenderFromParentRole() {
            if (!spouseGenderInput || !spouseParentRoleSelect) {
                return;
            }

            const role = (spouseParentRoleSelect.value || '').trim();
            spouseGenderInput.value = role === 'Father' ? 'Male' : (role === 'Mother' ? 'Female' : '');
        }

        function showsSpouseSection() {
            const status = (civilStatusSelect?.value || '').trim();
            return spouseSectionStatuses.includes(status);
        }

        function requiresSpouseDetails() {
            const status = (civilStatusSelect?.value || '').trim();
            return requiredSpouseStatuses.includes(status);
        }

        function hasAnySpouseInput() {
            return Boolean(
                (spouseFirstNameInput?.value || '').trim()
                || (spouseLastNameInput?.value || '').trim()
                || (spouseMiddleInitialInput?.value || '').trim()
                || (spouseBirthdateInput?.value || '').trim()
                || (spouseParentRoleSelect?.value || '').trim()
                || (spousePlaceOfBirthInput?.value || '').trim()
                || (spouseEducationalAttainmentSelect?.value || '').trim()
            );
        }

        function spouseRoleLabel() {
            const parentRole = (parentRoleSelect?.value || '').trim();
            if (parentRole === 'Father') {
                return 'Wife';
            }
            if (parentRole === 'Mother') {
                return 'Husband';
            }
            return 'Husband / Wife';
        }

        function buildSpouseFullName(firstName, middleInitial, lastName) {
            const middleSegment = middleInitial ? `${middleInitial.charAt(0).toUpperCase()}.` : '';
            return [firstName, middleSegment, lastName].filter(Boolean).join(' ').trim();
        }

        function updateHiddenSpouseFullName() {
            if (!spouseFullNameInput) {
                return;
            }

            const firstName = (spouseFirstNameInput?.value || '').trim();
            const lastName = (spouseLastNameInput?.value || '').trim();
            const middleInitialRaw = (spouseMiddleInitialInput?.value || '').trim();
            const middleInitial = middleInitialRaw ? middleInitialRaw.replace(/[^A-Za-z]/g, '').slice(0, 1).toUpperCase() : '';
            spouseFullNameInput.value = buildSpouseFullName(firstName, middleInitial, lastName);
        }

        function updateSpouseSectionCopy() {
            const label = spouseRoleLabel();
            if (spouseSectionTitle) {
                spouseSectionTitle.textContent = label;
            }
            if (spouseSectionHint) {
                const status = (civilStatusSelect?.value || '').trim();
                if (status === 'Separated') {
                    spouseSectionHint.textContent = `Optional: add your ${label.toLowerCase()}'s details if you want them on record.`;
                } else {
                    spouseSectionHint.textContent = `Enter your ${label.toLowerCase()}'s details (same fields as the representative).`;
                }
            }
        }

        function suggestSpouseParentRole() {
            if (!spouseParentRoleSelect || (spouseParentRoleSelect.value || '').trim()) {
                syncSpouseGenderFromParentRole();
                return;
            }

            const parentRole = (parentRoleSelect?.value || '').trim();
            if (parentRole === 'Father') {
                spouseParentRoleSelect.value = 'Mother';
            } else if (parentRole === 'Mother') {
                spouseParentRoleSelect.value = 'Father';
            }

            syncSpouseGenderFromParentRole();
        }

        function clearSpouseFields() {
            if (spouseFirstNameInput) spouseFirstNameInput.value = '';
            if (spouseLastNameInput) spouseLastNameInput.value = '';
            if (spouseMiddleInitialInput) spouseMiddleInitialInput.value = '';
            if (spouseBirthdateInput) spouseBirthdateInput.value = '';
            if (spouseParentRoleSelect) spouseParentRoleSelect.value = '';
            if (spouseGenderInput) spouseGenderInput.value = '';
            if (spousePlaceOfBirthInput) spousePlaceOfBirthInput.value = '';
            if (spouseEducationalAttainmentSelect) spouseEducationalAttainmentSelect.value = '';
            if (spouseFullNameInput) spouseFullNameInput.value = '';
            clearProfileBirthdateError(spouseBirthdateInput, spouseBirthdateClientError);
        }

        function syncSpouseSectionVisibility() {
            const shouldShow = showsSpouseSection();
            spouseSection?.classList.toggle('hidden', !shouldShow);

            if (spouseCivilStatusDisplay) {
                spouseCivilStatusDisplay.value = (civilStatusSelect?.value || '').trim();
            }

            if (!shouldShow) {
                clearSpouseFields();
                return;
            }

            updateSpouseSectionCopy();
            suggestSpouseParentRole();
            updateHiddenSpouseFullName();
        }

        function validateSpouseDetails() {
            if (!showsSpouseSection()) {
                return true;
            }

            if (!requiresSpouseDetails() && !hasAnySpouseInput()) {
                return true;
            }

            updateHiddenSpouseFullName();
            syncSpouseGenderFromParentRole();

            const label = spouseRoleLabel().toLowerCase();
            const firstName = (spouseFirstNameInput?.value || '').trim();
            const lastName = (spouseLastNameInput?.value || '').trim();
            const parentRole = (spouseParentRoleSelect?.value || '').trim();
            const placeOfBirth = (spousePlaceOfBirthInput?.value || '').trim();
            const educationalAttainment = (spouseEducationalAttainmentSelect?.value || '').trim();
            const repParentRole = (parentRoleSelect?.value || '').trim();

            if (!firstName) {
                showNotice('Required Field', `Please enter your ${label}'s first name.`);
                spouseFirstNameInput?.focus();
                return false;
            }

            if (!isValidPersonName(firstName)) {
                showNotice('Invalid Name', `${spouseRoleLabel()}'s first name may only use letters, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                spouseFirstNameInput?.focus();
                return false;
            }

            if (!lastName) {
                showNotice('Required Field', `Please enter your ${label}'s last name.`);
                spouseLastNameInput?.focus();
                return false;
            }

            if (!isValidPersonName(lastName)) {
                showNotice('Invalid Name', `${spouseRoleLabel()}'s last name may only use letters, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                spouseLastNameInput?.focus();
                return false;
            }

            if (!validateProfileBirthdate(spouseBirthdateInput, spouseBirthdateClientError, spouseBirthdateField, `your ${label}'s`)) {
                return false;
            }

            if (!parentRole) {
                showNotice('Required Field', `Please select your ${label}'s parent role.`);
                spouseParentRoleSelect?.focus();
                return false;
            }

            if (repParentRole && parentRole === repParentRole) {
                showNotice('Invalid Parent Role', `Your ${label}'s parent role must be different from yours.`);
                spouseParentRoleSelect?.focus();
                return false;
            }

            if (!(spouseGenderInput?.value || '').trim()) {
                showNotice('Required Field', `Please select your ${label}'s parent role.`);
                spouseParentRoleSelect?.focus();
                return false;
            }

            if (!placeOfBirth) {
                showNotice('Required Field', `Please enter your ${label}'s place of birth.`);
                spousePlaceOfBirthInput?.focus();
                return false;
            }

            if (!isValidTextField(placeOfBirth)) {
                showNotice('Invalid Place of Birth', `${spouseRoleLabel()}'s place of birth may only use letters, numbers, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                spousePlaceOfBirthInput?.focus();
                return false;
            }

            if (!educationalAttainment) {
                showNotice('Required Field', `Please select your ${label}'s educational attainment.`);
                spouseEducationalAttainmentSelect?.focus();
                return false;
            }

            return true;
        }

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        function formatChildBirthdateLabel(birthdate) {
            if (!birthdate) {
                return 'No birthdate';
            }

            const parsed = new Date(`${birthdate}T00:00:00`);
            if (Number.isNaN(parsed.getTime())) {
                return birthdate;
            }

            return parsed.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
        }

        function childInitialsFromName(name) {
            return (name || '').split(' ').filter(Boolean).slice(0, 2).map((part) => part[0]?.toUpperCase() || '').join('') || 'C';
        }

        function getChildStudyingValue() {
            const selected = document.querySelector('input[name="childCurrentlyStudying"]:checked');
            const value = (selected?.value || '').trim();
            return ['yes', 'no', 'infant'].includes(value) ? value : 'no';
        }

        function getChildAgeInMonths(birthdate) {
            if (!birthdate) {
                return null;
            }

            const born = new Date(`${birthdate}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (Number.isNaN(born.getTime()) || born > today) {
                return null;
            }

            let months = (today.getFullYear() - born.getFullYear()) * 12 + (today.getMonth() - born.getMonth());
            if (today.getDate() < born.getDate()) {
                months -= 1;
            }

            return Math.max(0, months);
        }

        function getChildAgeInYears(birthdate) {
            if (!birthdate) {
                return null;
            }

            const born = new Date(`${birthdate}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (Number.isNaN(born.getTime()) || born > today) {
                return null;
            }

            let years = today.getFullYear() - born.getFullYear();
            if (today.getMonth() < born.getMonth() || (today.getMonth() === born.getMonth() && today.getDate() < born.getDate())) {
                years -= 1;
            }

            return Math.max(0, years);
        }

        const INFANT_MAX_MONTHS = 59;

        function formatYearsAndMonthsLabel(totalMonths) {
            const years = Math.floor(totalMonths / 12);
            const remainder = totalMonths % 12;
            const yearPart = years === 1 ? '1 year' : `${years} years`;

            if (remainder === 0) {
                return `${yearPart} old`;
            }

            const monthPart = remainder === 1 ? '1 month' : `${remainder} months`;

            return `${yearPart} ${monthPart} old`;
        }

        function formatChildAgeLabel(birthdate) {
            if (!birthdate) {
                return '';
            }

            const born = new Date(`${birthdate}T00:00:00`);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (Number.isNaN(born.getTime()) || born > today) {
                return '';
            }

            const diffDays = Math.floor((today - born) / (1000 * 60 * 60 * 24));
            if (diffDays <= 0) {
                return 'Newborn';
            }
            if (diffDays < 30) {
                return diffDays === 1 ? '1 day old' : `${diffDays} days old`;
            }

            const months = getChildAgeInMonths(birthdate);
            if (months === null) {
                return '';
            }
            if (months < 12) {
                return months <= 1 ? '1 month old' : `${months} months old`;
            }
            if (months > INFANT_MAX_MONTHS) {
                const years = getChildAgeInYears(birthdate);
                if (years === null) {
                    return '';
                }

                return years === 1 ? '1 year old' : `${years} years old`;
            }

            return formatYearsAndMonthsLabel(months);
        }

        function setChildBirthdateMaxToToday() {
            if (!childBirthdateInput) {
                return;
            }

            const today = new Date();
            today.setHours(0, 0, 0, 0);
            childBirthdateInput.max = today.toISOString().slice(0, 10);
        }

        function formatChildAgeDetailedLabel(birthdate) {
            const shortLabel = formatChildAgeLabel(birthdate);
            if (!shortLabel) {
                return '';
            }

            return `Age: ${shortLabel} — Infant is recommended for children not yet in school.`;
        }

        function updateChildAgeHint() {
            const birthdate = (childBirthdateInput?.value || '').trim();
            const ageLabel = formatChildAgeDetailedLabel(birthdate);
            const months = getChildAgeInMonths(birthdate);

            if (childAgeHint) {
                if (!ageLabel) {
                    childAgeHint.textContent = '';
                    childAgeHint.classList.add('hidden');
                } else {
                    childAgeHint.textContent = ageLabel;
                    childAgeHint.classList.remove('hidden');
                }
            }

            if (months !== null && months <= 24 && getChildStudyingValue() === 'yes') {
                setChildStudyingValue('infant');
            }
        }

        function syncChildStudyingFields() {
            const status = getChildStudyingValue();
            const isStudying = status === 'yes';
            const isInfant = status === 'infant';
            childSchoolWrap?.classList.toggle('hidden', !isStudying);
            childEmploymentWrap?.classList.toggle('hidden', isStudying || isInfant);

            if (isStudying) {
                if (childEducationalAttainmentSelect) {
                    childEducationalAttainmentSelect.value = '';
                }
                if (childOccupationInput) {
                    childOccupationInput.value = '';
                }
            } else if (isInfant) {
                if (childSchoolInput) childSchoolInput.value = '';
                if (childEducationalAttainmentSelect) childEducationalAttainmentSelect.value = '';
                if (childOccupationInput) childOccupationInput.value = '';
            } else if (childSchoolInput) {
                childSchoolInput.value = '';
            }
        }

        function setChildStudyingValue(value) {
            childStudyingRadios.forEach((radio) => {
                radio.checked = radio.value === value;
            });
            syncChildStudyingFields();
        }

        function resetChildModalLayout() {
            if (childModalPanel) {
                childModalPanel.style.maxHeight = '';
                childModalPanel.style.height = '';
            }

            if (childModalBody) {
                childModalBody.style.paddingBottom = '';
            }
        }

        function adjustChildModalForKeyboard() {
            if (!childMemberModal || childMemberModal.classList.contains('hidden') || !childModalPanel) {
                resetChildModalLayout();
                return;
            }

            if (window.innerWidth >= 640 || !window.visualViewport) {
                resetChildModalLayout();
                return;
            }

            const viewport = window.visualViewport;
            const topInset = Math.max(8, viewport.offsetTop);
            const availableHeight = Math.max(260, viewport.height - topInset - 8);

            childModalPanel.style.maxHeight = `${availableHeight}px`;
            childModalPanel.style.height = `${availableHeight}px`;
        }

        function scrollChildModalFieldIntoView(field) {
            if (!field || !childModalBody || window.innerWidth >= 640) {
                return;
            }

            window.setTimeout(() => {
                adjustChildModalForKeyboard();

                const viewport = window.visualViewport;
                const visibleTop = viewport ? viewport.offsetTop + 12 : 12;
                const visibleBottom = viewport
                    ? viewport.offsetTop + viewport.height - 12
                    : window.innerHeight - 12;
                const fieldRect = field.getBoundingClientRect();

                if (fieldRect.bottom > visibleBottom) {
                    childModalBody.scrollTop += fieldRect.bottom - visibleBottom + 16;
                } else if (fieldRect.top < visibleTop) {
                    childModalBody.scrollTop -= visibleTop - fieldRect.top + 16;
                }
            }, 320);
        }

        function bindChildModalKeyboardHandling() {
            if (!childMemberModal || childMemberModal.dataset.keyboardBound === '1') {
                return;
            }

            childMemberModal.dataset.keyboardBound = '1';

            childMemberModal.addEventListener('focusin', (event) => {
                const field = event.target;
                if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                    scrollChildModalFieldIntoView(field);
                }
            });

            window.visualViewport?.addEventListener('resize', adjustChildModalForKeyboard);
            window.visualViewport?.addEventListener('scroll', adjustChildModalForKeyboard);
        }

        bindChildModalKeyboardHandling();

        function clearChildModalValidation() {
            childMemberModal?.querySelectorAll('.is-invalid').forEach((element) => {
                element.classList.remove('is-invalid');
            });
        }

        function syncChildEmploymentWrapInvalid() {
            const wrap = document.getElementById('childEmploymentWrap');
            if (!wrap) {
                return;
            }

            const hasInvalidChild = Boolean(
                childEducationalAttainmentSelect?.classList.contains('is-invalid')
                || childOccupationInput?.classList.contains('is-invalid')
            );

            wrap.classList.toggle('is-invalid', hasInvalidChild);
        }

        function markChildFieldInvalid(input) {
            if (!input) {
                return;
            }

            input.classList.add('is-invalid');
            input.closest('.child-form-field')?.classList.add('is-invalid');

            if (input.id === 'childEducationalAttainment' || input.id === 'childOccupation') {
                syncChildEmploymentWrapInvalid();
            }
        }

        function clearChildFieldInvalid(field) {
            if (!field) {
                return;
            }

            field.classList.remove('is-invalid');
            field.closest('.child-form-field')?.classList.remove('is-invalid');

            if (field.id === 'childEducationalAttainment' || field.id === 'childOccupation') {
                syncChildEmploymentWrapInvalid();
            }
        }

        function focusChildModalInvalidField(input) {
            if (!input) {
                return;
            }

            input.focus();
            scrollChildModalFieldIntoView(input);
        }

        function bindChildModalValidationClearing() {
            const fields = [
                childFullNameInput,
                childBirthdateInput,
                childGenderSelect,
                childRelationshipSelect,
                childSchoolInput,
                childEducationalAttainmentSelect,
                childOccupationInput,
            ];

            fields.forEach((field) => {
                field?.addEventListener('input', () => clearChildFieldInvalid(field));
                field?.addEventListener('change', () => clearChildFieldInvalid(field));
            });

            childStudyingRadios.forEach((radio) => {
                radio.addEventListener('change', clearChildModalValidation);
            });
        }

        bindChildModalValidationClearing();

        function resetChildModalForm() {
            editingChildIndex = null;
            clearChildModalValidation();
            if (childMemberModalTitle) {
                childMemberModalTitle.textContent = 'Add Child';
            }
            if (childFullNameInput) childFullNameInput.value = '';
            if (childBirthdateInput) childBirthdateInput.value = '';
            if (childGenderSelect) childGenderSelect.value = '';
            if (childRelationshipSelect) childRelationshipSelect.value = '';
            if (childSchoolInput) childSchoolInput.value = '';
            if (childEducationalAttainmentSelect) childEducationalAttainmentSelect.value = '';
            if (childOccupationInput) childOccupationInput.value = '';
            setChildStudyingValue('yes');
            if (childAgeHint) {
                childAgeHint.textContent = '';
                childAgeHint.classList.add('hidden');
            }
        }

        function openChildMemberModal(index = null) {
            if (requestSent) {
                return;
            }

            resetChildModalForm();
            setChildBirthdateMaxToToday();

            if (index !== null && householdChildren[index]) {
                const child = householdChildren[index];
                editingChildIndex = index;
                if (childMemberModalTitle) {
                    childMemberModalTitle.textContent = 'Edit Child';
                }
                if (childFullNameInput) childFullNameInput.value = formatChildFullName(child.full_name || '');
                if (childBirthdateInput) childBirthdateInput.value = child.birthdate || '';
                if (childGenderSelect) childGenderSelect.value = child.gender || '';
                if (childRelationshipSelect) childRelationshipSelect.value = child.relationship || '';
                setChildStudyingValue(['yes', 'no', 'infant'].includes(child.currently_studying) ? child.currently_studying : 'no');

                if (child.currently_studying === 'yes') {
                    if (childSchoolInput) {
                        childSchoolInput.value = formatTextField(child.school || '');
                    }
                } else if (child.currently_studying === 'no') {
                    if (childEducationalAttainmentSelect) {
                        childEducationalAttainmentSelect.value = child.educational_attainment || '';
                    }
                    if (childOccupationInput) {
                        childOccupationInput.value = formatTextField(child.occupation || '');
                    }
                }

                updateChildAgeHint();
            }

            syncChildStudyingFields();

            childMemberModal?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            adjustChildModalForKeyboard();

            if (window.innerWidth >= 640) {
                childFullNameInput?.focus();
            }
        }

        function closeChildMemberModal() {
            childMemberModal?.classList.add('hidden');
            resetChildModalForm();
            resetChildModalLayout();
            restoreBodyOverflowIfNoModal();
        }

        function renderHouseholdChildren() {
            if (!childrenSummaryList || !childrenHiddenFields) {
                return;
            }

            childrenSummaryList.innerHTML = '';
            childrenHiddenFields.innerHTML = '';

            householdChildren.forEach((child, index) => {
                const card = document.createElement('article');
                card.className = 'child-summary-card';
                const studying = child.currently_studying === 'yes';
                const isInfant = child.currently_studying === 'infant';
                const studyingChipClass = isInfant ? 'child-chip-violet' : (studying ? 'child-chip-green' : 'child-chip-amber');
                const studyingLabel = isInfant ? 'Infant' : (studying ? 'Studying' : 'Working');
                const ageLabel = formatChildAgeLabel(child.birthdate);
                const detailLine = isInfant
                    ? (ageLabel || 'Too young for school / work')
                    : studying
                    ? (child.school ? child.school : 'School not specified')
                    : [child.educational_attainment, child.occupation].filter(Boolean).join(' · ') || 'No work or school info';
                const detailIcon = isInfant ? 'child_care' : (studying ? 'school' : 'work');
                const initials = childInitialsFromName(child.full_name || 'Child');

                card.innerHTML = `
                    <div class="child-summary-avatar" aria-hidden="true">${escapeHtml(initials)}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm sm:text-[0.9375rem] font-bold text-gray-900 truncate">${escapeHtml(child.full_name || 'Child')}</p>
                            <div class="child-summary-actions">
                                <button type="button" class="child-action-btn edit-child-btn text-blue-600 hover:bg-blue-50" data-child-index="${index}" aria-label="Edit child">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button" class="child-action-btn remove-child-btn text-red-600 hover:bg-red-50" data-child-index="${index}" aria-label="Remove child">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="child-chip child-chip-blue">${escapeHtml(child.relationship || '—')}</span>
                            <span class="child-chip child-chip-neutral">${escapeHtml(child.gender || '—')}</span>
                            <span class="child-chip child-chip-neutral">${escapeHtml(formatChildBirthdateLabel(child.birthdate))}</span>
                            ${ageLabel ? `<span class="child-chip child-chip-violet">${escapeHtml(ageLabel)}</span>` : ''}
                            <span class="child-chip ${studyingChipClass}">${escapeHtml(studyingLabel)}</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-gray-400 shrink-0 leading-none">${detailIcon}</span>
                            <span class="min-w-0 break-words">${escapeHtml(detailLine)}</span>
                        </p>
                    </div>
                `;

                childrenSummaryList.appendChild(card);

                const fields = [
                    ['full_name', child.full_name || ''],
                    ['birthdate', child.birthdate || ''],
                    ['age_in_months', child.age_in_months ?? ''],
                    ['gender', child.gender || ''],
                    ['relationship', child.relationship || ''],
                    ['currently_studying', child.currently_studying || 'no'],
                    ['school', child.school || ''],
                    ['educational_attainment', child.educational_attainment || ''],
                    ['occupation', child.occupation || ''],
                ];

                fields.forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `children[${index}][${key}]`;
                    input.value = value;
                    childrenHiddenFields.appendChild(input);
                });
            });

            childrenEmptyHint?.classList.toggle('hidden', householdChildren.length > 0);

            if (childrenCountBadge) {
                const count = householdChildren.length;
                childrenCountBadge.textContent = count === 1 ? '1 added' : `${count} added`;
                childrenCountBadge.classList.toggle('hidden', count === 0);
            }

            childrenSummaryList.classList.toggle('hidden', householdChildren.length === 0);

            childrenSummaryList.querySelectorAll('.edit-child-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number.parseInt(button.dataset.childIndex || '', 10);
                    if (Number.isFinite(index)) {
                        openChildMemberModal(index);
                    }
                });
            });

            childrenSummaryList.querySelectorAll('.remove-child-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number.parseInt(button.dataset.childIndex || '', 10);
                    if (Number.isFinite(index)) {
                        householdChildren.splice(index, 1);
                        renderHouseholdChildren();
                    }
                });
            });
        }

        function withChildAgeFields(childData) {
            const months = getChildAgeInMonths(childData.birthdate);
            return {
                ...childData,
                age_in_months: months !== null ? months : '',
            };
        }

        function validateChildModalForm() {
            normalizeCapitalizedFieldValues();
            clearChildModalValidation();

            const fullName = (childFullNameInput?.value || '').trim();
            const birthdate = (childBirthdateInput?.value || '').trim();
            const gender = (childGenderSelect?.value || '').trim();
            const relationship = (childRelationshipSelect?.value || '').trim();
            const currentlyStudying = getChildStudyingValue();
            const educationalAttainment = (childEducationalAttainmentSelect?.value || '').trim();
            const occupation = (childOccupationInput?.value || '').trim();

            let firstInvalidInput = null;

            const flagInvalid = (input) => {
                markChildFieldInvalid(input);
                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
            };

            if (!fullName) {
                flagInvalid(childFullNameInput);
            } else if (!isValidChildFullName(fullName)) {
                flagInvalid(childFullNameInput);
            }

            if (!birthdate) {
                flagInvalid(childBirthdateInput);
            } else {
                const parsedBirthdate = new Date(`${birthdate}T00:00:00`);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (Number.isNaN(parsedBirthdate.getTime()) || parsedBirthdate > today) {
                    flagInvalid(childBirthdateInput);
                }
            }

            if (!gender) {
                flagInvalid(childGenderSelect);
            }

            if (!relationship) {
                flagInvalid(childRelationshipSelect);
            }

            if (currentlyStudying === 'yes') {
                const school = (childSchoolInput?.value || '').trim();
                if (!school) {
                    flagInvalid(childSchoolInput);
                } else if (!isValidTextField(school)) {
                    flagInvalid(childSchoolInput);
                }
            } else if (currentlyStudying === 'no') {
                if (!educationalAttainment) {
                    flagInvalid(childEducationalAttainmentSelect);
                }

                if (!occupation) {
                    flagInvalid(childOccupationInput);
                } else if (occupation && !isValidTextField(occupation)) {
                    flagInvalid(childOccupationInput);
                }
            }

            if (firstInvalidInput) {
                focusChildModalInvalidField(firstInvalidInput);
                return null;
            }

            if (currentlyStudying === 'yes') {
                const school = (childSchoolInput?.value || '').trim();

                return withChildAgeFields({
                    full_name: fullName,
                    birthdate,
                    gender,
                    relationship,
                    currently_studying: currentlyStudying,
                    school,
                    educational_attainment: '',
                    occupation: '',
                });
            }

            if (currentlyStudying === 'infant') {
                return withChildAgeFields({
                    full_name: fullName,
                    birthdate,
                    gender,
                    relationship,
                    currently_studying: 'infant',
                    school: '',
                    educational_attainment: '',
                    occupation: '',
                });
            }

            return withChildAgeFields({
                full_name: fullName,
                birthdate,
                gender,
                relationship,
                currently_studying: currentlyStudying,
                school: '',
                educational_attainment: educationalAttainment,
                occupation,
            });
        }

        function saveChildFromModal() {
            const childData = validateChildModalForm();
            if (!childData) {
                return;
            }

            clearChildModalValidation();

            if (editingChildIndex !== null) {
                householdChildren[editingChildIndex] = childData;
            } else {
                householdChildren.push(childData);
            }

            renderHouseholdChildren();
            closeChildMemberModal();
        }

        parentRoleSelect?.addEventListener('change', syncGenderFromParentRole);
        spouseParentRoleSelect?.addEventListener('change', syncSpouseGenderFromParentRole);
        civilStatusSelect?.addEventListener('change', syncSpouseSectionVisibility);
        openChildModalBtn?.addEventListener('click', () => openChildMemberModal());
        childMemberCloseBtn?.addEventListener('click', closeChildMemberModal);
        childMemberModal?.querySelectorAll('.child-member-cancel-btn').forEach((button) => {
            button.addEventListener('click', closeChildMemberModal);
        });
        childMemberModal?.querySelectorAll('.child-member-save-btn').forEach((button) => {
            button.addEventListener('click', saveChildFromModal);
        });
        childStudyingRadios.forEach((radio) => {
            radio.addEventListener('change', syncChildStudyingFields);
        });
        childBirthdateInput?.addEventListener('change', updateChildAgeHint);

        function clearSeniorModalValidation() {
            seniorMemberModal?.querySelectorAll('.is-invalid').forEach((element) => {
                element.classList.remove('is-invalid');
            });
        }

        function resetSeniorModalLayout() {
            if (seniorModalPanel) {
                seniorModalPanel.style.maxHeight = '';
                seniorModalPanel.style.height = '';
            }

            if (seniorModalBody) {
                seniorModalBody.style.paddingBottom = '';
            }
        }

        function adjustSeniorModalForKeyboard() {
            if (!seniorMemberModal || seniorMemberModal.classList.contains('hidden') || !seniorModalPanel) {
                resetSeniorModalLayout();
                return;
            }

            if (window.innerWidth >= 640 || !window.visualViewport) {
                resetSeniorModalLayout();
                return;
            }

            const viewport = window.visualViewport;
            const topInset = Math.max(8, viewport.offsetTop);
            const availableHeight = Math.max(260, viewport.height - topInset - 8);

            seniorModalPanel.style.maxHeight = `${availableHeight}px`;
            seniorModalPanel.style.height = `${availableHeight}px`;
        }

        function scrollSeniorModalFieldIntoView(field) {
            if (!field || !seniorModalBody || window.innerWidth >= 640) {
                return;
            }

            window.setTimeout(() => {
                adjustSeniorModalForKeyboard();

                const viewport = window.visualViewport;
                const visibleTop = viewport ? viewport.offsetTop + 12 : 12;
                const visibleBottom = viewport
                    ? viewport.offsetTop + viewport.height - 12
                    : window.innerHeight - 12;
                const fieldRect = field.getBoundingClientRect();

                if (fieldRect.bottom > visibleBottom) {
                    seniorModalBody.scrollTop += fieldRect.bottom - visibleBottom + 16;
                } else if (fieldRect.top < visibleTop) {
                    seniorModalBody.scrollTop -= visibleTop - fieldRect.top + 16;
                }
            }, 320);
        }

        function bindSeniorModalKeyboardHandling() {
            if (!seniorMemberModal || seniorMemberModal.dataset.keyboardBound === '1') {
                return;
            }

            seniorMemberModal.dataset.keyboardBound = '1';

            seniorMemberModal.addEventListener('focusin', (event) => {
                const field = event.target;
                if (field instanceof HTMLInputElement || field instanceof HTMLSelectElement || field instanceof HTMLTextAreaElement) {
                    scrollSeniorModalFieldIntoView(field);
                }
            });

            window.visualViewport?.addEventListener('resize', adjustSeniorModalForKeyboard);
            window.visualViewport?.addEventListener('scroll', adjustSeniorModalForKeyboard);
        }

        bindSeniorModalKeyboardHandling();

        function focusSeniorModalInvalidField(input) {
            if (!input) {
                return;
            }

            input.focus();
            scrollSeniorModalFieldIntoView(input);
        }

        function markSeniorFieldInvalid(input) {
            if (!input) {
                return;
            }

            input.closest('.child-form-field')?.classList.add('is-invalid');
        }

        function updateSeniorAgeHint() {
            const birthdate = (seniorBirthdateInput?.value || '').trim();
            const years = getChildAgeInYears(birthdate);

            if (!seniorAgeHint) {
                return;
            }

            if (years === null) {
                seniorAgeHint.textContent = '';
                seniorAgeHint.classList.add('hidden');
                return;
            }

            if (years < SENIOR_MIN_AGE) {
                seniorAgeHint.textContent = `Age: ${years} years — must be at least ${SENIOR_MIN_AGE} for senior citizen monitoring.`;
                seniorAgeHint.classList.remove('hidden');
                seniorAgeHint.classList.add('text-red-700');
                return;
            }

            seniorAgeHint.textContent = `Age: ${years} years — eligible for senior citizen monitoring.`;
            seniorAgeHint.classList.remove('hidden', 'text-red-700');
        }

        function resetSeniorModalForm() {
            editingSeniorIndex = null;
            if (seniorMemberModalTitle) {
                seniorMemberModalTitle.textContent = 'Add Elderly Member';
            }
            if (seniorFullNameInput) seniorFullNameInput.value = '';
            if (seniorBirthdateInput) seniorBirthdateInput.value = '';
            if (seniorGenderSelect) seniorGenderSelect.value = '';
            if (seniorRelationshipSelect) seniorRelationshipSelect.value = '';
            if (seniorPlaceOfBirthInput) seniorPlaceOfBirthInput.value = '';
            if (seniorEducationalAttainmentSelect) seniorEducationalAttainmentSelect.value = '';
            if (seniorOccupationInput) seniorOccupationInput.value = '';
            if (seniorAgeHint) {
                seniorAgeHint.textContent = '';
                seniorAgeHint.classList.add('hidden');
            }
            clearSeniorModalValidation();
        }

        function openSeniorMemberModal(index = null) {
            if (requestSent) {
                return;
            }

            resetSeniorModalForm();

            if (index !== null && householdSeniors[index]) {
                const senior = householdSeniors[index];
                editingSeniorIndex = index;
                if (seniorMemberModalTitle) {
                    seniorMemberModalTitle.textContent = 'Edit Elderly Member';
                }
                if (seniorFullNameInput) seniorFullNameInput.value = formatChildFullName(senior.full_name || '');
                if (seniorBirthdateInput) seniorBirthdateInput.value = senior.birthdate || '';
                if (seniorGenderSelect) seniorGenderSelect.value = senior.gender || '';
                if (seniorRelationshipSelect) seniorRelationshipSelect.value = senior.relationship || '';
                if (seniorPlaceOfBirthInput) seniorPlaceOfBirthInput.value = formatTextField(senior.place_of_birth || '');
                if (seniorEducationalAttainmentSelect) seniorEducationalAttainmentSelect.value = senior.educational_attainment || '';
                if (seniorOccupationInput) seniorOccupationInput.value = formatTextField(senior.occupation || '');
                updateSeniorAgeHint();
            }

            if (seniorBirthdateInput) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                seniorBirthdateInput.max = today.toISOString().slice(0, 10);
            }

            seniorMemberModal?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            adjustSeniorModalForKeyboard();

            if (window.innerWidth >= 640) {
                seniorFullNameInput?.focus();
            }
        }

        function closeSeniorMemberModal() {
            seniorMemberModal?.classList.add('hidden');
            resetSeniorModalForm();
            resetSeniorModalLayout();
            restoreBodyOverflowIfNoModal();
        }

        function withSeniorAgeFields(seniorData) {
            const years = getChildAgeInYears(seniorData.birthdate);
            return {
                ...seniorData,
                age_in_years: years !== null ? years : '',
            };
        }

        function renderHouseholdSeniors() {
            if (!seniorsSummaryList || !seniorsHiddenFields) {
                return;
            }

            seniorsSummaryList.innerHTML = '';
            seniorsHiddenFields.innerHTML = '';

            householdSeniors.forEach((senior, index) => {
                const card = document.createElement('article');
                card.className = 'child-summary-card';
                const ageLabel = formatChildAgeLabel(senior.birthdate);
                const profileLine = [senior.place_of_birth, senior.educational_attainment].filter(Boolean).join(' · ') || 'No profile details';
                const occupationLine = senior.occupation ? senior.occupation : '';
                const initials = childInitialsFromName(senior.full_name || 'Senior');

                card.innerHTML = `
                    <div class="senior-summary-avatar" aria-hidden="true">${escapeHtml(initials)}</div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-start justify-between gap-2">
                            <p class="text-sm sm:text-[0.9375rem] font-bold text-gray-900 truncate">${escapeHtml(senior.full_name || 'Senior')}</p>
                            <div class="child-summary-actions">
                                <button type="button" class="child-action-btn edit-senior-btn text-amber-700 hover:bg-amber-50" data-senior-index="${index}" aria-label="Edit elderly member">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button" class="child-action-btn remove-senior-btn text-red-600 hover:bg-red-50" data-senior-index="${index}" aria-label="Remove elderly member">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <span class="child-chip child-chip-amber">${escapeHtml(senior.relationship || 'Elderly')}</span>
                            <span class="child-chip child-chip-neutral">${escapeHtml(senior.gender || '—')}</span>
                            ${ageLabel ? `<span class="child-chip child-chip-violet">${escapeHtml(ageLabel)}</span>` : ''}
                            <span class="child-chip child-chip-blue">Senior citizen</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-600 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-gray-400 shrink-0 leading-none">location_on</span>
                            <span class="min-w-0 break-words">${escapeHtml(profileLine)}</span>
                        </p>
                        ${occupationLine ? `<p class="mt-1.5 text-xs text-gray-600 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-gray-400 shrink-0 leading-none">work</span>
                            <span class="min-w-0 break-words">${escapeHtml(occupationLine)}</span>
                        </p>` : ''}
                    </div>
                `;

                seniorsSummaryList.appendChild(card);

                const fields = [
                    ['full_name', senior.full_name || ''],
                    ['birthdate', senior.birthdate || ''],
                    ['age_in_years', senior.age_in_years ?? ''],
                    ['gender', senior.gender || ''],
                    ['relationship', senior.relationship || ''],
                    ['place_of_birth', senior.place_of_birth || ''],
                    ['educational_attainment', senior.educational_attainment || ''],
                    ['occupation', senior.occupation || ''],
                ];

                fields.forEach(([key, value]) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `seniors[${index}][${key}]`;
                    input.value = value;
                    seniorsHiddenFields.appendChild(input);
                });
            });

            seniorsEmptyHint?.classList.toggle('hidden', householdSeniors.length > 0);

            if (seniorsCountBadge) {
                const count = householdSeniors.length;
                seniorsCountBadge.textContent = count === 1 ? '1 added' : `${count} added`;
                seniorsCountBadge.classList.toggle('hidden', count === 0);
            }

            seniorsSummaryList.classList.toggle('hidden', householdSeniors.length === 0);

            seniorsSummaryList.querySelectorAll('.edit-senior-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.seniorIndex);
                    if (!Number.isNaN(index)) {
                        openSeniorMemberModal(index);
                    }
                });
            });

            seniorsSummaryList.querySelectorAll('.remove-senior-btn').forEach((button) => {
                button.addEventListener('click', () => {
                    const index = Number(button.dataset.seniorIndex);
                    if (!Number.isNaN(index)) {
                        householdSeniors.splice(index, 1);
                        renderHouseholdSeniors();
                    }
                });
            });
        }

        function validateSeniorModalForm() {
            normalizeCapitalizedFieldValues();
            clearSeniorModalValidation();

            const fullName = (seniorFullNameInput?.value || '').trim();
            const birthdate = (seniorBirthdateInput?.value || '').trim();
            const gender = (seniorGenderSelect?.value || '').trim();
            const relationship = (seniorRelationshipSelect?.value || '').trim();
            const placeOfBirth = (seniorPlaceOfBirthInput?.value || '').trim();
            const educationalAttainment = (seniorEducationalAttainmentSelect?.value || '').trim();
            const occupation = (seniorOccupationInput?.value || '').trim();
            let firstInvalidInput = null;

            const flagInvalid = (input) => {
                markSeniorFieldInvalid(input);
                if (!firstInvalidInput) {
                    firstInvalidInput = input;
                }
            };

            if (!fullName) {
                flagInvalid(seniorFullNameInput);
            } else if (!isValidChildFullName(fullName)) {
                flagInvalid(seniorFullNameInput);
            }

            if (!birthdate) {
                flagInvalid(seniorBirthdateInput);
            } else {
                const years = getChildAgeInYears(birthdate);
                if (years === null || years < SENIOR_MIN_AGE) {
                    flagInvalid(seniorBirthdateInput);
                }
            }

            if (!gender) {
                flagInvalid(seniorGenderSelect);
            }

            if (!relationship) {
                flagInvalid(seniorRelationshipSelect);
            }

            if (!placeOfBirth) {
                flagInvalid(seniorPlaceOfBirthInput);
            } else if (!isValidTextField(placeOfBirth)) {
                flagInvalid(seniorPlaceOfBirthInput);
            }

            if (!educationalAttainment) {
                flagInvalid(seniorEducationalAttainmentSelect);
            }

            if (occupation && !isValidTextField(occupation)) {
                flagInvalid(seniorOccupationInput);
            }

            if (firstInvalidInput) {
                focusSeniorModalInvalidField(firstInvalidInput);
                return null;
            }

            return withSeniorAgeFields({
                full_name: formatChildFullName(fullName),
                birthdate,
                gender,
                relationship,
                place_of_birth: formatTextField(placeOfBirth),
                educational_attainment: educationalAttainment,
                occupation: occupation ? formatTextField(occupation) : '',
            });
        }

        function saveSeniorFromModal() {
            const seniorData = validateSeniorModalForm();
            if (!seniorData) {
                return;
            }

            if (editingSeniorIndex !== null) {
                householdSeniors[editingSeniorIndex] = seniorData;
            } else {
                householdSeniors.push(seniorData);
            }

            renderHouseholdSeniors();
            closeSeniorMemberModal();
        }

        openSeniorModalBtn?.addEventListener('click', () => openSeniorMemberModal());
        seniorMemberCloseBtn?.addEventListener('click', closeSeniorMemberModal);
        seniorMemberModal?.querySelectorAll('.senior-member-cancel-btn').forEach((button) => {
            button.addEventListener('click', closeSeniorMemberModal);
        });
        seniorMemberModal?.querySelectorAll('.senior-member-save-btn').forEach((button) => {
            button.addEventListener('click', saveSeniorFromModal);
        });
        seniorBirthdateInput?.addEventListener('change', updateSeniorAgeHint);

        syncGenderFromParentRole();
        syncSpouseSectionVisibility();
        syncSpouseGenderFromParentRole();
        householdChildren = householdChildren.map((child) => withChildAgeFields(child));
        renderHouseholdChildren();
        householdSeniors = householdSeniors.map((senior) => withSeniorAgeFields(senior));
        renderHouseholdSeniors();

        function validateVerificationForm() {
            normalizeCapitalizedFieldValues();
            updateHiddenFullName();
            updateHiddenSpouseFullName();
            renderHouseholdChildren();
            renderHouseholdSeniors();

            const firstName = (firstNameInput?.value || '').trim();
            const lastName = (lastNameInput?.value || '').trim();

            if (!firstName) {
                showNotice('Required Field', 'Please enter your first name.');
                firstNameInput?.focus();
                return false;
            }

            if (!isValidPersonName(firstName)) {
                showNotice('Invalid Name', `First name may only use letters, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                firstNameInput?.focus();
                return false;
            }

            if (!lastName) {
                showNotice('Required Field', 'Please enter your last name.');
                lastNameInput?.focus();
                return false;
            }

            if (!isValidPersonName(lastName)) {
                showNotice('Invalid Name', `Last name may only use letters, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                lastNameInput?.focus();
                return false;
            }

            if (!validateProfileBirthdate(birthdateInput, birthdateClientError, birthdateField, 'your')) {
                return false;
            }

            syncGenderFromParentRole();

            if (!(parentRoleSelect?.value || '').trim()) {
                showNotice('Required Field', 'Please select whether you are the father or mother registering for your family.');
                parentRoleSelect?.focus();
                return false;
            }

            if (!(civilStatusSelect?.value || '').trim()) {
                showNotice('Required Field', 'Please select your marital status.');
                civilStatusSelect?.focus();
                return false;
            }

            if (!(placeOfBirthInput?.value || '').trim()) {
                showNotice('Required Field', 'Please enter your place of birth.');
                placeOfBirthInput?.focus();
                return false;
            }

            if (!isValidTextField(placeOfBirthInput.value)) {
                showNotice('Invalid Place of Birth', `Place of birth may only use letters, numbers, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                placeOfBirthInput?.focus();
                return false;
            }

            if (!(educationalAttainmentSelect?.value || '').trim()) {
                showNotice('Required Field', 'Please select your educational attainment.');
                educationalAttainmentSelect?.focus();
                return false;
            }

            if (!validateSpouseDetails()) {
                return false;
            }

            if (!(contactInput?.value || '').trim()) {
                showNotice('Required Field', 'Please enter your contact number.');
                contactInput?.focus();
                return false;
            }

            const contactNumber = sanitizeContactNumber(contactInput?.value || '');
            if (contactInput) {
                contactInput.value = contactNumber;
            }

            if (!/^\d{11}$/.test(contactNumber)) {
                showNotice('Invalid Contact Number', 'Contact number must be exactly 11 digits with no letters or symbols.');
                contactInput?.focus();
                return false;
            }

            if (!(barangayInput?.value || '').trim()) {
                showNotice('Barangay Required', 'Please set your barangay and purok before sending your request.');
                return false;
            }

            if (!isRealPurokName(purokInput?.value || '')) {
                showNotice('Purok Required', 'Please select a purok.');
                return false;
            }

            const locationMode = getLocationMode();

            if (locationMode === 'map' && !(addressInput?.value || '').trim()) {
                showNotice('Location Required', 'Please use map-based tracking and save your location first.');
                return false;
            }

            if (locationMode === 'manual' && addressInput) {
                addressInput.value = '';
            }

            if (!idTypeSelect?.value) {
                showNotice('Valid ID Required', 'Please select a valid ID type.');
                return false;
            }

            if (idTypeSelect.value === 'other' && !(idOtherLabelInput?.value || '').trim()) {
                showNotice('Valid ID Required', 'Please specify your ID type under Other.');
                return false;
            }

            if (idTypeSelect.value === 'other' && !isValidTextField(idOtherLabelInput?.value || '')) {
                showNotice('Invalid ID Label', `ID label may only use letters, numbers, spaces, and comma. ${STRING_SPECIAL_CHAR_MESSAGE}`);
                idOtherLabelInput?.focus();
                return false;
            }

            const frontFile = idPhotoFrontInput?.files?.[0];
            if (!frontFile) {
                startIdCapture('front');
                return false;
            }

            const frontError = validateIdPhotoFile(frontFile, 'Front ID photo');
            if (frontError) {
                showNotice('Invalid ID Photo', frontError);
                return false;
            }

            const backFile = idPhotoBackInput?.files?.[0];
            if (!backFile) {
                startIdCapture('back');
                return false;
            }

            const backError = validateIdPhotoFile(backFile, 'Back ID photo');
            if (backError) {
                showNotice('Invalid ID Photo', backError);
                return false;
            }

            return true;
        }

        async function queryGeolocationPermission() {
            if (!navigator.permissions?.query) {
                return 'unknown';
            }

            try {
                const status = await navigator.permissions.query({ name: 'geolocation' });
                return status.state;
            } catch (error) {
                return 'unknown';
            }
        }

        async function saveMapBasedLocation({ skipConfirm = false, autoClose = false } = {}) {
            if (!hasUsedCurrentPosition) {
                showNotice('Current Location Required', 'Please click Use Current Position first.');
                return false;
            }

            const barangayValue = (detectedBarangay || '').trim();
            if (!barangayValue) {
                showNotice('Missing Barangay', 'Your barangay could not be detected. Try manual selection.');
                return false;
            }

            const purokValue = (detectedPurok || '').trim();
            if (!isRealPurokName(purokValue)) {
                showNotice('Purok Not Detected', 'Your purok could not be detected from GPS. Switch to Manual and select your purok.');
                return false;
            }

            if (!skipConfirm) {
                const shouldSave = await showConfirm('Confirm Save', 'Confirm saving this barangay and purok area?');
                if (!shouldSave) {
                    return false;
                }
            }

            if (addressInput && Number.isFinite(selectedLat) && Number.isFinite(selectedLng)) {
                addressInput.value = `${selectedLat.toFixed(7)}, ${selectedLng.toFixed(7)}`;
            }

            applySavedLocationDisplay(barangayValue, purokValue, 'map');

            if (autoClose) {
                closeTrackModal();
            }

            return true;
        }

        function requestCurrentPosition() {
            if (requestSent || !navigator.geolocation) {
                if (!navigator.geolocation) {
                    showNotice('Location Unavailable', 'Geolocation is not supported on this device.');
                }
                return;
            }

            setMapLoading(true, 'Getting current location...');
            if (useCurrentPositionBtn) {
                useCurrentPositionBtn.disabled = true;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                selectedLat = position.coords.latitude;
                selectedLng = position.coords.longitude;
                hasUsedCurrentPosition = true;

                if (!map && isTrackModalOpen()) {
                    initializeTrackMap();
                }

                if (map && marker) {
                    map.setView([selectedLat, selectedLng], 16);
                    marker.setLatLng([selectedLat, selectedLng]);
                    refreshTrackMapSize();
                }

                await reverseGeocodeBarangay(selectedLat, selectedLng);
                setMapLoading(false);
                if (useCurrentPositionBtn) {
                    useCurrentPositionBtn.disabled = requestSent;
                }
                if (saveTrackModalBtn) {
                    saveTrackModalBtn.disabled = requestSent ? true : false;
                }
            }, (error) => {
                setMapLoading(false, 'Unable to get current location.');
                if (useCurrentPositionBtn) {
                    useCurrentPositionBtn.disabled = requestSent;
                }

                if (error?.code === error.PERMISSION_DENIED) {
                    showNotice('Location Blocked', 'Location access was blocked. Allow location for this site in your browser settings, then try again.');
                    return;
                }

                showNotice('Location Error', 'Unable to get current location. Please try again.');
            }, {
                enableHighAccuracy: true,
                timeout: 15000,
                maximumAge: 0,
            });
        }

        async function handleUseCurrentPosition() {
            if (requestSent) {
                return;
            }

            if (!navigator.geolocation) {
                showNotice('Location Unavailable', 'Geolocation is not supported on this device.');
                return;
            }

            const permissionState = await queryGeolocationPermission();

            if (permissionState === 'denied') {
                showNotice('Location Blocked', 'Location access is blocked in your browser. Enable it in your site settings and try again.');
                return;
            }

            setLocationConsent(true);
            hideConsentBanner(locationConsentBanner);
            requestCurrentPosition();
        }

        if (locationConsentAccepted && locationConsentInput && !locationConsentInput.value) {
            locationConsentInput.value = '1';
        }

        privacyConsentCheckbox?.addEventListener('change', () => {
            if (privacyConsentAcceptBtn) {
                privacyConsentAcceptBtn.disabled = !privacyConsentCheckbox.checked;
            }
        });

        locationConsentAcceptBtn?.addEventListener('click', () => {
            setLocationConsent(true);
            hideConsentBanner(locationConsentBanner);
            requestCurrentPosition();
        });

        locationConsentDeclineBtn?.addEventListener('click', () => {
            setLocationConsent(false);
            hideConsentBanner(locationConsentBanner);
            showNotice('Location Access Declined', 'You must accept the location terms to use your current position.');
        });

        locationConsentCloseBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            setLocationConsent(false);
            hideConsentBanner(locationConsentBanner);
        });

        privacyConsentAcceptBtn?.addEventListener('click', () => {
            if (!privacyConsentCheckbox?.checked) {
                return;
            }

            setPrivacyConsent(true);
            hideConsentBanner(privacyConsentBanner);

            if (pendingFormSubmit && verificationForm) {
                pendingFormSubmit = false;
                showVerificationSentFooterLinks();
                verificationForm.submit();
            }
        });

        privacyConsentDeclineBtn?.addEventListener('click', () => {
            setPrivacyConsent(false);
            pendingFormSubmit = false;
            if (privacyConsentCheckbox) {
                privacyConsentCheckbox.checked = false;
            }
            if (privacyConsentAcceptBtn) {
                privacyConsentAcceptBtn.disabled = true;
            }
            hideConsentBanner(privacyConsentBanner);
            showNotice('Request Not Sent', 'You must accept the data privacy terms to submit your verification request.');
        });

        privacyConsentCloseBtn?.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            setPrivacyConsent(false);
            pendingFormSubmit = false;
            if (privacyConsentCheckbox) {
                privacyConsentCheckbox.checked = false;
            }
            if (privacyConsentAcceptBtn) {
                privacyConsentAcceptBtn.disabled = true;
            }
            hideConsentBanner(privacyConsentBanner);
        });

        function revokeIdPhotoUrl(side) {
            const currentUrl = side === 'front' ? frontPhotoObjectUrl : backPhotoObjectUrl;

            if (currentUrl) {
                URL.revokeObjectURL(currentUrl);
            }

            if (side === 'front') {
                frontPhotoObjectUrl = null;
            } else {
                backPhotoObjectUrl = null;
            }
        }

        function updateIdCaptureButtonLabel() {
            const hasFront = Boolean(idPhotoFrontInput?.files?.[0]);
            const hasBack = Boolean(idPhotoBackInput?.files?.[0]);

            if (!startIdCaptureBtnLabel) {
                return;
            }

            if (!hasFront) {
                startIdCaptureBtnLabel.textContent = 'Capture ID Photos';
            } else if (!hasBack) {
                startIdCaptureBtnLabel.textContent = 'Capture ID Back';
            } else {
                startIdCaptureBtnLabel.textContent = 'Recapture ID Photos';
            }
        }

        function setIdPhotoCaptureState() {
            updateIdCaptureButtonLabel();
        }

        function assignCapturedFileToInput(input, blob, filename) {
            if (!input) {
                return;
            }

            const file = new File([blob], filename, { type: 'image/jpeg', lastModified: Date.now() });
            const transfer = new DataTransfer();
            transfer.items.add(file);
            input.files = transfer.files;
        }

        function restoreBodyOverflowIfNoModal() {
            const modalsOpen = [
                noticeModal,
                confirmModal,
                trackModal,
                idCameraModal,
                childMemberModal,
                seniorMemberModal,
            ].some((modal) => modal && !modal.classList.contains('hidden'));

            if (!modalsOpen) {
                document.body.style.overflow = '';
            }
        }

        function clearIdPhotoInputs() {
            if (idPhotoFrontInput) {
                idPhotoFrontInput.value = '';
            }

            if (idPhotoBackInput) {
                idPhotoBackInput.value = '';
            }

            revokeIdPhotoUrl('front');
            revokeIdPhotoUrl('back');
            setIdPhotoCaptureState();
        }

        function syncIdVerificationFields() {
            if (!idTypeSelect) {
                return;
            }

            const hasIdType = idTypeSelect.value !== '';
            const showOther = idTypeSelect.value === 'other';

            idOtherWrap?.classList.toggle('hidden', !showOther);
            idPhotoWrap?.classList.toggle('hidden', !hasIdType);

            if (idOtherLabelInput) {
                idOtherLabelInput.required = showOther && !requestSent;
            }

            if (!hasIdType) {
                clearIdPhotoInputs();
            }
        }

        function validateIdPhotoFile(file, label) {
            if (!file) {
                return null;
            }

            if (!allowedIdPhotoTypes.includes(file.type)) {
                return `${label} must be a captured JPEG photo.`;
            }

            if (file.size > maxIdPhotoBytes) {
                return `${label} must be 5 MB or smaller.`;
            }

            return null;
        }

        function saveCapturedPhoto(side, blob) {
            const input = side === 'front' ? idPhotoFrontInput : idPhotoBackInput;
            const filename = side === 'front' ? 'id-front.jpg' : 'id-back.jpg';
            const label = side === 'front' ? 'Front ID photo' : 'Back ID photo';
            const error = validateIdPhotoFile(new File([blob], filename, { type: 'image/jpeg' }), label);

            if (error) {
                showNotice('Invalid ID Photo', error);
                return false;
            }

            assignCapturedFileToInput(input, blob, filename);
            revokeIdPhotoUrl(side);
            const objectUrl = URL.createObjectURL(blob);

            if (side === 'front') {
                frontPhotoObjectUrl = objectUrl;
            } else {
                backPhotoObjectUrl = objectUrl;
            }

            setIdPhotoCaptureState();
            return true;
        }

        function updateIdCameraModalCopy() {
            const isFront = idCameraStep === 'front';

            if (idCameraModalTitle) {
                idCameraModalTitle.textContent = isFront ? 'Capture ID — Front' : 'Capture ID — Back';
            }

            if (idCameraModalSubtitle) {
                idCameraModalSubtitle.textContent = isFront ? 'Step 1 of 2' : 'Step 2 of 2';
            }
        }

        function setIdCameraStabilityUi(progress, state, statusText, hintText) {
            if (idCameraStabilityFill) {
                idCameraStabilityFill.style.width = `${Math.min(100, Math.max(0, progress))}%`;
                idCameraStabilityFill.classList.remove('is-ready', 'is-warning', 'is-error');
                idCameraStabilityFill.classList.add(
                    state === 'ready' ? 'is-ready' : (state === 'error' ? 'is-error' : 'is-warning')
                );
            }

            if (idCameraStatusText) {
                idCameraStatusText.textContent = statusText;
                idCameraStatusText.classList.toggle('text-green-700', state === 'ready');
                idCameraStatusText.classList.toggle('font-semibold', state === 'ready');
                idCameraStatusText.classList.toggle('leading-snug', state === 'ready');
                idCameraStatusText.classList.toggle('text-gray-700', state !== 'ready');
            }

            if (idCameraHintText) {
                idCameraHintText.textContent = hintText;
                idCameraHintText.classList.toggle('hidden', state === 'ready' || !hintText);
            }
        }

        function imageDataToGrayscale(imageData) {
            const gray = new Float32Array(imageData.width * imageData.height);
            const data = imageData.data;

            for (let i = 0, p = 0; i < data.length; i += 4, p += 1) {
                gray[p] = (0.299 * data[i]) + (0.587 * data[i + 1]) + (0.114 * data[i + 2]);
            }

            return gray;
        }

        function laplacianVariance(gray, width, height) {
            let sum = 0;
            let sumSq = 0;
            let count = 0;

            for (let y = 1; y < height - 1; y += 1) {
                for (let x = 1; x < width - 1; x += 1) {
                    const center = (y * width) + x;
                    const value = (
                        gray[center - width]
                        + gray[center - 1]
                        + gray[center + 1]
                        + gray[center + width]
                        - (4 * gray[center])
                    );
                    sum += value;
                    sumSq += value * value;
                    count += 1;
                }
            }

            if (count === 0) {
                return 0;
            }

            const mean = sum / count;
            return (sumSq / count) - (mean * mean);
        }

        function meanAbsoluteDifference(grayA, grayB) {
            let total = 0;

            for (let i = 0; i < grayA.length; i += 1) {
                total += Math.abs(grayA[i] - grayB[i]);
            }

            return total / grayA.length;
        }

        function stopIdCameraAnalysis() {
            if (idCameraAnalysisTimer) {
                clearInterval(idCameraAnalysisTimer);
                idCameraAnalysisTimer = null;
            }

            idCameraLastGrayFrame = null;
            idCameraSteadyFrames = 0;
            idCameraCaptureReady = false;
        }

        function setIdCameraLiveMode() {
            idCameraPreviewBlob = null;

            if (idCameraPreviewImg) {
                idCameraPreviewImg.classList.add('hidden');
                idCameraPreviewImg.src = '';
            }

            if (idCameraVideo) {
                idCameraVideo.classList.remove('hidden');
            }

            idCameraLiveControls?.classList.remove('hidden');
            idCameraCaptureBtn?.classList.remove('hidden');
            idCameraUsePhotoBtn?.classList.add('hidden');
            idCameraRetakeBtn?.classList.add('hidden');

            if (idCameraCaptureBtn) {
                idCameraCaptureBtn.disabled = true;
            }

            setIdCameraStabilityUi(0, 'warning', 'Hold your ID steady...', 'Align your ID inside the frame and hold still.');
        }

        function setIdCameraPreviewMode(previewUrl) {
            stopIdCameraAnalysis();

            if (idCameraVideo) {
                idCameraVideo.classList.add('hidden');
            }

            if (idCameraPreviewImg) {
                idCameraPreviewImg.src = previewUrl;
                idCameraPreviewImg.classList.remove('hidden');
            }

            idCameraLiveControls?.classList.add('hidden');
            idCameraCaptureBtn?.classList.add('hidden');
            idCameraUsePhotoBtn?.classList.remove('hidden');
            idCameraRetakeBtn?.classList.remove('hidden');
        }

        function analyzeIdCameraFrame() {
            if (!idCameraVideo || idCameraVideo.readyState < 2 || idCameraVideo.videoWidth === 0) {
                return;
            }

            const context = idCameraAnalysisCanvas.getContext('2d', { willReadFrequently: true });
            const width = 200;
            const height = Math.max(120, Math.round((idCameraVideo.videoHeight / idCameraVideo.videoWidth) * width));
            idCameraAnalysisCanvas.width = width;
            idCameraAnalysisCanvas.height = height;
            context.drawImage(idCameraVideo, 0, 0, width, height);

            const imageData = context.getImageData(0, 0, width, height);
            const gray = imageDataToGrayscale(imageData);
            const sharpness = laplacianVariance(gray, width, height);

            if (!idCameraLastGrayFrame) {
                idCameraLastGrayFrame = gray;
                idCameraSteadyFrames = 0;
                idCameraCaptureReady = false;

                if (idCameraCaptureBtn) {
                    idCameraCaptureBtn.disabled = true;
                }

                setIdCameraStabilityUi(0, 'warning', 'Hold your ID steady...', 'Align your ID inside the frame and hold still.');
                return;
            }

            const motion = meanAbsoluteDifference(gray, idCameraLastGrayFrame);
            idCameraLastGrayFrame = gray;

            const isSharp = sharpness >= ID_CAMERA_SHARPNESS_THRESHOLD;
            const isSteady = motion <= ID_CAMERA_MOTION_THRESHOLD;

            if (isSharp && isSteady) {
                idCameraSteadyFrames += 1;
            } else {
                idCameraSteadyFrames = 0;
            }

            const progress = (idCameraSteadyFrames / ID_CAMERA_STEADY_FRAMES) * 100;
            idCameraCaptureReady = idCameraSteadyFrames >= ID_CAMERA_STEADY_FRAMES;

            if (idCameraCaptureBtn) {
                idCameraCaptureBtn.disabled = !idCameraCaptureReady;
            }

            if (!isSharp) {
                setIdCameraStabilityUi(progress, 'error', 'Image is blurry', 'Move closer or tap to focus, then hold steady.');
            } else if (!isSteady) {
                setIdCameraStabilityUi(progress, 'warning', 'Hold steady...', 'Keep your hands still until the bar fills.');
            } else if (!idCameraCaptureReady) {
                setIdCameraStabilityUi(progress, 'warning', 'Almost ready...', 'Keep holding steady.');
            } else {
                setIdCameraStabilityUi(100, 'ready', ID_CAMERA_READY_MESSAGE, '');
            }
        }

        function startIdCameraAnalysis() {
            stopIdCameraAnalysis();
            idCameraAnalysisTimer = setInterval(analyzeIdCameraFrame, ID_CAMERA_ANALYSIS_MS);
        }

        async function stopIdCameraStream() {
            stopIdCameraAnalysis();

            if (idCameraStream) {
                idCameraStream.getTracks().forEach((track) => track.stop());
                idCameraStream = null;
            }

            if (idCameraVideo) {
                idCameraVideo.srcObject = null;
            }
        }

        async function startIdCameraStream() {
            await stopIdCameraStream();

            if (!navigator.mediaDevices?.getUserMedia) {
                showNotice('Camera Unavailable', 'Your device does not support live camera capture.');
                return false;
            }

            try {
                idCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: { ideal: 'environment' },
                        width: { ideal: 1920 },
                        height: { ideal: 1080 },
                    },
                    audio: false,
                });
            } catch (error) {
                showNotice('Camera Access Denied', 'Please allow camera access to capture your ID photos.');
                return false;
            }

            if (idCameraVideo) {
                idCameraVideo.srcObject = idCameraStream;
                await idCameraVideo.play();
            }

            setIdCameraLiveMode();
            startIdCameraAnalysis();
            return true;
        }

        function captureIdCameraPhoto() {
            if (!idCameraVideo || idCameraVideo.videoWidth === 0) {
                return;
            }

            const captureCanvas = document.createElement('canvas');
            captureCanvas.width = idCameraVideo.videoWidth;
            captureCanvas.height = idCameraVideo.videoHeight;
            const context = captureCanvas.getContext('2d');
            context.drawImage(idCameraVideo, 0, 0, captureCanvas.width, captureCanvas.height);

            captureCanvas.toBlob((blob) => {
                if (!blob) {
                    showNotice('Capture Failed', 'Could not capture the photo. Please try again.');
                    return;
                }

                idCameraPreviewBlob = blob;
                const previewUrl = URL.createObjectURL(blob);
                setIdCameraPreviewMode(previewUrl);

                window.setTimeout(() => {
                    URL.revokeObjectURL(previewUrl);
                }, 60000);
            }, 'image/jpeg', 0.92);
        }

        function closeIdCameraModal() {
            stopIdCameraStream();
            idCameraModal?.classList.add('hidden');
            idCameraPreviewBlob = null;
            restoreBodyOverflowIfNoModal();
        }

        async function openIdCameraModal(step) {
            if (requestSent) {
                return;
            }

            idCameraStep = step;
            updateIdCameraModalCopy();
            idCameraModal?.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            const started = await startIdCameraStream();
            if (!started) {
                closeIdCameraModal();
            }
        }

        async function confirmIdPhotoRecapture(step) {
            const hasFront = Boolean(idPhotoFrontInput?.files?.[0]);
            const hasBack = Boolean(idPhotoBackInput?.files?.[0]);

            if (step === 'front' && hasFront) {
                return showConfirm(
                    'Recapture ID Front?',
                    'Your current front ID photo will be replaced. Do you want to capture it again?',
                    { okLabel: 'Yes, Recapture', cancelLabel: 'Cancel' }
                );
            }

            if (step === 'back' && hasBack) {
                return showConfirm(
                    'Recapture ID Back?',
                    'Your current back ID photo will be replaced. Do you want to capture it again?',
                    { okLabel: 'Yes, Recapture', cancelLabel: 'Cancel' }
                );
            }

            if (!step && hasFront && hasBack) {
                return showConfirm(
                    'Recapture ID Photos?',
                    'Your current front and back ID photos will be replaced. Do you want to capture them again?',
                    { okLabel: 'Yes, Recapture', cancelLabel: 'Cancel' }
                );
            }

            return true;
        }

        async function startIdCapture(step = null) {
            if (requestSent) {
                return;
            }

            const hasFront = Boolean(idPhotoFrontInput?.files?.[0]);
            const hasBack = Boolean(idPhotoBackInput?.files?.[0]);
            let nextStep = step;

            if (!nextStep) {
                if (!hasFront) {
                    nextStep = 'front';
                } else if (!hasBack) {
                    nextStep = 'back';
                } else {
                    const confirmed = await confirmIdPhotoRecapture(null);
                    if (!confirmed) {
                        return;
                    }

                    clearIdPhotoInputs();
                    nextStep = 'front';
                }
            } else if ((step === 'front' && hasFront) || (step === 'back' && hasBack)) {
                const confirmed = await confirmIdPhotoRecapture(step);
                if (!confirmed) {
                    return;
                }
            }

            openIdCameraModal(nextStep);
        }

        async function confirmIdCameraPhoto() {
            if (!idCameraPreviewBlob) {
                return;
            }

            const saved = saveCapturedPhoto(idCameraStep, idCameraPreviewBlob);
            if (!saved) {
                return;
            }

            if (idCameraStep === 'front') {
                closeIdCameraModal();
                if (!idPhotoBackInput?.files?.[0]) {
                    await openIdCameraModal('back');
                }
                return;
            }

            closeIdCameraModal();
        }

        idTypeSelect?.addEventListener('change', syncIdVerificationFields);
        syncIdVerificationFields();
        setIdPhotoCaptureState();

        startIdCaptureBtn?.addEventListener('click', () => startIdCapture());

        idCameraCaptureBtn?.addEventListener('click', captureIdCameraPhoto);
        idCameraUsePhotoBtn?.addEventListener('click', () => confirmIdCameraPhoto());
        idCameraRetakeBtn?.addEventListener('click', async () => {
            setIdCameraLiveMode();
            await startIdCameraStream();
        });
        idCameraCancelBtn?.addEventListener('click', closeIdCameraModal);
        idCameraCloseBtn?.addEventListener('click', closeIdCameraModal);

        verificationForm?.addEventListener('submit', (event) => {
            if (requestSent) {
                return;
            }

            if (!validateVerificationForm()) {
                event.preventDefault();
                return;
            }

            if (!privacyConsentAccepted) {
                event.preventDefault();
                pendingFormSubmit = true;
                if (privacyConsentCheckbox) {
                    privacyConsentCheckbox.checked = false;
                }
                if (privacyConsentAcceptBtn) {
                    privacyConsentAcceptBtn.disabled = true;
                }
                showConsentBanner(privacyConsentBanner);
                return;
            }

            showVerificationSentFooterLinks();
        });

        let map;
        let marker;
        let selectedLat = null;
        let selectedLng = null;
        let detectedBarangay = '';
        let detectedPurok = '';
        let barangayBoundaryLayerGroup;
        let purokBoundaryLayerGroup;
        let confirmResolver = null;
        let hasUsedCurrentPosition = false;
        let mapInitTimer = null;

        function isTrackModalOpen() {
            return Boolean(trackModal && !trackModal.classList.contains('hidden'));
        }

        function refreshTrackMapSize() {
            if (!map || !isTrackModalOpen()) {
                return;
            }

            map.invalidateSize({ animate: false });

            window.requestAnimationFrame(() => {
                if (map && isTrackModalOpen()) {
                    map.invalidateSize({ animate: false });
                }
            });
        }

        function initializeTrackMap() {
            if (!isTrackModalOpen()) {
                return;
            }

            const lat = Number.isFinite(selectedLat) ? selectedLat : 14.5995;
            const lng = Number.isFinite(selectedLng) ? selectedLng : 120.9842;
            detectedBarangay = (barangayInput?.value || '').trim();
            detectedPurok = (purokInput?.value || '').trim();

            if (!map) {
                const mapContainer = document.getElementById('locationMap');
                if (!mapContainer) {
                    return;
                }

                map = L.map(mapContainer, { attributionControl: false }).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '',
                }).addTo(map);

                marker = L.marker([lat, lng], {
                    draggable: false,
                    interactive: false,
                }).addTo(map);

                renderBarangayBoundaries();
                renderPurokBoundaries();
            } else {
                map.setView([lat, lng], 15);
                marker?.setLatLng([lat, lng]);
                renderBarangayBoundaries();
                renderPurokBoundaries();
            }

            refreshTrackMapSize();
            window.setTimeout(refreshTrackMapSize, 280);
        }

        function scheduleTrackMapInit() {
            if (mapInitTimer) {
                clearTimeout(mapInitTimer);
                mapInitTimer = null;
            }

            mapInitTimer = window.setTimeout(() => {
                mapInitTimer = null;
                initializeTrackMap();
            }, 150);
        }

        function setMapLoading(isLoading, text = 'Getting current location...') {
            if (!mapLoadingOverlay) {
                return;
            }

            if (mapLoadingText) {
                mapLoadingText.textContent = text;
            }

            mapLoadingOverlay.classList.toggle('hidden', !isLoading);
        }

        function showNotice(title, message) {
            if (!noticeModal) {
                return;
            }

            if (noticeTitle) {
                noticeTitle.textContent = title;
            }

            if (noticeMessage) {
                noticeMessage.textContent = message;
            }

            hideConsentBanner(privacyConsentBanner);
            hideConsentBanner(locationConsentBanner);
            noticeModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeNotice() {
            noticeModal?.classList.add('hidden');
            restoreBodyOverflowIfNoModal();
        }

        function showConfirm(title, message, options = {}) {
            return new Promise((resolve) => {
                if (!confirmModal) {
                    resolve(false);
                    return;
                }

                if (confirmTitle) {
                    confirmTitle.textContent = title;
                }

                if (confirmMessage) {
                    confirmMessage.textContent = message;
                }

                if (confirmOkBtn) {
                    confirmOkBtn.textContent = options.okLabel || 'Save';
                }

                if (confirmCancelBtn) {
                    confirmCancelBtn.textContent = options.cancelLabel || 'Cancel';
                }

                confirmResolver = resolve;
                confirmModal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            });
        }

        function resolveConfirm(value) {
            confirmModal?.classList.add('hidden');

            if (confirmOkBtn) {
                confirmOkBtn.textContent = 'Save';
            }

            if (confirmCancelBtn) {
                confirmCancelBtn.textContent = 'Cancel';
            }

            restoreBodyOverflowIfNoModal();

            if (confirmResolver) {
                confirmResolver(value);
                confirmResolver = null;
            }
        }

        function pointInRing(lat, lng, ring) {
            let inside = false;

            if (!Array.isArray(ring) || ring.length < 3) {
                return false;
            }

            for (let i = 0, j = ring.length - 1; i < ring.length; j = i++) {
                const current = ring[i] || [];
                const previous = ring[j] || [];
                const xi = Number(current[0]);
                const yi = Number(current[1]);
                const xj = Number(previous[0]);
                const yj = Number(previous[1]);

                const intersects = ((yi > lat) !== (yj > lat))
                    && (lng < ((xj - xi) * (lat - yi)) / ((yj - yi) || 0.0000001) + xi);

                if (intersects) {
                    inside = !inside;
                }
            }

            return inside;
        }

        function geometryContainsPoint(geometry, lat, lng) {
            if (!geometry || typeof geometry !== 'object') {
                return false;
            }

            if (geometry.type === 'FeatureCollection') {
                return Array.isArray(geometry.features) && geometry.features.some((feature) => geometryContainsPoint(feature, lat, lng));
            }

            if (geometry.type === 'Feature') {
                return geometryContainsPoint(geometry.geometry, lat, lng);
            }

            if (geometry.type === 'Polygon') {
                const rings = Array.isArray(geometry.coordinates) ? geometry.coordinates : [];
                const outerRing = rings[0] || [];

                if (!pointInRing(lat, lng, outerRing)) {
                    return false;
                }

                return !rings.slice(1).some((hole) => pointInRing(lat, lng, hole));
            }

            if (geometry.type === 'MultiPolygon') {
                return Array.isArray(geometry.coordinates) && geometry.coordinates.some((polygon) => geometryContainsPoint({ type: 'Polygon', coordinates: polygon }, lat, lng));
            }

            return false;
        }

        function findBarangayByBoundary(lat, lng) {
            for (const barangay of barangayBoundaries) {
                if (geometryContainsPoint(barangay.geometry, lat, lng)) {
                    return barangay.name || '';
                }
            }

            return '';
        }

        function findPurokByBoundary(lat, lng, barangayName) {
            const normalizedBarangay = (barangayName || '').trim().toLowerCase();

            if (!normalizedBarangay || !Array.isArray(purokBoundaries)) {
                return '';
            }

            for (const purok of purokBoundaries) {
                if ((purok.barangay_name || '').trim().toLowerCase() !== normalizedBarangay) {
                    continue;
                }

                if (!isRealPurokName(purok.name)) {
                    continue;
                }

                if (purok.geometry && geometryContainsPoint(purok.geometry, lat, lng)) {
                    return purok.name || '';
                }
            }

            return '';
        }

        function clearMapBoundaryLayers() {
            if (purokBoundaryLayerGroup) {
                purokBoundaryLayerGroup.clearLayers();
            }

            if (barangayBoundaryLayerGroup) {
                barangayBoundaryLayerGroup.clearLayers();
            }

            marker?.unbindTooltip();
        }

        function renderPurokBoundaries() {
            clearMapBoundaryLayers();
        }

        function renderBarangayBoundaries() {
            clearMapBoundaryLayers();
        }

        function detectBarangayForPoint(lat, lng) {
            const boundaryBarangay = findBarangayByBoundary(lat, lng);

            if (boundaryBarangay) {
                detectedBarangay = boundaryBarangay;
                detectedPurok = findPurokByBoundary(lat, lng, boundaryBarangay);
                renderPurokBoundaries();
                return detectedBarangay;
            }

            detectedBarangay = '';
            detectedPurok = '';
            renderPurokBoundaries();
            return '';
        }

        function updateHiddenFullName() {
            if (!fullNameInput) {
                return;
            }

            const firstName = (firstNameInput?.value || '').trim();
            const lastName = (lastNameInput?.value || '').trim();
            const middleInitialRaw = (middleInitialInput?.value || '').trim();
            const middleInitial = middleInitialRaw ? `${middleInitialRaw.charAt(0).toUpperCase()}.` : '';
            fullNameInput.value = [firstName, middleInitial, lastName].filter(Boolean).join(' ').trim();
        }

        function updateTrackButtonAppearance() {
            const hasBarangay = Boolean((barangayInput?.value || '').trim());

            if (openTrackModalBtnLabel) {
                openTrackModalBtnLabel.textContent = hasBarangay ? 'Change location' : 'Set location';
            }

            if (!openTrackModalBtn || requestSent) {
                return;
            }

            openTrackModalBtn.classList.toggle('bg-blue-600', !hasBarangay);
            openTrackModalBtn.classList.toggle('text-white', !hasBarangay);
            openTrackModalBtn.classList.toggle('hover:bg-blue-700', !hasBarangay);
            openTrackModalBtn.classList.toggle('hover:shadow', !hasBarangay);
            openTrackModalBtn.classList.toggle('bg-white', hasBarangay);
            openTrackModalBtn.classList.toggle('text-blue-700', hasBarangay);
            openTrackModalBtn.classList.toggle('border', hasBarangay);
            openTrackModalBtn.classList.toggle('border-blue-300', hasBarangay);
            openTrackModalBtn.classList.toggle('hover:bg-blue-50', hasBarangay);
        }

        function openTrackModal() {
            if (requestSent || !trackModal) {
                return;
            }

            trackModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';

            const savedMode = (locationModeInput?.value || 'map') === 'manual' ? 'manual' : 'map';
            setTrackMode(savedMode);
            hasUsedCurrentPosition = false;

            const savedAddress = (addressInput?.value || '').trim();
            const addressMatch = savedAddress.match(/^(-?\d+(?:\.\d+)?),\s*(-?\d+(?:\.\d+)?)$/);
            if (addressMatch) {
                selectedLat = Number.parseFloat(addressMatch[1]);
                selectedLng = Number.parseFloat(addressMatch[2]);
                hasUsedCurrentPosition = true;
            } else {
                selectedLat = null;
                selectedLng = null;
            }

            detectedBarangay = (barangayInput?.value || '').trim();
            detectedPurok = (purokInput?.value || '').trim();

            if (manualBarangaySelect) {
                manualBarangaySelect.value = detectedBarangay;
            }
            populateManualPurokOptions(detectedBarangay, detectedPurok);
            if (manualPurokSelect && isRealPurokName(detectedPurok)) {
                manualPurokSelect.value = detectedPurok;
            }

            if (trackModalBody) {
                trackModalBody.scrollTop = 0;
            }

            if (saveTrackModalBtn) {
                saveTrackModalBtn.disabled = requestSent;
            }

            if (useCurrentPositionBtn) {
                useCurrentPositionBtn.disabled = requestSent;
            }

            setMapLoading(false);
            if (savedMode === 'map') {
                scheduleTrackMapInit();
            }
        }

        function destroyTrackMap() {
            if (mapInitTimer) {
                clearTimeout(mapInitTimer);
                mapInitTimer = null;
            }

            setMapLoading(false);

            if (marker) {
                marker.remove();
                marker = null;
            }

            if (purokBoundaryLayerGroup) {
                purokBoundaryLayerGroup.clearLayers();
                purokBoundaryLayerGroup = null;
            }

            if (barangayBoundaryLayerGroup) {
                barangayBoundaryLayerGroup.clearLayers();
                barangayBoundaryLayerGroup = null;
            }

            if (map) {
                map.remove();
                map = null;
            }

            selectedLat = null;
            selectedLng = null;
            hasUsedCurrentPosition = false;
            detectedBarangay = '';
            detectedPurok = '';

            if (saveTrackModalBtn) {
                saveTrackModalBtn.disabled = true;
            }

            if (useCurrentPositionBtn) {
                useCurrentPositionBtn.disabled = requestSent;
            }
        }

        function closeTrackModal() {
            trackModal?.classList.add('hidden');
            restoreBodyOverflowIfNoModal();
        }

        function closeAndRefreshTrackModal() {
            closeTrackModal();
            destroyTrackMap();
        }

        async function reverseGeocodeBarangay(lat, lng) {
            const boundaryMatch = detectBarangayForPoint(lat, lng);
            if (boundaryMatch) {
                return;
            }

            if (Array.isArray(barangayBoundaries) && barangayBoundaries.length > 0) {
                return;
            }

            try {
                const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${encodeURIComponent(lat)}&lon=${encodeURIComponent(lng)}&zoom=18&addressdetails=1`);
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                const address = data?.address || {};
                const barangay = address.suburb || address.village || address.neighbourhood || address.quarter || address.hamlet || '';

                if (barangay) {
                    detectedBarangay = barangay;
                    detectedPurok = findPurokByBoundary(lat, lng, barangay);
                    renderPurokBoundaries();
                }
            } catch (error) {
            }
        }

        openTrackModalBtn?.addEventListener('click', openTrackModal);
        window.addEventListener('resize', () => {
            if (isTrackModalOpen()) {
                refreshTrackMapSize();
            }
        });
        closeTrackModalBtn?.addEventListener('click', closeAndRefreshTrackModal);
        cancelTrackModalBtn?.addEventListener('click', closeAndRefreshTrackModal);
        noticeOkBtn?.addEventListener('click', closeNotice);
        confirmCancelBtn?.addEventListener('click', () => resolveConfirm(false));
        confirmOkBtn?.addEventListener('click', () => resolveConfirm(true));

        useCurrentPositionBtn?.addEventListener('click', () => {
            handleUseCurrentPosition();
        });

        saveTrackModalBtn?.addEventListener('click', async () => {
            if (requestSent) {
                return;
            }

            if (getLocationMode() === 'manual') {
                const barangayValue = (manualBarangaySelect?.value || '').trim();
                const purokValue = (manualPurokSelect?.value || '').trim();

                if (!barangayValue) {
                    showNotice('Missing Barangay', 'Please select your barangay.');
                    return;
                }

                if (!isRealPurokName(purokValue)) {
                    showNotice('Missing Purok', 'Please select your purok.');
                    return;
                }

                const shouldSave = await showConfirm('Confirm Save', 'Save this barangay and purok?');
                if (!shouldSave) {
                    return;
                }

                if (addressInput) {
                    addressInput.value = '';
                }

                applySavedLocationDisplay(barangayValue, purokValue, 'manual');
                closeTrackModal();
                return;
            }

            if (!hasUsedCurrentPosition) {
                showNotice('Current Location Required', 'Please click Use Current Position first before saving.');
                return;
            }

            await saveMapBasedLocation({ skipConfirm: false, autoClose: true });
        });

        trackModeMapBtn?.addEventListener('click', () => {
            setTrackMode('map');
            scheduleTrackMapInit();
        });

        trackModeManualBtn?.addEventListener('click', () => {
            setTrackMode('manual');
        });

        manualBarangaySelect?.addEventListener('change', () => {
            populateManualPurokOptions(manualBarangaySelect.value || '');
        });

        bindPersonNameInput(firstNameInput, updateHiddenFullName);
        bindPersonNameInput(lastNameInput, updateHiddenFullName);
        bindMiddleInitialInput(middleInitialInput, updateHiddenFullName);
        bindTextFieldInput(placeOfBirthInput);
        bindPersonNameInput(spouseFirstNameInput, updateHiddenSpouseFullName);
        bindPersonNameInput(spouseLastNameInput, updateHiddenSpouseFullName);
        bindMiddleInitialInput(spouseMiddleInitialInput, updateHiddenSpouseFullName);
        bindTextFieldInput(spousePlaceOfBirthInput);
        bindChildFullNameInput(childFullNameInput);
        bindTextFieldInput(childSchoolInput);
        bindTextFieldInput(childOccupationInput);
        bindChildFullNameInput(seniorFullNameInput);
        bindTextFieldInput(seniorPlaceOfBirthInput);
        bindTextFieldInput(seniorOccupationInput);
        bindTextFieldInput(idOtherLabelInput);
        bindContactInput(contactInput);

        setTrackMode(getLocationMode());
        if ((barangayInput?.value || '').trim()) {
            populateManualPurokOptions(barangayInput.value, (purokInput?.value || '').trim());
        }

        updateHiddenFullName();
        updateTrackButtonAppearance();
    </script>
</body>

</html>
