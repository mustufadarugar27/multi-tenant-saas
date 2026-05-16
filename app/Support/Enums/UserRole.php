<?php


namespace App\Support\Enums;

use App\Support\EnumConfig;

enum UserRole: string
{
    case SuperAdmin   = 'super_admin';
    case CompanyAdmin = 'company_admin';
    case Manager      = 'manager';
    case Employee     = 'employee';

    public function label(): string
    {
        return EnumConfig::label('user_role', $this->value);
    }

    public function privilege(): int
    {
        return EnumConfig::userRolePrivilege($this->value);
    }

    public function canManageTenant(): bool
    {
        return EnumConfig::userRoleCanManageTenant($this->value);
    }

    public function canManageProjects(): bool
    {
        return EnumConfig::userRoleCanManageProjects($this->value);
    }

    public function canManageUsers(): bool
    {
        return EnumConfig::userRoleCanManageUsers($this->value);
    }

    public function canCreateUsers(): bool
    {
        return EnumConfig::userRoleCanCreateUsers($this->value);
    }

    /** @return string[] */
    public function assignableRoles(): array
    {
        return EnumConfig::userRoleAssignableRoles($this->value);
    }

    public function outranks(self $other): bool
    {
        return $this->privilege() > $other->privilege();
    }
}
