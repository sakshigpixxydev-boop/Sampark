<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\School;
use App\Models\Student;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserAssignmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Run seeders
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\PermissionSeeder::class);
    }

    public function test_initiator_can_access_school_assignment_page()
    {
        $initiatorRole = Role::where('rolename', 'Initiator')->first();
        $permission = Permission::where('permkey', 10003)->first();
        
        // Assign permission to role
        $initiatorRole->permissions()->attach($permission->permission_id);
        
        $user = SysUser::create([
            'role_id' => $initiatorRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Initiator',
            'username' => 'testinitiator',
            'pswd' => 'password123',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('users.assign-schools', $user->sysuser_id));
        
        $response->assertStatus(200);
        $response->assertSee('Assign Schools');
    }

    public function test_school_user_can_access_school_assignment_page()
    {
        $schoolRole = Role::where('rolename', 'School')->first();
        $permission = Permission::where('permkey', 10003)->first();
        
        // Assign permission to role
        $schoolRole->permissions()->attach($permission->permission_id);
        
        $user = SysUser::create([
            'role_id' => $schoolRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'School',
            'username' => 'testschool',
            'pswd' => 'password123',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('users.assign-schools', $user->sysuser_id));
        
        $response->assertStatus(200);
        $response->assertSee('Assign Schools');
    }

    public function test_admin_cannot_access_school_assignment_page()
    {
        $adminRole = Role::where('rolename', 'Admin')->first();
        $permission = Permission::where('permkey', 10003)->first();
        
        // Assign permission to role
        $adminRole->permissions()->attach($permission->permission_id);
        
        $user = SysUser::create([
            'role_id' => $adminRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => 'password123',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('users.assign-schools', $user->sysuser_id));
        
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
    }

    public function test_initiator_can_access_student_assignment_page()
    {
        $initiatorRole = Role::where('rolename', 'Initiator')->first();
        $permission = Permission::where('permkey', 10003)->first();
        
        // Assign permission to role
        $initiatorRole->permissions()->attach($permission->permission_id);
        
        $user = SysUser::create([
            'role_id' => $initiatorRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Initiator',
            'username' => 'testinitiator2',
            'pswd' => 'password123',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('users.assign-students', $user->sysuser_id));
        
        $response->assertStatus(200);
        $response->assertSee('Assign Students');
    }

    public function test_non_initiator_cannot_access_student_assignment_page()
    {
        $schoolRole = Role::where('rolename', 'School')->first();
        $permission = Permission::where('permkey', 10003)->first();
        
        // Assign permission to role
        $schoolRole->permissions()->attach($permission->permission_id);
        
        $user = SysUser::create([
            'role_id' => $schoolRole->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'School',
            'username' => 'testschool2',
            'pswd' => 'password123',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('users.assign-students', $user->sysuser_id));
        
        $response->assertRedirect(route('users.index'));
        $response->assertSessionHas('error');
    }
}
