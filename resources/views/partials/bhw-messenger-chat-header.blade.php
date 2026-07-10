@php
    $headerSubtitle = $headerSubtitle ?? '';
    $headerStatusDot = $headerStatusDot ?? 'primary';
    $headerBadge = $headerBadge ?? null;
    $headerMeta = $headerMeta ?? null;
    $statusDotClass = match ($headerStatusDot) {
        'green' => 'bg-green-500',
        'amber' => 'bg-amber-500 animate-pulse',
        default => 'bg-primary',
    };
@endphp
<header class="bhw-messenger-header shrink-0 flex items-center gap-2.5 px-2 lg:px-5 pb-2.5 lg:py-3 bg-white dark:bg-card-dark border-b border-border-light dark:border-border-dark">
    <a href="{{ $backUrl }}" class="inline-flex h-10 w-10 items-center justify-center rounded-full text-text-light dark:text-text-dark hover:bg-background-light dark:hover:bg-background-dark transition shrink-0" aria-label="{{ $backAriaLabel ?? 'Go back' }}">
        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
    </a>
    <div class="flex items-center gap-3 min-w-0 flex-1">
        <div class="h-10 w-10 lg:h-11 lg:w-11 rounded-full bg-primary/15 text-primary flex items-center justify-center shrink-0 ring-2 ring-white dark:ring-card-dark shadow-sm overflow-hidden text-sm lg:text-base font-bold">
            @if ($peerAvatarUrl ?? null)
                <img src="{{ $peerAvatarUrl }}" alt="{{ $peerName }}" class="h-full w-full object-cover"/>
            @else
                {{ $peerInitials }}
            @endif
        </div>
        <div class="min-w-0">
            <p class="font-bold text-[15px] lg:text-base leading-tight truncate text-text-light dark:text-text-dark">{{ $peerName }}</p>
            @if ($headerSubtitle !== '')
                <p class="text-xs lg:text-sm text-text-light/60 dark:text-text-dark/60 truncate flex items-center gap-1.5 mt-0.5">
                    <span class="inline-block h-2 w-2 rounded-full {{ $statusDotClass }} shrink-0"></span>
                    <span>{{ $headerSubtitle }}</span>
                </p>
            @endif
        </div>
    </div>
    @if ($headerBadge || $headerMeta)
        <div class="hidden sm:flex flex-col items-end gap-1 shrink-0">
            @if ($headerBadge)
                <span @class([
                    'inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[10px] font-bold uppercase tracking-wide',
                    'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200' => ($headerBadge['tone'] ?? '') === 'green',
                    'bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200' => ($headerBadge['tone'] ?? '') === 'amber',
                ])>
                    @if (($headerBadge['tone'] ?? '') === 'amber')
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                    @elseif (($headerBadge['tone'] ?? '') === 'green')
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    @endif
                    {{ $headerBadge['label'] }}
                </span>
            @endif
            @if ($headerMeta)
                <p class="text-[10px] text-text-light/50 dark:text-text-dark/50">{{ $headerMeta }}</p>
            @endif
        </div>
    @endif
</header>
