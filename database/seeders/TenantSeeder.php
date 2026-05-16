<?php


namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use App\Support\Enums\UserRole;
use App\Support\LangTranslations;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds dummy data into the currently-initialized tenant database.
 * Run via: php artisan tenants:seed --class=TenantSeeder
 * Or after manually calling tenancy()->initialize($tenant) in your own command.
 */
final class TenantSeeder extends Seeder
{
    public function run(): void
    {
        LangTranslations::flush();

        $this->call(RoleAndPermissionSeeder::class);

        [$admin, $manager, $employees] = $this->seedUsers();

        $this->seedProjects($admin, $manager, $employees);

        $this->command->newLine();
        $this->command->info('─── Tenant data seeded ───────────────────────────────');
        $this->command->table(
            ['Credential', 'Value'],
            [
                ['Admin email',   'arjun@demo.test'],
                ['Manager email', 'priya@demo.test'],
                ['Password',      'password'],
            ]
        );
    }


    private function seedUsers(): array
    {
        $admin = $this->createUser('Arjun Sharma',   'arjun@demo.test',   UserRole::CompanyAdmin, verified: true);
        $manager = $this->createUser('Priya Verma',  'priya@demo.test',   UserRole::Manager,      verified: true);

        $employees = [
            $this->createUser('Rahul Gupta',   'rahul@demo.test',   UserRole::Employee, verified: true),
            $this->createUser('Sneha Patel',   'sneha@demo.test',   UserRole::Employee, verified: true),
            $this->createUser('Vikram Singh',  'vikram@demo.test',  UserRole::Employee, verified: false),
        ];

        $this->command->line('  Users seeded: 1 admin + 1 manager + 3 employees');

        return [$admin, $manager, $employees];
    }

    private function createUser(string $name, string $email, UserRole $role, bool $verified): User
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name'               => $name,
                'password'           => Hash::make('password'),
                'role'               => $role,
                'is_active'          => true,
                'email_verified_at'  => $verified ? now() : null,
                'password_changed_at' => now(),
            ]
        );

        $user->assignRole($role->value);

        return $user;
    }


    private function seedProjects(User $admin, User $manager, array $employees): void
    {
        $projects = [
            [
                'name'        => 'Bharat E-Commerce Portal',
                'description' => 'Full redesign of the company website with modern UI/UX tailored for Indian consumers.',
                'status'      => 'active',
                'start_date'  => now()->subDays(20),
                'end_date'    => now()->addDays(40),
                'budget'      => '15000.00',
                'created_by'  => $admin->id,
            ],
            [
                'name'        => 'DigiPay Mobile App',
                'description' => 'Build a cross-platform UPI-integrated mobile payment application.',
                'status'      => 'draft',
                'start_date'  => now()->addDays(5),
                'end_date'    => now()->addDays(90),
                'budget'      => '40000.00',
                'created_by'  => $manager->id,
            ],
            [
                'name'        => 'GST Records Migration',
                'description' => 'Migrate all GST billing records from the old ERP to the new system.',
                'status'      => 'completed',
                'start_date'  => now()->subDays(60),
                'end_date'    => now()->subDays(5),
                'budget'      => '8000.00',
                'created_by'  => $admin->id,
            ],
            [
                'name'        => 'Employee Self-Service Portal',
                'description' => 'Self-service HR portal for leave requests, payslips, and PF management.',
                'status'      => 'on_hold',
                'start_date'  => now()->subDays(10),
                'end_date'    => now()->addDays(60),
                'budget'      => '12000.00',
                'created_by'  => $admin->id,
            ],
        ];

        foreach ($projects as $data) {
            $creator = $data['created_by'] === $admin->id ? $admin : $manager;
            $project = Project::create($data);
            $this->seedTasksForProject($project, $creator, $manager, $employees);
        }

        $this->command->line('  Projects + tasks + comments seeded.');
    }


    private function seedTasksForProject(
        Project $project,
        User    $creator,
        User    $manager,
        array   $employees,
    ): void {
        foreach ($this->taskDefinitionsFor($project->status) as $def) {
            $assignee = $def['assignee'] === 'manager'
                ? $manager
                : $employees[array_rand($employees)];

            $task = Task::create([
                'project_id'      => $project->id,
                'title'           => $def['title'],
                'description'     => $def['description'],
                'status'          => $def['status'],
                'priority'        => $def['priority'],
                'assigned_to'     => $assignee->id,
                'created_by'      => $creator->id,
                'due_date'        => $def['due_date'],
                'estimated_hours' => $def['estimated_hours'],
                'actual_hours'    => $def['actual_hours'],
                'completed_at'    => $def['status'] === 'done' ? now() : null,
            ]);

            $this->seedCommentsForTask($task, $creator, $assignee);
        }
    }


    private function seedCommentsForTask(Task $task, User $creator, User $assignee): void
    {
        $root = TaskComment::create([
            'task_id'   => $task->id,
            'user_id'   => $creator->id,
            'parent_id' => null,
            'depth'     => 0,
            'content'   => 'Task created and ready for review. Please check the requirements.',
        ]);

        TaskComment::create([
            'task_id'   => $task->id,
            'user_id'   => $assignee->id,
            'parent_id' => $root->id,
            'depth'     => 1,
            'content'   => 'Acknowledged! I will start working on this shortly.',
        ]);

        TaskComment::create([
            'task_id'   => $task->id,
            'user_id'   => $assignee->id,
            'parent_id' => null,
            'depth'     => 0,
            'content'   => 'Added some notes on the approach. Let me know if you have feedback.',
        ]);
    }


    private function taskDefinitionsFor(string $status): array
    {
        return match ($status) {
            'active' => [
                [
                    'title'           => 'Design homepage wireframes in Hindi & English',
                    'description'     => 'Create bilingual low-fidelity and hi-fi wireframes for the landing page.',
                    'status'          => 'done',
                    'priority'        => 'high',
                    'assignee'        => 'employee',
                    'due_date'        => now()->subDays(5),
                    'estimated_hours' => '8.00',
                    'actual_hours'    => '9.50',
                ],
                [
                    'title'           => 'Implement regional language switcher',
                    'description'     => 'Build the navigation with support for Hindi, Tamil, and English toggling.',
                    'status'          => 'in_progress',
                    'priority'        => 'high',
                    'assignee'        => 'employee',
                    'due_date'        => now()->addDays(3),
                    'estimated_hours' => '6.00',
                    'actual_hours'    => '3.00',
                ],
                [
                    'title'           => 'Set up CI/CD pipeline',
                    'description'     => 'Configure GitHub Actions for automated deploy to staging server.',
                    'status'          => 'in_review',
                    'priority'        => 'medium',
                    'assignee'        => 'manager',
                    'due_date'        => now()->addDays(2),
                    'estimated_hours' => '4.00',
                    'actual_hours'    => '4.00',
                ],
                [
                    'title'           => 'Write About Us content',
                    'description'     => 'Draft and review the company About page copy in Hindi and English.',
                    'status'          => 'todo',
                    'priority'        => 'low',
                    'assignee'        => 'employee',
                    'due_date'        => now()->addDays(14),
                    'estimated_hours' => '2.00',
                    'actual_hours'    => null,
                ],
                [
                    'title'           => 'Mobile performance audit',
                    'description'     => 'Run Lighthouse audits targeting low-bandwidth Indian networks (2G/3G).',
                    'status'          => 'blocked',
                    'priority'        => 'critical',
                    'assignee'        => 'employee',
                    'due_date'        => now()->addDays(7),
                    'estimated_hours' => '5.00',
                    'actual_hours'    => '1.00',
                ],
            ],

            'draft' => [
                [
                    'title'           => 'Define UPI integration scope',
                    'description'     => 'Document must-have UPI, NEFT, and wallet features for the MVP.',
                    'status'          => 'todo',
                    'priority'        => 'high',
                    'assignee'        => 'manager',
                    'due_date'        => now()->addDays(10),
                    'estimated_hours' => '4.00',
                    'actual_hours'    => null,
                ],
                [
                    'title'           => 'Choose cross-platform framework',
                    'description'     => 'Evaluate React Native vs Flutter for UPI deep-link requirements.',
                    'status'          => 'todo',
                    'priority'        => 'medium',
                    'assignee'        => 'employee',
                    'due_date'        => now()->addDays(12),
                    'estimated_hours' => '3.00',
                    'actual_hours'    => null,
                ],
            ],

            'completed' => [
                [
                    'title'           => 'Export GST invoices from legacy ERP',
                    'description'     => 'Extract all GSTIN, HSN codes, and invoice records as CSV.',
                    'status'          => 'done',
                    'priority'        => 'critical',
                    'assignee'        => 'employee',
                    'due_date'        => now()->subDays(40),
                    'estimated_hours' => '6.00',
                    'actual_hours'    => '7.00',
                ],
                [
                    'title'           => 'Data cleansing and PAN deduplication',
                    'description'     => 'Remove duplicates and normalise PAN, Aadhaar-linked mobile formats.',
                    'status'          => 'done',
                    'priority'        => 'high',
                    'assignee'        => 'employee',
                    'due_date'        => now()->subDays(30),
                    'estimated_hours' => '10.00',
                    'actual_hours'    => '12.00',
                ],
                [
                    'title'           => 'Validate imported GST records',
                    'description'     => 'Spot-check 10% of records against GSTN portal for accuracy.',
                    'status'          => 'done',
                    'priority'        => 'medium',
                    'assignee'        => 'manager',
                    'due_date'        => now()->subDays(10),
                    'estimated_hours' => '4.00',
                    'actual_hours'    => '3.50',
                ],
            ],

            'on_hold' => [
                [
                    'title'           => 'Gather HR and PF requirements',
                    'description'     => 'Interview stakeholders and document leave, PF, and payslip portal needs.',
                    'status'          => 'done',
                    'priority'        => 'high',
                    'assignee'        => 'manager',
                    'due_date'        => now()->subDays(8),
                    'estimated_hours' => '5.00',
                    'actual_hours'    => '5.00',
                ],
                [
                    'title'           => 'Initial database schema design',
                    'description'     => 'ERD and schema for leave requests, payslips, PF contributions, and employees.',
                    'status'          => 'in_progress',
                    'priority'        => 'medium',
                    'assignee'        => 'employee',
                    'due_date'        => now()->addDays(20),
                    'estimated_hours' => '6.00',
                    'actual_hours'    => '2.00',
                ],
            ],

            default => [],
        };
    }
}
