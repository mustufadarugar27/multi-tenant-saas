<?php


namespace App\Domain\Auth\Actions;

use App\Domain\Auth\DTOs\AssignRoleDTO;
use App\Domain\Auth\Exceptions\PrivilegeEscalationException;
use App\Models\User;
use App\Support\EnumConfig;

class AssignRoleAction
{
    /**
     * @throws PrivilegeEscalationException
     */
    public function execute(AssignRoleDTO $dto, User $actor, User $target): void
    {
        $targetRole = $dto->role;

        if (EnumConfig::userRoleOutranks($actor->role, $targetRole) === false && $actor->role !== $targetRole) {
            throw new PrivilegeEscalationException();
        }

        $target->syncRoles([$targetRole]);
        $target->update(['role' => $targetRole]);
    }
}
