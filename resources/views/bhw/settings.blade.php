<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Bantay Kalusugan - BHW Settings</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
<style>
    .material-symbols-outlined {
        font-variation-settings:
        'FILL' 0,
        'wght' 400,
        'GRAD' 0,
        'opsz' 24
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
    $displayName = $user->full_name ?: $user->name;
    $profilePicturePath = $user->profile_picture_path;
@endphp
<div class="main-container">
    <div class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>

    <div id="overlay" class=""></div>

    @include('sidebars.bhw')

    <main class="flex-1 p-8 overflow-hidden md:ml-0">
        <div class="page-content">
            <div class="w-full max-w-4xl mx-auto">
                <header class="mb-6 rounded-2xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-primary/80 dark:text-primary/90">Welcome</p>
                    <div class="mt-2 flex flex-col gap-1">
                        <h1 class="text-4xl font-extrabold leading-tight text-[#10261a] dark:text-white">{{ $displayName }}</h1>
                        <p class="text-sm font-medium text-text-light/70 dark:text-text-dark/70">Update your profile information and account details.</p>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm">
                        <div class="flex items-center gap-2 rounded-full bg-primary/10 px-3 py-1.5 text-primary">
                            <span class="material-symbols-outlined text-[18px]">mail</span>
                            <span>{{ $user->email }}</span>
                        </div>
                        <div class="flex items-center gap-2 rounded-full bg-background-light dark:bg-background-dark px-3 py-1.5 text-text-light/70 dark:text-text-dark/70">
                            <span class="material-symbols-outlined text-[18px]">badge</span>
                            <span>BHW Account</span>
                        </div>
                    </div>
                </header>

                @if (session('status'))
                    <div class="mb-4 rounded-lg border border-secondary/40 bg-secondary/10 px-4 py-3 text-sm">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-lg border border-accent-red/40 bg-accent-red/10 px-4 py-3 text-sm">
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-card-light dark:bg-card-dark rounded-xl shadow-sm border border-border-light dark:border-border-dark p-6">
                    <form method="POST" action="{{ route('bhw.settings.update') }}" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @csrf

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-2">Profile Picture</label>
                            <div class="flex flex-col md:flex-row md:items-center gap-4 rounded-xl border border-border-light dark:border-border-dark bg-background-light dark:bg-background-dark p-4">
                                <div class="flex items-center gap-4">
                                    @if ($profilePicturePath)
                                        <img id="profilePreview" src="{{ route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]) }}" alt="Profile picture" class="size-20 rounded-full object-cover border border-border-light dark:border-border-dark bg-white" />
                                    @else
                                        <div id="profilePreview" class="flex size-20 items-center justify-center rounded-full bg-primary/15 text-2xl font-bold text-primary border border-border-light dark:border-border-dark">
                                            {{ strtoupper(substr($displayName, 0, 1)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <p class="text-sm font-semibold text-text-light dark:text-text-dark">Upload a new photo</p>
                                        <p class="text-xs text-text-light/70 dark:text-text-dark/70">PNG or JPG up to 2 MB.</p>
                                    </div>
                                </div>
                                <input id="profilePictureInput" type="file" name="profile_picture" accept="image/*" class="w-full rounded-lg bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark px-3 py-2 text-sm md:max-w-md">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Full Name</label>
                            <input type="text" name="full_name" value="{{ old('full_name', $user->full_name) }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Gender</label>
                            <select name="gender" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                                <option value="">Select gender</option>
                                @foreach (['Male', 'Female', 'Other'] as $gender)
                                    <option value="{{ $gender }}" @selected(old('gender', $user->gender) === $gender)>{{ $gender }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Date of Birth</label>
                            <input id="dateOfBirth" type="date" name="date_of_birth" value="{{ old('date_of_birth', optional($user->date_of_birth)->format('Y-m-d')) }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Age (auto-computed)</label>
                            <input id="computedAge" type="number" value="{{ old('age', $user->age) }}" readonly class="w-full h-10 rounded-lg bg-background-light/70 dark:bg-background-dark/70 border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Civil Status</label>
                            <input type="text" name="civil_status" value="{{ old('civil_status', $user->civil_status) }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $user->contact_number) }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1">Barangay</label>
                            <input type="text" name="barangay" value="{{ old('barangay', $user->barangay) }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Household No.</label>
                            <input type="text" name="household_no" value="{{ old('household_no', $user->household_no) }}" class="w-full h-10 rounded-lg bg-background-light dark:bg-background-dark border border-border-light dark:border-border-dark px-3">
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="h-10 px-4 rounded-lg bg-primary text-white text-sm font-semibold">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    const dateOfBirth = document.getElementById('dateOfBirth');
    const computedAge = document.getElementById('computedAge');
    const profilePictureInput = document.getElementById('profilePictureInput');
    const profilePreview = document.getElementById('profilePreview');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });

    function updateAgePreview() {
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

    if (profilePictureInput && profilePreview) {
        profilePictureInput.addEventListener('change', () => {
            const file = profilePictureInput.files && profilePictureInput.files[0];
            if (!file) {
                return;
            }

            const objectUrl = URL.createObjectURL(file);
            if (profilePreview.tagName === 'IMG') {
                profilePreview.src = objectUrl;
            } else {
                profilePreview.innerHTML = `<img src="${objectUrl}" alt="Profile preview" class="size-20 rounded-full object-cover">`;
            }
        });
    }
</script>
</body>
</html>
