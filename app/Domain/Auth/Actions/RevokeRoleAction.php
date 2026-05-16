<?php


namespace App\Domain\Auth\Actions;

use App\Domain\Auth\Exceptions\PrivilegeEscalationException;
use App\Models\User;
use App\Support\EnumConfig;

class RevokeRoleAction
{
    /**
     * @throws PrivilegeEscalationException
     */
    public function execute(string $role, User $actor, User $target): void
    {
        if (! EnumConfig::userRoleOutranks($actor->role, $role)) {
            throw new PrivilegeEscalationException('You cannot revoke a role equal to or higher than your own.');
        }

        $target->removeRole($role);
    }
}
