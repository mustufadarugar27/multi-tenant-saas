<?php


return [

    'billing_cycle' => [
        'monthly' => ['label' => 'Monthly', 'discount_percent' => 0],
        'yearly'  => ['label' => 'Yearly',  'discount_percent' => 20],
    ],

    'feature_flag' => [
        'api_access'         => ['label' => 'API Access'],
        'custom_roles'       => ['label' => 'Custom Roles'],
        'audit_logs'         => ['label' => 'Audit Logs'],
        'priority_support'   => ['label' => 'Priority Support'],
        'sso'                => ['label' => 'Single Sign-On'],
        'white_label'        => ['label' => 'White Label'],
        'advanced_reporting' => ['label' => 'Advanced Reporting'],
    ],

    'pending_registration_status' => [
        'pending'   => ['label' => 'Pending'],
        'completed' => ['label' => 'Completed'],
        'expired'   => ['label' => 'Expired'],
        'failed'    => ['label' => 'Failed'],
    ],

    'plan_slug' => [
        'starter'      => ['label' => 'Starter'],
        'professional' => ['label' => 'Professional'],
        'enterprise'   => ['label' => 'Enterprise'],
    ],

    'project_status' => [
        'draft'     => ['label' => 'Draft',     'terminal' => false, 'badge_class' => 'bg-gray-100 text-gray-700 ring-gray-300'],
        'active'    => ['label' => 'Active',    'terminal' => false, 'badge_class' => 'bg-green-50 text-green-700 ring-green-200'],
        'on_hold'   => ['label' => 'On Hold',   'terminal' => false, 'badge_class' => 'bg-yellow-50 text-yellow-700 ring-yellow-200'],
        'completed' => ['label' => 'Completed', 'terminal' => true,  'badge_class' => 'bg-blue-50 text-blue-700 ring-blue-200'],
        'cancelled' => ['label' => 'Cancelled', 'terminal' => true,  'badge_class' => 'bg-red-50 text-red-600 ring-red-200'],
    ],

    'subscription_status' => [
        'active'     => ['label' => 'Active',     'badge_color' => 'green',  'access_allowed' => true],
        'trialing'   => ['label' => 'Trialing',   'badge_color' => 'blue',   'access_allowed' => true],
        'past_due'   => ['label' => 'Past Due',   'badge_color' => 'yellow', 'access_allowed' => true],
        'cancelled'  => ['label' => 'Cancelled',  'badge_color' => 'gray',   'access_allowed' => false],
        'expired'    => ['label' => 'Expired',    'badge_color' => 'red',    'access_allowed' => false],
        'incomplete' => ['label' => 'Incomplete', 'badge_color' => 'orange', 'access_allowed' => false],
    ],

    'task_priority' => [
        'low'      => ['label' => 'Low',      'level' => 1, 'urgent' => false, 'badge_class' => 'bg-gray-100 text-gray-600 ring-gray-200',     'dot_class' => 'bg-gray-400'],
        'medium'   => ['label' => 'Medium',   'level' => 2, 'urgent' => false, 'badge_class' => 'bg-yellow-50 text-yellow-700 ring-yellow-200', 'dot_class' => 'bg-yellow-500'],
        'high'     => ['label' => 'High',     'level' => 3, 'urgent' => true,  'badge_class' => 'bg-orange-50 text-orange-700 ring-orange-200', 'dot_class' => 'bg-orange-500'],
        'critical' => ['label' => 'Critical', 'level' => 4, 'urgent' => true,  'badge_class' => 'bg-red-50 text-red-700 ring-red-200',          'dot_class' => 'bg-red-600'],
    ],

    'task_status' => [
        'todo'        => ['label' => 'To Do',       'terminal' => false, 'active' => false, 'badge_class' => 'bg-gray-100 text-gray-700 ring-gray-300',     'transitions' => ['in_progress', 'cancelled']],
        'in_progress' => ['label' => 'In Progress', 'terminal' => false, 'active' => true,  'badge_class' => 'bg-blue-50 text-blue-700 ring-blue-200',      'transitions' => ['in_review', 'blocked', 'done', 'cancelled']],
        'in_review'   => ['label' => 'In Review',   'terminal' => false, 'active' => true,  'badge_class' => 'bg-purple-50 text-purple-700 ring-purple-200', 'transitions' => ['in_progress', 'done', 'cancelled']],
        'blocked'     => ['label' => 'Blocked',     'terminal' => false, 'active' => false, 'badge_class' => 'bg-red-50 text-red-700 ring-red-200',          'transitions' => ['in_progress', 'cancelled']],
        'done'        => ['label' => 'Done',        'terminal' => true,  'active' => false, 'badge_class' => 'bg-green-50 text-green-700 ring-green-200',    'transitions' => []],
        'cancelled'   => ['label' => 'Cancelled',   'terminal' => true,  'active' => false, 'badge_class' => 'bg-gray-100 text-gray-500 ring-gray-200',      'transitions' => []],
    ],

    'tenant_status' => [
        'active'    => ['label' => 'Active',    'is_active' => true],
        'trial'     => ['label' => 'Trial',     'is_active' => true],
        'suspended' => ['label' => 'Suspended', 'is_active' => false],
        'cancelled' => ['label' => 'Cancelled', 'is_active' => false],
    ],

    'user_role' => [
        'super_admin'   => ['label' => 'Super Admin',   'privilege' => 4, 'manage_tenant' => true,  'manage_projects' => true,  'manage_users' => true,  'create_users' => true,  'assignable' => ['company_admin', 'manager', 'employee']],
        'company_admin' => ['label' => 'Company Admin', 'privilege' => 3, 'manage_tenant' => true,  'manage_projects' => true,  'manage_users' => true,  'create_users' => true,  'assignable' => ['manager', 'employee']],
        'manager'       => ['label' => 'Manager',       'privilege' => 2, 'manage_tenant' => false, 'manage_projects' => true,  'manage_users' => false, 'create_users' => true,  'assignable' => ['employee']],
        'employee'      => ['label' => 'Employee',      'privilege' => 1, 'manage_tenant' => false, 'manage_projects' => false, 'manage_users' => false, 'create_users' => false, 'assignable' => []],
    ],

];
