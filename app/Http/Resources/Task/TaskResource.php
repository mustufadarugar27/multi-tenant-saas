<?php


namespace App\Http\Resources\Task;

use App\Support\EnumConfig;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'priority_label' => EnumConfig::label('task_priority', $this->priority),
            'status' => $this->status,
            'status_label' => EnumConfig::label('task_status', $this->status),
            'due_date' => $this->due_date?->toDateString(),
            'estimated_hours' => $this->estimated_hours !== null ? (float) $this->estimated_hours : null,
            'actual_hours' => $this->actual_hours !== null ? (float) $this->actual_hours : null,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'is_overdue' => $this->isOverdue(),
            'is_due_soon' => $this->isDueSoon(),
            'project' => $this->whenLoaded('project', fn () => [
                'id' => $this->project?->id,
                'name' => $this->project?->name,
            ]),
            'assignee' => $this->whenLoaded('assignee', fn () => $this->assigned_to ? [
                'id' => $this->assignee?->id,
                'name' => $this->assignee?->name,
            ] : null),
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'comments_count' => $this->whenCounted('comments'),
            'attachments_count'=> $this->whenCounted('attachments'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
