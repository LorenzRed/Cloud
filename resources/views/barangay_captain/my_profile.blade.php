<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.favicon')
<meta charset="utf-8"/>
@include('partials.bhw-viewport')
<title>Bantay Kalusugan - Profile</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.captain-shell-styles')
<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
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
@include('sidebars.barangay_captain')

<div id="captainContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.captain-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 px-4 sm:px-5 md:px-6 lg:px-8 pt-4 lg:pt-6 pb-24 lg:pb-6 overflow-y-auto">
        <div class="w-full max-w-2xl mx-auto md:max-w-none md:mx-0 space-y-6">
            <div class="flex justify-center">
                <div class="flex flex-col items-center text-center gap-2">
                    <div class="relative mt-2 lg:mt-4 mb-2 h-28 w-28 overflow-visible">
                        <div class="flex h-28 w-28 items-center justify-center overflow-hidden rounded-full border-2 border-primary/20 bg-primary/10">
                            @if ($user->profile_picture_path)
                                <img src="{{ route('profile.picture', ['user' => $user->id, 'v' => optional($user->updated_at)->timestamp]) }}" alt="Profile picture" class="h-full w-full object-cover" />
                            @else
                                @php
                                    $profileInitial = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $user->full_name ?: $user->name ?: 'U') ?: 'U', 0, 1));
                                @endphp
                                <span class="text-4xl font-bold text-primary">{{ $profileInitial }}</span>
                            @endif
                        </div>
                        <button type="button" id="avatarEditBtn" class="absolute -bottom-1 -right-1 inline-flex h-10 w-10 items-center justify-center rounded-full bg-primary text-white border-2 border-card-light dark:border-card-dark shadow-md" aria-label="Edit profile picture">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                        </button>
                        <input id="avatarFileInput" type="file" accept="image/*" class="hidden" />
                    </div>
                    <div>
                        <p class="text-xl sm:text-2xl font-bold text-text-light dark:text-text-dark">{{ $user->full_name ?: $user->name }}</p>
                        <p class="text-sm sm:text-base text-primary font-medium mt-1">{{ $user->contact_number ?: $user->email }}</p>
                        <p class="text-xs sm:text-sm text-text-light/60 dark:text-text-dark/60 mt-2 max-w-md mx-auto">Barangay Captain · Bantay Kalusugan</p>
                    </div>
                </div>
            </div>

            <section class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-sm overflow-hidden divide-y divide-border-light dark:divide-border-dark">
                <a href="{{ route('barangay-captain.settings') }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-primary/5 transition-colors">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">Profile</p>
                        <h2 class="mt-1 text-lg font-bold text-text-light dark:text-text-dark">Profile Details</h2>
                        <p class="mt-1 text-sm text-text-light/70 dark:text-text-dark/70">Update your name and password.</p>
                    </div>
                    <span class="material-symbols-outlined text-[22px] text-text-light/50 dark:text-text-dark/50 shrink-0">chevron_right</span>
                </a>
                <a href="{{ route('barangay-captain.all-records') }}" class="w-full flex items-center justify-between gap-4 px-5 py-4 text-left hover:bg-primary/5 transition-colors">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60">History</p>
                        <h2 class="mt-1 text-lg font-bold text-text-light dark:text-text-dark">All Records</h2>
                        <p class="mt-1 text-sm text-text-light/70 dark:text-text-dark/70">Browse transactions and maintenance visits across all puroks.</p>
                    </div>
                    <span class="material-symbols-outlined text-[22px] text-text-light/50 dark:text-text-dark/50 shrink-0">chevron_right</span>
                </a>
            </section>

            <section class="space-y-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-text-light/60 dark:text-text-dark/60 px-1">Account</p>
                <div class="rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark shadow-sm overflow-hidden">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-3 px-5 py-4 text-left text-accent-red hover:bg-accent-red/5 transition-colors">
                            <span class="material-symbols-outlined text-[22px]">logout</span>
                            <span class="font-semibold text-base">Logout</span>
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>

    @include('partials.captain-mobile-nav')
</div>

<script>
    (function () {
        const avatarBtn = document.getElementById('avatarEditBtn');
        const fileInput = document.getElementById('avatarFileInput');
        if (!avatarBtn || !fileInput) return;

        avatarBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', () => {
            if (!(fileInput.files && fileInput.files.length)) return;

            const fd = new FormData();
            fd.append('profile_picture', fileInput.files[0]);
            fd.append('_token', @json(csrf_token()));

            fetch(@json(route('barangay-captain.my-profile.avatar')), {
                method: 'POST',
                body: fd,
                credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            }).then(async (res) => {
                if (!res.ok) {
                    const data = await res.json().catch(() => null);
                    alert(data?.errors?.join(', ') || 'Upload failed.');
                    return;
                }

                const data = await res.json();
                if (data?.url) {
                    window.location.reload();
                }
            }).catch(() => alert('Upload failed.'));
        });
    })();
</script>
</body>
</html>
