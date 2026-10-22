<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ForgotPinController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password reset routes
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');

    // PIN reset routes
    Route::get('/forgot-pin', [ForgotPinController::class, 'showLinkRequestForm'])->name('pin.request');
    Route::post('/forgot-pin', [ForgotPinController::class, 'sendResetLinkEmail'])->name('pin.email');
    Route::get('/reset-pin/{token}', [ForgotPinController::class, 'showResetForm'])->name('pin.reset');
    Route::post('/reset-pin', [ForgotPinController::class, 'reset'])->name('pin.update');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile routes
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    // Change password routes
    Route::get('/profile/change-password', [App\Http\Controllers\ChangePasswordController::class, 'show'])->name('password.change');
    Route::put('/profile/change-password', [App\Http\Controllers\ChangePasswordController::class, 'update'])->name('password.update.profile');

    // Change PIN routes
    Route::get('/profile/change-pin', [App\Http\Controllers\ChangePinController::class, 'show'])->name('pin.change');
    Route::put('/profile/change-pin', [App\Http\Controllers\ChangePinController::class, 'update'])->name('pin.update.profile');

    // Header cohort switch: narrows every page to school or regular students.
    Route::get('/view-mode/{cohort}', [App\Http\Controllers\ViewCohortController::class, 'switch'])
        ->name('view-mode.switch');

    // Dashboard routes
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/impersonate/stop', [App\Http\Controllers\UserController::class, 'stopImpersonating'])->name('users.stop-impersonating');

    Route::get('/admin/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Admin Registration Review Panel
    Route::prefix('admin/registrations')->name('admin.registrations.')->middleware(['auth', 'permission:10001|11201|11601|11602|11603|11604|11605|11606'])->group(function () {
        Route::get('/', [\App\Http\Controllers\AdminRegistrationController::class, 'index'])->name('index');
        Route::get('/history', [\App\Http\Controllers\AdminRegistrationController::class, 'history'])->name('history')->middleware('permission:11401');
        Route::get('/api/batches/{bundleId}', [\App\Http\Controllers\AdminRegistrationController::class, 'getBatches'])->name('batches');
        Route::post('/sync-all-inprogress', [\App\Http\Controllers\AdminRegistrationController::class, 'syncAllInProgress'])->name('sync-all-inprogress');
        Route::post('/bulk-shift', [\App\Http\Controllers\AdminRegistrationController::class, 'bulkShift'])->name('bulk-shift');
        Route::post('/bulk-email', [\App\Http\Controllers\AdminRegistrationController::class, 'bulkEmail'])->name('bulk-email');
        Route::post('/bulk-sync-payments', [\App\Http\Controllers\AdminRegistrationController::class, 'bulkSyncPayments'])->name('bulk-sync-payments');
        Route::post('/publish-batch', [\App\Http\Controllers\AdminRegistrationController::class, 'publishBatch'])->name('publish-batch');
        Route::get('/batch-payment/{batchId}', [\App\Http\Controllers\AdminRegistrationController::class, 'batchPayment'])->name('batch-payment');
        Route::post('/batch-payment/{batchId}/verify', [\App\Http\Controllers\AdminRegistrationController::class, 'verifyBatchPayment'])->name('verify-batch-payment');
        Route::post('/export', [\App\Http\Controllers\AdminRegistrationController::class, 'export'])->name('export');
        Route::get('/{id}', [\App\Http\Controllers\AdminRegistrationController::class, 'show'])->name('show');
        Route::get('/{id}/photo', [\App\Http\Controllers\AdminRegistrationController::class, 'showPhoto'])->name('photo');
        Route::post('/{id}/approve', [\App\Http\Controllers\AdminRegistrationController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\AdminRegistrationController::class, 'reject'])->name('reject');
        Route::post('/{id}/approve-school-payment', [\App\Http\Controllers\AdminRegistrationController::class, 'approveSchoolPayment'])->name('approve-school-payment');
        Route::post('/{id}/reject-school-payment', [\App\Http\Controllers\AdminRegistrationController::class, 'rejectSchoolPayment'])->name('reject-school-payment');
        Route::post('/{id}/send-reminder', [\App\Http\Controllers\AdminRegistrationController::class, 'sendReminder'])->name('send-reminder');
        Route::post('/{id}/sync-payment', [\App\Http\Controllers\AdminRegistrationController::class, 'syncPayment'])->name('sync-payment');
        Route::post('/{id}/update-batch', [\App\Http\Controllers\AdminRegistrationController::class, 'updateBatch'])->name('update-batch');
        Route::delete('/{id}', [\App\Http\Controllers\AdminRegistrationController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [\App\Http\Controllers\AdminRegistrationController::class, 'restore'])->name('restore');
        Route::get('/api/batches/{bundleId}', [\App\Http\Controllers\AdminRegistrationController::class, 'getBatches'])->name('batches');
    });


    Route::get('/initiator/dashboard', [App\Http\Controllers\InitiatorDashboardController::class, 'index'])->name('initiator.dashboard');
    Route::get('/initiator/earnings', [App\Http\Controllers\InitiatorDashboardController::class, 'earnings'])->name('initiator.earnings');

    Route::get('/school/dashboard', [App\Http\Controllers\SchoolDashboardController::class, 'index'])->name('school.dashboard');
    Route::get('/school/registration-settings', [App\Http\Controllers\SchoolRegistrationSettingsController::class, 'edit'])->name('school.registration-settings.edit');
    Route::post('/school/registration-settings', [App\Http\Controllers\SchoolRegistrationSettingsController::class, 'update'])->name('school.registration-settings.update');
    Route::post('/school/registration-settings/generate-token', [App\Http\Controllers\SchoolRegistrationSettingsController::class, 'generateToken'])->name('school.registration-settings.generate-token');

    // School Registration listing: bulk imports + registration-link students.
    // The summary lives on the admin dashboard's Registration Overview tabs.
    Route::prefix('school/registrations')->name('school.registrations.')
        ->middleware('permission:10001|11201|11601|11602|11603|11604|11605|11606')
        ->group(function () {
            Route::get('/', [App\Http\Controllers\SchoolRegistrationOverviewController::class, 'index'])->name('index');
        });

    Route::prefix('school/educators')->name('school.educators.')->group(function () {
        Route::get('/', [App\Http\Controllers\SchoolEducatorController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SchoolEducatorController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\SchoolEducatorController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [App\Http\Controllers\SchoolEducatorController::class, 'edit'])->name('edit');
        Route::put('/{id}', [App\Http\Controllers\SchoolEducatorController::class, 'update'])->name('update');
        Route::delete('/{id}', [App\Http\Controllers\SchoolEducatorController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('permission:10051')->group(function () {
        Route::get('/student/dashboard', [App\Http\Controllers\StudentDashboardController::class, 'index'])->name('student.dashboard');
        Route::post('/student/upload-photo', [App\Http\Controllers\StudentDashboardController::class, 'uploadPhoto'])->name('student.upload-photo');
    });

    Route::get('/accountant/dashboard', function () {
        return view('accountant.dashboard');
    })->name('accountant.dashboard');

    // Accountant Batch Verification routes
    Route::prefix('accountant/batches')->name('accountant.batches.')->middleware('permission:10081')->group(function () {
        Route::get('/', [App\Http\Controllers\AccountantController::class, 'index'])->name('index');
        Route::get('/{id}', [App\Http\Controllers\AccountantController::class, 'show'])->name('show');
        Route::get('/{id}/approve', [App\Http\Controllers\AccountantController::class, 'showApprovalForm'])->name('approve-form');
        Route::post('/{id}/approve', [App\Http\Controllers\AccountantController::class, 'approve'])->name('approve');
        Route::get('/{id}/reject', [App\Http\Controllers\AccountantController::class, 'showRejectionForm'])->name('reject-form');
        Route::post('/{id}/reject', [App\Http\Controllers\AccountantController::class, 'reject'])->name('reject');
    });

    // Role management routes (protected by permission middleware)
    Route::middleware('permission:10001')->group(function () {
        Route::resource('roles', App\Http\Controllers\RoleController::class);
    });

    // Permission management routes (protected by permission middleware)
    Route::middleware('permission:10011')->group(function () {
        Route::resource('permissions', App\Http\Controllers\PermissionController::class)->except(['show', 'destroy']);
    });

    // User management routes (protected by permission middleware)
    Route::middleware('permission:10021')->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class)->except(['show'])
            ->middlewareFor('destroy', 'permission:10023');

        // User-School assignment routes
        Route::get('users/{user}/assign-schools', [App\Http\Controllers\UserController::class, 'assignSchools'])
            ->name('users.assign-schools');
        Route::post('users/{user}/assign-schools', [App\Http\Controllers\UserController::class, 'updateSchools'])
            ->name('users.update-schools');

        // User-Student assignment routes
        Route::get('users/{user}/assign-students', [App\Http\Controllers\UserController::class, 'assignStudents'])
            ->name('users.assign-students');
        Route::post('users/{user}/assign-students', [App\Http\Controllers\UserController::class, 'updateStudents'])
            ->name('users.update-students');

        // Impersonation route
        Route::post('users/{user}/impersonate', [App\Http\Controllers\UserController::class, 'impersonate'])
            ->name('users.impersonate');

        // Initiator Financial Config routes
        Route::middleware('permission:10301')->group(function () {
            Route::get('users/{user}/financial-config', [App\Http\Controllers\InitiatorFinancialController::class, 'edit'])
                ->name('users.financial-config');
            Route::post('users/{user}/financial-config', [App\Http\Controllers\InitiatorFinancialController::class, 'update'])
                ->name('users.financial-config.update');

            // School Financial Config routes
            Route::get('users/{user}/school-financial-config', [App\Http\Controllers\SchoolFinancialController::class, 'edit'])
                ->name('users.school-financial-config');
            Route::post('users/{user}/school-financial-config', [App\Http\Controllers\SchoolFinancialController::class, 'update'])
                ->name('users.school-financial-config.update');

            // Initiator Payments routes
            Route::get('users/{user}/initiator-payments', [App\Http\Controllers\InitiatorPaymentController::class, 'index'])
                ->name('users.initiator-payments');
            Route::post('users/{user}/initiator-payments', [App\Http\Controllers\InitiatorPaymentController::class, 'store'])
                ->name('users.initiator-payments.store');
            Route::delete('initiator-payments/{payment}', [App\Http\Controllers\InitiatorPaymentController::class, 'destroy'])
                ->name('initiator-payments.destroy');
        });
    });

    // Master Data Management routes
    // Read gates the whole module; create/edit additionally need Write and
    // delete additionally needs Manage, so a read-only role cannot change data.
    Route::middleware('permission:10121')->group(function () {
        Route::resource('school-types', App\Http\Controllers\SchoolTypeController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10122')
            ->middlewareFor('destroy', 'permission:10123');
    });
    Route::middleware('permission:10131')->group(function () {
        Route::resource('educational-boards', App\Http\Controllers\EducationalBoardController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10132')
            ->middlewareFor('destroy', 'permission:10133');
    });
    Route::middleware('permission:10141')->group(function () {
        Route::resource('student-types', App\Http\Controllers\StudentTypeController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10142')
            ->middlewareFor('destroy', 'permission:10143');
    });
    Route::middleware('permission:10151')->group(function () {
        Route::resource('semester-batches', App\Http\Controllers\SemesterBatchController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10152')
            ->middlewareFor('destroy', 'permission:10153');
    });

    // School Management routes
    Route::middleware('permission:10031')->group(function () {
        Route::resource('schools', App\Http\Controllers\SchoolController::class)->except(['show', 'destroy']);
        
        // School Agreement routes
        Route::get('schools/{school}/agreement', [App\Http\Controllers\SchoolAgreementController::class, 'show'])
            ->name('schools.agreement.show');
        Route::get('schools/{school}/agreement/download-sample', [App\Http\Controllers\SchoolAgreementController::class, 'downloadSample'])
            ->name('schools.agreement.download-sample');
        Route::post('schools/{school}/agreement/upload', [App\Http\Controllers\SchoolAgreementController::class, 'uploadAgreement'])
            ->name('schools.agreement.upload');
        Route::post('schools/{school}/agreement/approve-initiator', [App\Http\Controllers\SchoolAgreementController::class, 'approveInitiator'])
            ->name('schools.agreement.approve-initiator');
        Route::post('schools/{school}/agreement/approve-coordinator', [App\Http\Controllers\SchoolAgreementController::class, 'approveCoordinator'])
            ->name('schools.agreement.approve-coordinator');
        Route::post('schools/{school}/agreement/upload-signature', [App\Http\Controllers\SchoolAgreementController::class, 'uploadSignature'])
            ->name('schools.agreement.upload-signature');
    });

    // Student Management routes
    Route::middleware('permission:10041|11201|11602|11604')->group(function () {
        Route::post('students/{id}/restore', [App\Http\Controllers\StudentController::class, 'restore'])->name('students.restore');
        Route::resource('students', App\Http\Controllers\StudentController::class);

        // Student Export route
        Route::get('students/export', [App\Http\Controllers\StudentController::class, 'export'])
            ->name('students.export');

        // Student Bulk Upload routes
        Route::middleware('permission:10041|11201|11604')->group(function () {
            Route::get('students/bulk-upload/form', [App\Http\Controllers\StudentBulkUploadController::class, 'showUploadForm'])
                ->name('students.bulk-upload.form');
            Route::get('students/bulk-upload/sample', [App\Http\Controllers\StudentBulkUploadController::class, 'downloadSample'])
                ->name('students.bulk-upload.sample');
            Route::get('students/bulk-upload/physical-form', [App\Http\Controllers\StudentBulkUploadController::class, 'downloadPhysicalForm'])
                ->name('students.bulk-upload.physical-form');
            Route::get('students/bulk-upload/fee-plans', [App\Http\Controllers\StudentBulkUploadController::class, 'getFeePlans'])
                ->name('students.bulk-upload.fee-plans');
            Route::post('students/bulk-upload/process', [App\Http\Controllers\StudentBulkUploadController::class, 'upload'])
                ->name('students.bulk-upload.process');
            Route::get('students/bulk-upload/preview', [App\Http\Controllers\StudentBulkUploadController::class, 'preview'])
                ->name('students.bulk-upload.preview');
            Route::post('students/bulk-upload/update-preview', [App\Http\Controllers\StudentBulkUploadController::class, 'updatePreview'])
                ->name('students.bulk-upload.update-preview');
            Route::post('students/bulk-upload/submit', [App\Http\Controllers\StudentBulkUploadController::class, 'submitForVerification'])
                ->name('students.bulk-upload.submit');
            Route::post('students/bulk-upload/create-razorpay-order', [App\Http\Controllers\StudentBulkUploadController::class, 'createRazorpayOrder'])
                ->name('students.bulk-upload.create-razorpay-order');
            Route::post('students/bulk-upload/verify-payment', [App\Http\Controllers\StudentBulkUploadController::class, 'verifyRazorpayPayment'])
                ->name('students.bulk-upload.verify-payment');
        });

        // Migrated Students Routes
        Route::get('admin/migrated-students/upload', [App\Http\Controllers\MigratedStudentUploadController::class, 'showUploadForm'])
            ->name('migrated-students.upload.form')->middleware('permission:11404|11604');
        Route::post('admin/migrated-students/process', [App\Http\Controllers\MigratedStudentUploadController::class, 'upload'])
            ->name('migrated-students.upload.process')->middleware('permission:11404|11604');
        Route::get('admin/migrated-students/preview', [App\Http\Controllers\MigratedStudentUploadController::class, 'preview'])
            ->name('migrated-students.upload.preview')->middleware('permission:11404|11604');
        Route::post('admin/migrated-students/submit', [App\Http\Controllers\MigratedStudentUploadController::class, 'submitForVerification'])
            ->name('migrated-students.upload.submit')->middleware('permission:11404|11604');
        Route::post('admin/migrated-students/update-preview', [App\Http\Controllers\MigratedStudentUploadController::class, 'updatePreview'])
            ->name('migrated-students.upload.update-preview')->middleware('permission:11404|11604');

    });

    // Student Announcement Module
    Route::middleware('permission:10222')->group(function () {
        Route::get('students/announcements', [App\Http\Controllers\StudentAnnouncementController::class, 'index'])->name('students.announcements.index');
        Route::get('students/announcements/create', [App\Http\Controllers\StudentAnnouncementController::class, 'create'])->name('students.announcements.create');
        Route::post('students/announcements', [App\Http\Controllers\StudentAnnouncementController::class, 'store'])->name('students.announcements.store');
        Route::get('students/announcements/{id}', [App\Http\Controllers\StudentAnnouncementController::class, 'show'])->name('students.announcements.show');
        Route::post('students/announcements/{id}/reuse', [App\Http\Controllers\StudentAnnouncementController::class, 'reuse'])->name('students.announcements.reuse');

        // Template API (Accessible to anyone with Read Announcements permission for creating/previewing)
        Route::get('students/announcements-templates/api/{id}', [App\Http\Controllers\GlobalTemplateController::class, 'getTemplate'])->name('students.announcements.templates.api');

        // Announcement Templates Management
        Route::middleware('permission:10223')->group(function () {
            Route::resource('students/announcements-templates', App\Http\Controllers\GlobalTemplateController::class)
                ->names('students.announcements.templates');
        });
    });

    // Guardian Management & Bulk Messaging
    Route::middleware('permission:10101')->group(function () {
        // Bulk Messaging (Unique paths to avoid resource collision)
        Route::post('bulk-messages/initiate', [App\Http\Controllers\GuardianController::class, 'initiateBulkMessage'])->name('guardians.bulk-message');
        Route::get('bulk-messages/create/{id}', [App\Http\Controllers\GuardianController::class, 'createMessage'])->name('guardians.message.create');
        Route::post('bulk-messages/store/{id}', [App\Http\Controllers\GuardianController::class, 'storeMessage'])->name('guardians.message.store');
        Route::get('bulk-messages/history', [App\Http\Controllers\GuardianController::class, 'messagesIndex'])->name('guardians.messages.index');
        Route::get('bulk-messages/show/{id}', [App\Http\Controllers\GuardianController::class, 'messagesShow'])->name('guardians.messages.show');

        Route::resource('guardians', App\Http\Controllers\GuardianController::class)->except(['show']);
    });

    // Invoice payment CSV import (Admin only).
    // Declared BEFORE the invoices resource route so 'import-payments' is not
    // swallowed by the {invoice} wildcard.
    Route::middleware('permission:10001')->group(function () {
        Route::get('invoices/import-payments', [App\Http\Controllers\InvoicePaymentImportController::class, 'form'])
            ->name('invoices.import-payments.form');
        Route::post('invoices/import-payments/preview', [App\Http\Controllers\InvoicePaymentImportController::class, 'preview'])
            ->name('invoices.import-payments.preview');
        Route::post('invoices/import-payments/apply', [App\Http\Controllers\InvoicePaymentImportController::class, 'apply'])
            ->name('invoices.import-payments.apply');
        Route::get('invoices/import-payments/{runId}/{file}', [App\Http\Controllers\InvoicePaymentImportController::class, 'report'])
            ->name('invoices.import-payments.report');
    });

    // Invoice Management routes
    Route::middleware('permission:10061')->group(function () {
        Route::post('invoices/export', [App\Http\Controllers\InvoiceController::class, 'export'])->name('invoices.export');
        Route::post('invoices/export-pdf', [App\Http\Controllers\InvoiceController::class, 'exportPdf'])->name('invoices.export-pdf');
        Route::get('invoices/{invoice}/pdf', [App\Http\Controllers\InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
        Route::resource('invoices', App\Http\Controllers\InvoiceController::class)->except(['destroy', 'edit']);
        Route::post('invoices/{invoice}/void', [App\Http\Controllers\InvoiceController::class, 'void'])->name('invoices.void');
        Route::post('invoices/{invoice}/recalculate', [App\Http\Controllers\InvoiceController::class, 'recalculate'])->name('invoices.recalculate');
        Route::post('invoices/{invoice}/mark-as-paid', [App\Http\Controllers\InvoiceController::class, 'markAsPaid'])->name('invoices.mark-as-paid');
    });

    // Fee Plan Management routes
    Route::middleware('permission:10161')->group(function () {
        Route::resource('fee-plans', App\Http\Controllers\FeePlanController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10162')
            ->middlewareFor('destroy', 'permission:10163');
        Route::resource('school-fee-plans', App\Http\Controllers\SchoolFeePlanController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10162')
            ->middlewareFor('destroy', 'permission:10163');
    });

    // Initiator Fee Plan Management routes
    Route::middleware('permission:10301')->group(function () {
        Route::resource('initiator-fee-plans', App\Http\Controllers\InitiatorFeePlanController::class);
    });

    // Discount Rule Management routes
    Route::middleware('permission:10171')->group(function () {
        Route::resource('discount-rules', App\Http\Controllers\DiscountRuleController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10172')
            ->middlewareFor('destroy', 'permission:10173');
    });

    // Payment Mode Management routes
    Route::middleware('permission:10181')->group(function () {
        Route::resource('payment-modes', App\Http\Controllers\PaymentModeController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10182')
            ->middlewareFor('destroy', 'permission:10183');
    });

    // Payment Log Management routes
    Route::middleware('permission:10071')->group(function () {
        Route::resource('payments', App\Http\Controllers\PaymentLogController::class)->except(['destroy']);
        Route::post('payments/{payment}/update-status', [App\Http\Controllers\PaymentLogController::class, 'updateStatus'])
            ->name('payments.update-status');
    });

    // Parcel Management routes
    Route::middleware('permission:10111')->group(function () {
        Route::resource('parcels', App\Http\Controllers\ParcelController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10112')
            ->middlewareFor('destroy', 'permission:10113');
    });

    // Parcel Company Management routes
    Route::middleware('permission:10191')->group(function () {
        Route::resource('parcel-companies', App\Http\Controllers\ParcelCompanyController::class)
            ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10192')
            ->middlewareFor('destroy', 'permission:10193');
    });

    // Event Management routes
    Route::middleware('permission:10401')->group(function () {
        Route::resource('events', App\Http\Controllers\EventController::class)->except(['show']);
        Route::get('events/{event}/reuse', [App\Http\Controllers\EventController::class, 'reuse'])->name('events.reuse');
        Route::get('events/{event}', [App\Http\Controllers\EventController::class, 'show'])->name('events.show');
    });

    // Growth Card Management routes
    Route::middleware('permission:10501')->group(function () {
        Route::get('student-growth/student/{student}', [App\Http\Controllers\StudentGrowthController::class, 'studentGrowth'])->name('student-growth.student');
        Route::resource('student-growth', App\Http\Controllers\StudentGrowthController::class);

        // Growth Criteria Management (nested under student-growth permission or its own)
        Route::middleware('permission:10504')->group(function () {
            Route::resource('growth-criteria', App\Http\Controllers\GrowthCriteriaController::class);
        });
    });


    // Material Management routes
    Route::middleware('permission:10601')->group(function () {
        Route::resource('materials', App\Http\Controllers\MaterialController::class);
        Route::delete('material-images/{image}', [App\Http\Controllers\MaterialController::class, 'deleteImage'])->name('materials.delete-image');
    });

    // Material Request Management routes
    Route::middleware('permission:10611')->group(function () {
        Route::resource('material-requests', App\Http\Controllers\MaterialRequestController::class);
        Route::post('material-requests/{materialRequest}/update-status', [App\Http\Controllers\MaterialRequestController::class, 'updateStatus'])
            ->name('material-requests.update-status');
    });

    // Blog Management routes
    Route::middleware('permission:10701')->group(function () {
        Route::resource('blogs', App\Http\Controllers\BlogController::class);
        Route::get('blogs/{blog}/review', [App\Http\Controllers\BlogController::class, 'review'])->name('blogs.review');
        Route::post('blogs/{blog}/review', [App\Http\Controllers\BlogController::class, 'processReview'])->name('blogs.process-review');
    });

    // Ticketing System routes
    Route::middleware('permission:10801')->group(function () {
        // Tickets index and basic CRUD
        Route::get('tickets', [App\Http\Controllers\TicketController::class, 'index'])->name('tickets.index');
        Route::get('tickets/create', [App\Http\Controllers\TicketController::class, 'create'])->name('tickets.create');
        Route::post('tickets', [App\Http\Controllers\TicketController::class, 'store'])->name('tickets.store');
        Route::get('tickets/{ticket}', [App\Http\Controllers\TicketController::class, 'show'])->name('tickets.show');
        Route::post('tickets/{ticket}/status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.update-status');
        Route::post('tickets/{ticket}/comments', [App\Http\Controllers\TicketController::class, 'storeComment'])->name('tickets.comments.store');

        // Ticket Categories CRUD
        Route::resource('ticket-categories', App\Http\Controllers\TicketCategoryController::class);
    });

    // Notification Management
    Route::get('/notifications-manage', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications-manage/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');

    // Mark notification as read
    Route::get('/notifications/{id}/read', function ($id) {
        $notification = auth()->user()->notifications()->find($id);
        if ($notification) {
            $notification->markAsRead();

            // Redirect to link if exists
            if (isset($notification->data['link'])) {
                return redirect($notification->data['link']);
            }
        }
        return redirect()->back();
    })->name('notifications.read');

    // Certificates
    Route::middleware('permission:10901')->group(function () {
        Route::get('certificates/preview/{id}', [App\Http\Controllers\CertificateController::class, 'preview'])->name('certificates.preview');
        Route::resource('certificates', App\Http\Controllers\CertificateController::class);
    });

    // Global Search route
    Route::get('/search', [App\Http\Controllers\SearchController::class, 'search'])->name('search');

    // Location Management routes
    Route::prefix('locations')->name('locations.')->group(function () {
        Route::get('countries', [App\Http\Controllers\LocationController::class, 'indexCountries'])->name('countries.index');
        Route::get('countries/create', [App\Http\Controllers\LocationController::class, 'createCountry'])->name('countries.create');
        Route::post('countries', [App\Http\Controllers\LocationController::class, 'storeCountry'])->name('countries.store');
        Route::get('countries/{country}/edit', [App\Http\Controllers\LocationController::class, 'editCountry'])->name('countries.edit');
        Route::put('countries/{country}', [App\Http\Controllers\LocationController::class, 'updateCountry'])->name('countries.update');
        Route::delete('countries/{country}', [App\Http\Controllers\LocationController::class, 'destroyCountry'])->name('countries.destroy');

        Route::get('states', [App\Http\Controllers\LocationController::class, 'indexStates'])->name('states.index');
        Route::get('states/create', [App\Http\Controllers\LocationController::class, 'createState'])->name('states.create');
        Route::post('states', [App\Http\Controllers\LocationController::class, 'storeState'])->name('states.store');
        Route::get('states/{state}/edit', [App\Http\Controllers\LocationController::class, 'editState'])->name('states.edit');
        Route::put('states/{state}', [App\Http\Controllers\LocationController::class, 'updateState'])->name('states.update');
        Route::delete('states/{state}', [App\Http\Controllers\LocationController::class, 'destroyState'])->name('states.destroy');

        Route::get('districts', [App\Http\Controllers\LocationController::class, 'indexDistricts'])->name('districts.index');
        Route::get('districts/create', [App\Http\Controllers\LocationController::class, 'createDistrict'])->name('districts.create');
        Route::post('districts', [App\Http\Controllers\LocationController::class, 'storeDistrict'])->name('districts.store');
        Route::get('districts/{district}/edit', [App\Http\Controllers\LocationController::class, 'editDistrict'])->name('districts.edit');
        Route::put('districts/{district}', [App\Http\Controllers\LocationController::class, 'updateDistrict'])->name('districts.update');
        Route::delete('districts/{district}', [App\Http\Controllers\LocationController::class, 'destroyDistrict'])->name('districts.destroy');

        Route::get('talukos', [App\Http\Controllers\LocationController::class, 'indexTalukos'])->name('talukos.index');
        Route::get('talukos/create', [App\Http\Controllers\LocationController::class, 'createTaluko'])->name('talukos.create');
        Route::post('talukos', [App\Http\Controllers\LocationController::class, 'storeTaluko'])->name('talukos.store');
        Route::get('talukos/{taluko}/edit', [App\Http\Controllers\LocationController::class, 'editTaluko'])->name('talukos.edit');
        Route::put('talukos/{taluko}', [App\Http\Controllers\LocationController::class, 'updateTaluko'])->name('talukos.update');
        Route::delete('talukos/{taluko}', [App\Http\Controllers\LocationController::class, 'destroyTaluko'])->name('talukos.destroy');

        // API endpoints for cascading dropdowns (details remain authenticated)
        Route::get('api/district-details/{district}', [App\Http\Controllers\LocationController::class, 'getDistrictDetails'])->name('api.district-details');
        Route::get('api/taluka-details/{taluka}', [App\Http\Controllers\LocationController::class, 'getTalukaDetails'])->name('api.taluka-details');
    });
});

// Lead Management routes (Initiator/Admin only)
Route::middleware(['auth', 'permission:10001|10201'])->group(function () {
    Route::resource('leads', App\Http\Controllers\LeadController::class)
        ->middlewareFor(['create', 'store', 'edit', 'update'], 'permission:10001|10202')
        ->middlewareFor('destroy', 'permission:10001|10203');
    Route::post('leads/{lead}/regenerate-token', [App\Http\Controllers\LeadController::class, 'regenerateToken'])
        ->name('leads.regenerate-token')->middleware('permission:10001|10202');
});

// Student Lead routes (people captured while regular registration was closed)
// NOTE: /export must be declared before /{id}, and /{id} constrained to digits,
// or GET /student-leads/export resolves to show('export').
Route::middleware(['auth', 'permission:10001|11701|11703'])->prefix('student-leads')->name('student-leads.')->group(function () {
    Route::get('/', [App\Http\Controllers\StudentLeadController::class, 'index'])->name('index');
    Route::get('/export', [App\Http\Controllers\StudentLeadController::class, 'export'])->name('export');
    Route::post('/bulk-send-payment-link', [App\Http\Controllers\StudentLeadController::class, 'bulkSendPaymentLink'])
        ->name('bulk-send-payment-link')->middleware('permission:10001|11703');
    Route::get('/{id}', [App\Http\Controllers\StudentLeadController::class, 'show'])->name('show')->whereNumber('id');
    Route::post('/{id}/send-payment-link', [App\Http\Controllers\StudentLeadController::class, 'sendPaymentLink'])
        ->name('send-payment-link')->whereNumber('id')->middleware('permission:10001|11703');
    Route::delete('/{id}', [App\Http\Controllers\StudentLeadController::class, 'destroy'])
        ->name('destroy')->whereNumber('id')->middleware('permission:10001|11703');
});

// Enquiry routes (records posted in by the external site)
Route::middleware(['auth', 'permission:10001|11704|11706'])->prefix('enquiries')->name('enquiries.')->group(function () {
    Route::get('/', [App\Http\Controllers\EnquiryController::class, 'index'])->name('index');
    Route::get('/export', [App\Http\Controllers\EnquiryController::class, 'export'])->name('export');
    Route::get('/{enquiry}', [App\Http\Controllers\EnquiryController::class, 'show'])->name('show')->whereNumber('enquiry');
    Route::post('/{enquiry}/status', [App\Http\Controllers\EnquiryController::class, 'updateStatus'])
        ->name('update-status')->whereNumber('enquiry')->middleware('permission:10001|11705|11706');
    Route::delete('/{enquiry}', [App\Http\Controllers\EnquiryController::class, 'destroy'])
        ->name('destroy')->whereNumber('enquiry')->middleware('permission:10001|11706');
});

// Registration Control (open/close the public registration form)
Route::middleware(['auth', 'permission:10001|11707'])->group(function () {
    Route::get('/settings/registration-control', [App\Http\Controllers\RegistrationControlController::class, 'edit'])
        ->name('settings.registration.edit');
    Route::put('/settings/registration-control', [App\Http\Controllers\RegistrationControlController::class, 'update'])
        ->name('settings.registration.update');
});

// School Registration routes (Public with token validation)
Route::get('register-school/{token}', [App\Http\Controllers\SchoolRegistrationController::class, 'showRegistrationForm'])
    ->name('school-registration.landing');
Route::post('register-school/check-user', [App\Http\Controllers\SchoolRegistrationController::class, 'checkExistingUser'])
    ->name('school-registration.check-user');
Route::post('register-school/create-account', [App\Http\Controllers\SchoolRegistrationController::class, 'createAccount'])
    ->name('school-registration.create-account');
Route::post('register-school/login', [App\Http\Controllers\SchoolRegistrationController::class, 'loginExisting'])
    ->name('school-registration.login');
Route::get('register-school/{token}/create-account', [App\Http\Controllers\SchoolRegistrationController::class, 'showCreateAccountForm'])
    ->name('school-registration.create-account.form');
Route::get('register-school/{token}/school-form', [App\Http\Controllers\SchoolRegistrationController::class, 'showSchoolForm'])
    ->middleware('auth')
    ->name('school-registration.school-form');
Route::post('register-school/submit', [App\Http\Controllers\SchoolRegistrationController::class, 'registerSchool'])
    ->middleware('auth')
    ->name('school-registration.submit');

// Student Registration via School Link (Public)
Route::get('register-student/school/{token}', [App\Http\Controllers\RegistrationController::class, 'registerFromSchoolLink'])
    ->name('register-student.school-link');
Route::get('register-student/school-payment/{encodedId}', [App\Http\Controllers\RegistrationController::class, 'schoolPaymentForm'])
    ->name('registration.school-payment');
Route::post('register-student/school-payment/{encodedId}', [App\Http\Controllers\RegistrationController::class, 'storeSchoolPayment'])
    ->name('registration.store-school-payment');

// School Approval routes (Admin only)
Route::middleware(['auth', 'permission:10032'])->group(function () {
    Route::get('schools/{school}/approve', [App\Http\Controllers\SchoolController::class, 'showApprovalForm'])
        ->name('schools.approve-form');
    Route::post('schools/{school}/approve', [App\Http\Controllers\SchoolController::class, 'approve'])
        ->name('schools.approve');
});

// Batch Verification routes (Master Admin/Accountant)
Route::middleware(['auth', 'permission:10081'])->group(function () {
    Route::resource('batch-verification', App\Http\Controllers\BatchVerificationController::class)
        ->only(['index', 'show']);
    Route::post('batch-verification/{batch}/verify', [App\Http\Controllers\BatchVerificationController::class, 'verify'])
        ->name('batch-verification.verify');
    Route::get('batch-verification/{batch}/verify', function ($batch) {
        return redirect()->route('batch-verification.show', $batch);
    });
    Route::post('batch-verification/{batch}/upload-document', [App\Http\Controllers\BatchVerificationController::class, 'uploadDocument'])
        ->name('batch-verification.upload-document');
});

Route::get('/language/{lang}', function ($lang) {
    if (in_array($lang, ['en', 'hi', 'gu'])) {
        session()->put('locale', $lang);
    }
    return redirect()->back();
})->name('language.switch');

Route::get('/', [App\Http\Controllers\RegistrationController::class, 'index'])->name('home');

Route::post('/registration/submit', [App\Http\Controllers\RegistrationController::class, 'store'])->name('registration.submit');
Route::post('/registration/send-pre-registration-otp', [App\Http\Controllers\RegistrationController::class, 'sendPreRegistrationOtp'])->name('registration.send-pre-registration-otp');
Route::post('/registration/save-progress', [App\Http\Controllers\RegistrationController::class, 'saveProgress'])->name('registration.save-progress');

// Student Lead capture (used only while regular registration is closed)
Route::post('/registration/lead-submit', [App\Http\Controllers\RegistrationController::class, 'storeLead'])->name('registration.lead-submit');
Route::get('/registration/lead-thanks/{id}', [App\Http\Controllers\RegistrationController::class, 'leadThanks'])->name('registration.lead-thanks');

// Public Location APIs for Cascading Dropdowns (Unauthenticated)
Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('api/states/{country}', [App\Http\Controllers\LocationController::class, 'getStates'])->name('api.states');
    Route::get('api/districts/{state}', [App\Http\Controllers\LocationController::class, 'getDistricts'])->name('api.districts');
    Route::get('api/talukos/{district}', [App\Http\Controllers\LocationController::class, 'getTalukos'])->name('api.talukos');
});

// School Autocomplete APIs
Route::get('api/schools/suggest', [App\Http\Controllers\RegistrationController::class, 'suggestSchools'])->name('api.schools.suggest');
Route::get('api/schools/check', [App\Http\Controllers\RegistrationController::class, 'checkSchool'])->name('api.schools.check');

// Public Enquiry intake for the external website. Guarded by a shared API key
// (X-API-KEY header) and rate limited; CSRF-exempt via bootstrap/app.php.
Route::post('api/enquiries', [App\Http\Controllers\Api\EnquiryApiController::class, 'store'])
    ->middleware(['api.key', 'throttle:60,1'])
    ->name('api.enquiries.store');

// Post-Registration Flow
Route::get('/plans/{id}', [App\Http\Controllers\RegistrationController::class, 'selectPlan'])->name('registration.plans');
Route::post('/save-plan', [App\Http\Controllers\RegistrationController::class, 'savePlan'])->name('registration.save-plan');
Route::post('/pay-later', [App\Http\Controllers\RegistrationController::class, 'payLater'])->name('registration.pay-later');
Route::get('/payment/{id}/{plan_id}', [App\Http\Controllers\RegistrationController::class, 'paymentForm'])->name('registration.payment');
Route::get('/payment-status', [App\Http\Controllers\RegistrationController::class, 'paymentStatus'])->name('registration.payment-status');
Route::post('/razorpay/webhook', [App\Http\Controllers\RazorpayWebhookController::class, 'handle'])->name('razorpay.webhook');
Route::get('/documents/{id}', [App\Http\Controllers\RegistrationController::class, 'documentForm'])->name('documents.form');
Route::post('/documents-store', [App\Http\Controllers\RegistrationController::class, 'documentStore'])->name('documents.store');
Route::post('/documents/send-otp', [App\Http\Controllers\RegistrationController::class, 'sendOtp'])->name('documents.send-otp');
Route::post('/documents/verify-otp', [App\Http\Controllers\RegistrationController::class, 'verifyOtp'])->name('documents.verify-otp');
Route::get('/registration/success/{id}', [App\Http\Controllers\RegistrationController::class, 'success'])->name('registration.success');


Route::get('/registration', function () {
    return redirect()->route('home');
})->name('registration.index');

// Debug routes (remove in production)
if (app()->environment('local')) {
    require __DIR__ . '/debug.php';
}

Route::get('/test-mail', function (\App\Services\ExternalEmailService $emailService) {
    $toEmail = request('email', 'chiragkanjariya0712+ipdc@gmail.com');
    $to = [$toEmail => 'Chirag Kanjariya'];
    $subject = 'Test Mail API Integration';
    $messageHtml = '<p>This is a test email sent via ExternalEmailService API to verify the mail routing works correctly.</p>';

    $result = $emailService->sendSmtpMail($to, $subject, $messageHtml);

    if ($result) {
        return 'Test email sent successfully to ' . $toEmail . '. Check your inbox.';
    } else {
        return 'Failed to send test email. Check the application logs for details.';
    }
});

// Audit Log routes (Admin only)
Route::middleware('permission:10001')->group(function () {
    Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('/audit-logs/{auditLog}', [App\Http\Controllers\AuditLogController::class, 'show'])->name('audit-logs.show');
    Route::post('/audit-logs/for-model', [App\Http\Controllers\AuditLogController::class, 'forModel'])->name('audit-logs.for-model');
});

// Agreement Template routes
Route::middleware('permission:11001')->group(function () {
    Route::resource('agreement-templates', App\Http\Controllers\AgreementTemplateController::class)->except(['show']);
});

// Mail Template routes
Route::middleware('permission:11301')->group(function () {
    Route::resource('mail-templates', App\Http\Controllers\MailTemplateController::class)->except(['show']);
});

// Laravel Logs routes
Route::middleware(['auth', 'permission:11501'])->group(function () {
    Route::get('laravel-logs', [App\Http\Controllers\LaravelLogController::class, 'index'])->name('laravel-logs.index');
});
Route::middleware(['auth', 'permission:11502'])->group(function () {
    Route::post('laravel-logs/clear', [App\Http\Controllers\LaravelLogController::class, 'clear'])->name('laravel-logs.clear');
});
Route::middleware(['auth', 'permission:11503'])->group(function () {
    Route::delete('laravel-logs/delete', [App\Http\Controllers\LaravelLogController::class, 'destroy'])->name('laravel-logs.destroy');
});

// Storage Fallback Route (for environments where symlink is missing/broken)
Route::get('storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);
    if (!file_exists($fullPath)) {
        abort(404);
    }

    $file = file_get_contents($fullPath);
    $type = mime_content_type($fullPath);

    return response($file)->header('Content-Type', $type);
})->where('path', '.*');

// Pincode Proxy Route to avoid SSL/CORS issues with Fallback
Route::get('/api/pincode/{pincode}', function ($pincode) {
    try {
        $context = stream_context_create(['http' => ['timeout' => 5]]);
        $response = @file_get_contents('http://www.postalpincode.in/api/pincode/' . $pincode, false, $context);
        if ($response) {
            $data = json_decode($response, true);
            if (isset($data['Status']) && $data['Status'] === 'Success') {
                return response()->json([$data]);
            }
        }
    } catch (\Exception $e) {
        // ignore and fallback
    }

    try {
        // Fallback to zippopotam.us
        $context = stream_context_create([
            'http' => ['timeout' => 5],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        $response = @file_get_contents('https://api.zippopotam.us/IN/' . $pincode, false, $context);
        if ($response) {
            $zipData = json_decode($response, true);

            if (isset($zipData['places']) && count($zipData['places']) > 0) {
                $postOffices = [];
                foreach ($zipData['places'] as $place) {
                    $postOffices[] = [
                        'Name' => $place['place name'] ?? '',
                        'State' => $place['state'] ?? '',
                        'District' => '', // zippopotam doesn't provide District consistently
                        'Block' => ''
                    ];
                }

                return response()->json([
                    [
                        'Status' => 'Success',
                        'PostOffice' => $postOffices
                    ]
                ]);
            }
        }
        return response()->json([['Status' => 'Error', 'Message' => 'No places found']]);
    } catch (\Exception $e2) {
        return response()->json([['Status' => 'Error', 'Message' => 'Fallback Failed: ' . $e2->getMessage()]]);
    }
})->name('api.pincode');
