<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Roles Module
            ['permkey' => 10001, 'permname' => 'Read Roles', 'created_at' => now()],
            ['permkey' => 10002, 'permname' => 'Write Roles', 'created_at' => now()],
            ['permkey' => 10003, 'permname' => 'Manage Roles', 'created_at' => now()],

            // Permissions Module
            ['permkey' => 10011, 'permname' => 'Read Permissions', 'created_at' => now()],
            ['permkey' => 10012, 'permname' => 'Write Permissions', 'created_at' => now()],
            ['permkey' => 10013, 'permname' => 'Manage Permissions', 'created_at' => now()],

            // Users Module
            ['permkey' => 10021, 'permname' => 'Read Users', 'created_at' => now()],
            ['permkey' => 10022, 'permname' => 'Write Users', 'created_at' => now()],
            ['permkey' => 10023, 'permname' => 'Manage Users', 'created_at' => now()],

            // Schools Module
            ['permkey' => 10031, 'permname' => 'Read Schools', 'created_at' => now()],
            ['permkey' => 10032, 'permname' => 'Write Schools', 'created_at' => now()],
            ['permkey' => 10033, 'permname' => 'Manage Schools', 'created_at' => now()],

            // Students Module
            ['permkey' => 10041, 'permname' => 'Read Students', 'created_at' => now()],
            ['permkey' => 10042, 'permname' => 'Write Students', 'created_at' => now()],
            ['permkey' => 10043, 'permname' => 'Manage Students', 'created_at' => now()],

            // Dashboard Module
            ['permkey' => 10051, 'permname' => 'Read Dashboard', 'created_at' => now()],
            ['permkey' => 10052, 'permname' => 'Write Dashboard', 'created_at' => now()],
            ['permkey' => 10053, 'permname' => 'Manage Dashboard', 'created_at' => now()],

            // Invoices Module
            ['permkey' => 10061, 'permname' => 'Read Invoices', 'created_at' => now()],
            ['permkey' => 10062, 'permname' => 'Write Invoices', 'created_at' => now()],
            ['permkey' => 10063, 'permname' => 'Manage Invoices', 'created_at' => now()],

            // Payments Module
            ['permkey' => 10071, 'permname' => 'Read Payments', 'created_at' => now()],
            ['permkey' => 10072, 'permname' => 'Write Payments', 'created_at' => now()],
            ['permkey' => 10073, 'permname' => 'Manage Payments', 'created_at' => now()],

            // Upload Batches Module
            ['permkey' => 10081, 'permname' => 'Read Upload Batches', 'created_at' => now()],
            ['permkey' => 10082, 'permname' => 'Write Upload Batches', 'created_at' => now()],
            ['permkey' => 10083, 'permname' => 'Manage Upload Batches', 'created_at' => now()],

            // Student Batches Module
            ['permkey' => 10091, 'permname' => 'Read Student Batches', 'created_at' => now()],
            ['permkey' => 10092, 'permname' => 'Write Student Batches', 'created_at' => now()],
            ['permkey' => 10093, 'permname' => 'Manage Student Batches', 'created_at' => now()],

            // Guardians Module
            ['permkey' => 10101, 'permname' => 'Read Guardians', 'created_at' => now()],
            ['permkey' => 10102, 'permname' => 'Write Guardians', 'created_at' => now()],
            ['permkey' => 10103, 'permname' => 'Manage Guardians', 'created_at' => now()],

            // Parcels Module
            ['permkey' => 10111, 'permname' => 'Read Parcels', 'created_at' => now()],
            ['permkey' => 10112, 'permname' => 'Write Parcels', 'created_at' => now()],
            ['permkey' => 10113, 'permname' => 'Manage Parcels', 'created_at' => now()],

            // School Types Module
            ['permkey' => 10121, 'permname' => 'Read School Types', 'created_at' => now()],
            ['permkey' => 10122, 'permname' => 'Write School Types', 'created_at' => now()],
            ['permkey' => 10123, 'permname' => 'Manage School Types', 'created_at' => now()],

            // Educational Boards Module
            ['permkey' => 10131, 'permname' => 'Read Educational Boards', 'created_at' => now()],
            ['permkey' => 10132, 'permname' => 'Write Educational Boards', 'created_at' => now()],
            ['permkey' => 10133, 'permname' => 'Manage Educational Boards', 'created_at' => now()],

            // Student Types Module
            ['permkey' => 10141, 'permname' => 'Read Student Types', 'created_at' => now()],
            ['permkey' => 10142, 'permname' => 'Write Student Types', 'created_at' => now()],
            ['permkey' => 10143, 'permname' => 'Manage Student Types', 'created_at' => now()],

            // Semester Batches Module
            ['permkey' => 10151, 'permname' => 'Read Semester Batches', 'created_at' => now()],
            ['permkey' => 10152, 'permname' => 'Write Semester Batches', 'created_at' => now()],
            ['permkey' => 10153, 'permname' => 'Manage Semester Batches', 'created_at' => now()],

            // Fee Plans Module
            ['permkey' => 10161, 'permname' => 'Read Fee Plans', 'created_at' => now()],
            ['permkey' => 10162, 'permname' => 'Write Fee Plans', 'created_at' => now()],
            ['permkey' => 10163, 'permname' => 'Manage Fee Plans', 'created_at' => now()],

            // Discount Rules Module
            ['permkey' => 10171, 'permname' => 'Read Discount Rules', 'created_at' => now()],
            ['permkey' => 10172, 'permname' => 'Write Discount Rules', 'created_at' => now()],
            ['permkey' => 10173, 'permname' => 'Manage Discount Rules', 'created_at' => now()],

            // Payment Modes Module
            ['permkey' => 10181, 'permname' => 'Read Payment Modes', 'created_at' => now()],
            ['permkey' => 10182, 'permname' => 'Write Payment Modes', 'created_at' => now()],
            ['permkey' => 10183, 'permname' => 'Manage Payment Modes', 'created_at' => now()],

            // Parcel Companies Module
            ['permkey' => 10191, 'permname' => 'Read Parcel Companies', 'created_at' => now()],
            ['permkey' => 10192, 'permname' => 'Write Parcel Companies', 'created_at' => now()],
            ['permkey' => 10193, 'permname' => 'Manage Parcel Companies', 'created_at' => now()],
        ];

        foreach ($permissions as $permission) {
            DB::table('permission')->updateOrInsert(
                ['permkey' => $permission['permkey']],
                $permission
            );
        }
    }
}
