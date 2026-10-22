<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Permission;

class LeadFlowPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create specific permission for Lead Management accessible by Initiator
        Permission::updateOrCreate(
            ['permkey' => 10201],
            [
                'permkey' => 10201,
                'permname' => 'Lead Management',
            ]
        );

        $this->command->info('Lead Flow permissions setup complete.');
        $this->command->info('Created/ensured: - Lead Management: 10201');
    }
}
