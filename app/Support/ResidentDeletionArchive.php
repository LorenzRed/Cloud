<?php

namespace App\Support;

use App\Models\ResidentDeletionLog;
use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResidentDeletionArchive
{
    public static function capture(ResidentRequest $resident, User $actor, string $action, array $payload, callable $remove): mixed
    {
        return DB::transaction(function () use ($resident, $actor, $action, $payload, $remove) {
            ResidentRequest::query()->lockForUpdate()->findOrFail($resident->id);
            $resident->refresh();
            $before = self::snapshot($resident);
            $memberName = match ($action) {
                'remove_child' => $resident->household_children[$payload['member_index'] ?? -1]['full_name'] ?? 'Child',
                'remove_senior' => $resident->otherFamilyMembers()[$payload['member_index'] ?? -1]['full_name'] ?? 'Family member',
                'remove_spouse' => $resident->spouse_full_name ?? 'Spouse',
                default => $resident->full_name,
            };
            $result = $remove();
            $after = self::snapshot($resident);
            $changes = [];
            foreach ($before as $table => $rows) {
                foreach ($rows as $id => $row) {
                    foreach ($row as $field => $value) {
                        if (in_array($field, ['updated_at', 'remember_token', 'password'])) {
                            continue;
                        }
                        $new = $after[$table][$id][$field] ?? null;
                        if ($value !== $new) {
                            $changes[] = compact('table', 'id', 'field') + ['before' => $value, 'after' => $new];
                        }
                    }
                }
            }
            if ($changes !== []) {
                ResidentDeletionLog::create([
                    'resident_request_id' => $resident->id,
                    'resident_name' => $resident->full_name,
                    'member_name' => $memberName,
                    'action' => $action,
                    'deleted_by' => $actor->id,
                    'deleted_by_name' => $actor->full_name ?: $actor->name,
                    'deleted_at' => now(),
                    'changes' => $changes,
                ]);
            }
            return $result;
        });
    }

    private static function snapshot(ResidentRequest $resident): array
    {
        return [
            'resident_requests' => DB::table('resident_requests')->where('id', $resident->id)->get()->keyBy('id')->map(fn ($row) => (array) $row)->all(),
            'users' => DB::table('users')->where('id', $resident->user_id)->get()->keyBy('id')->map(fn ($row) => (array) $row)->all(),
            'household_monitoring_cases' => DB::table('household_monitoring_cases')->where('resident_request_id', $resident->id)->get()->keyBy('id')->map(fn ($row) => (array) $row)->all(),
        ];
    }

    public static function restore(ResidentDeletionLog $log, User $actor): void
    {
        abort_unless($actor->role === User::ROLE_LGU, 403);
        DB::transaction(function () use ($log, $actor) {
            $log = ResidentDeletionLog::query()->lockForUpdate()->findOrFail($log->id);
            if ($log->restored_at) {
                throw ValidationException::withMessages(['restore' => 'This record has already been restored.']);
            }
            foreach ($log->changes as $change) {
                $row = DB::table($change['table'])->where('id', $change['id'])->lockForUpdate()->first();
                if (! $row || $row->{$change['field']} !== $change['after']) {
                    throw ValidationException::withMessages(['restore' => 'The household has changed since this deletion. Restore newer deletions first or review the current record before restoring.']);
                }
            }
            foreach ($log->changes as $change) {
                DB::table($change['table'])->where('id', $change['id'])->update([$change['field'] => $change['before']]);
            }
            $log->update(['restored_at' => now(), 'restored_by' => $actor->id, 'restored_by_name' => $actor->full_name ?: $actor->name]);
        });
    }
}
