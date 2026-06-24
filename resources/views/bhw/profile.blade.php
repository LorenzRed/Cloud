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
        display: block;
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
        overflow-y: auto;
    }

    .page-content::-webkit-scrollbar {
        width: 6px;
    }

    .page-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }

    .page-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 10px;
    }

    .page-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
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
    $residentBarangay = $resident->barangay ?? ($resident->user->barangay ?? 'N/A');

    $residentHousehold = trim((string) ($residentUser?->household_no ?? ''));
    $residentMunicipality = trim((string) ($residentUser?->municipality ?? ''));
    $residentProvince = trim((string) ($residentUser?->province ?? ''));
    $residentAddressParts = array_values(array_filter([
        $residentHousehold !== '' ? $residentHousehold : null,
        filled($residentBarangay) && $residentBarangay !== 'N/A' ? $residentBarangay : null,
        $residentMunicipality !== '' ? $residentMunicipality : null,
        $residentProvince !== '' ? $residentProvince : null,
    ]));
    $residentAddress = !empty($residentAddressParts)
        ? implode(', ', $residentAddressParts)
        : ($resident->address ?: null);

    $residentContact = $resident->contact ?: ($resident->user->contact_number ?? null);
    $residentId = 'RR-'.str_pad((string) $resident->id, 6, '0', STR_PAD_LEFT);
    $residentProfilePicturePath = $residentUser?->profile_picture_path;
@endphp

<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>

    <div id="overlay" class=""></div>

    @include('sidebars.bhw')

    <main class="flex-1 p-6 lg:p-10 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="max-w-7xl mx-auto">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                    <div>
                        <nav class="text-sm text-text-light/70 dark:text-text-dark/70 mb-2">
                            <a class="hover:underline" href="{{ route('bhw.residents') }}">Residents</a>
                            <span class="mx-2">/</span>
                            <span>{{ $residentName }}</span>
                        </nav>
                        <h1 class="text-3xl font-bold text-text-light dark:text-text-dark">Resident Profile</h1>
                    </div>
                </div>

                <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                    <div class="xl:col-span-1 space-y-6">
                        <div class="bg-card-light dark:bg-card-dark rounded-lg p-6 shadow-sm border border-border-light dark:border-border-dark">
                            <div class="flex flex-col items-center text-center">
                                @if ($residentProfilePicturePath && $residentUser)
                                    <img
                                        src="{{ route('profile.picture', ['user' => $residentUser->id, 'v' => optional($residentUser->updated_at)->timestamp]) }}"
                                        alt="Resident profile picture"
                                        class="w-28 h-28 mb-4 rounded-full object-cover border border-border-light dark:border-border-dark"
                                    />
                                @else
                                    <div class="w-28 h-28 mb-4 rounded-full bg-primary/15 text-primary flex items-center justify-center text-3xl font-bold">
                                        {{ strtoupper(substr($residentName, 0, 1)) }}
                                    </div>
                                @endif
                                <p class="text-text-light dark:text-text-dark text-xl font-bold leading-tight">{{ $residentName }}</p>
                                <p class="text-text-light/70 dark:text-text-dark/70 text-sm">Resident ID: {{ $residentId }}</p>
                                <p class="text-text-light/70 dark:text-text-dark/70 text-sm">
                                    {{ $residentAge !== null ? $residentAge.' yrs old' : 'Age N/A' }} | {{ $residentGender }} | {{ $residentBarangay }}
                                </p>
                                <div class="mt-2 flex gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Community Record</span>
                                </div>
                            </div>
                            <div class="mt-6">
                                <div class="border-t border-border-light dark:border-border-dark py-4">
                                    <p class="text-text-light/70 dark:text-text-dark/70 text-xs font-semibold uppercase tracking-wider">Contact Number</p>
                                    <p class="text-text-light dark:text-text-dark text-sm mt-1">{{ $residentContact ?: 'N/A' }}</p>
                                </div>
                                <div class="border-t border-border-light dark:border-border-dark py-4">
                                    <p class="text-text-light/70 dark:text-text-dark/70 text-xs font-semibold uppercase tracking-wider">Barangay</p>
                                    <p class="text-text-light dark:text-text-dark text-sm mt-1">{{ $residentBarangay }}</p>
                                </div>
                                <div class="border-t border-border-light dark:border-border-dark py-4">
                                    <p class="text-text-light/70 dark:text-text-dark/70 text-xs font-semibold uppercase tracking-wider">Address</p>
                                    <p class="text-text-light dark:text-text-dark text-sm mt-1">{{ $residentAddress ?: 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="xl:col-span-2">
                        <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-sm border border-border-light dark:border-border-dark min-h-[520px]">
                            <div class="border-b border-border-light dark:border-border-dark">
                                <nav aria-label="Tabs" class="flex overflow-x-auto space-x-4 px-6">
                                    <a aria-current="page" class="px-1 py-4 text-sm font-semibold text-primary border-b-2 border-primary whitespace-nowrap tab-link" data-tab="overview" href="#">Overview</a>
                                    <a class="px-1 py-4 text-sm font-medium text-text-light/70 dark:text-text-dark/70 hover:text-primary border-b-2 border-transparent whitespace-nowrap tab-link" data-tab="medical-history" href="#">Medical History</a>
                                    <a class="px-1 py-4 text-sm font-medium text-text-light/70 dark:text-text-dark/70 hover:text-primary border-b-2 border-transparent whitespace-nowrap tab-link" data-tab="consultations" href="#">Consultations</a>
                                </nav>
                            </div>

                            <div class="p-6">
                                <div id="overview" class="tab-content active">
                                    <h4 class="text-lg font-bold mb-4">Resident Summary</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4 bg-background-light dark:bg-background-dark">
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Full name</p>
                                            <p class="font-semibold">{{ $residentName }}</p>
                                        </div>
                                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4 bg-background-light dark:bg-background-dark">
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Age</p>
                                            <p class="font-semibold">{{ $residentAge ?? 'N/A' }}</p>
                                        </div>
                                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4 bg-background-light dark:bg-background-dark">
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Gender</p>
                                            <p class="font-semibold">{{ $residentGender }}</p>
                                        </div>
                                        <div class="rounded-lg border border-border-light dark:border-border-dark p-4 bg-background-light dark:bg-background-dark">
                                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">Last accepted date</p>
                                            <p class="font-semibold">{{ $resident->reviewed_at ? $resident->reviewed_at->format('M d, Y') : 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div id="medical-history" class="tab-content">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <h4 class="text-lg font-bold">Medical History</h4>
                                        <button type="button" id="toggleMedicalHistoryForm" class="inline-flex items-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                            <span class="material-symbols-outlined text-base">add</span>
                                            <span>Add Medical Record</span>
                                        </button>
                                    </div>

                                    @if (session('status'))
                                        <div class="mb-4 rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-sm text-green-800 dark:bg-green-900/30 dark:border-green-700 dark:text-green-200">
                                            {{ session('status') }}
                                        </div>
                                    @endif

                                    @if ($errors->any())
                                        <div class="mb-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800 dark:bg-red-900/30 dark:border-red-700 dark:text-red-200">
                                            <ul class="list-disc pl-5 space-y-1">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div id="medicalHistoryFormWrapper" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
                                        <div class="w-full max-w-2xl rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 shadow-xl max-h-[90vh] overflow-y-auto">
                                            <div class="flex items-center justify-between gap-3 mb-4">
                                                <div>
                                                    <h5 class="text-lg font-bold">Add Medical Record</h5>
                                                    <p class="text-sm text-text-light/70 dark:text-text-dark/70">Record health details and calculate BMI automatically when height and weight are provided.</p>
                                                </div>
                                                <button type="button" id="closeMedicalHistoryForm" class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" aria-label="Close medical record form">
                                                    <span class="material-symbols-outlined text-base">close</span>
                                                </button>
                                            </div>

                                            <form method="POST" action="{{ route('bhw.residents.medical-history.store', $resident) }}" class="space-y-3">
                                            @csrf
                                            <div>
                                                <p class="text-sm font-semibold mb-2">Common Barangay Medical Record Types</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @foreach (['Vaccination', 'Height', 'Weight', 'BMI', 'Blood Pressure', 'Blood Type', 'Body Temperature', 'Heart Rate', 'Oxygen Saturation', 'Allergies', 'Maintenance Medicines', 'Existing Conditions', 'Medical Note'] as $commonType)
                                                        <button type="button" class="record-type-chip inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" data-record-type="{{ $commonType }}">
                                                            {{ $commonType }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold mb-1" for="record_type">Medical Record Type</label>
                                                <input id="record_type" name="record_type" value="{{ old('record_type') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="Type record (e.g., Vaccination, Height, Weight, BMI)" type="text" required />
                                            </div>

                                            <div>
                                                <label class="block text-sm font-semibold mb-1" for="record_value">Record Value</label>
                                                <input id="record_value" name="record_value" value="{{ old('record_value') }}" class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="Enter value (e.g., Vaccinated, 165 cm, 60 kg, 22.4)" type="text" required />
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 rounded-lg border border-border-light dark:border-border-dark p-3 bg-card-light dark:bg-card-dark">
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="height_cm">Height (cm)</label>
                                                    <input id="height_cm" name="height_cm" value="{{ old('height_cm') }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="e.g., 165" type="number" min="0" step="0.1" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="weight_kg">Weight (kg)</label>
                                                    <input id="weight_kg" name="weight_kg" value="{{ old('weight_kg') }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm" placeholder="e.g., 60" type="number" min="0" step="0.1" />
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-semibold mb-1" for="bmi_value">BMI</label>
                                                    <input id="bmi_value" type="text" readonly class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark text-sm" placeholder="Auto-calculated" />
                                                </div>
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

                                    <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @forelse ($resident->medicalRecords as $medicalRecord)
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                                <div class="flex items-start justify-between gap-2">
                                                    <div>
                                                        <p class="text-sm text-text-light/70 dark:text-text-dark/70">{{ $medicalRecord->record_type }}</p>
                                                        <p class="font-semibold mt-1">{{ $medicalRecord->record_value }}</p>
                                                    </div>
                                                    <button type="button" class="edit-record-btn inline-flex items-center justify-center w-8 h-8 rounded-md border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark" data-record-id="{{ $medicalRecord->id }}" aria-label="Edit medical record">
                                                        <span class="material-symbols-outlined text-base">edit</span>
                                                    </button>
                                                </div>

                                                <form method="POST" action="{{ route('bhw.medical-records.update', $medicalRecord) }}" class="edit-record-form hidden mt-3 space-y-2" data-record-id="{{ $medicalRecord->id }}">
                                                    @csrf
                                                    <input
                                                        name="record_type"
                                                        value="{{ $medicalRecord->record_type }}"
                                                        class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                        placeholder="Medical record type"
                                                        type="text"
                                                        required
                                                    />
                                                    <input
                                                        name="record_value"
                                                        value="{{ $medicalRecord->record_value }}"
                                                        class="w-full h-10 rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                        placeholder="Record value"
                                                        type="text"
                                                        required
                                                    />
                                                    <textarea
                                                        name="note"
                                                        rows="2"
                                                        maxlength="500"
                                                        class="w-full rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark focus:ring-primary focus:border-primary text-sm"
                                                        placeholder="Optional note"
                                                    >{{ $medicalRecord->note }}</textarea>
                                                    <div class="flex justify-end">
                                                        <button type="submit" class="inline-flex items-center gap-1 h-9 px-3 rounded-lg bg-primary text-white text-sm font-semibold hover:opacity-90">
                                                            <span class="material-symbols-outlined text-base">save</span>
                                                            Save
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        @empty
                                            <div class="rounded-lg border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4 text-sm text-text-light/70 dark:text-text-dark/70 md:col-span-2">
                                                No medical records posted yet.
                                            </div>
                                        @endforelse
                                    </div>
                                </div>

                                <div id="consultations" class="tab-content">
                                    <div class="flex items-center justify-between gap-3 mb-4">
                                        <h4 class="text-lg font-bold">Consultations</h4>
                                        <button type="button" id="toggleConsultationForm" class="inline-flex items-center gap-2 rounded-lg h-10 px-4 bg-primary text-white text-sm font-bold leading-normal tracking-wide whitespace-nowrap">
                                            <span class="material-symbols-outlined text-base">event</span>
                                            <span>Add Appointment</span>
                                        </button>
                                    </div>

                                    <div id="consultationFormWrapper" class="hidden rounded-lg border border-border-light dark:border-border-dark p-4 bg-background-light dark:bg-background-dark mb-4">
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
                item.classList.remove('text-primary', 'border-primary', 'font-semibold');
                item.classList.add('text-text-light/70', 'dark:text-text-dark/70', 'border-transparent', 'font-medium');
                item.setAttribute('aria-current', 'false');
            });

            tab.classList.remove('text-text-light/70', 'dark:text-text-dark/70', 'border-transparent', 'font-medium');
            tab.classList.add('text-primary', 'border-primary', 'font-semibold');
            tab.setAttribute('aria-current', 'page');

            tabContents.forEach((content) => content.classList.remove('active'));
            document.getElementById(targetTab).classList.add('active');
        });
    });

    document.getElementById('printRecordBtn')?.addEventListener('click', () => {
        window.print();
    });

    const recordTypeInput = document.getElementById('record_type');
    const recordValueInput = document.getElementById('record_value');
    const heightInput = document.getElementById('height_cm');
    const weightInput = document.getElementById('weight_kg');
    const bmiValueInput = document.getElementById('bmi_value');
    const toggleMedicalHistoryFormBtn = document.getElementById('toggleMedicalHistoryForm');
    const closeMedicalHistoryFormBtn = document.getElementById('closeMedicalHistoryForm');
    const medicalHistoryFormWrapper = document.getElementById('medicalHistoryFormWrapper');
    const medicalHistoryModalPanel = medicalHistoryFormWrapper?.querySelector('div');
    const toggleConsultationFormBtn = document.getElementById('toggleConsultationForm');
    const consultationFormWrapper = document.getElementById('consultationFormWrapper');

    toggleMedicalHistoryFormBtn?.addEventListener('click', () => {
        medicalHistoryFormWrapper?.classList.remove('hidden');
        recordTypeInput?.focus();
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

    const calculateBmi = () => {
        if (!heightInput || !weightInput || !bmiValueInput) {
            return;
        }

        const heightCm = parseFloat(heightInput.value);
        const weightKg = parseFloat(weightInput.value);

        if (!Number.isFinite(heightCm) || !Number.isFinite(weightKg) || heightCm <= 0 || weightKg <= 0) {
            bmiValueInput.value = '';
            return;
        }

        const bmi = weightKg / Math.pow(heightCm / 100, 2);
        const bmiText = bmi.toFixed(1);

        bmiValueInput.value = bmiText;

        const currentRecordType = (recordTypeInput?.value || '').trim().toLowerCase();
        if ((!currentRecordType || currentRecordType === 'bmi') && recordTypeInput) {
            recordTypeInput.value = 'BMI';
        }

        if ((!currentRecordType || currentRecordType === 'bmi') && recordValueInput) {
            recordValueInput.value = bmiText;
        }
    };

    heightInput?.addEventListener('input', calculateBmi);
    weightInput?.addEventListener('input', calculateBmi);

    recordTypeInput?.addEventListener('input', () => {
        if ((recordTypeInput.value || '').trim().toLowerCase() === 'bmi') {
            calculateBmi();
        }
    });

    document.querySelectorAll('.record-type-chip').forEach((chip) => {
        chip.addEventListener('click', () => {
            if (!recordTypeInput) {
                return;
            }

            recordTypeInput.value = chip.getAttribute('data-record-type') || '';
            recordTypeInput.focus();
            calculateBmi();
        });
    });

    document.querySelectorAll('.edit-record-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const recordId = button.getAttribute('data-record-id');
            if (!recordId) {
                return;
            }

            const form = document.querySelector(`.edit-record-form[data-record-id="${recordId}"]`);
            form?.classList.toggle('hidden');
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

    if (hasValidationErrors && (!pageTab || pageTab === 'medical-history')) {
        medicalHistoryFormWrapper?.classList.remove('hidden');
    }

    calculateBmi();

    if (pageTab === 'consultations') {
        consultationFormWrapper?.classList.remove('hidden');
    }
</script>
</body>
</html>
