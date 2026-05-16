<?php


namespace App\Events;

use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TaskComment $comment,
        public readonly User $actor,
    ) {}
}
