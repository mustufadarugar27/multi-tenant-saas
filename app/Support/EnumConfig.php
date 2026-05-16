<?php


namespace App\Support;

final class EnumConfig
{
    public static function label(string $group, string $value): string
    {
        return LangTranslations::attr($group, $value, 'label')
            ?? ucfirst(str_replace('_', ' ', $value));
    }

    public static function values(string $group): array
    {
        return LangTranslations::keys($group);
    }

    /** @return EnumOption[] */
    public static function options(string $group): array
    {
        return LangTranslations::options($group);
    }

    public static function isTaskStatusTerminal(string $status): bool
    {
        return (bool) LangTranslations::attr('task_status', $status, 'terminal', false);
    }

    public static function isTaskStatusActive(string $status): bool
    {
        return (bool) LangTranslations::attr('task_status', $status, 'active', false);
    }

    public static function taskStatusCanTransitionTo(string $from, string $to): bool
    {
        return in_array($to, LangTranslations::attr('task_status', $from, 'transitions', []), true);
    }

    public static function isProjectStatusTerminal(string $status): bool
    {
        return (bool) LangTranslations::attr('project_status', $status, 'terminal', false);
    }

    public static function isSubscriptionAccessAllowed(string $status): bool
    {
        return (bool) LangTranslations::attr('subscription_status', $status, 'access_allowed', false);
    }

    public static function subscriptionBadgeColor(string $status): string
    {
        return LangTranslations::attr('subscription_status', $status, 'badge_color', 'gray');
    }

    public static function isTenantStatusActive(string $status): bool
    {
        return (bool) LangTranslations::attr('tenant_status', $status, 'is_active', false);
    }

    public static function userRolePrivilege(string $role): int
    {
        return (int) LangTranslations::attr('user_role', $role, 'privilege', 0);
    }

    public static function userRoleOutranks(string $role, string $other): bool
    {
        return self::userRolePrivilege($role) > self::userRolePrivilege($other);
    }

    public static function userRoleCanManageTenant(string $role): bool
    {
        return (bool) LangTranslations::attr('user_role', $role, 'manage_tenant', false);
    }

    public static function userRoleCanManageProjects(string $role): bool
    {
        return (bool) LangTranslations::attr('user_role', $role, 'manage_projects', false);
    }

    public static function userRoleCanManageUsers(string $role): bool
    {
        return (bool) LangTranslations::attr('user_role', $role, 'manage_users', false);
    }

    public static function userRoleCanCreateUsers(string $role): bool
    {
        return (bool) LangTranslations::attr('user_role', $role, 'create_users', false);
    }

    public static function userRoleAssignableRoles(string $role): array
    {
        return LangTranslations::attr('user_role', $role, 'assignable', []);
    }

    public static function taskPriorityLevel(string $priority): int
    {
        return (int) LangTranslations::attr('task_priority', $priority, 'level', 0);
    }

    public static function isTaskPriorityUrgent(string $priority): bool
    {
        return (bool) LangTranslations::attr('task_priority', $priority, 'urgent', false);
    }

    public static function billingCycleDiscountPercent(string $cycle): int
    {
        return (int) LangTranslations::attr('billing_cycle', $cycle, 'discount_percent', 0);
    }
}
