<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('app_settings')) {
            Schema::create('app_settings', function (Blueprint $table) {
                $table->bigIncrements('setting_id');
                $table->string('setting_key', 100)->unique();
                $table->text('setting_value')->nullable();
                // string | bool | int | json
                $table->string('setting_type', 20)->default('string');
                $table->string('setting_group', 50)->default('general');
                $table->string('description', 255)->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        $defaults = [
            [
                'setting_key' => 'registration.open',
                'setting_value' => '1',
                'setting_type' => 'bool',
                'setting_group' => 'registration',
                'description' => 'When off, the public (non school-link) registration form captures Student Leads instead of enrolling.',
            ],
            [
                'setting_key' => 'registration.next_batch_note_en',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_group' => 'registration',
                'description' => 'Optional note shown to leads (English) about when the next batch opens.',
            ],
            [
                'setting_key' => 'registration.next_batch_note_hi',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_group' => 'registration',
                'description' => 'Optional note shown to leads (Hindi) about when the next batch opens.',
            ],
            [
                'setting_key' => 'registration.next_batch_note_gu',
                'setting_value' => '',
                'setting_type' => 'string',
                'setting_group' => 'registration',
                'description' => 'Optional note shown to leads (Gujarati) about when the next batch opens.',
            ],
        ];

        foreach ($defaults as $setting) {
            DB::table('app_settings')->updateOrInsert(
                ['setting_key' => $setting['setting_key']],
                [
                    'setting_value' => $setting['setting_value'],
                    'setting_type' => $setting['setting_type'],
                    'setting_group' => $setting['setting_group'],
                    'description' => $setting['description'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};
