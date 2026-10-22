<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AgreementTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\AgreementTemplate::create([
            'slug' => 'school_registration',
            'title' => 'School Terms & Conditions',
            'content' => '<h1>School Terms & Conditions</h1><p>Welcome to the Pramukh Sankalp School Management System.</p><ul><li>Schools must provide accurate student data.</li><li>Fee calculations are based on approved plans.</li><li>Sharing registration links with unauthorized parties is prohibited.</li></ul>',
            'is_active' => true,
        ]);

        \App\Models\AgreementTemplate::create([
            'slug' => 'initiator_agreement',
            'title' => 'Initiator Terms & Conditions',
            'content' => '<h1>Initiator Terms & Conditions</h1><p>As an initiator, you agree to:</p><ol><li>Verify the legitimacy of schools before adding them as leads.</li><li>Maintain confidentiality of school contact information.</li><li>Follow educational board guidelines for enrollment.</li></ol>',
            'is_active' => true,
        ]);
    }
}
