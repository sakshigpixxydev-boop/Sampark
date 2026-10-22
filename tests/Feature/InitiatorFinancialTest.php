<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InitiatorFinancialTest extends TestCase
{
    protected $financialService;
    protected $roleId;
    protected $studenttypeId;
    protected $eduboardId;
    protected $stdId;
    protected $schooltypeId;
    protected $schoolId;
    protected $takulaId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->financialService = app(\App\Services\InitiatorFinancialService::class);

        // Seed required lookups safely
        $countryId = \App\Models\Country::where('cname', 'India')->value('country_id') 
            ?? \App\Models\Country::create(['cname' => 'India'])->country_id;

        $stateId = \App\Models\State::where('sname', 'Gujarat')->value('state_id')
            ?? \App\Models\State::create(['sname' => 'Gujarat', 'country_id' => $countryId])->state_id;

        $distId = \App\Models\District::where('dname', 'Test District')->value('dist_id')
            ?? \App\Models\District::create(['dname' => 'Test District', 'state_id' => $stateId])->dist_id;

        $this->takulaId = \App\Models\Taluko::where('tname', 'Test Taluko')->value('takula_id')
            ?? \App\Models\Taluko::create(['tname' => 'Test Taluko', 'dist_id' => $distId])->takula_id;

        $this->schooltypeId = (\App\Models\SchoolType::first()->schooltype_id ?? null)
            ?? \App\Models\SchoolType::create(['schooltypename' => 'Test Type'])->schooltype_id;

        $this->studenttypeId = \App\Models\StudentType::where('studenttypename', 'Test Type')->value('studenttype_id')
            ?? \App\Models\StudentType::create(['studenttypename' => 'Test Type'])->studenttype_id;

        $this->eduboardId = (\App\Models\EducationalBoard::first()->eduboard_id ?? null)
            ?? \App\Models\EducationalBoard::create(['boardname' => 'Test Board'])->eduboard_id;

        $this->stdId = (\App\Models\Standard::first()->std_id ?? null)
            ?? \App\Models\Standard::create(['stdname' => 'Test Class'])->std_id;

        $this->schoolId = \App\Models\School::where('schoolname', 'Test School')->value('school_id')
            ?? \App\Models\School::create(['schoolname' => 'Test School', 'takula_id' => $this->takulaId, 'schooltype_id' => $this->schooltypeId])->school_id;

        $this->roleId = \App\Models\Role::where('rolename', 'Initiator')->value('role_id')
            ?? \App\Models\Role::create(['rolename' => 'Initiator'])->role_id;
    }

    /**
     * Test logic for Per Student calculation type
     */
    public function test_per_student_calculation()
    {
        $unique = microtime(true);
        $user = \App\Models\SysUser::create([
            'fname' => 'Test',
            'lname' => 'Initiator',
            'email' => "test{$unique}@example.com",
            'username' => "testinitiator{$unique}",
            'pswd' => 'password',
            'pin' => '1234',
            'role_id' => $this->roleId,
            'financial_type' => 'paid',
        ]);

        $config = \App\Models\InitiatorFinancialConfig::create([
            'sysuser_id' => $user->sysuser_id,
            'student_calculation_type' => \App\Models\InitiatorFinancialConfig::TYPE_PER_STUDENT,
            'per_student_commission_first_semester' => 10,
            'per_student_commission_repeat_semester' => 5,
            'new_school_registration_commission' => 0,
            'school_count_base' => 0,
            'school_count_slab_amount' => 0,
        ]);

        $student = \App\Models\Student::create([
            'fname' => 'Student',
            'mname' => 'Middle',
            'lname' => 'One',
            'email' => "s1_{$unique}@example.com",
            'gender' => 'M',
            'birthdate' => '2000-01-01',
            'contact_no' => substr(str_replace('.', '', $unique), 0, 10),
            'studenttype_id' => $this->studenttypeId,
            'eduboard_id' => $this->eduboardId,
            'std_id' => $this->stdId,
            'takula_id' => $this->takulaId,
            'school_id' => $this->schoolId,
        ]);
        
        $invoice = \App\Models\Invoice::create([
            'student_id' => $student->student_id,
            'total_due' => 1000,
            'status' => 'ISSUED'
        ]);

        $mockUser = $this->getMockBuilder(\App\Models\SysUser::class)
            ->onlyMethods(['getAssignedStudentIds', 'getAssignedSchoolIds', 'isPaidInitiator'])
            ->getMock();
        $mockUser->method('getAssignedStudentIds')->willReturn([$student->student_id]);
        $mockUser->method('getAssignedSchoolIds')->willReturn([]);
        $mockUser->method('isPaidInitiator')->willReturn(true);
        $mockUser->sysuser_id = $user->sysuser_id;
        $mockUser->setRelation('financialConfig', $config);

        $earnings = $this->financialService->calculateEarnings($mockUser);

        $this->assertEquals(100, $earnings['breakdown']['students']['earnings']);
    }

    public function test_slab_based_percentage_calculation()
    {
        $unique = microtime(true);
        $user = \App\Models\SysUser::create([
            'fname' => 'Test',
            'lname' => 'Initiator 2',
            'email' => "test2_{$unique}@example.com",
            'username' => "testinitiator2_{$unique}",
            'pswd' => 'password',
            'pin' => '1234',
            'role_id' => $this->roleId,
            'financial_type' => 'paid',
        ]);

        $config = \App\Models\InitiatorFinancialConfig::create([
            'sysuser_id' => $user->sysuser_id,
            'student_calculation_type' => \App\Models\InitiatorFinancialConfig::TYPE_SLAB_BASED,
            'new_school_registration_commission' => 0,
            'school_count_base' => 0,
            'school_count_slab_amount' => 0,
        ]);

        \App\Models\InitiatorFinancialSlab::create([
            'config_id' => $config->id,
            'min_students' => 1,
            'commission_percentage' => 15,
            'fixed_amount' => 0,
        ]);

        $student = \App\Models\Student::create([
            'fname' => 'Student',
            'mname' => 'Middle',
            'lname' => 'Two',
            'email' => "s2_{$unique}@example.com",
            'gender' => 'M',
            'birthdate' => '2000-01-01',
            'contact_no' => substr(str_replace('.', '', $unique), 0, 10),
            'studenttype_id' => $this->studenttypeId,
            'eduboard_id' => $this->eduboardId,
            'std_id' => $this->stdId,
            'takula_id' => $this->takulaId,
            'school_id' => $this->schoolId,
        ]);
        
        $invoice = \App\Models\Invoice::create([
            'student_id' => $student->student_id,
            'total_due' => 2000,
            'status' => 'ISSUED'
        ]);

        $mockUser = $this->getMockBuilder(\App\Models\SysUser::class)
            ->onlyMethods(['getAssignedStudentIds', 'getAssignedSchoolIds', 'isPaidInitiator'])
            ->getMock();
        $mockUser->method('getAssignedStudentIds')->willReturn([$student->student_id]);
        $mockUser->method('getAssignedSchoolIds')->willReturn([]);
        $mockUser->method('isPaidInitiator')->willReturn(true);
        $mockUser->sysuser_id = $user->sysuser_id;
        $mockUser->setRelation('financialConfig', $config);
        $config->load('slabs');

        $earnings = $this->financialService->calculateEarnings($mockUser);

        $this->assertEquals(300, $earnings['breakdown']['slabs']['earnings']);
    }

    public function test_slab_based_fixed_calculation()
    {
        $unique = microtime(true);
        $user = \App\Models\SysUser::create([
            'fname' => 'Test',
            'lname' => 'Initiator 3',
            'email' => "test3_{$unique}@example.com",
            'username' => "testinitiator3_{$unique}",
            'pswd' => 'password',
            'pin' => '1234',
            'role_id' => $this->roleId,
            'financial_type' => 'paid',
        ]);

        $config = \App\Models\InitiatorFinancialConfig::create([
            'sysuser_id' => $user->sysuser_id,
            'student_calculation_type' => \App\Models\InitiatorFinancialConfig::TYPE_SLAB_BASED,
            'new_school_registration_commission' => 0,
            'school_count_base' => 0,
            'school_count_slab_amount' => 0,
        ]);

        \App\Models\InitiatorFinancialSlab::create([
            'config_id' => $config->id,
            'min_students' => 1,
            'commission_percentage' => 0,
            'fixed_amount' => 50,
        ]);

        $student = \App\Models\Student::create([
            'fname' => 'Student',
            'mname' => 'Middle',
            'lname' => 'Three',
            'email' => "s3_{$unique}@example.com",
            'gender' => 'M',
            'birthdate' => '2000-01-01',
            'contact_no' => substr(str_replace('.', '', $unique), 0, 10),
            'studenttype_id' => $this->studenttypeId,
            'eduboard_id' => $this->eduboardId,
            'std_id' => $this->stdId,
            'takula_id' => $this->takulaId,
            'school_id' => $this->schoolId,
        ]);

        $mockUser = $this->getMockBuilder(\App\Models\SysUser::class)
            ->onlyMethods(['getAssignedStudentIds', 'getAssignedSchoolIds', 'isPaidInitiator'])
            ->getMock();
        $mockUser->method('getAssignedStudentIds')->willReturn([$student->student_id]);
        $mockUser->method('getAssignedSchoolIds')->willReturn([]);
        $mockUser->method('isPaidInitiator')->willReturn(true);
        $mockUser->sysuser_id = $user->sysuser_id;
        $mockUser->setRelation('financialConfig', $config);
        $config->load('slabs');

        $earnings = $this->financialService->calculateEarnings($mockUser);

        $this->assertEquals(50, $earnings['breakdown']['slabs']['earnings']);
    }

    public function test_slab_based_both_calculation()
    {
        $unique = microtime(true);
        $user = \App\Models\SysUser::create([
            'fname' => 'Test',
            'lname' => 'Initiator 4',
            'email' => "test4_{$unique}@example.com",
            'username' => "testinitiator4_{$unique}",
            'pswd' => 'password',
            'pin' => '1234',
            'role_id' => $this->roleId,
            'financial_type' => 'paid',
        ]);

        $config = \App\Models\InitiatorFinancialConfig::create([
            'sysuser_id' => $user->sysuser_id,
            'student_calculation_type' => \App\Models\InitiatorFinancialConfig::TYPE_SLAB_BASED,
            'new_school_registration_commission' => 0,
            'school_count_base' => 0,
            'school_count_slab_amount' => 0,
        ]);

        \App\Models\InitiatorFinancialSlab::create([
            'config_id' => $config->id,
            'min_students' => 1,
            'commission_percentage' => 10,
            'fixed_amount' => 50,
        ]);

        $student = \App\Models\Student::create([
            'fname' => 'Student',
            'mname' => 'Middle',
            'lname' => 'Four',
            'email' => "s4_{$unique}@example.com",
            'gender' => 'M',
            'birthdate' => '2000-01-01',
            'contact_no' => substr(str_replace('.', '', $unique), 0, 10),
            'studenttype_id' => $this->studenttypeId,
            'eduboard_id' => $this->eduboardId,
            'std_id' => $this->stdId,
            'takula_id' => $this->takulaId,
            'school_id' => $this->schoolId,
        ]);

        \App\Models\Invoice::create([
            'student_id' => $student->student_id,
            'total_due' => 1000,
            'status' => 'ISSUED'
        ]);

        $mockUser = $this->getMockBuilder(\App\Models\SysUser::class)
            ->onlyMethods(['getAssignedStudentIds', 'getAssignedSchoolIds', 'isPaidInitiator'])
            ->getMock();
        $mockUser->method('getAssignedStudentIds')->willReturn([$student->student_id]);
        $mockUser->method('getAssignedSchoolIds')->willReturn([]);
        $mockUser->method('isPaidInitiator')->willReturn(true);
        $mockUser->sysuser_id = $user->sysuser_id;
        $mockUser->setRelation('financialConfig', $config);
        $config->load('slabs');

        $earnings = $this->financialService->calculateEarnings($mockUser);

        $this->assertEquals(150, $earnings['breakdown']['slabs']['earnings']);
    }
}
