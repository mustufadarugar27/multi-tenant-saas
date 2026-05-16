<?php


namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\Project;
use App\Models\ProjectHistory;
use App\Support\EnumConfig;

class ProjectObserver
{
    private const TRACKABLE = [
        'name', 'description', 'start_date', 'end_date', 'budget',
    ];

    public function updating(Project $project): void
    {
        $userId = auth()->id();

        $this->recordFieldChanges($project, $userId);
        $this->handleStatusChange($project, $userId);
    }

    private function recordFieldChanges(Project $project, ?string $userId): void
    {
        foreach (self::TRACKABLE as $field) {
            if (! $project->isDirty($field)) {
                continue;
            }

            $old = (string) ($project->getRawOriginal($field) ?? '');
            $new = (string) ($project->getAttribute($field) ?? '');

            if ($old === $new) {
                continue;
            }

            ProjectHistory::create([
                'project_id' => $project->id,
                'user_id'    => $userId,
                'event'      => 'field_changed',
                'field'      => $field,
                'old_value'  => $old ?: null,
                'new_value'  => $new ?: null,
            ]);
        }
    }

    private function handleStatusChange(Project $project, ?string $userId): void
    {
        if (! $project->isDirty('status')) {
            return;
        }

        $from = $project->getRawOriginal('status');
        $to   = $project->status;

        $fromLabel = EnumConfig::label('project_status', $from);
        $toLabel   = EnumConfig::label('project_status', $to);

        ProjectHistory::create([
            'project_id' => $project->id,
            'user_id'    => $userId,
            'event'      => 'status_changed',
            'field'      => 'status',
            'old_value'  => $fromLabel,
            'new_value'  => $toLabel,
        ]);

        ActivityLog::create([
            'user_id'      => $userId,
            'event'        => 'project.status_changed',
            'description'  => "Project \"{$project->name}\" status changed from {$fromLabel} to {$toLabel}",
            'subject_type' => Project::class,
            'subject_id'   => $project->id,
            'properties'   => ['from' => $from, 'to' => $to],
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }
}
