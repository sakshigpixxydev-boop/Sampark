<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\Guardian;
use App\Models\Student;
use App\Models\Relation;
use App\Models\StudentType;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\Taluko;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuardianTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_authenticated_user_can_view_guardians_list()
    {
        $adminRole = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('guardians.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Guardians');
    }

    public function test_authenticated_user_can_create_guardian()
    {
        $adminRole = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        // Create required data if not exists
        $studentType = StudentType::firstOrCreate(
            ['studenttypename' => 'Regular'],
            ['studenttypename' => 'Regular']
        );
        
        $board = EducationalBoard::firstOrCreate(
            ['boardname' => 'Test Board'],
            ['boardname' => 'Test Board']
        );
        
        $standard = Standard::firstOrCreate(
            ['stdname' => 'Grade 1'],
            ['stdname' => 'Grade 1']
        );
        
        $taluka = Taluko::firstOrCreate(
            ['tname' => 'Test Taluka', 'dist_id' => 1],
            ['tname' => 'Test Taluka', 'dist_id' => 1]
        );
        
        $relation = Relation::firstOrCreate(
            ['relation' => 'Father'],
            ['relation' => 'Father']
        );
        
        $student = Student::create([
            'studenttype_id' => $studentType->studenttype_id,
            'eduboard_id' => $board->eduboard_id,
            'std_id' => $standard->std_id,
            'takula_id' => $taluka->takula_id,
            'fname' => 'Test',
            'mname' => 'Student',
            'lname' => 'Name',
            'email' => 'teststudent@example.com',
        ]);
        
        $this->actingAs($user);
        
        $guardianData = [
            'student_id' => $student->student_id,
            'relation_id' => $relation->relation_id,
            'fname' => 'Guardian',
            'mname' => 'Test',
            'lname' => 'Name',
            'gender' => 'M',
            'phone' => '1234567890',
            'email' => 'guardian@example.com',
        ];
        
        $response = $this->post(route('guardians.store'), $guardianData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('guardian', [
            'fname' => 'Guardian',
            'lname' => 'Name',
            'email' => 'guardian@example.com',
        ]);
    }
}
