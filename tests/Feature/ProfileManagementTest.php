<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\ResetPinNotification;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_user_can_view_profile()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('password123'),
            'email' => 'test@example.com',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('profile.show'));
        
        $response->assertStatus(200);
        $response->assertSee('Test');
        $response->assertSee('testuser');
    }

    public function test_user_can_update_profile()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('password123'),
            'email' => 'test@example.com',
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->put(route('profile.update'), [
            'fname' => 'Updated',
            'mname' => 'User',
            'lname' => 'Name',
            'email' => 'updated@example.com',
            'phone' => '1234567890',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('sysuser', [
            'sysuser_id' => $user->sysuser_id,
            'fname' => 'Updated',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_user_can_change_password()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('oldpassword'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('password.change'), [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        
        $response->assertRedirect();
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->pswd));
    }

    public function test_change_password_fails_with_wrong_current_password()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testuser',
            'pswd' => Hash::make('oldpassword'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('password.change'), [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        
        $response->assertSessionHasErrors('current_password');
    }

    public function test_user_can_change_pin()
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
        
        $this->actingAs($user);
        
        $response = $this->post(route('pin.change'), [
            'current_pin' => '1234',
            'pin' => '5678',
            'pin_confirmation' => '5678',
        ]);
        
        $response->assertRedirect();
        
        $user->refresh();
        $this->assertTrue(Hash::check('5678', $user->pin));
    }

    public function test_change_pin_fails_with_wrong_current_pin()
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
        
        $this->actingAs($user);
        
        $response = $this->post(route('pin.change'), [
            'current_pin' => '9999',
            'pin' => '5678',
            'pin_confirmation' => '5678',
        ]);
        
        $response->assertSessionHasErrors('current_pin');
    }

    public function test_forgot_password_page_displays()
    {
        $response = $this->get(route('password.request'));
        
        $response->assertStatus(200);
        $response->assertSee('Forgot Password');
    }

    public function test_forgot_pin_page_displays()
    {
        $response = $this->get(route('pin.request'));
        
        $response->assertStatus(200);
        $response->assertSee('Forgot PIN');
    }
}
