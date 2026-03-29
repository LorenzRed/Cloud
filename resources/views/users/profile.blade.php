<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Bantay Kalusugan - Edit Profile</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,100..900;1,100..900&amp;display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#19e66b",
                        "background-light": "#f6f8f7",
                        "background-dark": "#112117",
                    },
                    fontFamily: {
                        "display": ["Public Sans", "sans-serif"]
                    },
                },
            },
        }
    </script>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-[#0e1b13] dark:text-gray-200">
    @php
        $selectedGender = ucfirst(strtolower(trim((string) old('gender', $user->gender))));
    @endphp
    <div class="min-h-screen px-6 md:px-8 py-8">
        <div class="mx-auto w-full max-w-4xl">
            <header class="mb-6 flex items-center justify-between gap-3">
                <div>
                    <h1 class="text-3xl font-bold">Profile Settings</h1>
                    <p class="text-sm text-[#4e976b] dark:text-gray-400 mt-1">Update your resident profile information.</p>
                </div>
                <a href="{{ route('users.main') }}" class="inline-flex items-center gap-1.5 text-sm font-semibold text-primary hover:underline">
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    Back to Dashboard
                </a>
            </header>

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

            <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-[#d0e7d9] dark:border-gray-700 shadow-sm">
                <form id="profileForm" method="POST" action="{{ route('users.profile.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @csrf

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Profile Picture (optional)</label>
                        <input type="file" name="profile_picture" accept="image/*" class="w-full rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3 py-2">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Full Name</label>
                        <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Gender</label>
                        <select name="gender" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                            <option value="">Select gender</option>
                            @foreach (['Male', 'Female', 'Other'] as $gender)
                                <option value="{{ $gender }}" @selected($selectedGender === $gender)>{{ $gender }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Date of Birth</label>
                        <input id="dateOfBirth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Age</label>
                        <input id="computedAge" type="number" value="{{ old('age', $user->age) }}" readonly class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Civil Status</label>
                        <input type="text" name="civil_status" value="{{ old('civil_status', $user->civil_status) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3" placeholder="---">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Contact Number</label>
                        <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Household No.</label>
                        <input type="text" name="household_no" value="{{ old('household_no', $user->household_no) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-background-light dark:bg-gray-900 px-3" placeholder="---">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium mb-1">Barangay</label>
                        <input type="text" name="barangay" value="{{ old('barangay', $user->barangay) }}" readonly class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-gray-100 dark:bg-gray-900/60 px-3 cursor-not-allowed">
                        <p class="text-xs text-[#4e976b] dark:text-gray-400 mt-1">Cannot be changed.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Municipality</label>
                        <input type="text" id="municipalityAddressInput" name="municipality" value="{{ old('municipality', $user->municipality) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 px-3" placeholder="---">
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Province</label>
                        <input type="text" id="provinceAddressInput" name="province" value="{{ old('province', $user->province) }}" class="w-full h-10 rounded-lg border border-[#d0e7d9] dark:border-gray-700 bg-white dark:bg-gray-800 px-3" placeholder="---">
                    </div>

                    <div class="md:col-span-2 pt-2">
                        <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-[#0e1b13] text-sm font-bold hover:opacity-90">Update Profile</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const dateOfBirth = document.getElementById('dateOfBirth');
        const computedAge = document.getElementById('computedAge');
        function updateAgePreview() {
            if (!dateOfBirth || !computedAge) {
                return;
            }

            if (!dateOfBirth.value) {
                computedAge.value = '';
                return;
            }

            const birthDate = new Date(dateOfBirth.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            computedAge.value = age >= 0 ? age : '';
        }

        if (dateOfBirth) {
            dateOfBirth.addEventListener('input', updateAgePreview);
        }

        updateAgePreview();
    </script>
</body>
</html>
