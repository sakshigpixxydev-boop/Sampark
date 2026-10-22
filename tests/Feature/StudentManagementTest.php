<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\Role;
use App\Models\Student;
use App\Models\StudentType;
use App\Models\EducationalBoard;
use App\Models\Standard;
use App\Models\Taluko;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StudentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
        $this->seed(\Database\Seeders\MasterDataSeeder::class);
    }

    public function test_authenticated_user_can_view_students_list()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->get(route('students.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Students');
    }

    public function test_authenticated_user_can_create_student()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $studentType = StudentType::first();
        $board = EducationalBoard::first();
        $standard = Standard::first();
        $taluka = Taluko::first();
        $school = School::factory()->create();
        
        $this->actingAs($user);
        
        $studentData = [
            'studenttype_id' => $studentType->studenttype_id,
            'eduboard_id' => $board->eduboard_id,
            'std_id' => $standard->std_id,
            'takula_id' => $taluka->takula_id,
            'school_id' => $school->school_id,
            'fname' => 'John',
            'mname' => 'Michael',
            'lname' => 'Doe',
            'email' => 'john.doe@example.com',
            'gender' => 'M',
        ];
        
        $response = $this->post(route('students.store'), $studentData);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('student', [
            'fname' => 'John',
            'lname' => 'Doe',
            'email' => 'john.doe@example.com',
        ]);
    }

    public function test_student_creation_validates_required_fields()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $this->actingAs($user);
        
        $response = $this->post(route('students.store'), []);
        
        $response->assertSessionHasErrors([
            'studenttype_id',
            'eduboard_id',
            'std_id',
            'takula_id',
            'fname',
            'mname',
            'lname',
            'email',
        ]);
    }

    public function test_student_creation_validates_email_uniqueness()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $existingStudent = Student::factory()->create([
            'email' => 'existing@example.com'
        ]);
        
        $studentType = StudentType::first();
        $board = EducationalBoard::first();
        $standard = Standard::first();
        $taluka = Taluko::first();
        
        $this->actingAs($user);
        
        $studentData = [
            'studenttype_id' => $studentType->studenttype_id,
            'eduboard_id' => $board->eduboard_id,
            'std_id' => $standard->std_id,
            'takula_id' => $taluka->takula_id,
            'fname' => 'Jane',
            'mname' => 'Mary',
            'lname' => 'Smith',
            'email' => 'existing@example.com',
        ];
        
        $response = $this->post(route('students.store'), $studentData);
        
        $response->assertSessionHasErrors('email');
    }

    public function test_authenticated_user_can_update_student()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $student = Student::factory()->create([
            'fname' => 'Original',
            'email' => 'original@example.com'
        ]);
        
        $this->actingAs($user);
        
        $response = $this->put(route('students.update', $student->student_id), [
            'studenttype_id' => $student->studenttype_id,
            'eduboard_id' => $student->eduboard_id,
            'std_id' => $student->std_id,
            'takula_id' => $student->takula_id,
            'fname' => 'Updated',
            'mname' => $student->mname,
            'lname' => $student->lname,
            'email' => 'updated@example.com',
        ]);
        
        $response->assertRedirect();
        $this->assertDatabaseHas('student', [
            'student_id' => $student->student_id,
            'fname' => 'Updated',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_authenticated_user_can_view_student_details()
    {
        $role = Role::where('rolename', 'Admin')->first();
        
        $user = SysUser::create([
            'role_id' => $role->role_id,
            'fname' => 'Test',
            'mname' => 'User',
            'lname' => 'Admin',
            'username' => 'testadmin',
            'pswd' => bcrypt('password123'),
            'inactive' => false,
        ]);
        
        $student = Student::factory()->create();
        
        $this->actingAs($user);
        
        $response = $this->get(route('students.edit', $student->student_id));
        
        $response->assertStatus(200);
        $response->assertSee($student->fname);
        $response->assertSee($student->email);
    }
}
