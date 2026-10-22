<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_login_page_displays_correctly()
    {
        $response = $this->get(route('login'));
        
        $response->assertStatus(200);
        $response->assertSee('Login');
    }

    public function test_user_can_login_with_password()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('password123'),
            'inactive' => false,
        ]);
        
        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'password' => 'password123',
        ]);
        
        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_login_with_pin()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pin' => Hash::make('1234'),
            'inactive' => false,
        ]);
        
        $response = $this->post(route('login'), [
            'username' => 'testuser',
            'pin' => '1234',
        ]);
        
        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_inactive_user_cannot_login()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'inactiveuser',
            'pswd' => Hash::make('password123'),
            'inactive' => true,
        ]);
        
        $response = $this->post(route('login'), [
            'username' => 'inactiveuser',
            'password' => 'password123',
        ]);
        
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->post(route('login'), [
            'username' => 'nonexistent',
            'password' => 'wrongpassword',
        ]);
        
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_authenticated_user_redirects_to_dashboard()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('login'));
        
        $response->assertRedirect(route('dashboard'));
    }

    public function test_user_can_logout()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('logout'));
        
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }
}
