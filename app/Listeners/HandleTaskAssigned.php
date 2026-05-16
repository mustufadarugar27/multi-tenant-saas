<?php


namespace App\Listeners;

use App\Events\TaskAssigned;
use App\Models\ActivityLog;
use App\Notifications\Task\TaskAssignedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HandleTaskAssigned
{
    public function handle(TaskAssigned $event): void
    {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => TaskAssignedNotification::class,
            'notifiable_type' => $event->assignee::class,
            'notifiable_id' => $event->assignee->id,
            'data' => json_encode([
                'type' => 'task_assigned',
                'task_id' => $event->task->id,
                'task_title' => $event->task->title,
                'project_id' => $event->task->project_id,
                'priority' => $event->task->priority,
                'status' => $event->task->status,
                'due_date' => $event->task->due_date?->toDateString(),
                'assigned_by' => [
                    'id' => $event->actor->id,
                    'name' => $event->actor->name,
                ],
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $event->assignee->notify(new TaskAssignedNotification($event->task, $event->actor));

        ActivityLog::create([
            'user_id' => $event->actor->id,
            'event' => 'task.assigned',
            'description' => "Task \"{$event->task->title}\" assigned to {$event->assignee->name}",
            'subject_type' => $event->task::class,
            'subject_id' => $event->task->id,
            'properties' => ['assignee_id' => $event->assignee->id],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
