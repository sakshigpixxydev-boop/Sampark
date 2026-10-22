<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    /**
     * Test admin dashboard access
     */
    public function test_admin_can_access_admin_dashboard()
    {
        $admin = SysUser::where('username', 'admin')->first();
        
        $response = $this->actingAs($admin)->get('/admin/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Students');
        $response->assertSee('Total Schools');
        $response->assertSee('Total Initiators');
    }

    /**
     * Test initiator dashboard access
     */
    public function test_initiator_can_access_initiator_dashboard()
    {
        $initiator = SysUser::where('username', 'initiator')->first();
        
        $response = $this->actingAs($initiator)->get('/initiator/dashboard');
        
        if ($response->status() !== 200) {
            dump($response->getContent());
        }
        
        $response->assertStatus(200);
        $response->assertSee('Initiator Dashboard');
        $response->assertSee('Assigned Students');
        $response->assertSee('Assigned Schools');
    }

    /**
     * Test school dashboard access
     */
    public function test_school_can_access_school_dashboard()
    {
        $school = SysUser::where('username', 'school')->first();
        
        $response = $this->actingAs($school)->get('/school/dashboard');
        
        $response->assertStatus(200);
        $response->assertSee('School Dashboard');
        $response->assertSee('Total Students');
    }

    /**
     * Test role-based dashboard routing
     */
    public function test_dashboard_redirects_based_on_role()
    {
        $admin = SysUser::where('username', 'admin')->first();
        
        $response = $this->actingAs($admin)->get('/dashboard');
        
        $response->assertRedirect('/admin/dashboard');
    }
}
