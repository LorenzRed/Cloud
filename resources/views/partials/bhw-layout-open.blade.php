@include('sidebars.bhw')

<div id="bhwContent" class="flex min-h-[100dvh] flex-col w-full min-w-0 lg:ml-64 lg:w-[calc(100%-16rem)] lg:h-screen">
    @include('partials.bhw-mobile-topbar')

    <main class="flex-1 flex flex-col min-h-0 overflow-hidden {{ $mainClass ?? 'px-4 pt-3 pb-24 sm:px-5 lg:px-8 lg:pt-8 lg:pb-6' }}">
