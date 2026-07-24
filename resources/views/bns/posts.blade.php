<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
@include('partials.bns-viewport')
<title>Bantay Kalusugan - BNS Posts</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bns-shell-styles')
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
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
    main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-height: 0; }
    .page-content { height: 100%; overflow-y: auto; -webkit-overflow-scrolling: touch; }
</style>
</head>
<body class="font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@include('sidebars.bns')

<div id="bnsContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bns-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 pt-3 sm:pt-4 px-4 sm:px-5 md:px-6 lg:px-8 lg:pt-6 pb-24 lg:pb-6 overflow-hidden">
        <div class="page-content">
            <div class="max-w-5xl mx-auto w-full space-y-4">
                <header>
                    <h1 class="text-2xl font-bold">Posts</h1>
                    <p class="text-sm text-text-light/70 dark:text-text-dark/70 mt-1">
                        Consultation notices and announcements you posted (date only, no exact time).
                        @if (filled($barangayName ?? null))
                            · {{ $barangayName }}
                        @endif
                    </p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @forelse ($posts as $post)
                        @php $isExpired = $post->isExpired(); @endphp
                        <article class="rounded-xl border {{ $isExpired ? 'border-border-light dark:border-border-dark opacity-90' : 'border-primary/25' }} bg-card-light dark:bg-card-dark p-4 sm:p-5 shadow-sm">
                            <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="inline-flex items-center rounded-full bg-primary/15 px-2.5 py-1 text-xs font-semibold text-primary">Consultation</span>
                                    @if ($isExpired)
                                        <span class="inline-flex items-center rounded-full bg-gray-200 dark:bg-gray-700 px-2.5 py-1 text-xs font-semibold">Expired</span>
                                    @endif
                                </div>
                                <span class="text-xs text-text-light/60 dark:text-text-dark/60">
                                    {{ $post->event_date?->format('M d, Y') ?? 'TBA' }}
                                </span>
                            </div>
                            <h2 class="text-lg font-bold leading-snug">{{ $post->title }}</h2>
                            <p class="mt-2 text-sm text-text-light/80 dark:text-text-dark/80 leading-relaxed">
                                {{ $post->description ?: 'No message' }}
                            </p>
                            <p class="mt-4 text-xs text-text-light/50 dark:text-text-dark/50 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-sm">calendar_today</span>
                                Consultation date · {{ $post->event_date?->format('M d, Y') ?? 'TBA' }}
                            </p>
                        </article>
                    @empty
                        <div class="md:col-span-2 rounded-xl border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark px-4 py-10 text-center">
                            <span class="material-symbols-outlined text-4xl text-text-light/40 dark:text-text-dark/40 mb-2 block">campaign</span>
                            <p class="text-sm text-text-light/70 dark:text-text-dark/70">No posts yet. Notify a consultation date from a child’s Immunization tab in Monitoring.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>

    @include('partials.bns-mobile-nav')
</div>
</body>
</html>
