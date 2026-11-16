@php
    $lguMobileLinks = [
        ['route' => 'lgu.main', 'label' => 'Home', 'icon' => 'dashboard'],
        ['route' => 'lgu.barangays', 'label' => 'Barangays', 'icon' => 'location_city'],
        ['route' => 'lgu.system-logs', 'label' => 'Logs', 'icon' => 'history'],
        ['route' => 'lgu.deletion-log', 'label' => 'Deleted', 'icon' => 'restore_from_trash'],
        ['route' => 'lgu.reports', 'label' => 'Reports', 'icon' => 'description'],
    ];
@endphp
<header class="lgu-mobile-header no-print">
    <div class="lgu-mobile-brand">
        <img src="{{ asset('logo/logo.png') }}" alt="">
        <div><strong>Bantay Kalusugan <span class="text-primary">LGU</span></strong><p>{{ Auth::user()->name }}</p></div>
    </div>
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="lgu-mobile-logout" aria-label="Log out" title="Log out"><span class="material-symbols-outlined" aria-hidden="true">logout</span></button>
    </form>
</header>
<nav class="lgu-mobile-nav no-print" aria-label="LGU mobile navigation">
    @foreach ($lguMobileLinks as $link)
        <a href="{{ route($link['route']) }}" @if (request()->routeIs($link['route'])) aria-current="page" @endif>
            <span class="material-symbols-outlined" aria-hidden="true">{{ $link['icon'] }}</span>
            <span>{{ $link['label'] }}</span>
        </a>
    @endforeach
</nav>
<script>
    (() => {
        function syncLguKeyboard() {
            const editing = document.activeElement?.matches('input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),textarea,select,[contenteditable="true"]');
            document.body.classList.toggle('lgu-keyboard-open', window.innerWidth < 1024 && Boolean(editing));
        }
        document.addEventListener('focusin', syncLguKeyboard);
        document.addEventListener('focusout', () => requestAnimationFrame(syncLguKeyboard));
        window.addEventListener('resize', syncLguKeyboard);
    })();
</script>
