@forelse ($logs as $log)
    <tr class="border-t align-top">
        <td data-label="When" class="p-4 whitespace-nowrap text-gray-500">{{ $log->created_at->timezone('Asia/Manila')->format('M d, Y h:i:s A') }}<span class="block text-xs">#{{ $log->id }}</span></td>
        <td data-label="Who" class="p-4 font-medium">{{ $log->actor_name }}<span class="block text-xs font-normal text-gray-500">{{ $log->actor_role ?? 'Guest' }}{{ $log->user_id ? ' · User #'.$log->user_id : '' }}</span></td>
        <td data-label="Activity" class="p-4">{{ $log->action }}<span class="block text-xs text-gray-500">{{ $log->method }}</span></td>
        <td data-label="Record" class="p-4 text-xs">@forelse ($log->targets ?? [] as $type => $id)<span class="block">{{ ucwords(str_replace('_', ' ', $type)) }} #{{ $id }}</span>@empty<span class="text-gray-400">—</span>@endforelse</td>
        <td data-label="Result" class="p-4"><span class="rounded-full px-3 py-1 text-xs font-semibold {{ $log->outcome === 'completed' ? 'bg-green-100 text-green-800' : ($log->outcome === 'error' ? 'bg-red-100 text-red-800' : 'bg-amber-100 text-amber-800') }}">{{ ucfirst($log->outcome) }}</span><span class="mt-2 block text-xs text-gray-500">HTTP {{ $log->status_code }}</span></td>
    </tr>
@empty
    <tr><td colspan="5" class="p-10 text-center text-gray-500">No activity found. New system activity will appear here.</td></tr>
@endforelse
