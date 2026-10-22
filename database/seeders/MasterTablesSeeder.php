<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Educational Boards
        $boards = [
            ['boardname' => 'GSEB'],
            ['boardname' => 'CBSE'],
            ['boardname' => 'ICSE'],
            ['boardname' => 'IB'],
            ['boardname' => 'Other'],
        ];

        foreach ($boards as $board) {
            DB::table('ms_eductionalboard')->updateOrInsert(
                ['boardname' => $board['boardname']],
                ['created_at' => now()]
            );
        }

        // 2. Seed Standards
        $standards = [
            ['stdname' => 'Standard 5'],
            ['stdname' => 'Standard 6'],
            ['stdname' => 'Standard 7'],
            ['stdname' => 'Standard 8'],
            ['stdname' => 'Standard 9'],
            ['stdname' => 'Standard 10'],
            ['stdname' => 'Standard 11'],
            ['stdname' => 'Standard 12'],
        ];

        foreach ($standards as $std) {
            DB::table('ms_standard')->updateOrInsert(
                ['stdname' => $std['stdname']],
                ['created_at' => now()]
            );
        }

        // 3. Seed ALL Shikharabaddha Mandirs
        $mandirs = [
            'Atladara', 'Ahmedabad', 'Anand', 'Bangalore', 'Bhadra', 
            'Bharuch', 'Bhavnagar', 'Bhuj', 'Bochasan', 'Bodeli', 
            'Chennai', 'Delhi', 'Dhari', 'Dholka', 'Dhulia', 
            'Gadhada', 'Gandhinagar', 'Godhra', 'Gondal', 'Himmatnagar', 
            'Jamnagar', 'Junagadh', 'Kolkata', 'Limbdi', 'Mahuva', 
            'Mehlav', 'Mehsana', 'Mumbai', 'Nadiad', 'Nagpur', 
            'Nashik', 'Navsari', 'Pune', 'Rajasthan', 'Rajkot', 
            'Sankari', 'Sarangpur', 'Secunderabad', 'Selvas', 'Surat', 
            'Surendranagar', 'Tithal', 'Ukai', 'Vidyanagar'
        ];

        foreach ($mandirs as $mname) {
            DB::table('ms_shikharmandir')->updateOrInsert(
                ['mname' => $mname],
                ['created_at' => now()]
            );
        }

        // 4. Seed default Satsang Mandals
        $mandals = [
            'Bal Sabha', 'Balika Sabha', 'Yuvak Sabha', 'Yuvati Sabha', 'Samyukt Sabha'
        ];

        foreach ($mandals as $mandalname) {
            DB::table('ms_satsangmandal')->updateOrInsert(
                ['mandalname' => $mandalname],
                ['created_at' => now()]
            );
        }

        // 5. Seed Educational Mediums
        $mediums = [
            'Gujarati', 'Hindi', 'English', 'Other'
        ];

        foreach ($mediums as $m) {
            DB::table('ms_medium')->updateOrInsert(
                ['mediumname' => $m],
                ['created_at' => now()]
            );
        }
    }
}
