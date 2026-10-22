<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SysUser;
use App\Models\UploadBatch;
use App\Models\SemesterBatch;
use App\Models\PendingStudent;
use App\Models\PaymentMode;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AccountantTest extends TestCase
{
    /**
     * Test accountant can access batch list
     */
    public function test_accountant_can_access_batch_list()
    {
        // Find or create an accountant user
        $accountant = SysUser::where('username', 'admin')->first();
        
        $response = $this->actingAs($accountant)->get(route('accountant.batches.index'));
        
        $response->assertStatus(200);
        $response->assertSee('Upload Batch Verification');
    }

    /**
     * Test accountant can view batch details
     */
    public function test_accountant_can_view_batch_details()
    {
        $accountant = SysUser::where('username', 'admin')->first();
        
        // Create a test batch
        $semesterBatch = SemesterBatch::first();
        if (!$semesterBatch) {
            $this->markTestSkipped('No semester batch available for testing');
        }
        
        $batch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $semesterBatch->sembatches_id,
            'uploaded_by' => $accountant->sysuser_id,
            'upload_date' => now(),
            'total_students' => 5,
            'fee_per_student' => 1000.00,
            'total_fees' => 5000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $response = $this->actingAs($accountant)->get(route('accountant.batches.show', $batch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee($batch->batch_code);
        $response->assertSee('Batch Details');
        
        // Cleanup
        $batch->delete();
    }

    /**
     * Test accountant can access approval form
     */
    public function test_accountant_can_access_approval_form()
    {
        $accountant = SysUser::where('username', 'admin')->first();
        
        // Create a test batch
        $semesterBatch = SemesterBatch::first();
        if (!$semesterBatch) {
            $this->markTestSkipped('No semester batch available for testing');
        }
        
        $batch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $semesterBatch->sembatches_id,
            'uploaded_by' => $accountant->sysuser_id,
            'upload_date' => now(),
            'total_students' => 5,
            'fee_per_student' => 1000.00,
            'total_fees' => 5000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $response = $this->actingAs($accountant)->get(route('accountant.batches.approve-form', $batch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee('Approve Payment for Batch');
        $response->assertSee($batch->batch_code);
        
        // Cleanup
        $batch->delete();
    }

    /**
     * Test accountant can access rejection form
     */
    public function test_accountant_can_access_rejection_form()
    {
        $accountant = SysUser::where('username', 'admin')->first();
        
        // Create a test batch
        $semesterBatch = SemesterBatch::first();
        if (!$semesterBatch) {
            $this->markTestSkipped('No semester batch available for testing');
        }
        
        $batch = UploadBatch::create([
            'batch_code' => 'TEST-' . time(),
            'sembatches_id' => $semesterBatch->sembatches_id,
            'uploaded_by' => $accountant->sysuser_id,
            'upload_date' => now(),
            'total_students' => 5,
            'fee_per_student' => 1000.00,
            'total_fees' => 5000.00,
            'status' => 'pending',
            'payment_verified' => false,
        ]);
        
        $response = $this->actingAs($accountant)->get(route('accountant.batches.reject-form', $batch->batch_id));
        
        $response->assertStatus(200);
        $response->assertSee('Reject Batch');
        $response->assertSee($batch->batch_code);
        
        // Cleanup
        $batch->delete();
    }
}
