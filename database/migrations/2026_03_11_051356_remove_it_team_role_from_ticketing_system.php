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
        // 1. Re-assign IT tickets to School Initiators
        $itTickets = DB::table('tickets')
            ->where('ticket_type', 'IT')
            ->whereNull('assigned_to')
            ->get();

        foreach ($itTickets as $ticket) {
            $school = DB::table('school')->where('school_id', $ticket->school_id)->first();
            if ($school && $school->initiator_id) {
                DB::table('tickets')
                    ->where('ticket_id', $ticket->ticket_id)
                    ->update(['assigned_to' => $school->initiator_id]);
            }
        }

        // 2. Identify 'IT Team' role
        $itRole = DB::table('role')->where('rolename', 'IT Team')->first();
        
        if ($itRole) {
            // 3. Remove users with this role (based on requirement to remove role safely)
            // Note: In some cases you might want to switch their role, but here we remove it-team user if any.
            DB::table('sysuser')->where('role_id', $itRole->role_id)->delete();

            // 4. Remove role permissions
            DB::table('rolepermission')->where('role_id', $itRole->role_id)->delete();

            // 5. Remove the role itself
            DB::table('role')->where('role_id', $itRole->role_id)->delete();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // It's hard to reverse user deletion and ticket re-assignment accurately without backups,
        // but we can at least recreate the role.
        DB::table('role')->updateOrInsert(
            ['rolename' => 'IT Team'],
            ['rolename' => 'IT Team', 'ispredefined' => true, 'created_at' => now()]
        );
    }
};
