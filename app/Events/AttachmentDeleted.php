<?php


namespace App\Events;

use App\Models\TaskAttachment;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly TaskAttachment $attachment,
        public readonly User $actor,
    ) {}
}
