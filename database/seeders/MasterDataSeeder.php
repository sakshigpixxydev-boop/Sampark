<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Countries
        DB::table('ms_country')->insert([
            ['cname' => 'India', 'created_at' => now()],
            ['cname' => 'United States', 'created_at' => now()],
            ['cname' => 'United Kingdom', 'created_at' => now()],
        ]);

        // States (for India)
        $indiaId = DB::table('ms_country')->where('cname', 'India')->first()->country_id;
        DB::table('ms_state')->insert([
            ['country_id' => $indiaId, 'sname' => 'Gujarat', 'created_at' => now()],
            ['country_id' => $indiaId, 'sname' => 'Maharashtra', 'created_at' => now()],
            ['country_id' => $indiaId, 'sname' => 'Rajasthan', 'created_at' => now()],
        ]);

        // Payment Modes
        DB::table('paymentmode')->insert([
            ['paymodename' => 'Cash', 'created_at' => now()],
            ['paymodename' => 'Credit Card', 'created_at' => now()],
            ['paymodename' => 'Debit Card', 'created_at' => now()],
            ['paymodename' => 'Check', 'created_at' => now()],
            ['paymodename' => 'Online Transfer', 'created_at' => now()],
        ]);

        // School Types
        DB::table('schooltype')->insert([
            ['schooltypename' => 'Public', 'created_at' => now()],
            ['schooltypename' => 'Private', 'created_at' => now()],
            ['schooltypename' => 'Charter', 'created_at' => now()],
        ]);

        // Student Types
        DB::table('studenttype')->insert([
            ['studenttypename' => 'BAPS', 'created_at' => now()],
            ['studenttypename' => 'Non-BAPS', 'created_at' => now()],
        ]);

        // Educational Boards
        DB::table('ms_eductionalboard')->insert([
            ['boardname' => 'CBSE', 'created_at' => now()],
            ['boardname' => 'ICSE', 'created_at' => now()],
            ['boardname' => 'State Board', 'created_at' => now()],
            ['boardname' => 'IB', 'created_at' => now()],
        ]);

        // Standards
        $standards = [];
        for ($i = 1; $i <= 12; $i++) {
            $standards[] = ['stdname' => "Standard {$i}", 'created_at' => now()];
        }
        DB::table('ms_standard')->insert($standards);

        // Relations
        DB::table('ms_relation')->insert([
            ['relation' => 'Father', 'created_at' => now()],
            ['relation' => 'Mother', 'created_at' => now()],
            ['relation' => 'Guardian', 'created_at' => now()],
            ['relation' => 'Brother', 'created_at' => now()],
            ['relation' => 'Sister', 'created_at' => now()],
        ]);

        // Languages
        DB::table('language')->insert([
            ['languagecode' => 'GU', 'languagename' => 'Gujarati'],
            ['languagecode' => 'HI', 'languagename' => 'Hindi'],
            ['languagecode' => 'EN', 'languagename' => 'English'],
            ['languagecode' => 'MR', 'languagename' => 'Marathi'],
        ]);

        // User Types
        DB::table('usertype')->insert([
            ['usertypename' => 'Admin', 'created_at' => now()],
            ['usertypename' => 'Initiator', 'created_at' => now()],
            ['usertypename' => 'School', 'created_at' => now()],
            ['usertypename' => 'Accountant', 'created_at' => now()],
        ]);
    }
}
