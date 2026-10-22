# Accountant User Guide - Pramukh Sankalp School Management System

## Introduction

Welcome to the Pramukh Sankalp School Management System. As an Accountant user, you are responsible for verifying and processing student payments, particularly for bulk upload batches. You play a critical role in the student registration workflow.

## Getting Started

### Logging In

1. Navigate to the login page
2. Enter your username
3. Choose authentication method:
   - **Password**: Enter your password
   - **PIN**: Enter your 4-digit PIN
4. Click "Login"

### Dashboard Overview

After logging in, you'll see the Accountant Dashboard with:

- **Pending Batches**: Upload batches awaiting verification
- **Recent Payments**: Latest payment transactions
- **Payment Summary**: Total payments processed

## Upload Batch Verification

### Understanding Upload Batches

When users upload multiple students via CSV, they are grouped into an "upload batch" that requires payment verification before students are fully registered in the system.

Each batch contains:
- Multiple student records
- Calculated fees per student
- Total fees for the entire batch
- Uploader information
- Semester batch assignment

### Viewing Pending Batches

1. Navigate to **Accountant** → **Upload Batches**
2. View list of batches awaiting verification
3. Each batch shows:
   - Batch code
   - Upload date
   - Uploader name
   - Semester batch
   - Total students
   - Fee per student
   - Total fees
   - Status

### Filtering Batches

Use filters to find specific batches:
- **Date Range**: Filter by upload date
- **Semester Batch**: Filter by batch
- **School**: Filter by school
- **Status**: Pending, Approved, Rejected

### Reviewing Batch Details

1. Click on a batch from the list
2. View detailed information:
   - Batch summary
   - Complete list of students
   - Individual student details
   - Calculated fees per student
   - Total batch fees

### Verifying Student Data

Before approving a batch, verify:
- Student information is complete and accurate
- Email addresses are valid and unique
- Fees are calculated correctly
- All required fields are filled
- No validation errors exist

## Approving Upload Batches

### Approval Process

1. Navigate to **Accountant** → **Upload Batches**
2. Click on the batch to review
3. Verify all student data
4. Click **Approve Payment**
5. Fill in payment details:
   - **Payment Mode**: Cash, Credit Card, Debit Card, Check, etc.
   - **Transaction ID**: Unique transaction identifier
   - **Total Amount**: Total payment received
   - **Payment Date**: Date payment was received
6. Click **Confirm Approval**

### What Happens After Approval

When you approve a batch:
1. All students in the batch are marked as "paid"
2. Students are converted from "pending" to "registered" status
3. Students can now access the system
4. Invoices are generated for each student
5. Payment log is created
6. Uploader receives confirmation notification
7. Batch status changes to "approved"

### Bulk Approval Benefits

- Process multiple students at once
- Single payment transaction for entire batch
- Efficient workflow for large enrollments
- Consistent fee application

## Rejecting Upload Batches

### When to Reject

Reject a batch if:
- Payment information is incorrect or incomplete
- Student data has errors or inconsistencies
- Fees are calculated incorrectly
- Duplicate students exist
- Required documentation is missing

### Rejection Process

1. Navigate to **Accountant** → **Upload Batches**
2. Click on the batch to review
3. Click **Reject Batch**
4. Enter rejection reason (required)
5. Provide specific details about what needs correction
6. Click **Confirm Rejection**

### What Happens After Rejection

When you reject a batch:
1. Batch status changes to "rejected"
2. Uploader receives email notification with rejection reason
3. Students remain in "pending" status
4. Uploader can correct issues and re-submit
5. Batch appears in rejected batches list

### Best Practices for Rejection

- Provide clear, specific reasons
- List all issues found
- Suggest corrections needed
- Be constructive and helpful

## Payment Management

### Recording Individual Payments

For students not part of bulk uploads:

1. Navigate to **Payments** → **Create Payment**
2. Fill in required fields:
   - **Student**: Select student
   - **Payment Mode**: Select payment method
   - **Payment Date**: Date payment received
   - **Transaction ID**: Unique identifier
   - **Amount**: Payment amount
3. Optional fields:
   - Notes
   - Gateway response
4. Click **Save**

### Payment Status

- **PENDING**: Awaiting processing
- **AUTHORIZED**: Approved but not completed
- **PAID**: Successfully completed
- **FAILED**: Transaction failed
- **REFUNDED**: Payment returned
- **CANCELLED**: Transaction cancelled

### Updating Payment Status

1. Navigate to **Payments**
2. Find the payment record
3. Click **Edit**
4. Update status
5. Add notes if needed
6. Click **Save**

### Viewing Payment History

1. Navigate to **Payments**
2. Filter by:
   - Date range
   - Student
   - Payment mode
   - Status
3. Export report if needed

## Invoice Management

### Viewing Invoices

1. Navigate to **Invoices**
2. View all invoices in the system
3. Filter by:
   - Student
   - School
   - Status
   - Date range

### Invoice Status Management

Update invoice status as payments are processed:
- **DRAFT** → **ISSUED**: When invoice is sent
- **ISSUED** → **PARTIAL**: When partial payment received
- **PARTIAL** → **PAID**: When fully paid
- Any status → **VOID**: To cancel invoice

### Verifying Invoice Calculations

Check that invoices correctly show:
- Fee plan items
- Subtotal
- Discounts applied (school-level and student-level)
- Total due
- Payment received
- Balance remaining

## Discount Verification

### Reviewing Applied Discounts

When reviewing batches and invoices, verify:
- Correct discount rules are applied
- Discount amounts are calculated correctly
- School-level discounts applied first
- Student-level discounts applied second
- Discount validity dates are current

### Discount Types

- **FLAT**: Fixed amount discount (e.g., ₹500 off)
- **PERCENT**: Percentage discount (e.g., 10% off)

### Discount Scope

- **SCHOOL**: Applies to all students in a school
- **STUDENT**: Applies to specific student only

## Reports and Analytics

### Payment Reports

Generate reports for:
- Daily payment collections
- Monthly revenue
- Payment mode breakdown
- Pending payments
- Failed transactions

### Batch Reports

Track:
- Batches processed per day/week/month
- Average batch size
- Total students processed
- Approval vs. rejection rate

### Audit Trail

Review audit logs for:
- Payment transactions
- Batch approvals/rejections
- Status changes
- User actions

## Profile Management

### Updating Your Profile

1. Click on your name in the top right
2. Select **Profile**
3. Update your information
4. Click **Save**

### Changing Password

1. Go to **Profile** → **Change Password**
2. Enter current password
3. Enter new password
4. Confirm new password
5. Click **Update**

### Changing PIN

1. Go to **Profile** → **Change PIN**
2. Enter current PIN
3. Enter new 4-digit PIN
4. Confirm new PIN
5. Click **Update**

## Best Practices

1. **Timely Processing**: Review and process batches promptly
2. **Thorough Verification**: Always verify student data before approval
3. **Clear Communication**: Provide detailed rejection reasons
4. **Accurate Records**: Ensure payment details are correct
5. **Regular Monitoring**: Check for pending batches daily
6. **Audit Trail**: Review audit logs regularly
7. **Documentation**: Keep records of payment confirmations

## Common Tasks

### Processing a Bulk Upload Batch

1. Navigate to **Accountant** → **Upload Batches**
2. Click on pending batch
3. Review all student records
4. Verify fees are correct
5. Check for validation errors
6. Click **Approve Payment**
7. Enter payment details
8. Confirm approval

### Handling Payment Discrepancies

1. Identify the discrepancy
2. Contact uploader or admin
3. Verify correct amount
4. If minor: Approve with notes
5. If major: Reject with explanation

### Following Up on Rejected Batches

1. Navigate to rejected batches
2. Check if re-submitted
3. Verify corrections were made
4. Process re-submitted batch

### Recording a Refund

1. Navigate to **Payments**
2. Find original payment
3. Click **Edit**
4. Change status to **REFUNDED**
5. Add refund details in notes
6. Click **Save**

## Troubleshooting

### Cannot Approve Batch
- Verify all validation errors are resolved
- Check if batch is already processed
- Ensure you have accountant permissions
- Contact administrator if issue persists

### Payment Not Recording
- Verify all required fields are filled
- Check transaction ID is unique
- Ensure student exists in system
- Check payment mode is active

### Fees Calculated Incorrectly
- Verify fee plan exists for semester batch
- Check discount rules are configured correctly
- Review fee plan items
- Contact administrator to update fee plans

### Batch Stuck in Pending
- Check if there are validation errors
- Verify batch was submitted properly
- Contact uploader to re-submit if needed

## Security and Compliance

### Payment Security

- Never share transaction IDs publicly
- Verify payment sources
- Keep payment gateway responses secure
- Follow PCI compliance guidelines

### Data Privacy

- Protect student financial information
- Don't share payment details via unsecured channels
- Follow data protection regulations
- Report any security concerns immediately

### Audit Compliance

- All actions are logged
- Payment transactions are tracked
- Maintain accurate records
- Review audit logs regularly

## Notifications

### Email Notifications

You may receive notifications for:
- New batches awaiting verification
- Payment confirmations
- System alerts

### In-App Notifications

Watch for toast notifications for:
- Successful approvals (green)
- Errors (red)
- Warnings (yellow)
- Information (blue)

## Support

For assistance:
- Contact administrator for access issues
- Review the FAQ document
- Check audit logs for transaction history
- Email support for technical issues

## Quick Reference

### Batch Approval Checklist
- ✓ All student data is complete
- ✓ No validation errors
- ✓ Fees calculated correctly
- ✓ Payment received and verified
- ✓ Transaction ID recorded
- ✓ Payment mode selected
- ✓ Payment date entered

### Payment Status Flow
PENDING → AUTHORIZED → PAID
    ↓         ↓
  FAILED   REFUNDED
    ↓         ↓
CANCELLED  CANCELLED

### Required Payment Fields
- Student
- Payment Mode
- Payment Date
- Transaction ID
- Amount

---

*Last Updated: November 2025*
*Version: 1.0*
