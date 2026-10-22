<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionSeeder extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-06
     * Task: Standardise Initiator data flow, add Student role, and refine permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-06...');

        // 1. Ensure 'Student' role exists
        DB::table('role')->updateOrInsert(
            ['rolename' => 'Student'],
            ['rolename' => 'Student', 'ispredefined' => true, 'created_at' => now()]
        );
        $this->command->info('- Student role ensured.');

        // 2. Ensure modular permissions exist
        $permissions = [
            ['permkey' => 10201, 'permname' => 'Lead Management'],
            ['permkey' => 10401, 'permname' => 'Read Events'],
            ['permkey' => 10402, 'permname' => 'Write Events'],
            ['permkey' => 10403, 'permname' => 'Manage Events'],
            ['permkey' => 10301, 'permname' => 'Manage Initiator Financials'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Lead Management, Events, Financials).');

        // 3. Update Admin Role permissions (Grant Financial Management)
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $permission = DB::table('permission')->where('permkey', 10301)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                    ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                );
            }
            $this->command->info('- Admin role granted Financial Management permission.');
        }

        // 4. Update Initiator Role permissions
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [
                10031, // Read Schools (to see assigned schools)
                10041, 10042, 10043, // Students: Read, Write, Manage
                10051, // Dashboard: Read
                10061, // Invoices: Read
                10071, // Payments: Read
                10091, 10092, // Student Batches: Read, Write
                10101, 10102, // Guardians: Read, Write
                10201, // Lead Management
            ];

            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Initiator role permissions refined.');
        }

        // 4. Update Student Role permissions
        $studentRole = DB::table('role')->where('rolename', 'Student')->first();
        if ($studentRole) {
            $studentPermKeys = [
                10051, // Dashboard: Read
            ];

            foreach ($studentPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Student role permissions refined.');
        }

        $this->call(Update20260307GrowthCardModule::class);
        $this->call(Update20260307MaterialRequestModule::class);
        $this->call(Update20260309BlogModule::class);
        $this->call(Update20260309TicketingModule::class);
        $this->call(Update20260310GuardianMessageModule::class);
        $this->call(Update20260311AnnouncementModule::class);
        $this->call(Update20260311CertificateModule::class);
        $this->call(Update20260312InitiatorFinancialModule::class);
        $this->call(Update20260325AgreementTemplateModule::class);
        $this->call(Update20260325GrowthCriteriaModule::class);
        $this->call(Update20260325MaterialModule::class);
        $this->call(Update20260327EventModule::class);
        $this->call(Update20260403NotificationModule::class);
        $this->call(Update20260512RegistrationModule::class);
        $this->call(Update20260512DynamicAgreementTemplates::class);
        $this->call(Update20260530MailTemplateModule::class);
        $this->call(Update20260615SemesterBatchModule::class);
        $this->call(Update20260626StudentHistoryModule::class);
        $this->call(Update20260701LaravelLogsModule::class);
        $this->call(Update20260704SchoolRegistrationModule::class);
        $this->call(Update20260627MigratedStudentPermission::class);
        $this->call(Update20260709SchoolEnhancementsModule::class);
        $this->call(Update20260713MigratedStudentMailTemplates::class);
        $this->call(Update20260724SchoolCoordinatorModule::class);

        $this->command->info('Production data update completed successfully.');
    }
}

class Update20260327EventModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-27
     * Task: Assign Event module permissions to relevant roles.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-27 (Event Module Permissions)...');

        $eventPermKeys = [10401, 10402, 10403];

        // 1. Admin Role (Full Access)
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            foreach ($eventPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted all Event permissions.');
        }

        // 2. Initiator Role (Full Access for their areas)
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            foreach ($eventPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Initiator role granted all Event permissions.');
        }

        // 3. School Role (Read Only)
        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        if ($schoolRole) {
            $permission = DB::table('permission')->where('permkey', 10401)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id],
                    ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id]
                );
            }
            $this->command->info('- School role granted Read Events permission.');
        }

        // 4. Student Role (Read Only)
        $studentRole = DB::table('role')->where('rolename', 'Student')->first();
        if ($studentRole) {
            $readPermission = DB::table('permission')->where('permkey', 10401)->first();
            if ($readPermission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $studentRole->role_id, 'permission_id' => $readPermission->permission_id],
                    ['role_id' => $studentRole->role_id, 'permission_id' => $readPermission->permission_id]
                );
            }

            // Revoke Write/Manage permissions if they exist (Students should only Read)
            $otherPermIds = DB::table('permission')->whereIn('permkey', [10402, 10403])->pluck('permission_id');
            DB::table('rolepermission')->where('role_id', $studentRole->role_id)->whereIn('permission_id', $otherPermIds)->delete();

            $this->command->info('- Student role restricted to Read Events permission.');
        }

        $this->command->info('Event module permissions setup completed.');
    }
}

class Update20260325AgreementTemplateModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-25
     * Task: Add Agreement Template module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-25 (Agreement Template Module)...');

        // Ensure Agreement Template module permissions exist
        $permissions = [
            ['permkey' => 11001, 'permname' => 'Read Agreement Templates'],
            ['permkey' => 11002, 'permname' => 'Write Agreement Templates'],
            ['permkey' => 11003, 'permname' => 'Manage Agreement Templates'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Agreement Templates).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [11001, 11002, 11003];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Agreement Template permissions.');
        }

        $this->command->info('Agreement Template module permissions setup completed.');
    }
}

class Update20260309TicketingModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-09
     * Task: Add Ticketing module permissions and standardise roles.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-09 (Ticketing Module)...');

        // 1. Ensure modular permissions exist
        $permissions = [
            ['permkey' => 10801, 'permname' => 'Read Tickets'],
            ['permkey' => 10802, 'permname' => 'Write Tickets'],
            ['permkey' => 10803, 'permname' => 'Manage Tickets'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- Ticketing permissions ensured.');

        // Update Role permissions
        
        // Admin: All permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10801, 10802, 10803];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
        }

        // School: Create (Write), View (Read)
        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        if ($schoolRole) {
            $schoolPermKeys = [10801, 10802];
            foreach ($schoolPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
        }

        // Initiator: View (Read), Resolve (Manage/Update Status)
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [10801, 10803];
            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
        }

        $this->command->info('Ticketing module module permissions setup completed.');
    }
}

class Update20260307GrowthCardModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-07
     * Task: Add Growth Card module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-07 (Growth Card Module)...');

        // Ensure Growth Card module permissions exist
        $permissions = [
            ['permkey' => 10501, 'permname' => 'Read Growth Card'],
            ['permkey' => 10502, 'permname' => 'Write Growth Card'],
            ['permkey' => 10503, 'permname' => 'Manage Growth Card'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Growth Card).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10501, 10502, 10503];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Growth Card permissions.');
        }

        // Update Initiator Role permissions
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [10501, 10502];
            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Initiator role granted Growth Card permissions.');
        }

        // Update School Role permissions
        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        if ($schoolRole) {
            $schoolPermKeys = [10501]; // Read Only
            foreach ($schoolPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- School role granted Read Growth Card permission.');
        }

        // Update Student Role permissions
        $studentRole = DB::table('role')->where('rolename', 'Student')->first();
        if ($studentRole) {
            $studentPermKeys = [10501]; // Read Only
            foreach ($studentPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Student role granted Read Growth Card permission.');
        }

        $this->command->info('Growth Card module module permissions setup completed.');
    }
}

class Update20260307MaterialRequestModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-07
     * Task: Add Material Request module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-07 (Material Request Module)...');

        // Ensure Material Request module permissions exist
        $permissions = [
            ['permkey' => 10601, 'permname' => 'Read Materials'],
            ['permkey' => 10602, 'permname' => 'Write Materials'],
            ['permkey' => 10603, 'permname' => 'Manage Materials'],
            ['permkey' => 10611, 'permname' => 'Read Material Requests'],
            ['permkey' => 10612, 'permname' => 'Write Material Requests'],
            ['permkey' => 10613, 'permname' => 'Manage Material Requests'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Materials & Requests).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10601, 10602, 10603, 10611, 10612, 10613];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Material Module permissions.');
        }

        // Update School Role permissions
        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        if ($schoolRole) {
            $schoolPermKeys = [10601, 10611, 10612];
            foreach ($schoolPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- School role granted Material Request permissions.');
        }

        $this->command->info('Material Request module permissions setup completed.');
    }
}

class Update20260325MaterialModule extends Seeder
{
    /**
     * Run the database seeds for sample materials.
     */
    public function run(): void
    {
        $this->command->info('Seeding sample materials...');

        $materials = [
            [
                'name' => 'Student Kit (Level 1)',
                'description' => 'Comprehensive learning kit for Level 1 students including workbook and stationary.',
                'price' => 500.00,
                'discounted_price' => 450.00,
                'discount_start_date' => now()->subDays(5)->format('Y-m-d'),
                'discount_end_date' => now()->addDays(25)->format('Y-m-d'),
                'material_type' => 1, // Printable
                'inactive' => 0,
                'filters' => null, // Display to all by default
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Teacher Training Guide',
                'description' => 'Digital PDF guide for teachers to manage student growth and assessments.',
                'price' => 250.00,
                'discounted_price' => null,
                'discount_start_date' => null,
                'discount_end_date' => null,
                'material_type' => 0, // Digital
                'inactive' => 0,
                'filters' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Annual Workbook Set',
                'description' => 'Complete set of workbooks for all subjects.',
                'price' => 1200.00,
                'discounted_price' => 999.00,
                'discount_start_date' => now()->subDays(2)->format('Y-m-d'),
                'discount_end_date' => now()->addDays(10)->format('Y-m-d'),
                'material_type' => 1, // Printable
                'inactive' => 0,
                'filters' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        $hasNewColumns = Schema::hasColumn('materials', 'discounted_price');

        foreach ($materials as $material) {
            $data = $material;
            
            // If the migration to add enhanced fields hasn't run yet, remove those keys
            if (!$hasNewColumns) {
                unset($data['discounted_price'], $data['discount_start_date'], $data['discount_end_date'], $data['filters']);
            }

            DB::table('materials')->updateOrInsert(
                ['name' => $data['name']],
                $data
            );
        }

        $this->command->info('- Sample materials seeded.');
    }
}

class Update20260309BlogModule extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting production data update for Blog Module...');

        $permissions = [
            ['permkey' => 10701, 'permname' => 'Read Blogs'],
            ['permkey' => 10702, 'permname' => 'Write Blogs'],
            ['permkey' => 10703, 'permname' => 'Manage Blogs'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Blogs).');

        // Admin
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10701, 10702, 10703];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
        }

        // Student
        $studentRole = DB::table('role')->where('rolename', 'Student')->first();
        if ($studentRole) {
            $studentPermKeys = [10701, 10702];
            foreach ($studentPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
        }

        // Initiator
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [10701, 10703];
            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
        }
    }
}

class Update20260310GuardianMessageModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-10
     * Task: Add Guardian Messaging module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-10 (Guardian Messaging Module)...');

        // Ensure Guardian Messaging module permissions exist
        $permissions = [
            ['permkey' => 10211, 'permname' => 'Write Guardian Messages'],
            ['permkey' => 10212, 'permname' => 'Send Guardian Messages'],
            ['permkey' => 10213, 'permname' => 'Manage Guardian Messages'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Guardian Messaging).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10211, 10212, 10213];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Guardian Messaging permissions.');
        }

        $this->command->info('Guardian Messaging module permissions setup completed.');
    }
}

class Update20260311AnnouncementModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-11
     * Task: Add Student Announcement module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-11 (Student Announcement Module)...');

        // Ensure Announcement module permissions exist
        $permissions = [
            ['permkey' => 10221, 'permname' => 'Write Student Announcements'],
            ['permkey' => 10222, 'permname' => 'Read Student Announcements'],
            ['permkey' => 10223, 'permname' => 'Manage Announcement Templates'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Student Announcements).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10221, 10222, 10223];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Student Announcement permissions.');
        }

        // Update Initiator Role permissions (Read, Write, Manage Templates)
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [10221, 10222, 10223]; // Write, Read, and Manage Templates
            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Initiator role granted Student Announcement permissions.');
        }

        // Update Student Role permissions (Read only)
        $studentRole = DB::table('role')->where('rolename', 'Student')->first();
        if ($studentRole) {
            $studentPermKeys = [10222]; // Read Student Announcements
            foreach ($studentPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Student role granted Read Student Announcements permission.');
        }

        $this->command->info('Student Announcement module permissions setup completed.');
    }
}

class Update20260311CertificateModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-11
     * Task: Add Certificate module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-11 (Certificate Module)...');

        // Ensure Certificate module permissions exist
        $permissions = [
            ['permkey' => 10901, 'permname' => 'Read Certificates'],
            ['permkey' => 10902, 'permname' => 'Write Certificates'],
            ['permkey' => 10903, 'permname' => 'Manage Certificates'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Certificates).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10901, 10902, 10903];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Certificate permissions.');
        }

        // Update Initiator Role permissions (Read, Write)
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [10901, 10902];
            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Initiator role granted Certificate permissions.');
        }

        // Update Student Role permissions (Read only)
        $studentRole = DB::table('role')->where('rolename', 'Student')->first();
        if ($studentRole) {
            $studentPermKeys = [10901]; // Read Certificates
            foreach ($studentPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $studentRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Student role granted Read Certificate permissions.');
        }

        $this->command->info('Certificate module permissions setup completed.');
    }
}

class Update20260312InitiatorFinancialModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-12
     * Task: Add Initiator Financial configuration permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-12 (Initiator Financial Module)...');

        // Ensure Initiator Financial permissions exist
        $permissions = [
            ['permkey' => 10301, 'permname' => 'Manage Initiator Financials'],
            ['permkey' => 10302, 'permname' => 'View Initiator Commissions'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Initiator Financials).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [10301, 10302];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Initiator Financial permissions.');
        }

        // Update Initiator Role permissions (View Only)
        $initiatorRole = DB::table('role')->where('rolename', 'Initiator')->first();
        if ($initiatorRole) {
            $initiatorPermKeys = [10302];
            foreach ($initiatorPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $initiatorRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Initiator role granted View Commission permission.');
        }

        $this->command->info('Initiator Financial module permissions setup completed.');
    }
}

class Update20260325GrowthCriteriaModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-03-25
     * Task: Add Growth Criteria module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-03-25 (Growth Criteria Module)...');

        // Ensure Growth Criteria module permission exists
        $permissions = [
            ['permkey' => 10504, 'permname' => 'Manage Growth Criteria'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permission ensured (Manage Growth Criteria).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $permission = DB::table('permission')->where('permkey', 10504)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                    ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                );
            }
            $this->command->info('- Admin role granted Manage Growth Criteria permission.');
        }

        $this->command->info('Growth Criteria module setup completed.');
    }
}

class Update20260403NotificationModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-04-03
     * Task: Add Notification Management module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-04-03 (Notification Module)...');

        $permissions = [
            ['permkey' => 11101, 'permname' => 'Read Notifications'],
            ['permkey' => 11102, 'permname' => 'Write Notifications'],
            ['permkey' => 11103, 'permname' => 'Manage Notifications'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Notifications).');

        // Optimized Role assigned: Fetch all required permission IDs first to avoid query in loop
        $permKeys = [11101, 11102, 11103];
        $permMap = DB::table('permission')
            ->whereIn('permkey', $permKeys)
            ->pluck('permission_id', 'permkey');

        if ($permMap->isEmpty()) {
            $this->command->error('Notification permissions not found in database.');
            return;
        }

        // 1. Admin Role (Full Access)
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            foreach ($permKeys as $key) {
                if (isset($permMap[$key])) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permMap[$key]],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permMap[$key]]
                    );
                }
            }
            $this->command->info('- Admin role granted all Notification permissions.');
        }

        // 2. Read Access for shared roles (Initiator, School, Accountant)
        if (isset($permMap[11101])) {
            $sharedRoles = DB::table('role')
                ->whereIn('rolename', ['Initiator', 'School', 'Accountant'])
                ->get();

            foreach ($sharedRoles as $role) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $role->role_id, 'permission_id' => $permMap[11101]],
                    ['role_id' => $role->role_id, 'permission_id' => $permMap[11101]]
                );
                $this->command->info("- {$role->rolename} role granted Read Notifications permission.");
            }
        }

        $this->command->info('Notification module permissions setup completed.');
    }
}

class Update20260512RegistrationModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-05-12
     * Task: Add Registration Review module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-05-12 (Registration Review Module)...');

        // Ensure Registration Review module permission exists
        $permission = [
            'permkey' => 11201,
            'permname' => 'Registration Review',
            'created_at' => now()
        ];

        DB::table('permission')->updateOrInsert(
            ['permkey' => 11201],
            $permission
        );
        
        $this->command->info('- New permission ensured (Registration Review).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $perm = DB::table('permission')->where('permkey', 11201)->first();
            if ($perm) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $adminRole->role_id, 'permission_id' => $perm->permission_id],
                    ['role_id' => $adminRole->role_id, 'permission_id' => $perm->permission_id]
                );
            }
            $this->command->info('- Admin role granted Registration Review permission.');
        }

        $this->command->info('Registration Review module setup completed.');
    }
}

class Update20260512DynamicAgreementTemplates extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-05-12
     * Task: Initialize dynamic terms and conditions with multi-language support.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-05-12 (Dynamic Agreement Templates)...');

        $content_en = '
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">What is the SANKALP Programme?</h3>
            <p>The Pramukh SANKALP Programme is a visionary leadership development and Civil Services foundation programme designed for school students of Standard 6 to 12. It aims to nurture ethical, disciplined, and service-driven future leaders and officers, who contribute meaningfully to society and align with the national vision of Viksit Bharat. Rooted in Indian ethos, values, and Sanatan wisdom, the programme focuses on the balanced development of Head (Knowledge), Heart (Values), and Hand (Skills).</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">1. Purpose of This Form</h3>
            <p>This application form is meant only for individual students of Std. 6 to 12 who wish to join the SANKALP Programme independently (online mode).</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">2. Voluntary Participation</h3>
            <p>By filling this form, you confirm that you are joining the programme voluntarily, with full understanding and consent of your parent/guardian.</p>
            
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">3. Programme Orientation</h3>
            <p>The SANKALP Programme draws inspiration from Indian Vedic culture, Sanatan scriptures, and value-based leadership principles, while remaining relevant to modern education and leadership needs.</p>
            
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">4. Programme Fee (Online Mode)</h3>
            {{FEE_PLANS}}
            <p class="text-sm text-gray-600 italic mt-2">The above fee includes application, study material, courier charges, and access to the SANKALP App.</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">5. Study Material & Access</h3>
            <p>After successful enrolment:</p>
            <ul>
                <li>Application (App) access will be provided</li>
                <li>Study materials and handouts will be couriered to the address mentioned in this form</li>
            </ul>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">6. Understanding Before Applying</h3>
            <p>Applicants and guardians are strongly advised to carefully understand the Pramukh SANKALP Programme before submitting this application.</p>

            <p>👉 Detailed programme information is available on our website: <a href="https://pramukh.academy/pramukh-sankalp-programme/" class="text-[#00bde0] font-bold">https://pramukh.academy/pramukh-sankalp-programme/</a></p>

            <h3 class="text-xl text-red-600 font-black border-l-4 border-red-600 pl-4">7. School Students Already Enrolled</h3>
            <p class="font-bold text-red-600">Do NOT fill this form if the SANKALP Programme is already running in your school.</p>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">Parent / Guardian Responsibility</h4>
            <ul>
                <li>Each student must have a designated SANKALP Guardian (parent or responsible adult).</li>
                <li>Guardian support is essential for:</li>
                <li>Guiding handout activities</li>
                <li>Ensuring discipline and continuity</li>
                <li>Supporting value-based learning at home</li>
            </ul>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">Need Help?</h4>
            <p>For any queries or clarification, please contact:</p>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">Programme Coordinator</h4>
            <p class="text-lg font-bold flex items-center gap-2">
                📞 <a href="tel:+919510060898" onclick="navigator.clipboard.writeText(\'+91 95100 60898\'); alert(\'Copied to clipboard\');" class="hover:text-[#00bde0] transition-colors" title="Click to copy and call">+91 95100 60898</a>
            </p>
        ';

        $content_gu = '
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">સંકલ્પ પ્રોગ્રામ શું છે?</h3>
            <p>પ્રમુખ સંકલ્પ પ્રોગ્રામ એ ધોરણ 6 થી 12 ના વિદ્યાર્થીઓ માટે તૈયાર કરવામાં આવેલ એક વિઝનરી લીડરશીપ ડેવલપમેન્ટ અને સિવિલ સર્વિસીસ ફાઉન્ડેશન પ્રોગ્રામ છે. તેનો મુખ્ય ઉદ્દેશ્ય ભાવિ નૈતિક, શિસ્તબદ્ધ અને સેવાભાવી લીડર્સ અને અધિકારીઓનું ઘડતર કરવાનો છે, જેઓ સમાજમાં સાર્થક યોગદાન આપે અને ‘વિકસિત ભારત’ના રાષ્ટ્રીય વિઝન સાથે સુસંગત હોય. ભારતીય સંસ્કૃતિ, મૂલ્યો અને સનાતન જ્ઞાન પર આધારિત આ પ્રોગ્રામમાં મસ્તક (જ્ઞાન), હૃદય (મૂલ્યો) તેમ જ હાથ (કૌશલ્યો) ના સંતુલિત અને સર્વાંગી વિકાસ પર ધ્યાન કેન્દ્રિત કરવામાં આવ્યું છે.</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">૧. આ ફોર્મનો હેતુ</h3>
            <p>આ એપ્લિકેશન ફોર્મ ફક્ત ધોરણ 6 થી 12 ના એવા વ્યક્તિગત વિદ્યાર્થીઓ માટે છે જેઓ ઓનલાઇન માધ્યમથી અને પોતે સ્વૈચ્છિક સંકલ્પ પ્રોગ્રામમાં જોડાવા માંગતા હોય.</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">૨. સ્વૈચ્છિક ભાગીદારી</h3>
            <p>આ ફોર્મ ભરીને, તમે પુષ્ટિ કરો છો કે તમે તમારા માતા-પિતા/વાલીની સંપૂર્ણ સમજ અને સંમતિ સાથે, સ્વૈચ્છિક રીતે આ પ્રોગ્રામમાં જોડાઈ રહ્યા છો.</p>
            
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">૩. પ્રોગ્રામની રૂપરેખા</h3>
            <p>સંકલ્પ પ્રોગ્રામ ભારતીય વૈદિક સંસ્કૃતિ, સનાતન શાસ્ત્રો અને મૂલ્ય-આધારિત નેતૃત્વના સિદ્ધાંતોમાંથી પ્રેરણા લે છે, અને સાથે સાથે તે આધુનિક શિક્ષણ અને નેતૃત્વની જરૂરિયાતો સાથે પણ સુસંગત છે.</p>
            
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">૪. પ્રોગ્રામ ફી (ઓનલાઇન મોડ)</h3>
            {{FEE_PLANS}}
            <p class="text-sm text-gray-600 italic mt-2">ઉપરોક્ત ફીમાં એપ્લિકેશન, અભ્યાસ સામગ્રી, કુરિયર ચાર્જ અને સંકલ્પ એપનો ઉપયોગ સામેલ છે.</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">૫. સ્ટડી મટિરિયલ અને એક્સેસ</h3>
            <p>સફળ રજીસ્ટ્રેશન (Enrolment) પછી:</p>
            <ul>
                <li>એપ્લિકેશન (App) નું એક્સેસ આપવામાં આવશે</li>
                <li>આ ફોર્મમાં દર્શાવેલ સરનામે સ્ટડી મટિરિયલ અને હેન્ડઆઉટ્સ કુરિયર કરવામાં આવશે</li>
            </ul>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">૬. અરજી કરતા પહેલા સમજવાની બાબત</h3>
            <p>અરજદારો અને વાલીઓને ભારપૂર્વક સલાહ આપવામાં આવે છે કે આ એપ્લિકેશન સબમિટ કરતા પહેલા ‘પ્રમુખ સંકલ્પ પ્રોગ્રામ’ વિશે કાળજીપૂર્વક તમામ બાબતો સમજી લેવી.</p>

            <p>👉 પ્રોગ્રામની વિગતવાર માહિતી અમારી વેબસાઇટ પર ઉપલબ્ધ છે: <a href="https://pramukh.academy/pramukh-sankalp-programme/" class="text-[#00bde0] font-bold">https://pramukh.academy/pramukh-sankalp-programme/</a></p>

            <h3 class="text-xl text-red-600 font-black border-l-4 border-red-600 pl-4">૭. શાળામાં પહેલાથી જોડાયેલા વિદ્યાર્થીઓ</h3>
            <p class="font-bold text-red-600">જો તમારી શાળામાં સંકલ્પ કાર્યક્રમ પહેલેથી જ ચાલી રહ્યો હોય અથવા આ પ્રોગ્રામ ત્યાં શરૂ થવાનો હોય તો ત્યાં શાળા દ્વારા રજીસ્ટ્રેશન થશે આથી આપ આ ફોર્મ ભરશો નહીં.</p>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">માતા-પિતા / વાલીની જવાબદારી</h4>
            <ul>
                <li>દરેક વિદ્યાર્થી પાસે એક નિયુક્ત ‘સંકલ્પ ગાર્ડીયન’ (માતા-પિતા અથવા જવાબદાર વયસ્ક) હોવા આવશ્યક છે.</li>
                <li>ગાર્ડિયન સપોર્ટ આ માટે જરૂરી છે:</li>
                <li>હેન્ડઆઉટની એક્ટિવિટીમાં માર્ગદર્શન આપવા માટે</li>
                <li>શિસ્ત અને સાતત્ય સુનિશ્ચિત કરવા માટે</li>
                <li>ઘરે મૂલ્ય-આધારિત શિક્ષણને સપોર્ટ કરવા માટે</li>
            </ul>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">મદદ જોઈએ છે?</h4>
            <p>કોઈપણ પ્રશ્ન કે સ્પષ્ટતા માટે, કૃપા કરીને સંપર્ક કરો:</p>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">પ્રોગ્રામ કોઓર્ડિનેટર</h4>
            <p class="text-lg font-bold flex items-center gap-2">
                📞 <a href="tel:+919510060898" onclick="navigator.clipboard.writeText(\'+91 95100 60898\'); alert(\'Copied to clipboard\');" class="hover:text-[#00bde0] transition-colors" title="Click to copy and call">+91 95100 60898</a>
            </p>
        ';

        $content_hi = '
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">संकल्प कार्यक्रम क्या है?</h3>
            <p>प्रमुख संकल्प कार्यक्रम कक्षा 6 से 12 के छात्रों के लिए तैयार किया गया एक विजनरी लीडरशिप डेवलपमेंट और सिविल सर्विसेज फाउंडेशन कार्यक्रम है। इसका मुख्य उद्देश्य भविष्य के नैतिक, अनुशासित और सेवा-उन्मुख लीडर्स और अधिकारियों का निर्माण करना है, जो समाज में सार्थक योगदान दें और \'विकसित भारत\' के राष्ट्रीय विजन के साथ संरेखित हों। भारतीय लोकाचार, मूल्यों और सनातन ज्ञान में निहित, यह कार्यक्रम सिर (ज्ञान), हृदय (मूल्य) और हाथ (कौशल) के संतुलित और सर्वांगीण विकास पर केंद्रित है।</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">१. इस फॉर्म का उद्देश्य</h3>
            <p>यह आवेदन फॉर्म केवल उन कक्षा 6 से 12 के व्यक्तिगत छात्रों के लिए है जो स्वतंत्र रूप से (ऑनलाइन मोड) संकल्प कार्यक्रम में शामिल होना चाहते हैं।</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">२. स्वैच्छिक भागीदारी</h3>
            <p>इस फॉर्म को भरकर, आप पुष्टि करते हैं कि आप अपने माता-पिता/अभिभावक की पूर्ण समझ और सहमति के साथ स्वेच्छा से कार्यक्रम में शामिल हो रहे हैं।</p>
            
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">३. कार्यक्रम अभिविन्यास</h3>
            <p>संकल्प कार्यक्रम भारतीय वैदिक संस्कृति, सनातन शास्त्रों और नेतृत्व सिद्धांतों से प्रेरणा लेता है, जबकि आधुनिक शिक्षा और नेतृत्व की जरूरतों के लिए प्रासंगिक बना रहता है।</p>
            
            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">४. कार्यक्रम शुल्क (ऑनलाइन मोड)</h3>
            {{FEE_PLANS}}
            <p class="text-sm text-gray-600 italic mt-2">उपरोक्त शुल्क में आवेदन, अध्ययन सामग्री, कूरियर शुल्क और संकल्प ऐप का उपयोग शामिल है।</p>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">५. अध्ययन सामग्री और पहुंच</h3>
            <p>सफल नामांकन के बाद:</p>
            <ul>
                <li>एप्लिकेशन (ऐप) एक्सेस प्रदान किया जाएगा</li>
                <li>इस फॉर्म में बताए गए पते पर अध्ययन सामग्री और हैंडआउट्स कुरियर किए जाएंगे</li>
            </ul>

            <h3 class="text-xl text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">६. आवेदन करने से पहले समझना</h3>
            <p>आवेदकों और अभिभावकों को दृढ़ता से सलाह दी जाती है कि वे इस आवेदन को जमा करने से पहले प्रमुख संकल्प कार्यक्रम को ध्यान से समझें।</p>

            <p>👉 विस्तृत कार्यक्रम की जानकारी हमारी वेबसाइट पर उपलब्ध है: <a href="https://pramukh.academy/pramukh-sankalp-programme/" class="text-[#00bde0] font-bold">https://pramukh.academy/pramukh-sankalp-programme/</a></p>

            <h3 class="text-xl text-red-600 font-black border-l-4 border-red-600 pl-4">૭. स्कूल के छात्र पहले से ही नामांकित हैं</h3>
            <p class="font-bold text-red-600">यदि आपके स्कूल में संकल्प कार्यक्रम पहले से ही चल रहा है, तो इस फॉर्म को न भरें।</p>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">माता-पिता / अभिभावक की जिम्मेदारी</h4>
            <ul>
                <li>प्रत्येक छात्र के पास एक नामित संकल्प अभिभावक (माता-पिता या जिम्मेदार वयस्क) होना चाहिए।</li>
                <li>अभिभावक सहायता इनके लिए आवश्यक है:</li>
                <li>हैंडआउट गतिविधियों का मार्गदर्शन करना</li>
                <li>अनुशासन और निरंतरता सुनिश्चित करना</li>
                <li>घर पर मूल्य-आधारित शिक्षा का समर्थन करना</li>
            </ul>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">मदદ चाहिए?</h4>
            <p>किसी भी प्रश्न या स्पष्टीकरण के लिए, कृपया संपर्क करें:</p>

            <h4 class="text-lg text-[#00bde0] font-black border-l-4 border-[#00bde0] pl-4">कार्यक्रम समन्वयक</h4>
            <p class="text-lg font-bold flex items-center gap-2">
                📞 <a href="tel:+919510060898" onclick="navigator.clipboard.writeText(\'+91 95100 60898\'); alert(\'Copied to clipboard\');" class="hover:text-[#00bde0] transition-colors" title="Click to copy and call">+91 95100 60898</a>
            </p>
        ';

        DB::table('agreement_templates')->updateOrInsert(
            ['slug' => 'school_registration'],
            [
                'title' => 'Student Registration Terms & Conditions',
                'content_en' => $content_en,
                'content_hi' => $content_hi,
                'content_gu' => $content_gu,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $this->command->info('- Dynamic agreement templates initialized.');
    }
}

class Update20260530MailTemplateModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-05-30
     * Task: Add Mail Template permissions and seed initial templates.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-05-30 (Mail Template Module)...');

        // Restore Notifications permissions (repairing any prior 1110x override)
        $notificationsPermissions = [
            ['permkey' => 11101, 'permname' => 'Read Notifications'],
            ['permkey' => 11102, 'permname' => 'Write Notifications'],
            ['permkey' => 11103, 'permname' => 'Manage Notifications'],
        ];

        foreach ($notificationsPermissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- Notifications permissions restored successfully.');

        // 1. Ensure Mail Template module permissions exist (using conflict-free 1130x keys)
        $permissions = [
            ['permkey' => 11301, 'permname' => 'Read Mail Templates'],
            ['permkey' => 11302, 'permname' => 'Write Mail Templates'],
            ['permkey' => 11303, 'permname' => 'Manage Mail Templates'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Mail Templates 1130x).');

        // 2. Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [11301, 11302, 11303];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Mail Template permissions.');
        }

        // 3. Ensure default mail templates exist
        $wrapperStart = '<div style="font-family: \'Outfit\', \'Inter\', sans-serif; background-color: #f7f9fa; padding: 30px 15px; margin: 0; width: 100%;"><div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #00bde0;"><div style="background-color: #ffffff; padding: 25px; text-align: center; border-bottom: 1px solid #edf2f7;"><h1 style="color: #1a202c; font-size: 22px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">PRAMUKH SANKALP</h1><p style="color: #718096; font-size: 13px; margin: 5px 0 0 0; letter-spacing: 1px;">PRAMUKH ACADEMY</p></div><div style="padding: 30px 25px; color: #2d3748; line-height: 1.6; font-size: 15px;">';
        $wrapperEnd = '</div></div></div>';

        $templates = [
            [
                'identifier' => 'pre_registration_otp',
                'title_en' => 'Email Verification OTP for Pramukh SANKALP Registration',
                'content_en' => $wrapperStart . '<p>Dear <strong>{{guardian_name}}</strong>,</p><p>Your OTP for registering <strong>{{student_name}}</strong> in the Pramukh SANKALP Programme is:</p><div style="text-align: center; margin: 25px 0;"><span style="display: inline-block; background-color: #f0fdfa; border: 2px dashed #00bde0; color: #00bde0; font-size: 32px; font-weight: 700; padding: 10px 30px; letter-spacing: 5px; border-radius: 8px;">{{otp}}</span></div><p style="color: #e53e3e; font-weight: 600; font-size: 14px;">This OTP is valid for 10 minutes.</p><p>If you closed the registration page, you can click the link below to resume and confirm your registration:</p><div style="text-align: center; margin: 25px 0;"><a href="{{verify_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">Resume Registration</a></div><p style="font-size: 13px; color: #718096; text-align: center;">Or copy and paste this link into your web browser:<br><a href="{{verify_link}}" style="color: #00bde0; text-decoration: none;">{{verify_link}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm regards,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'title_hi' => 'प्रमुख संकल्प पंजीकरण के लिए ईमेल सत्यापन ओटीपी',
                'content_hi' => $wrapperStart . '<p>प्रिय <strong>{{guardian_name}}</strong>,</p><p>प्रमुख संकल्प कार्यक्रम में <strong>{{student_name}}</strong> को पंजीकृत करने के लिए आपका ओटीपी (OTP) है:</p><div style="text-align: center; margin: 25px 0;"><span style="display: inline-block; background-color: #f0fdfa; border: 2px dashed #00bde0; color: #00bde0; font-size: 32px; font-weight: 700; padding: 10px 30px; letter-spacing: 5px; border-radius: 8px;">{{otp}}</span></div><p style="color: #e53e3e; font-weight: 600; font-size: 14px;">यह ओटीपी 10 मिनट के लिए वैध है।</p><p>यदि आपने पंजीकरण पृष्ठ बंद कर दिया था, तो आप अपने पंजीकरण को फिर से शुरू करने और पुष्टि करने के लिए नीचे दिए गए लिंक पर क्लिक कर सकते हैं:</p><div style="text-align: center; margin: 25px 0;"><a href="{{verify_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">पंजीकरण फिर से शुरू करें</a></div><p style="font-size: 13px; color: #718096; text-align: center;">या इस लिंक को अपने वेब ब्राउज़र में कॉपी और पेस्ट करें:<br><a href="{{verify_link}}" style="color: #00bde0; text-decoration: none;">{{verify_link}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">सादर,<br><strong>प्रमुख संकल्प टीम</strong><br>प्रमुख एकेडमी</p>' . $wrapperEnd,
                'title_gu' => 'પ્રમુખ સંકલ્પ નોંધણી માટે ઈમેલ વેરિફિકેશન OTP',
                'content_gu' => $wrapperStart . '<p>પ્રિય <strong>{{guardian_name}}</strong>,</p><p>પ્રમુખ સંકલ્પ પ્રોગ્રામમાં <strong>{{student_name}}</strong> ની નોંધણી માટેનો તમારો OTP નીચે મુજબ છે:</p><div style="text-align: center; margin: 25px 0;"><span style="display: inline-block; background-color: #f0fdfa; border: 2px dashed #00bde0; color: #00bde0; font-size: 32px; font-weight: 700; padding: 10px 30px; letter-spacing: 5px; border-radius: 8px;">{{otp}}</span></div><p style="color: #e53e3e; font-weight: 600; font-size: 14px;">આ OTP ૧૦ મિનિટ માટે માન્ય છે.</p><p>જો તમે નોંધણી પૃષ્ઠ બંધ કર્યું હોય, તો તમે તમારી નોંધણી પુનઃ શરૂ કરવા અને તેની પુષ્ટિ કરવા માટે નીચેની લિંક પર ક્લિક કરી શકો છો:</p><div style="text-align: center; margin: 25px 0;"><a href="{{verify_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">નોંધણી પુનઃ શરૂ કરો</a></div><p style="font-size: 13px; color: #718096; text-align: center;">અથવા આ લિંકને તમારા વેબ બ્રાઉઝરમાં કોપી અને પેસ્ટ કરો:<br><a href="{{verify_link}}" style="color: #00bde0; text-decoration: none;">{{verify_link}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">સાદર,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong><br>પ્રમુખ એકેડેમી</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'payment_reminder',
                'title_en' => 'Reminder: Complete Your SANKALP Registration',
                'content_en' => $wrapperStart . '<p>Namaste <strong>{{guardian_name}}</strong>,</p><p>This is a gentle reminder that the registration for the Pramukh SANKALP Programme is still pending due to incomplete payment.</p><p>A timely confirmation will help <strong>{{student_name}}</strong> begin this meaningful journey of knowledge, values, leadership, and future-readiness without delay.</p><p>Please complete the payment using the link below:</p><div style="text-align: center; margin: 25px 0;"><a href="{{payment_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">Complete Payment Now</a></div><p style="font-size: 13px; color: #718096; text-align: center;">If the button/link does not open, please copy and paste the following link into your web browser:<br><a href="{{payment_link}}" style="color: #00bde0; text-decoration: none;">{{payment_link}}</a></p><p>Once payment is completed, your registration will move forward for confirmation.</p><p style="background-color: #fffaf0; border-left: 4px solid #dd6b20; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #744210;"><strong>Important:</strong> If the amount has already been deducted from your account, please do not make another payment immediately. Kindly contact our support team with your payment screenshot for verification.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Thank you,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'title_hi' => 'अनुस्मारक: अपना संकल्प पंजीकरण पूरा करें',
                'content_hi' => $wrapperStart . '<p>नमस्ते <strong>{{guardian_name}}</strong>,</p><p>यह एक विनम्र अनुस्मारक है कि भुगतान अपूर्ण होने के कारण प्रमुख संकल्प कार्यक्रम के लिए पंजीकरण अभी भी लंबित है।</p><p>समय पर पुष्टि करने से <strong>{{student_name}}</strong> को ज्ञान, मूल्यों, नेतृत्व और भविष्य की तैयारी की इस सार्थक यात्रा को बिना किसी देरी के शुरू करने में मदद मिलेगी।</p><p>कृपया नीचे दिए गए लिंक का उपयोग करके भुगतान पूरा करें:</p><div style="text-align: center; margin: 25px 0;"><a href="{{payment_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">अब भुगतान पूरा करें</a></div><p style="font-size: 13px; color: #718096; text-align: center;">यदि लिंक नहीं खुलता है, तो कृपया निम्नलिखित लिंक को अपने वेब ब्राउज़र में कॉपी और पेस्ट करें:<br><a href="{{payment_link}}" style="color: #00bde0; text-decoration: none;">{{payment_link}}</a></p><p>भुगतान पूरा होने के बाद, आपका पंजीकरण पुष्टि के लिए आगे बढ़ जाएगा।</p><p style="background-color: #fffaf0; border-left: 4px solid #dd6b20; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #744210;"><strong>महत्वपूर्ण:</strong> यदि राशि आपके खाते से पहले ही कट चुकी है, तो कृपया तुरंत दूसरा भुगतान न करें। कृपया सत्यापन के लिए हमारे सहायता दल से संपर्क करें।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">धन्यवाद,<br><strong>प्रमुख संकल्प टीम</strong><br>प्रमुख एकेडमी</p>' . $wrapperEnd,
                'title_gu' => 'યાદી: તમારી સંકલ્પ નોંધણી પૂર્ણ કરો',
                'content_gu' => $wrapperStart . '<p>નમસ્તે <strong>{{guardian_name}}</strong>,</p><p>આ એક નમ્ર રીમાઇન્ડર છે કે અપૂર્ણ ચુકવણીને કારણે પ્રમુખ સંકલ્પ પ્રોગ્રામ માટેની નોંધણી હજુ પણ બાકી છે.</p><p>સમયસર પુષ્ટિ કરવાથી <strong>{{student_name}}</strong> ને જ્ઞાન, મૂલ્યો, નેતૃત્વ અને ભવિષ્યની સજ્જતાની આ સાર્થક યાત્રા કોઈપણ વિલંબ વિના શરૂ કરવામાં મદદ મળશે.</p><p>કૃપા કરીને નીચેની લિંકનો ઉપયોગ કરીને ચુકવણી પૂર્ણ કરો:</p><div style="text-align: center; margin: 25px 0;"><a href="{{payment_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">હમણાં જ ચુકવણી પૂર્ણ કરો</a></div><p style="font-size: 13px; color: #718096; text-align: center;">જો લિંક ન ખુલે, તો કૃપા કરીને નીચેની લિંકને તમારા વેબ બ્રાઉઝરમાં કોપી અને પેસ્ટ કરો:<br><a href="{{payment_link}}" style="color: #00bde0; text-decoration: none;">{{payment_link}}</a></p><p>ચુકવણી પૂર્ણ થયા પછી, તમારી નોંધણી પુષ્ટિ માટે આગળ વધશે.</p><p style="background-color: #fffaf0; border-left: 4px solid #dd6b20; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #744210;"><strong>મહત્વપૂર્ણ:</strong> જો તમારા ખાતામાંથી રકમ પહેલેથી જ કપાઈ ગઈ હોય, તો કૃપા કરીને તરત જ બીજી ચુકવણી કરશો નહીં. કૃપા કરીને ચકાસણી માટે તમારા પેમેન્ટ સ્ક્રીનશોટ સાથે અમારી સપોર્ટ ટીમનો સંપર્ક કરો.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">આભાર,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong><br>પ્રમુખ એકેડેમી</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'payment_success',
                'title_en' => 'Payment Successful – SANKALP Programme Enrolment in Process',
                'content_en' => $wrapperStart . '<p>Namaste <strong>{{guardian_name}}</strong>,</p><p>We are happy to inform you that the payment for the Pramukh SANKALP Programme has been received successfully.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Registration Details</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">Student Name:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{student_name}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Subscription Plan:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{subscription}}</td></tr></table></div><p>A receipt has been attached with this email.</p><p>Our team will now proceed with enrolling <strong>{{student_name}}</strong> in the SANKALP course. Once the enrolment is completed, we will share the next steps regarding course access, study material and orientation details.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Thank you,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'title_hi' => 'भुगतान सफल - संकल्प कार्यक्रम नामांकन प्रक्रिया में',
                'content_hi' => $wrapperStart . '<p>नमस्ते <strong>{{guardian_name}}</strong>,</p><p>हमें आपको सूचित करते हुए खुशी हो रही है कि प्रमुख संकल्प कार्यक्रम के लिए भुगतान सफलतापूर्वक प्राप्त हो गया है।</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">पंजीकरण विवरण</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">छात्र का नाम:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{student_name}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">योजना (Subscription):</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{subscription}}</td></tr></table></div><p>एक रसीद इस ईमेल के साथ संलग्न है।</p><p>हमारी टीम अब <strong>{{student_name}}</strong> को संकल्प पाठ्यक्रम में नामांकित करने की प्रक्रिया शुरू करेगी। नामांकन पूरा होने के बाद, हम पाठ्यक्रम पहुंच, अध्ययन सामग्री और ओरिएंटेशन विवरण के बारे में अगले चरण साझा करेंगे।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">धन्यवाद,<br><strong>प्रमुख संकल्प टीम</strong><br>प्रमुख एकेडमी</p>' . $wrapperEnd,
                'title_gu' => 'ચુકવણી સફળ - સંકલ્પ પ્રોગ્રામ પ્રવેશ પ્રક્રિયામાં',
                'content_gu' => $wrapperStart . '<p>નમસ્તે <strong>{{guardian_name}}</strong>,</p><p>અમે આપને જણાવતા આનંદ અનુભવીએ છીએ કે પ્રમુખ સંકલ્પ પ્રોગ્રામ માટેની ચુકવણી સફળતાપૂર્વક મળી ગઈ છે.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">નોંધણી વિગતો</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">વિદ્યાર્થીનું નામ:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{student_name}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">સબ્સ્ક્રિપ્શન પ્લાન:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{subscription}}</td></tr></table></div><p>આ ઈમેલ સાથે ચુકવણીની રસીદ સામેલ છે.</p><p>અમારી ટીમ હવે <strong>{{student_name}}</strong> ને સંકલ્પ કોર્સમાં દાખલ કરવાની પ્રક્રિયા શરૂ કરશે. એકવાર આ પ્રક્રિયા પૂર્ણ થયા પછી, અમે કોર્સ એક્સેસ, સ્ટડી મટિરિયલ અને ઓરિએન્ટેશન વિશેની આગામી વિગતો શેર કરીશું.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">આભાર,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong><br>પ્રમુખ એકેડેમી</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'student_welcome',
                'title_en' => 'Welcome to Pramukh SANKALP – Portal Access Details',
                'content_en' => $wrapperStart . '<p>Namaste SANKALP Guardian <strong>{{guardian_name}}</strong>,</p><p>Congratulations!</p><p>Student has been successfully enrolled in the Pramukh SANKALP Programme.</p><p>This marks the beginning of SANKALP Aspirant - <strong>{{student_name}}</strong>’s SANKALP journey towards becoming a virtuous changemaker for Viksit Bharat.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">SANKALP Portal Login Details</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">Portal Link:</td><td style="padding: 6px 0; color: #2d3748;"><a href="{{portal_link}}" style="color: #00bde0; text-decoration: none; font-weight: 600;">{{portal_link}}</a></td></tr><tr><td style="padding: 6px 0; color: #718096;">SANKALP ID Number:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{enrolment_no}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Portal Password (You can change it later):</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{lmspswd}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Subscription Validity:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{course_validity}}</td></tr></table></div><p>Through the SANKALP Portal, you will be able to update your details, track study material status, check payment status, view growth cards, and manage other important programme-related information.</p><p>Please note: The online learning Session, lecture schedules, and LMS application access details will be shared separately through another email.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm Welcome,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'title_hi' => 'प्रमुख संकल्प में आपका स्वागत है - पोर्टल एक्सेस विवरण',
                'content_hi' => $wrapperStart . '<p>नमस्ते संकल्प अभिभावक <strong>{{guardian_name}}</strong>,</p><p>बधाई हो!</p><p>छात्र को प्रमुख संकल्प कार्यक्रम में सफलतापूर्वक नामांकित कर लिया गया है।</p><p>यह <strong>{{student_name}}</strong> की विकसित भारत के लिए एक सदाचारी परिवर्तनकर्ता बनने की दिशा में संकल्प यात्रा की शुरुआत है।</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">संकल्प पोर्टल लॉगिन विवरण</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">पोर्टल लिंक:</td><td style="padding: 6px 0; color: #2d3748;"><a href="{{portal_link}}" style="color: #00bde0; text-decoration: none; font-weight: 600;">{{portal_link}}</a></td></tr><tr><td style="padding: 6px 0; color: #718096;">संकल्प आईडी नंबर:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{enrolment_no}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">पोर्टल पासवर्ड (इसे बाद में बदला जा सकता है):</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{lmspswd}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">वैधता (Subscription Validity):</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{course_validity}}</td></tr></table></div><p>संकल्प पोर्टल के माध्यम से, आप अपने विवरण अपडेट कर सकेंगे, अध्ययन सामग्री की स्थिति ट्रैक कर सकेंगे, भुगतान स्थिति देख सकेंगे, ग्रोथ कार्ड देख सकेंगे और अन्य महत्वपूर्ण कार्यक्रम-संबंधित जानकारी प्रबंधित कर सकेंगे।</p><p>कृपया ध्यान दें: ऑनलाइन लर्निंग सेशन, व्याख्यान कार्यक्रम और ऐप एक्सेस विवरण अलग से एक अन्य ईमेल के माध्यम से साझा किए जाएंगे।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">हार्दिक स्वागत,<br><strong>प्रमुख संकल्प टीम</strong><br>प्रमुख एकेडमी</p>' . $wrapperEnd,
                'title_gu' => 'પ્રમુખ સંકલ્પમાં આપનું સ્વાગત છે - પોર્ટલ એક્સેસ વિગતો',
                'content_gu' => $wrapperStart . '<p>નમસ્તે સંકલ્પ વાલી <strong>{{guardian_name}}</strong>,</p><p>અભિનંદન!</p><p>વિદ્યાર્થીને પ્રમુખ સંકલ્પ પ્રોગ્રામમાં સફળતાપૂર્વક દાખલ કરવામાં આવ્યો છે.</p><p>આ સંકલ્પ એસ્પિરન્ટ - <strong>{{student_name}}</strong> ની વિકસિત ભારત માટે સદ્ગુણી પરિવર્તનકર્તા બનવા તરફની સંકલ્પ યાત્રાની શરૂઆત દર્શાવે છે.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">સંકલ્પ પોર્ટલ લોગિન વિગતો</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">પોર્ટલ લિંક:</td><td style="padding: 6px 0; color: #2d3748;"><a href="{{portal_link}}" style="color: #00bde0; text-decoration: none; font-weight: 600;">{{portal_link}}</a></td></tr><tr><td style="padding: 6px 0; color: #718096;">સંકલ્પ ID નંબર:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{enrolment_no}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">પોર્ટલ પાસવર્ડ (તમે પછીથી બદલી શકો છો):</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{lmspswd}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">સબ્સ્ક્રિપ્શન માન્યતા:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{course_validity}}</td></tr></table></div><p>સંકલ્પ પોર્ટલ દ્વારા, તમે તમારી વિગતો અપડેટ કરી શકશો, અભ્યાસ સામગ્રીની સ્થિતિ ટ્રેક કરી શકશો, ચુકવણીની સ્થિતિ તપાસી શકશો, ગ્રોથ કાર્ડ જોઈ શકશો અને અન્ય મહત્વપૂર્ણ પ્રોગ્રામ સંબંધિત માહિતી મેનેજ કરી શકશો.</p><p>મહેરબાની કરીને નોંધ કરો: ઓનલાઇન લર્નિંગ સેશન, લેક્ચર શેડ્યૂલ અને LMS એપ્લિકેશન એક્સેસ વિગતો અન્ય ઇમેઇલ દ્વારા અલગથી શેર કરવામાં આવશે.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">હાર્દિક સ્વાગત,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong><br>પ્રમુખ એકેડેમી</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'student_rejection',
                'title_en' => 'SANKALP Programme Registration Update',
                'content_en' => $wrapperStart . '<p>Namaste SANKALP Guardian <strong>{{guardian_name}}</strong>,</p><p>Thank you for showing interest in the Pramukh SANKALP Programme.</p><p>After carefully reviewing the submitted details, we sincerely regret to inform you that your registration cannot be processed at this stage.</p><p style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #9b2c2c;"><strong>Reason:</strong> {{reason}}</p><p>We truly appreciate your trust in Pramukh SANKALP Programme. We sincerely pray for <strong>{{student_name}}</strong>’s continuous growth, success, and bright future.</p><p>Since the registration cannot be processed, the payment made for registration will be refunded to the original payment account as per the payment gateway/bank processing timeline.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Payment / Refund Details</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">Payment Amount:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{payment_amount}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Transaction ID:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{transaction_id}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Refund Status:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{refund_status}}</td></tr></table></div><p>Thank you for your understanding.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm regards,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'title_hi' => 'संकल्प कार्यक्रम पंजीकरण अपडेट',
                'content_hi' => $wrapperStart . '<p>नमस्ते संकल्प अभिभावक <strong>{{guardian_name}}</strong>,</p><p>प्रमुख संकल्प कार्यक्रम में रुचि दिखाने के लिए धन्यवाद।</p><p>प्रस्तुत विवरणों की सावधानीपूर्वक समीक्षा करने के बाद, हमें अत्यंत खेद के साथ सूचित करना पड़ रहा है कि इस स्तर पर आपके पंजीकरण की प्रक्रिया आगे नहीं बढ़ाई जा सकती है।</p><p style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #9b2c2c;"><strong>कारण:</strong> {{reason}}</p><p>हम प्रमुख संकल्प कार्यक्रम में आपके विश्वास की सराहना करते हैं। हम ईमानदारी से <strong>{{student_name}}</strong> के निरंतर विकास, सफलता और उज्ज्वल भविष्य की प्रार्थना करते हैं।</p><p>चूंकि पंजीकरण संसाधित नहीं किया जा सकता है, इसलिए पंजीकरण के लिए किया गया भुगतान गेटवे/बैंक की समय-सीमा के अनुसार मूल भुगतान खाते में वापस (Refund) कर दिया जाएगा।</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">भुगतान / धनवापसी (Refund) विवरण</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">भुगतान राशि:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{payment_amount}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">लेन-देन आईडी:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{transaction_id}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">वापसी की स्थिति (Refund Status):</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{refund_status}}</td></tr></table></div><p>आपकी समझ के लिए धन्यवाद।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">सादर,<br><strong>प्रमुख संकल्प टीम</strong><br>प्रमुख एकेडमी</p>' . $wrapperEnd,
                'title_gu' => 'સંકલ્પ પ્રોગ્રામ નોંધણી અપડેટ',
                'content_gu' => $wrapperStart . '<p>નમસ્તે સંકલ્પ વાલી <strong>{{guardian_name}}</strong>,</p><p>પ્રમુખ સંકલ્પ પ્રોગ્રામમાં રસ દાખવવા બદલ આભાર.</p><p>સબમિટ કરેલી વિગતોની કાળજીપૂર્વક સમીક્ષા કર્યા પછી, અમે દિલગીરી સાથે જણાવવા માંગીએ છીએ કે આ તબક્કે તમારી નોંધણી પ્રક્રિયા આગળ વધારી શકાશે નહીં.</p><p style="background-color: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #9b2c2c;"><strong>કારણ:</strong> {{reason}}</p><p>અમે પ્રમુખ સંકલ્પ પ્રોગ્રામમાં તમારા વિશ્વાસની પ્રશંસા કરીએ છીએ. અમે <strong>{{student_name}}</strong> ના સતત વિકાસ, સફળતા અને ઉજ્જવળ ભવિષ્ય માટે પ્રાર્થના કરીએ છીએ.</p><p>નોંધણી પ્રક્રિયા આગળ ન વધવાને કારણે, રજીસ્ટ્રેશન માટે કરાયેલ પેમેન્ટ મૂળ પેમેન્ટ ખાતામાં રીફંડ કરી દેવામાં આવશે.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">પેમેન્ટ / રીફંડ વિગતો</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">પેમેન્ટ રકમ:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{payment_amount}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">ટ્રાન્ઝેક્શન ID:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{transaction_id}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">રીફંડ સ્ટેટસ:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{refund_status}}</td></tr></table></div><p>સમજણ બદલ આભાર.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">સાદર,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong><br>પ્રમુખ એકેડેમી</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'study_material_dispatch',
                'title_en' => 'SANKALP Study Material & Handouts Dispatched',
                'content_en' => $wrapperStart . '<p>Namaste SANKALP Guardian <strong>{{guardian_name}}</strong>,</p><p>The study material for SANKALP <strong>{{current_semester}}</strong> has been dispatched.</p><p>This material will support SANKALP Aspirant - <strong>{{student_name}}</strong> in the SANKALP journey through handout-based learning, activities, reflection tasks, and programme-related practice.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Dispatch Details</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">Courier Partner:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{courier_partner}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Tracking ID:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{tracking_id}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Dispatch Date:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{dispatch_date}}</td></tr></table></div><div style="text-align: center; margin: 25px 0;"><a href="{{tracking_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">Track Shipment</a></div><p style="font-size: 13px; color: #718096; text-align: center;">If the button/link does not open, please copy and paste the following link into your web browser:<br><a href="{{tracking_link}}" style="color: #00bde0; text-decoration: none;">{{tracking_link}}</a></p><p style="background-color: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #2b6cb0;">Please keep the study material safely, as it will be used regularly during the programme.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Wishes for best learning,<br><strong>Pramukh SANKALP Team</strong></p>' . $wrapperEnd,
                'title_hi' => 'संकल्प अध्ययन सामग्री और हैंडआउट भेजे गए',
                'content_hi' => $wrapperStart . '<p>नमस्ते संकल्प अभिभावक <strong>{{guardian_name}}</strong>,</p><p>संकल्प <strong>{{current_semester}}</strong> के लिए अध्ययन सामग्री भेज (dispatch) दी गई है।</p><p>यह सामग्री संकल्प आकांक्षी - <strong>{{student_name}}</strong> को हैंडआउट-आधारित शिक्षा, गतिविधियों, चिंतन कार्यों और अभ्यास के माध्यम से उनकी संकल्प यात्रा में सहायता करेगी।</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">प्रेषण (Dispatch) विवरण</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">कूरियर पार्टनर:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{courier_partner}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">ट्रैकिंग आईडी:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{tracking_id}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">प्रेषण तिथि:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{dispatch_date}}</td></tr></table></div><div style="text-align: center; margin: 25px 0;"><a href="{{tracking_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">शिपमेंट ट्रैक करें</a></div><p style="font-size: 13px; color: #718096; text-align: center;">यदि बटन/लिंक काम नहीं करता है, तो कृपया इस लिंक को ब्राउज़र में पेस्ट करें:<br><a href="{{tracking_link}}" style="color: #00bde0; text-decoration: none;">{{tracking_link}}</a></p><p style="background-color: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #2b6cb0;">कृपया अध्ययन सामग्री सुरक्षित रखें, क्योंकि कार्यक्रम के दौरान इसका नियमित रूप से उपयोग किया जाएगा।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">सीखने की शुभकामनाओं के साथ,<br><strong>प्रमुख संकल्प टीम</strong></p>' . $wrapperEnd,
                'title_gu' => 'સંકલ્પ અભ્યાસ સામગ્રી અને હેન્ડઆઉટ્સ મોકલેલ છે',
                'content_gu' => $wrapperStart . '<p>નમસ્તે સંકલ્પ વાલી <strong>{{guardian_name}}</strong>,</p><p>સંકલ્પ <strong>{{current_semester}}</strong> માટેનું સ્ટડી મટિરિયલ મોકલી (dispatch) દેવામાં આવ્યું છે.</p><p>આ મટિરિયલ સંકલ્પ એસ્પિરન્ટ - <strong>{{student_name}}</strong> ને હેન્ડઆઉટ-આધારિત લર્નિંગ, પ્રવૃત્તિઓ અને પ્રેક્ટિસ દ્વારા તેમની સંકલ્પ યાત્રામાં મદદરૂપ થશે.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">ડિસ્પેચ વિગતો</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">કુરિયર પાર્ટનર:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{courier_partner}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">ટ્રેકિંગ ID:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{tracking_id}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">ડિસ્પેચ તારીખ:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{dispatch_date}}</td></tr></table></div><div style="text-align: center; margin: 25px 0;"><a href="{{tracking_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">ટ્રેક શિપમેન્ટ</a></div><p style="font-size: 13px; color: #718096; text-align: center;">જો બટન/લિંક કામ ન કરે, તો કૃપા કરીને આ લિંકને બ્રાઉઝરમાં પેસ્ટ કરો:<br><a href="{{tracking_link}}" style="color: #00bde0; text-decoration: none;">{{tracking_link}}</a></p><p style="background-color: #ebf8ff; border-left: 4px solid #3182ce; padding: 15px; margin: 20px 0; border-radius: 0 8px 8px 0; font-size: 14px; color: #2b6cb0;">કૃપા કરીને આ અભ્યાસ સામગ્રી સાચવીને રાખો, કારણ કે આ પ્રોગ્રામ દરમિયાન તેનો નિયમિત ઉપયોગ થશે.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">લર્નિંગની શુભકામનાઓ સાથે,<br><strong>પ્રમુખ સંકલ્પ ટીમ</strong></p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        // Delete all other templates not matching our standardized ones
        DB::table('mail_templates')
            ->whereNotIn('identifier', [
                'pre_registration_otp',
                'payment_reminder',
                'payment_success',
                'student_welcome',
                'student_rejection',
                'study_material_dispatch',
                'student_batch_shift',
                'semester_renewal_reminder'
            ])->delete();

        foreach ($templates as $template) {
            $exists = DB::table('mail_templates')->where('identifier', $template['identifier'])->exists();
            DB::table('mail_templates')->updateOrInsert(
                ['identifier' => $template['identifier']],
                $exists ? ['updated_at' => now()] : $template
            );
        }
        $this->command->info('- Seeded initial mail templates.');

        $this->command->info('Mail Template module permissions and templates setup completed.');
    }
}

class Update20260615SemesterBatchModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-06-15
     * Task: Add Semester Batch display_name and is_school_batch fields support.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for Semester Batch Module...');

        // Update existing semester batches with default display_name if null
        $batchesWithoutDisplayName = DB::table('sembatches')
            ->whereNull('display_name')
            ->get();

        foreach ($batchesWithoutDisplayName as $batch) {
            $displayName = 'Semester ' . $batch->sembatches_id;
            DB::table('sembatches')
                ->where('sembatches_id', $batch->sembatches_id)
                ->update([
                    'display_name' => $displayName,
                    'is_school_batch' => 0,
                ]);
        }
        $this->command->info('- Updated ' . count($batchesWithoutDisplayName) . ' semester batches with default display_name and is_school_batch values.');

        $this->command->info('Semester Batch module data update completed.');
    }
}

class Update20260626StudentHistoryModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-06-26
     * Task: Add Student History module permissions and default templates.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-06-26 (Student History Module)...');

        $permissions = [
            ['permkey' => 11401, 'permname' => 'Read Student History'],
            ['permkey' => 11402, 'permname' => 'Write Student History'],
            ['permkey' => 11403, 'permname' => 'Manage Student History'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Student History).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [11401, 11402, 11403];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Student History permissions.');
        }

        // Add default mail templates for batch shift and renewal reminder
        $wrapperStart = '<div style="font-family: \'Outfit\', \'Inter\', sans-serif; background-color: #f7f9fa; padding: 30px 15px; margin: 0; width: 100%;"><div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #00bde0;"><div style="background-color: #ffffff; padding: 25px; text-align: center; border-bottom: 1px solid #edf2f7;"><h1 style="color: #1a202c; font-size: 22px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">PRAMUKH SANKALP</h1><p style="color: #718096; font-size: 13px; margin: 5px 0 0 0; letter-spacing: 1px;">PRAMUKH ACADEMY</p></div><div style="padding: 30px 25px; color: #2d3748; line-height: 1.6; font-size: 15px;">';
        $wrapperEnd = '</div></div></div>';

        $templates = [
            [
                'identifier' => 'student_batch_shift',
                'title_en' => 'Student Semester Batch Shifted – Pramukh SANKALP',
                'content_en' => $wrapperStart . '<p>Dear SANKALP Guardian <strong>{{guardian_name}}</strong>,</p><p>This is to inform you that your ward, <strong>{{student_name}}</strong>, has been successfully shifted/migrated to a new semester batch in the Pramukh SANKALP Programme.</p><div style="background-color: #f7fafc; border-radius: 8px; padding: 20px; margin: 20px 0; border: 1px solid #edf2f7;"><h3 style="margin-top: 0; color: #2d3748; font-size: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Migration Details</h3><table style="width: 100%; border-collapse: collapse; font-size: 14px;"><tr><td style="padding: 6px 0; color: #718096; width: 40%;">Previous Batch/Semester:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{previous_semester}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">New Batch/Semester:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{new_semester}}</td></tr><tr><td style="padding: 6px 0; color: #718096;">Migration Date:</td><td style="padding: 6px 0; color: #2d3748; font-weight: 600;">{{migration_date}}</td></tr></table></div><p>The SANKALP App and portal access details remain the same. The student can log in using their existing SANKALP ID Number: <strong>{{enrolment_no}}</strong> to access the new semester\'s content and growth cards.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm regards,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'semester_renewal_reminder',
                'title_en' => 'Reminder: Renew Registration for Next Semester – Pramukh SANKALP',
                'content_en' => $wrapperStart . '<p>Namaste SANKALP Guardian <strong>{{guardian_name}}</strong>,</p><p>We hope <strong>{{student_name}}</strong> is having a wonderful and enriching learning experience in the current semester of the Pramukh SANKALP Programme.</p><p>As the current semester is nearing completion, it is time to renew and enroll for the next semester: <strong>{{next_semester}}</strong>, to ensure continuous learning and growth without any interruptions.</p><div style="text-align: center; margin: 25px 0;"><a href="{{renewal_link}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 15px; box-shadow: 0 4px 6px rgba(0,189,224,0.2);">Renew / Enroll Now</a></div><p style="font-size: 13px; color: #718096; text-align: center;">Or copy and paste this link into your web browser:<br><a href="{{renewal_link}}" style="color: #00bde0; text-decoration: none;">{{renewal_link}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">Warm regards,<br><strong>Pramukh SANKALP Team</strong><br>Pramukh Academy</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        foreach ($templates as $template) {
            $exists = DB::table('mail_templates')->where('identifier', $template['identifier'])->exists();
            DB::table('mail_templates')->updateOrInsert(
                ['identifier' => $template['identifier']],
                $exists ? ['updated_at' => now()] : $template
            );
        }
        $this->command->info('- Seeded batch migration and renewal mail templates.');

        $this->command->info('Student History module setup completed.');
    }
}

class Update20260701LaravelLogsModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-07-01
     * Task: Add Laravel Logs module permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-07-01 (Laravel Logs Module)...');

        $permissions = [
            ['permkey' => 11501, 'permname' => 'Read Laravel Logs'],
            ['permkey' => 11502, 'permname' => 'Clear Laravel Logs'],
            ['permkey' => 11503, 'permname' => 'Manage Laravel Logs'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Laravel Logs).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [11501, 11502, 11503];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Laravel Logs permissions.');
        }

        $this->command->info('Laravel Logs module setup completed.');
    }
}

class Update20260627MigratedStudentPermission extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-06-27 (Migrated Student Module)...');

        $permissions = [
            ['permkey' => 11404, 'permname' => 'Migrate Students Data'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- New permissions ensured (Migrate Students Data).');

        // Update Admin Role permissions
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            $adminPermKeys = [11404];
            foreach ($adminPermKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Migrate Students Data permissions.');
        }
    }
}

class Update20260704SchoolRegistrationModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-07-04
     * Task: Assign Registration Review permission to School role.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-07-04 (School Registration Module)...');

        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        if ($schoolRole) {
            $permission = DB::table('permission')->where('permkey', 11201)->first();
            if ($permission) {
                DB::table('rolepermission')->updateOrInsert(
                    ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id],
                    ['role_id' => $schoolRole->role_id, 'permission_id' => $permission->permission_id]
                );
                $this->command->info('- School role granted Registration Review permission (11201).');
            } else {
                $this->command->error('- Permission 11201 not found.');
            }
        } else {
            $this->command->error('- School role not found.');
        }

        // Associate school@example.com with the first available school for manual testing
        $testUser = DB::table('sysuser')->where('email', 'school@example.com')->first();
        if ($testUser) {
            $school = DB::table('school')->first();
            if ($school) {
                DB::table('userschool')->updateOrInsert(
                    ['sysuser_id' => $testUser->sysuser_id, 'school_id' => $school->school_id],
                    ['sysuser_id' => $testUser->sysuser_id, 'school_id' => $school->school_id]
                );
                $this->command->info("- Assigned school {$school->schoolname} to test user school@example.com.");
            }
        }

        $this->command->info('School Registration module seeder setup completed.');
    }
}

class Update20260709SchoolEnhancementsModule extends Seeder
{
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-07-09 (School Admin Module Enhancements)...');

        // 1. Ensure Educator role exists
        DB::table('role')->updateOrInsert(
            ['rolename' => 'Educator'],
            ['rolename' => 'Educator', 'ispredefined' => true, 'created_at' => now()]
        );
        $this->command->info('- Educator role ensured.');

        // 2. Ensure new Educator/School permissions exist
        $permissions = [
            ['permkey' => 11601, 'permname' => 'Fee Collection'],
            ['permkey' => 11602, 'permname' => 'Registration Process'],
            ['permkey' => 11603, 'permname' => 'Form Generation'],
            ['permkey' => 11604, 'permname' => 'Bulk Upload'],
            ['permkey' => 11605, 'permname' => 'Student Approve'],
            ['permkey' => 11606, 'permname' => 'Student Reject'],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                array_merge($permission, ['created_at' => now()])
            );
        }
        $this->command->info('- Educator/School permissions ensured.');

        // 3. Assign to Admin role
        $adminRole = DB::table('role')->where('rolename', 'Admin')->first();
        if ($adminRole) {
            foreach ($permissions as $permission) {
                $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();
                if ($perm) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $adminRole->role_id, 'permission_id' => $perm->permission_id],
                        ['role_id' => $adminRole->role_id, 'permission_id' => $perm->permission_id]
                    );
                }
            }
            $this->command->info('- Admin role granted Educator permissions.');
        }

        // 4. Assign to School role
        $schoolRole = DB::table('role')->where('rolename', 'School')->first();
        if ($schoolRole) {
            foreach ($permissions as $permission) {
                $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();
                if ($perm) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $perm->permission_id],
                        ['role_id' => $schoolRole->role_id, 'permission_id' => $perm->permission_id]
                    );
                }
            }
            $this->command->info('- School role granted Educator permissions.');
        }

        // 5. Assign to Educator role
        $educatorRole = DB::table('role')->where('rolename', 'Educator')->first();
        if ($educatorRole) {
            foreach ($permissions as $permission) {
                $perm = DB::table('permission')->where('permkey', $permission['permkey'])->first();
                if ($perm) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $educatorRole->role_id, 'permission_id' => $perm->permission_id],
                        ['role_id' => $educatorRole->role_id, 'permission_id' => $perm->permission_id]
                    );
                }
            }
            $this->command->info('- Educator role granted Educator permissions.');
        }

        $this->command->info('School Enhancements Module seeder setup completed.');
    }
}

class Update20260713MigratedStudentMailTemplates extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-07-13
     * Task: Add migrated batch completion and reminder templates.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-07-13 (Migrated Student Mail Templates)...');

        $wrapperStart = '<div style="font-family: \'Outfit\', \'Inter\', sans-serif; background-color: #f7f9fa; padding: 30px 15px; margin: 0; width: 100%;"><div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-top: 5px solid #00bde0;"><div style="background-color: #ffffff; padding: 25px; text-align: center; border-bottom: 1px solid #edf2f7;"><h1 style="color: #1a202c; font-size: 22px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">PRAMUKH SANKALP</h1><p style="color: #718096; font-size: 13px; margin: 5px 0 0 0; letter-spacing: 1px;">PRAMUKH ACADEMY</p></div><div style="padding: 30px 25px; color: #2d3748; line-height: 1.6; font-size: 15px;">';
        $wrapperEnd = '</div></div></div>';

        $templates = [
            [
                'identifier' => 'auto_enrolment_sem2',
                'title_en' => 'You\'re Automatically Enrolled in Sem 02 — Pramukh SANKALP Programme',
                'content_en' => $wrapperStart . '<p>Dear {{student_fname}},</p><p>Congratulations on completing Sem 01 of the Pramukh SANKALP Programme!</p><p>Since you have already paid for the full year (Sem 01 & Sem 02), you are automatically enrolled in Sem 02. No further payment is required at this stage.</p><p>Your Sem 02 details will be updated on your learning portal shortly. Please log in to check your schedule and materials for the upcoming semester.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">For any questions, contact us at +91 951 006 0898.</p>' . $wrapperEnd,
                'title_hi' => 'आप स्वचालित रूप से सेम 02 में नामांकित हैं — प्रमुख संकल्प कार्यक्रम',
                'content_hi' => $wrapperStart . '<p>प्रिय {{student_fname}},</p><p>प्रमुख संकल्प कार्यक्रम के सेम 01 को पूरा करने पर बधाई!</p><p>चूंकि आपने पूरे वर्ष (सेम 01 और सेम 02) के लिए पहले ही भुगतान कर दिया है, इसलिए आप स्वचालित रूप से सेम 02 में नामांकित हो गए हैं। इस स्तर पर आगे किसी भुगतान की आवश्यकता नहीं है।</p><p>आपके सेम 02 का विवरण जल्द ही आपके लर्निंग पोर्टल पर अपडेट कर दिया जाएगा। आगामी सेमेस्टर के लिए अपना शेड्यूल और सामग्री देखने के लिए कृपया लॉग इन करें।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">किसी भी प्रश्न के लिए, हमसे +91 951 006 0898 पर संपर्क करें।</p>' . $wrapperEnd,
                'title_gu' => 'તમે સેમ 02 માં આપમેળે નોંધાયેલા છો — પ્રમુખ સંકલ્પ કાર્યક્રમ',
                'content_gu' => $wrapperStart . '<p>પ્રિય {{student_fname}},</p><p>પ્રમુખ સંકલ્પ કાર્યક્રમનું સેમેસ્ટર 01 પૂર્ણ કરવા બદલ અભિનંદન!</p><p>તમે આખા વર્ષ માટે (સેમ 01 અને સેમ 02) પહેલેથી જ ચૂકવણી કરી દીધી હોવાથી, તમે સેમ 02 માં આપમેળે નોંધાઈ ગયા છો. આ તબક્કે આગળ કોઈ ચૂકવણીની જરૂર નથી.</p><p>તમારા સેમ 02 ની વિગતો ટૂંક સમયમાં તમારા લર્નિંગ પોર્ટલ પર અપડેટ કરવામાં આવશે. આગામી સેમેસ્ટર માટે તમારું શિડ્યુઅલ અને મટિરિયલ્સ જોવા માટે કૃપા કરીને લોગિન કરો.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">કોઈપણ પ્રશ્ન માટે, અમારો સંપર્ક કરો +91 951 006 0898 પર.</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'payment_required_sem2',
                'title_en' => 'Sem 01 Completed — Continue to Sem 02 · Pramukh SANKALP Programme',
                'content_en' => $wrapperStart . '<p>Dear {{student_fname}},</p><p>Congratulations on successfully completing Sem 01 of the Pramukh SANKALP Programme!</p><p>To continue your journey in Sem 02, your next payment is now due. Please choose the plan that works best for you:</p><p><strong>Option 1 — Sem 02 Only</strong><br>Continue for one semester. Fee: ₹{{sem2_fee}}<br><a href="{{payment_link_opt1}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">Pay ₹{{sem2_fee}}</a></p><p><strong>Option 2 — Sem 02 & Sem 03 (Full Year)</strong><br>Continue for the full year at a combined rate. Fee: ₹{{sem2_sem3_fee}}<br><a href="{{payment_link_opt2}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">Pay ₹{{sem2_sem3_fee}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">For any questions, contact us at +91 951 006 0898.</p>' . $wrapperEnd,
                'title_hi' => 'सेम 01 पूर्ण — सेम 02 जारी रखने के लिए भुगतान आवश्यक · प्रमुख संकल्प कार्यक्रम',
                'content_hi' => $wrapperStart . '<p>प्रिय {{student_fname}},</p><p>प्रमुख संकल्प कार्यक्रम के सेम 01 को सफलतापूर्वक पूरा करने पर बधाई!</p><p>सेम 02 में अपनी यात्रा जारी रखने के लिए, आपका अगला भुगतान अब देय है। कृपया वह योजना चुनें जो आपके लिए सबसे उपयुक्त हो:</p><p><strong>विकल्प 1 — केवल सेम 02</strong><br>एक सेमेस्टर के लिए जारी रखें। शुल्क: ₹{{sem2_fee}}<br><a href="{{payment_link_opt1}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">भुगतान करें ₹{{sem2_fee}}</a></p><p><strong>विकल्प 2 — सेम 02 और सेम 03 (पूरा वर्ष)</strong><br>संयुक्त दर पर पूरे वर्ष के लिए जारी रखें। शुल्क: ₹{{sem2_sem3_fee}}<br><a href="{{payment_link_opt2}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">भुगतान करें ₹{{sem2_sem3_fee}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">किसी भी प्रश्न के लिए, हमसे +91 951 006 0898 पर संपर्क करें।</p>' . $wrapperEnd,
                'title_gu' => 'સેમ 01 પૂર્ણ — સેમ 02 ચાલુ રાખવા માટે ચૂકવણી જરૂરી · પ્રમુખ સંકલ્પ કાર્યક્રમ',
                'content_gu' => $wrapperStart . '<p>પ્રિય {{student_fname}},</p><p>પ્રમુખ સંકલ્પ કાર્યક્રમનું સેમેસ્ટર 01 સફળતાપૂર્વક પૂર્ણ કરવા બદલ અભિનંદન!</p><p>સેમ 02 માં તમારી સફર ચાલુ રાખવા માટે, તમારી આગામી ચૂકવણી હવે બાકી છે. કૃપા કરીને તમારા માટે શ્રેષ્ઠ કામ કરતો પ્લાન પસંદ કરો:</p><p><strong>વિકલ્પ 1 — ફક્ત સેમ 02</strong><br>એક સેમેસ્ટર માટે ચાલુ રાખો. ફી: ₹{{sem2_fee}}<br><a href="{{payment_link_opt1}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">ચૂકવણી કરો ₹{{sem2_fee}}</a></p><p><strong>વિકલ્પ 2 — સેમ 02 અને સેમ 03 (આખું વર્ષ)</strong><br>સંયુક્ત દરે આખા વર્ષ માટે ચાલુ રાખો. ફી: ₹{{sem2_sem3_fee}}<br><a href="{{payment_link_opt2}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">ચૂકવણી કરો ₹{{sem2_sem3_fee}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">કોઈપણ પ્રશ્ન માટે, અમારો સંપર્ક કરો +91 951 006 0898 પર.</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'auto_enrolment_sem5',
                'title_en' => 'You\'re Automatically Enrolled in Sem 05 — Pramukh SANKALP Programme',
                'content_en' => $wrapperStart . '<p>Dear {{student_fname}},</p><p>Congratulations on completing Sem 04 of the Pramukh SANKALP Programme!</p><p>Since you have already paid for the full year (Sem 04 & Sem 05), you are automatically enrolled in Sem 05. No further payment is required at this stage.</p><p>Your Sem 05 details will be updated on your learning portal shortly. Please log in to check your schedule and materials for the upcoming semester.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">For any questions, contact us at +91 951 006 0898.</p>' . $wrapperEnd,
                'title_hi' => 'आप स्वचालित रूप से सेम 05 में नामांकित हैं — प्रमुख संकल्प कार्यक्रम',
                'content_hi' => $wrapperStart . '<p>प्रिय {{student_fname}},</p><p>प्रमुख संकल्प कार्यक्रम के सेम 04 को पूरा करने पर बधाई!</p><p>चूंकि आपने पूरे वर्ष (सेम 04 और सेम 05) के लिए पहले ही भुगतान कर दिया है, इसलिए आप स्वचालित रूप से सेम 05 में नामांकित हो गए हैं। इस स्तर पर आगे किसी भुगतान की आवश्यकता नहीं है।</p><p>आपके सेम 05 का विवरण जल्द ही आपके लर्निंग पोर्टल पर अपडेट कर दिया जाएगा। आगामी सेमेस्टर के लिए अपना शेड्यूल और सामग्री देखने के लिए कृपया लॉग इन करें।</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">किसी भी प्रश्न के लिए, हमसे +91 951 006 0898 पर संपर्क करें।</p>' . $wrapperEnd,
                'title_gu' => 'તમે સેમ 05 માં આપમેળે નોંધાયેલા છો — પ્રમુખ સંકલ્પ કાર્યક્રમ',
                'content_gu' => $wrapperStart . '<p>પ્રિય {{student_fname}},</p><p>પ્રમુખ સંકલ્પ કાર્યક્રમનું સેમેસ્ટર 04 પૂર્ણ કરવા બદલ અભિનંદન!</p><p>તમે આખા વર્ષ માટે (સેમ 04 અને સેમ 05) પહેલેથી જ ચૂકવણી કરી દીધી હોવાથી, તમે સેમ 05 માં આપમેળે નોંધાઈ ગયા છો. આ તબક્કે આગળ કોઈ ચૂકવણીની જરૂર નથી.</p><p>તમારા સેમ 05 ની વિગતો ટૂંક સમયમાં તમારા લર્નિંગ પોર્ટલ પર અપડેટ કરવામાં આવશે. આગામી સેમેસ્ટર માટે તમારું શિડ્યુઅલ અને મટિરિયલ્સ જોવા માટે કૃપા કરીને લોગિન કરો.</p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">કોઈપણ પ્રશ્ન માટે, અમારો સંપર્ક કરો +91 951 006 0898 પર.</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'identifier' => 'payment_required_sem5',
                'title_en' => 'Sem 04 Completed — Continue to Sem 05 · Pramukh SANKALP Programme',
                'content_en' => $wrapperStart . '<p>Dear {{student_fname}},</p><p>Congratulations on successfully completing Sem 04 of the Pramukh SANKALP Programme!</p><p>To continue your journey in Sem 05, your next payment is now due. Please choose the plan that works best for you:</p><p><strong>Option 1 — Sem 05 Only</strong><br>Continue for one semester. Fee: ₹{{sem5_fee}}<br><a href="{{payment_link_opt1}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">Pay ₹{{sem5_fee}}</a></p><p><strong>Option 2 — Sem 05 & Sem 06 (Full Year)</strong><br>Continue for the full year at a combined rate. Fee: ₹{{sem5_sem6_fee}}<br><a href="{{payment_link_opt2}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">Pay ₹{{sem5_sem6_fee}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">For any questions, contact us at +91 951 006 0898.</p>' . $wrapperEnd,
                'title_hi' => 'सेम 04 पूर्ण — सेम 05 जारी रखने के लिए भुगतान आवश्यक · प्रमुख संकल्प कार्यक्रम',
                'content_hi' => $wrapperStart . '<p>प्रिय {{student_fname}},</p><p>प्रमुख संकल्प कार्यक्रम के सेम 04 को सफलतापूर्वक पूरा करने पर बधाई!</p><p>सेम 05 में अपनी यात्रा जारी रखने के लिए, आपका अगला भुगतान अब देय है। कृपया वह योजना चुनें जो आपके लिए सबसे उपयुक्त हो:</p><p><strong>विकल्प 1 — केवल सेम 05</strong><br>एक सेमेस्टर के लिए जारी रखें। शुल्क: ₹{{sem5_fee}}<br><a href="{{payment_link_opt1}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">भुगतान करें ₹{{sem5_fee}}</a></p><p><strong>विकल्प 2 — सेम 05 और सेम 06 (पूरा वर्ष)</strong><br>संयुक्त दर पर पूरे वर्ष के लिए जारी रखें। शुल्क: ₹{{sem5_sem6_fee}}<br><a href="{{payment_link_opt2}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">भुगतान करें ₹{{sem5_sem6_fee}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">किसी भी प्रश्न के लिए, हमसे +91 951 006 0898 पर संपर्क करें।</p>' . $wrapperEnd,
                'title_gu' => 'સેમ 04 પૂર્ણ — સેમ 05 ચાલુ રાખવા માટે ચૂકવણી જરૂરી · પ્રમુખ સંકલ્પ કાર્યક્રમ',
                'content_gu' => $wrapperStart . '<p>પ્રિય {{student_fname}},</p><p>પ્રમુખ સંકલ્પ કાર્યક્રમનું સેમેસ્ટર 04 સફળતાપૂર્વક પૂર્ણ કરવા બદલ અભિનંદન!</p><p>સેમ 05 માં તમારી સફર ચાલુ રાખવા માટે, તમારી આગામી ચૂકવણી હવે બાકી છે. કૃપા કરીને તમારા માટે શ્રેષ્ઠ કામ કરતો પ્લાન પસંદ કરો:</p><p><strong>વિકલ્પ 1 — ફક્ત સેમ 05</strong><br>એક સેમેસ્ટર માટે ચાલુ રાખો. ફી: ₹{{sem5_fee}}<br><a href="{{payment_link_opt1}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">ચૂકવણી કરો ₹{{sem5_fee}}</a></p><p><strong>વિકલ્પ 2 — સેમ 05 અને સેમ 06 (આખું વર્ષ)</strong><br>સંયુક્ત દરે આખા વર્ષ માટે ચાલુ રાખો. ફી: ₹{{sem5_sem6_fee}}<br><a href="{{payment_link_opt2}}" style="display: inline-block; background-color: #00bde0; color: #ffffff; padding: 8px 18px; text-decoration: none; border-radius: 6px; font-weight: bold; font-size: 14px; margin-top: 5px;">ચૂકવણી કરો ₹{{sem5_sem6_fee}}</a></p><p style="margin-top: 25px; border-top: 1px solid #edf2f7; padding-top: 15px;">કોઈપણ પ્રશ્ન માટે, અમારો સંપર્ક કરો +91 951 006 0898 પર.</p>' . $wrapperEnd,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($templates as $template) {
            $exists = DB::table('mail_templates')->where('identifier', $template['identifier'])->exists();
            DB::table('mail_templates')->updateOrInsert(
                ['identifier' => $template['identifier']],
                $exists ? ['updated_at' => now()] : $template
            );
        }

        $this->command->info('- Seeded migrated student completion and reminder mail templates.');
    }
}

class Update20260724SchoolCoordinatorModule extends Seeder
{
    /**
     * Run the database seeds for production updates.
     * Date: 2026-07-24
     * Task: Add School Coordinator role and assign appropriate permissions.
     */
    public function run(): void
    {
        $this->command->info('Starting production data update for 2026-07-24 (School Coordinator Module)...');

        // 1. Ensure 'School Coordinator' role exists
        DB::table('role')->updateOrInsert(
            ['rolename' => 'School Coordinator'],
            ['rolename' => 'School Coordinator', 'ispredefined' => true, 'created_at' => now()]
        );
        $this->command->info('- School Coordinator role ensured.');

        // 2. Fetch School Coordinator role object to associate permissions
        $coordRole = DB::table('role')->where('rolename', 'School Coordinator')->first();
        if ($coordRole) {
            // Assign necessary permissions
            $permKeys = [
                10051,
                10031, 10032, 10033,
                10041, 10042, 10043,
                10061, 10062,
                10071, 10072,
                10081, 10082, 10083,
                10091, 10092,
                10101, 10102,
                10151,
                10161, 10162, 10163, // Fee Plans & School Fee Plans
                10021,                // Read Users (required to view users list and access initiator payment info)
                10301, 10302,         // Initiator Financials (Initiator Fee Plans and Initiator Payments)
                10171, 10172, 10173   // Discount Rules
            ];

            foreach ($permKeys as $permKey) {
                $permission = DB::table('permission')->where('permkey', $permKey)->first();
                if ($permission) {
                    DB::table('rolepermission')->updateOrInsert(
                        ['role_id' => $coordRole->role_id, 'permission_id' => $permission->permission_id],
                        ['role_id' => $coordRole->role_id, 'permission_id' => $permission->permission_id]
                    );
                }
            }
            $this->command->info('- School Coordinator role permissions assigned.');
        }
    }
}



