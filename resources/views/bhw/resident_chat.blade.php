<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta name="csrf-token" content="{{ csrf_token() }}"/>
<title>Bantay Kalusugan - Resident Chat</title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet"/>
@include('partials.bhw-messenger-chat-styles')
<script>
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    "primary": "#4A90E2",
                    "secondary": "#50E3C2",
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
<body class="bhw-messenger-chat-page font-display bg-background-light dark:bg-background-dark text-text-light dark:text-text-dark">
@php
    $residentInitials = collect(explode(' ', $residentRequest->full_name))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') ?: 'R';
    $bhwInitials = collect(explode(' ', Auth::user()->name ?? 'BHW'))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->join('') ?: 'B';
    $residentUser = $residentRequest->user;
    $peerAvatarUrl = $residentUser?->profile_picture_path
        ? route('profile.picture', ['user' => $residentUser->id, 'v' => optional($residentUser->updated_at)->timestamp])
        : null;
    $myAvatarUrl = Auth::user()->profile_picture_path
        ? route('profile.picture', ['user' => Auth::id(), 'v' => optional(Auth::user()->updated_at)->timestamp])
        : null;
    $purokLabel = $residentRequest->purok?->name ?? 'No purok';
@endphp
<div class="main-container">
    <div id="bhwChatMenuToggle" class="md:hidden fixed top-4 left-4 z-50">
        <button id="menuToggle" type="button" class="p-2 rounded-md bg-card-light dark:bg-card-dark shadow-md">
            <span class="material-symbols-outlined">menu</span>
        </button>
    </div>
    <div id="overlay"></div>
    @include('sidebars.bhw')

    <main class="bhw-messenger-main flex-1 min-h-0 flex flex-col overflow-hidden">
        <section class="bhw-messenger-shell flex-1 min-h-0 flex flex-col overflow-hidden">
            @include('partials.bhw-messenger-chat-header', [
                'backUrl' => route('bhw.residents.profile', $residentRequest),
                'backAriaLabel' => 'Back to resident profile',
                'peerAvatarUrl' => $peerAvatarUrl,
                'peerInitials' => $residentInitials,
                'peerName' => $residentRequest->full_name,
                'headerSubtitle' => $purokLabel,
                'headerStatusDot' => 'green',
                'headerBadge' => ['label' => 'Accepted', 'tone' => 'green'],
                'headerMeta' => $purokLabel,
            ])

            <div class="bhw-messenger-chat-body flex flex-col flex-1 min-h-0">
                @include('partials.resident-bhw-chat', [
                    'messages' => $messages,
                    'messageStoreUrl' => route('bhw.residents.chat.message', $residentRequest),
                    'messageActionUrl' => fn ($id) => route('bhw.residents.chat.message.update', [$residentRequest, $id]),
                    'messageReactionUrl' => fn ($id) => route('bhw.residents.chat.message.reaction', [$residentRequest, $id]),
                    'myInitials' => $bhwInitials,
                    'peerInitials' => $residentInitials,
                    'peerName' => $residentRequest->full_name,
                    'peerLabel' => $residentRequest->full_name,
                    'myLabel' => 'You · BHW',
                    'theme' => 'bhw',
                    'myAvatarUrl' => $myAvatarUrl,
                    'peerAvatarUrl' => $peerAvatarUrl,
                    'showMessengerIntro' => true,
                    'composerPlaceholder' => 'Aa',
                    'emptyTitle' => $purokLabel,
                    'emptySubtitle' => 'Start a conversation with this resident.',
                    'chatMessagesId' => 'chatMessages',
                    'chatFormId' => 'chatForm',
                    'chatInputId' => 'chatInput',
                    'chatSendBtnId' => 'chatSendBtn',
                    'unreadMessageIds' => $unreadBhwMessageIds ?? [],
                ])
            </div>
        </section>
    </main>
</div>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    menuToggle?.addEventListener('click', () => { sidebar?.classList.toggle('active'); overlay?.classList.toggle('active'); });
    overlay?.addEventListener('click', () => { sidebar?.classList.remove('active'); overlay?.classList.remove('active'); });
</script>
@if (session('status'))
<div data-auto-dismiss="3500" class="fixed top-4 right-4 z-[60] w-[min(92vw,24rem)] rounded-xl border border-green-300 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800 shadow-lg toast-enter dark:bg-green-900/30 dark:border-green-700 dark:text-green-200" role="status" aria-live="polite">
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
