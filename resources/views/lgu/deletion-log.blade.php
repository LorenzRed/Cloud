<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Deletion Log - Bantay Kalusugan</title>
    @include('partials.favicon')
    @include('partials.submit-guard')
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <script>tailwind.config = {darkMode: 'class', theme: {extend: {colors: {primary: '#4A90E2', 'card-light': '#ffffff', 'card-dark': '#1f2937', 'border-light': '#e5e7eb', 'border-dark': '#374151', 'text-light': '#111827', 'text-dark': '#f3f4f6'}}}};</script>
    @include('partials.lgu-responsive-styles')
</head>
<body class="lgu-layout bg-gray-50 text-gray-900 dark:bg-gray-900 dark:text-gray-100">
<div class="flex min-h-screen">
    <div class="hidden lg:block">@include('sidebars.lgu')</div>
    <main class="min-w-0 flex-1 p-4 md:p-8">
        <a href="{{ route('lgu.main') }}" class="lgu-back-link text-primary md:hidden">Back to dashboard</a>
        <h1 class="text-2xl font-bold">Deletion Log</h1>
        <p class="mt-2 text-sm text-gray-500">Records removed by BHWs are kept here. Only LGU users can restore them to their original household. Times shown in Philippine time.</p>
        @if (session('status'))
            <p role="status" class="my-4 rounded-lg bg-green-100 p-3 text-green-900">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <p role="alert" class="my-4 rounded-lg bg-red-100 p-3 text-red-900">{{ $errors->first() }}</p>
        @endif
        <div class="mt-6 overflow-x-auto rounded-xl border bg-white dark:bg-gray-800">
            <table class="lgu-mobile-table w-full text-left text-sm">
                <thead class="bg-gray-100 dark:bg-gray-700"><tr>
                    <th class="p-4">Deleted record</th><th class="p-4">Original household</th><th class="p-4">Deleted by</th><th class="p-4">Deleted at</th><th class="p-4">Status / action</th>
                </tr></thead>
                <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t align-top">
                        <td data-label="Deleted record" class="p-4 font-medium">{{ $log->member_name }}<span class="block text-xs font-normal text-gray-500">{{ ucwords(str_replace('_', ' ', $log->action)) }} · #{{ $log->id }}</span></td>
                        <td data-label="Original household" class="p-4">{{ $log->resident_name }}<span class="block text-xs text-gray-500">Household record #{{ $log->resident_request_id }}</span></td>
                        <td data-label="Deleted by" class="p-4">{{ $log->deleted_by_name }}<span class="block text-xs text-gray-500">User #{{ $log->deleted_by }}</span></td>
                        <td data-label="Deleted at" class="p-4 whitespace-nowrap">{{ $log->deleted_at->timezone('Asia/Manila')->format('M d, Y h:i:s A') }}</td>
                        <td data-label="Status / action" class="p-4">
                            @if ($log->restored_at)
                                <span class="font-medium text-green-700">Restored</span>
                                <p class="text-xs">By {{ $log->restored_by_name }}<br>{{ $log->restored_at->timezone('Asia/Manila')->format('M d, Y h:i:s A') }}</p>
                            @else
                                <form method="POST" action="{{ route('lgu.deletion-log.restore', $log) }}" onsubmit="return confirm('Restore this record to its original household?')">
                                    @csrf
                                    <button type="submit" class="rounded-lg bg-primary px-4 py-2 font-semibold text-white">Restore</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">No deleted records yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </main>
</div>
@include('partials.lgu-mobile-nav')
</body>
</html>
