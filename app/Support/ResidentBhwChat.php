<?php

namespace App\Support;

use App\Models\ResidentBhwMessage;
use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ResidentBhwChat
{
    public const MESSAGE_EDIT_MINUTES = 15;

    /** @var list<string> */
    public const REACTION_EMOJIS = ['❤️', '😂', '😮', '😢', '😡', '👍'];
    public static function residentLocation(User $resident): array
    {
        $resident->loadMissing(['purok', 'residentRequest.purok']);

        $purokId = $resident->purok_id ?: $resident->residentRequest?->purok_id;
        $barangay = trim((string) ($resident->barangay ?? ''));
        if ($barangay === '') {
            $barangay = trim((string) ($resident->residentRequest?->barangay ?? ''));
        }

        return [
            'purok_id' => $purokId ? (int) $purokId : null,
            'barangay' => $barangay !== '' ? $barangay : null,
        ];
    }

    public static function assignedBhwForResident(User $resident): ?User
    {
        if ($resident->role !== 'users') {
            return null;
        }

        ['purok_id' => $purokId, 'barangay' => $barangay] = self::residentLocation($resident);

        if (! $purokId || ! $barangay) {
            return null;
        }

        return User::query()
            ->where('role', 'BHW')
            ->whereRaw('LOWER(TRIM(barangay)) = ?', [mb_strtolower($barangay)])
            ->whereHas('assignedPuroks', fn ($query) => $query->where('puroks.id', $purokId))
            ->orderBy('name')
            ->first();
    }

    public static function canChat(User $resident, User $bhw): bool
    {
        if ($resident->role !== 'users' || $bhw->role !== 'BHW') {
            return false;
        }

        $assigned = self::assignedBhwForResident($resident);

        return $assigned !== null && (int) $assigned->id === (int) $bhw->id;
    }

    public static function canBhwAccessResident(User $bhw, User $resident): bool
    {
        if ($bhw->role !== 'BHW' || $resident->role !== 'users') {
            return false;
        }

        $pendingRequest = ResidentRequest::query()
            ->where('user_id', $resident->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingRequest && $pendingRequest->isAccessibleByBhw($bhw)) {
            return true;
        }

        $acceptedRequest = ResidentRequest::query()
            ->where('user_id', $resident->id)
            ->where('status', 'accepted')
            ->first();

        if ($acceptedRequest) {
            return $acceptedRequest->isAccessibleByBhw($bhw);
        }

        return self::canChat($resident, $bhw);
    }

    public static function threadQuery(User $resident, User $bhw, int $viewerId): Builder
    {
        return ResidentBhwMessage::query()
            ->where('resident_user_id', $resident->id)
            ->where('bhw_user_id', $bhw->id)
            ->where(function (Builder $query) use ($viewerId) {
                $query->whereNull('hidden_for_sender_at')
                    ->orWhere('sender_id', '!=', $viewerId);
            })
            ->orderBy('created_at');
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public static function threadMessages(User $resident, User $bhw, int $viewerId): Collection
    {
        return self::threadQuery($resident, $bhw, $viewerId)
            ->with('reactions')
            ->get()
            ->map(fn (ResidentBhwMessage $message) => self::formatMessage($message, $viewerId))
            ->values();
    }

    /**
     * @return array{summary: list<array{emoji: string, count: int, reacted_by_me: bool}>, my_emoji: ?string}
     */
    public static function formatReactions(ResidentBhwMessage $message, int $viewerId): array
    {
        if ($message->unsent_at !== null) {
            return ['summary' => [], 'my_emoji' => null];
        }

        $grouped = $message->reactions->groupBy('emoji');
        $summary = [];

        foreach ($grouped as $emoji => $reactions) {
            $summary[] = [
                'emoji' => (string) $emoji,
                'count' => $reactions->count(),
                'reacted_by_me' => $reactions->contains(fn ($reaction) => (int) $reaction->user_id === $viewerId),
            ];
        }

        usort($summary, fn (array $a, array $b) => $b['count'] <=> $a['count']);

        $myEmoji = $message->reactions->firstWhere('user_id', $viewerId)?->emoji;

        return [
            'summary' => $summary,
            'my_emoji' => $myEmoji,
        ];
    }

    public static function toggleReaction(ResidentBhwMessage $message, User $viewer, string $emoji): ResidentBhwMessage
    {
        if ($message->unsent_at !== null) {
            abort(422, 'You cannot react to an unsent message.');
        }

        if (! self::canAccessMessage($viewer, $message)) {
            abort(403, 'Unable to react to this message.');
        }

        if (! in_array($emoji, self::REACTION_EMOJIS, true)) {
            abort(422, 'Invalid reaction.');
        }

        $existing = $message->reactions()->where('user_id', $viewer->id)->first();

        if ($existing && $existing->emoji === $emoji) {
            $existing->delete();
        } elseif ($existing) {
            $existing->update(['emoji' => $emoji]);
        } else {
            $message->reactions()->create([
                'user_id' => $viewer->id,
                'emoji' => $emoji,
            ]);
        }

        return $message->fresh(['reactions']);
    }

    public static function formatMessage(ResidentBhwMessage $message, int $viewerId): array
    {
        $isUnsent = $message->unsent_at !== null;
        $isImage = ! $isUnsent && $message->attachment_path
            ? (bool) preg_match('/\.(jpe?g|png|gif|webp)$/i', $message->attachment_path)
            : false;

        return [
            'id' => $message->id,
            'message' => $isUnsent ? '' : $message->message,
            'body' => $isUnsent ? '' : $message->message,
            'sent_at' => $message->created_at?->format('M d, Y h:i A'),
            'edited_at' => $message->edited_at?->format('M d, Y h:i A'),
            'unsent' => $isUnsent,
            'unsent_at' => $message->unsent_at?->format('M d, Y h:i A'),
            'attachment_url' => ! $isUnsent && $message->attachment_path
                ? route('resident-bhw.message.attachment', $message)
                : null,
            'attachment_name' => ! $isUnsent && $message->attachment_path ? basename($message->attachment_path) : null,
            'is_image' => $isImage,
            'is_mine' => (int) $message->sender_id === $viewerId,
            'sender_role' => (int) $message->sender_id === (int) $message->bhw_user_id ? 'bhw' : 'resident',
            'can_edit' => ! $isUnsent
                && (int) $message->sender_id === $viewerId
                && self::messageWithinEditWindow($message)
                && ! $isImage,
            'created_at_ms' => $message->created_at ? $message->created_at->getTimestamp() * 1000 : null,
            'reactions' => self::formatReactions($message, $viewerId),
        ];
    }

    public static function messageWithinEditWindow(ResidentBhwMessage $message): bool
    {
        if (! $message->created_at) {
            return false;
        }

        return $message->created_at->greaterThan(now()->subMinutes(self::MESSAGE_EDIT_MINUTES));
    }

    public static function canEditMessage(User $viewer, ResidentBhwMessage $message): bool
    {
        if ($message->attachment_path
            && preg_match('/\.(jpe?g|png|gif|webp)$/i', $message->attachment_path)) {
            return false;
        }

        return self::canModifyMessage($viewer, $message)
            && self::messageWithinEditWindow($message);
    }

    public static function canAccessMessage(User $viewer, ResidentBhwMessage $message): bool
    {
        if ((int) $viewer->id === (int) $message->resident_user_id && $viewer->role === 'users') {
            return self::canChat($viewer, $message->bhw);
        }

        if ((int) $viewer->id === (int) $message->bhw_user_id && $viewer->role === 'BHW') {
            return self::canBhwAccessResident($viewer, $message->resident);
        }

        return false;
    }

    public static function canModifyMessage(User $viewer, ResidentBhwMessage $message): bool
    {
        return self::canAccessMessage($viewer, $message)
            && (int) $message->sender_id === (int) $viewer->id
            && $message->unsent_at === null;
    }

    public static function storeMessage(Request $request, User $resident, User $bhw, User $sender): ResidentBhwMessage
    {
        $data = $request->validate([
            'message' => SafeInput::chatMessageRules(false),
            'attachment' => ['nullable', 'file', 'max:5120', 'mimes:jpg,jpeg,png,gif,webp,pdf'],
        ]);

        $messageText = SafeInput::chatMessage($data['message'] ?? null);

        if ($messageText === '' && ! $request->hasFile('attachment')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'message' => 'Enter a message or attach a file.',
            ]);
        }

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('bhw-resident-chat', 'public');
        }

        return ResidentBhwMessage::query()->create([
            'resident_user_id' => $resident->id,
            'bhw_user_id' => $bhw->id,
            'sender_id' => $sender->id,
            'message' => $messageText !== '' ? $messageText : 'Sent a file attachment.',
            'attachment_path' => $attachmentPath,
        ]);
    }

    public static function updateMessage(ResidentBhwMessage $message, string $body): ResidentBhwMessage
    {
        if ($message->attachment_path
            && preg_match('/\.(jpe?g|png|gif|webp)$/i', $message->attachment_path)) {
            abort(422, 'Photo messages cannot be edited.');
        }

        if (! self::messageWithinEditWindow($message)) {
            abort(422, 'You can only edit messages within '.self::MESSAGE_EDIT_MINUTES.' minutes of sending.');
        }

        $message->update([
            'message' => SafeInput::chatMessage($body),
            'edited_at' => now(),
        ]);

        return $message->fresh();
    }

    public static function unsendMessage(ResidentBhwMessage $message, string $scope, int $viewerId): array
    {
        if ($message->unsent_at !== null) {
            abort(422, 'This message was already unsent.');
        }

        if ($scope === 'you') {
            $message->update(['hidden_for_sender_at' => now()]);

            return ['id' => $message->id, 'scope' => 'you'];
        }

        if ($message->attachment_path && Storage::disk('public')->exists($message->attachment_path)) {
            Storage::disk('public')->delete($message->attachment_path);
        }

        $message->reactions()->delete();

        $message->update([
            'message' => 'This message was unsent.',
            'attachment_path' => null,
            'unsent_at' => now(),
        ]);

        return [
            'id' => $message->id,
            'scope' => 'everyone',
            'unsent_at' => $message->unsent_at?->format('M d, Y h:i A'),
        ];
    }

    public static function markReadForBhw(User $bhw, User $resident): void
    {
        ResidentBhwMessage::query()
            ->where('resident_user_id', $resident->id)
            ->where('bhw_user_id', $bhw->id)
            ->where('sender_id', $resident->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function markReadForResident(User $resident, User $bhw): void
    {
        ResidentBhwMessage::query()
            ->where('resident_user_id', $resident->id)
            ->where('bhw_user_id', $bhw->id)
            ->where('sender_id', $bhw->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public static function unreadCountForResident(User $resident, User $bhw): int
    {
        return ResidentBhwMessage::query()
            ->where('resident_user_id', $resident->id)
            ->where('bhw_user_id', $bhw->id)
            ->where('sender_id', $bhw->id)
            ->whereNull('read_at')
            ->whereNull('unsent_at')
            ->count();
    }

    /**
     * @return list<int>
     */
    public static function unreadMessageIdsForResident(User $resident, User $bhw): array
    {
        return ResidentBhwMessage::query()
            ->where('resident_user_id', $resident->id)
            ->where('bhw_user_id', $bhw->id)
            ->where('sender_id', $bhw->id)
            ->whereNull('read_at')
            ->whereNull('unsent_at')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return list<int>
     */
    public static function unreadMessageIdsForBhw(User $bhw, User $resident): array
    {
        return ResidentBhwMessage::query()
            ->where('resident_user_id', $resident->id)
            ->where('bhw_user_id', $bhw->id)
            ->where('sender_id', $resident->id)
            ->whereNull('read_at')
            ->whereNull('unsent_at')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    public static function unreadCountForBhw(User $bhw, ?User $resident = null): int
    {
        $query = ResidentBhwMessage::query()
            ->where('bhw_user_id', $bhw->id)
            ->whereColumn('sender_id', 'resident_user_id')
            ->whereNull('read_at')
            ->whereNull('unsent_at');

        if ($resident) {
            $query->where('resident_user_id', $resident->id);
        }

        return $query->count();
    }

    public static function unreadCountForBhwByRequestStatus(User $bhw, string $status): int
    {
        $residentUserIds = ResidentRequest::query()
            ->forBhw($bhw)
            ->where('status', $status)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        if ($residentUserIds === []) {
            return 0;
        }

        return ResidentBhwMessage::query()
            ->where('bhw_user_id', $bhw->id)
            ->whereIn('resident_user_id', $residentUserIds)
            ->whereColumn('sender_id', 'resident_user_id')
            ->whereNull('read_at')
            ->whereNull('unsent_at')
            ->count();
    }

    /**
     * @param  list<int>  $residentUserIds
     * @return array<int, int>
     */
    public static function unreadCountsByResidentForBhw(User $bhw, array $residentUserIds): array
    {
        if ($residentUserIds === []) {
            return [];
        }

        return ResidentBhwMessage::query()
            ->where('bhw_user_id', $bhw->id)
            ->whereIn('resident_user_id', $residentUserIds)
            ->whereColumn('sender_id', 'resident_user_id')
            ->whereNull('read_at')
            ->whereNull('unsent_at')
            ->groupBy('resident_user_id')
            ->selectRaw('resident_user_id, COUNT(*) as unread_count')
            ->pluck('unread_count', 'resident_user_id')
            ->map(fn ($count) => (int) $count)
            ->all();
    }
}
