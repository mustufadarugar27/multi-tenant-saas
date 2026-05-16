<?php


namespace App\Observers;

use App\Models\Task;
use App\Models\TaskHistory;
use App\Models\User;
use App\Support\EnumConfig;

class TaskObserver
{
    private const TRACKABLE = [
        'title', 'description', 'assigned_to', 'priority',
        'due_date', 'estimated_hours', 'actual_hours',
    ];

    public function updating(Task $task): void
    {
        $userId = auth()->id();

        $this->recordFieldChanges($task, $userId);
        $this->handleStatusChange($task, $userId);
    }

    private function recordFieldChanges(Task $task, ?string $userId): void
    {
        foreach (self::TRACKABLE as $field) {
            if (! $task->isDirty($field)) {
                continue;
            }

            $old = (string) ($task->getRawOriginal($field) ?? '');
            $new = (string) ($task->getAttribute($field) ?? '');

            if ($old === $new) {
                continue;
            }

            if ($field === 'assigned_to') {
                $oldName = $old ? (User::find($old)?->name ?? $old) : null;
                $newName = $new ? (User::find($new)?->name ?? $new) : null;

                TaskHistory::create([
                    'task_id'   => $task->id,
                    'user_id'   => $userId,
                    'event'     => 'field_changed',
                    'field'     => $field,
                    'old_value' => $oldName,
                    'new_value' => $newName,
                ]);

                continue;
            }

            TaskHistory::create([
                'task_id'   => $task->id,
                'user_id'   => $userId,
                'event'     => 'field_changed',
                'field'     => $field,
                'old_value' => $old ?: null,
                'new_value' => $new ?: null,
            ]);
        }
    }

    private function handleStatusChange(Task $task, ?string $userId): void
    {
        if (! $task->isDirty('status')) {
            return;
        }

        $from = $task->getRawOriginal('status');
        $to   = $task->status;

        if ($to === 'done' && $task->completed_at === null) {
            $task->completed_at = now();
        }

        if ($from === 'done' && ! EnumConfig::isTaskStatusTerminal($to)) {
            $task->completed_at = null;
        }

        $fromLabel = EnumConfig::label('task_status', $from);
        $toLabel   = EnumConfig::label('task_status', $to);

        TaskHistory::create([
            'task_id'   => $task->id,
            'user_id'   => $userId,
            'event'     => 'status_changed',
            'field'     => 'status',
            'old_value' => $fromLabel,
            'new_value' => $toLabel,
        ]);
    }
}
