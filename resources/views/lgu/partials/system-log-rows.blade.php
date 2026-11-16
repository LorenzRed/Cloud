@forelse ($logs as $log)
    <tr class="border-t align-top">
        <td data-label="When" class="p-4 whitespace-nowrap text-gray-500">{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y h:i:s A') }}<span class="block text-xs">#{{ $log->id }}</span></td>
        <td data-label="Who" class="p-4 font-medium">{{ $log->actor_name }}<span class="block text-xs font-normal text-gray-500">{{ $log->actor_role ?? 'Unverified / system' }}{{ $log->user_id ? ' / User #'.$log->user_id : '' }}</span>@if ($log->attempted_email)<span class="mt-1 block break-all text-xs font-normal text-gray-500">Email entered: {{ $log->attempted_email }}</span>@endif</td>
        <td data-label="Event" class="p-4">{{ $log->action }}<span class="block text-xs text-gray-500">{{ $log->route_name }}</span></td>
        <td data-label="Source" class="p-4 text-xs"><span class="block">{{ $log->ip_address ?? 'Not recorded' }}</span>@if ($log->user_agent)<details class="mt-2"><summary class="cursor-pointer text-primary">Device details</summary><p class="mt-1 break-words">{{ $log->user_agent }}</p></details>@endif</td>
        <td data-label="Result" class="p-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $log->outcome === 'completed' ? 'bg-green-100 text-green-800' : ($log->outcome === 'error' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($log->outcome) }}</span><span class="mt-2 block text-xs text-gray-500">HTTP {{ $log->status_code }}</span></td>
    </tr>
@empty
    <tr><td colspan="5" class="p-10 text-center text-gray-500">No login attempts or system errors found in the last 30 days.</td></tr>
@endforelse
