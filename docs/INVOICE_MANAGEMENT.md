# Invoice Management Implementation

## Overview

The Invoice Management system has been successfully implemented as part of Task 14. This system allows for generating, managing, and tracking invoices for students based on fee plans and discount rules.

## Components Implemented

### 1. InvoiceService (`app/Services/InvoiceService.php`)

The service layer handles all business logic for invoice operations:

**Key Methods:**
- `generateInvoice($studentId, $semesterBatchId, $options)` - Generates a new invoice for a student
- `getApplicableFeePlans($student, $semesterBatchId)` - Fetches fee plans based on school, standard, and batch
- `calculateDiscounts($student, $subtotal)` - Applies school-level and student-level discounts
- `updateStatus($invoiceId, $status)` - Updates invoice status with validation
- `voidInvoice($invoiceId)` - Voids an invoice
- `recalculateInvoice($invoiceId)` - Recalculates invoice totals

**Invoice Generation Logic:**
1. Fetch applicable fee plans for the student/school/batch combination
2. Calculate subtotal from all fee plan items
3. Apply discount rules (school-level first, then student-level)
4. Calculate total_due = subtotal - discount_total
5. Create invoice record with calculated totals
6. Create invoice line items for each fee plan

**Discount Application:**
- School-level discounts are applied first
- Student-level discounts are applied second
- Supports both FLAT and PERCENT discount types
- Only active discounts within valid date ranges are applied

### 2. InvoiceController (`app/Http/Controllers/InvoiceController.php`)

The controller handles HTTP requests and responses:

**Routes:**
- `GET /invoices` - List all invoices with filters
- `GET /invoices/create` - Show invoice creation form
- `POST /invoices` - Generate a new invoice
- `GET /invoices/{id}` - Show invoice details
- `PUT /invoices/{id}` - Update invoice status and notes
- `POST /invoices/{id}/void` - Void an invoice
- `POST /invoices/{id}/recalculate` - Recalculate invoice totals

**Role-Based Access:**
- Admin: Can view all invoices
- Initiator: Can view invoices for assigned schools
- School: Can view invoices for their school only

**Filters Available:**
- Status (DRAFT, ISSUED, PARTIAL, PAID, VOID)
- School
- Student
- Date range (from/to)

### 3. Views

**Invoice List (`resources/views/invoices/index.blade.php`):**
- Displays all invoices in a table format
- Shows invoice number, date, student, school, amounts, and status
- Includes filters for status and date range
- Pagination support
- Color-coded status badges

**Invoice Create (`resources/views/invoices/create.blade.php`):**
- Form to generate a new invoice
- Student selection dropdown
- Semester batch selection
- Due date picker (defaults to 30 days from now)
- Status selection (DRAFT or ISSUED)
- Notes field

**Invoice Detail (`resources/views/invoices/show.blade.php`):**
- Complete invoice information display
- Student and school details
- Itemized list of fees
- Subtotal, discount, and total due breakdown
- Payment history (if any)
- Status update form
- Actions: Void invoice, Recalculate totals
- Color-coded status display

## Invoice Status Flow

```
DRAFT → ISSUED → PARTIAL → PAID
  ↓       ↓
VOID    VOID
```

**Status Descriptions:**
- **DRAFT**: Invoice created but not yet issued
- **ISSUED**: Invoice sent to student/school
- **PARTIAL**: Partial payment received
- **PAID**: Fully paid
- **VOID**: Invoice cancelled/voided

## Fee Plan Selection Logic

The system selects applicable fee plans based on:
1. Semester batch match
2. School-specific OR standard-specific OR general (no school/standard)
3. Valid date range (valid_from <= today AND (valid_to IS NULL OR valid_to >= today))

Priority order:
1. School-specific fee plans
2. Standard-specific fee plans
3. General fee plans

## Discount Application Logic

**School-Level Discounts:**
- Applied to all students in a school
- Scope: SCHOOL
- Requires school_id

**Student-Level Discounts:**
- Applied to specific students
- Scope: STUDENT
- Requires student_id

**Discount Types:**
- **FLAT**: Fixed amount discount (e.g., ₹500 off)
- **PERCENT**: Percentage discount (e.g., 10% off)

**Validation:**
- Only active discounts are applied
- Must be within valid date range
- Can specify which fee components the discount applies to

## Usage Examples

### Generate Invoice via UI
1. Navigate to Invoices → Generate Invoice
2. Select student from dropdown
3. Select semester batch
4. Set due date (optional)
5. Choose status (DRAFT or ISSUED)
6. Add notes (optional)
7. Click "Generate Invoice"

### Generate Invoice Programmatically
```php
use App\Services\InvoiceService;

$invoiceService = new InvoiceService();

$invoice = $invoiceService->generateInvoice(
    studentId: 1,
    semesterBatchId: 1,
    options: [
        'due_date' => '2025-12-31',
        'notes' => 'First semester fees',
        'status' => 'ISSUED'
    ]
);
```

### Update Invoice Status
```php
$invoiceService->updateStatus(invoiceId: 1, status: 'PAID');
```

### Void Invoice
```php
$invoiceService->voidInvoice(invoiceId: 1);
```

### Recalculate Invoice
```php
$invoiceService->recalculateInvoice(invoiceId: 1);
```

## Database Tables Used

- `invoice` - Main invoice records
- `invoice_item` - Invoice line items
- `fee_plan` - Fee plan definitions
- `discount_rule` - Discount rules
- `student` - Student information
- `school` - School information
- `sembatches` - Semester batch information

## Requirements Satisfied

✅ **Requirement 17.1**: Generate invoices based on fee plans for schools or individual students
✅ **Requirement 17.2**: Calculate total before discount from fee plan items
✅ **Requirement 17.3**: Apply discount rules automatically when applicable
✅ **Requirement 17.4**: Calculate total due as total before discount minus discount total
✅ **Requirement 17.5**: Set invoice status to ISSUED by default
✅ **Requirement 17.6**: Allow invoice status updates (DRAFT, PARTIAL, PAID, VOID)

## Testing Recommendations

1. **Unit Tests:**
   - Test fee plan selection logic
   - Test discount calculation (FLAT and PERCENT)
   - Test invoice total calculations
   - Test status transitions

2. **Feature Tests:**
   - Test invoice generation workflow
   - Test role-based access control
   - Test invoice filtering
   - Test void functionality
   - Test recalculation logic

3. **Integration Tests:**
   - Test complete invoice generation with multiple fee plans
   - Test discount application with multiple rules
   - Test invoice status transitions
   - Test payment recording against invoices

## Future Enhancements

- PDF invoice generation
- Email invoice to students
- Bulk invoice generation
- Invoice templates
- Payment gateway integration
- Automated payment reminders
- Invoice analytics and reporting
