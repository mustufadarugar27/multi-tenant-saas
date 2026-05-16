<?php


namespace App\Events\Broadcast;

use App\Models\Project;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\SerializesModels;

class ProjectUpdatedBroadcast implements ShouldBroadcast, ShouldBeUnique
{
    use InteractsWithSockets, SerializesModels;

    public readonly string $tenantId;

    public function __construct(
        public readonly Project $project,
        public readonly User $actor,
        public readonly array $changes,
        string $tenantId,
    ) {
        $this->tenantId = $tenantId;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("tenant.{$this->tenantId}.project.{$this->project->id}"),
            new PrivateChannel("tenant.{$this->tenantId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'project.updated';
    }

    public function broadcastWith(): array
    {
        return [
            'project' => [
                'id' => $this->project->id,
                'name' => $this->project->name,
                'status' => $this->project->status,
            ],
            'changes' => $this->changes,
            'actor' => [
                'id' => $this->actor->id,
                'name' => $this->actor->name,
            ],
        ];
    }

    public function broadcastQueue(): string
    {
        return 'broadcasts';
    }

    public function uniqueId(): string
    {
        return "project-updated:{$this->tenantId}:{$this->project->id}";
    }

    public int $uniqueFor = 5;
}
