# Student Bulk Upload Guide

## Overview

The Student Bulk Upload System allows you to upload multiple students at once via CSV file. The system validates the data, calculates fees based on the selected semester batch, and allows you to preview and edit before submitting for verification.

## Features

1. **CSV Upload**: Upload up to 500 students at once
2. **Validation**: Automatic validation of all required fields and data formats
3. **Fee Calculation**: Automatic fee calculation based on semester batch and fee plans
4. **Preview & Edit**: Review all students with calculated fees before submission
5. **Error Handling**: Clear error messages for validation issues
6. **Batch Tracking**: Each upload is tracked with a unique batch code

## CSV File Format

### Required Columns

- `studenttype_id`: Student Type ID (must exist in studenttype table)
- `eduboard_id`: Educational Board ID (must exist in ms_eductionalboard table)
- `std_id`: Standard ID (must exist in ms_standard table)
- `takula_id`: Taluka ID (must exist in ms_taluko table)
- `fname`: First Name (max 100 characters)
- `mname`: Middle Name (max 100 characters)
- `lname`: Last Name (max 100 characters)
- `email`: Email address (must be unique and valid format)

### Optional Columns

- `school_id`: School ID (if applicable)
- `gender`: Gender (M, F, or O)
- `birthdate`: Birth date (format: YYYY-MM-DD)
- `address`: Address (max 500 characters)
- `city_village`: City/Village (max 100 characters)
- `pincode`: Pincode (max 10 characters)
- `contact_no`: Contact Number (max 20 characters)
- `whatsapp_no`: WhatsApp Number (max 20 characters)
- `educationmedium`: Education Medium (max 50 characters)
- `institute`: Institute Name (max 200 characters)
- `achievement_stsang`: Satsang Achievements (text)
- `achievement_academic`: Academic Achievements (text)
- `activities`: Activities (text)
- `learnabout`: How they learned about us (max 200 characters)
- `materiallanguage`: Material Language (max 50 characters)
- `taluka_id_postal`: Postal Taluka ID
- `city_postal`: Postal City (max 100 characters)
- `address_postal`: Postal Address (max 500 characters)
- `notes`: Notes (text)
- `refby`: Referred By (max 200 characters)

### Sample CSV

```csv
studenttype_id,eduboard_id,std_id,takula_id,fname,mname,lname,email,gender,birthdate
1,1,1,1,John,Kumar,Doe,john.doe@example.com,M,2000-01-15
1,1,1,1,Jane,Devi,Smith,jane.smith@example.com,F,2001-05-20
1,1,2,1,Bob,Singh,Johnson,bob.johnson@example.com,M,1999-12-10
```

## Upload Process

### Step 1: Access Bulk Upload Form

1. Navigate to Students page
2. Click "Bulk Upload" button
3. Select the semester batch for fee calculation
4. Choose your CSV file
5. Click "Upload and Preview"

### Step 2: Review Preview

The preview page shows:
- **Summary Cards**: Total students, fee per student, and total fees
- **Student Table**: All students with their data and calculated fees
- **Validation Status**: Each row shows if it's valid or has errors
- **Error Details**: Any validation errors are displayed below the row

### Step 3: Fix Errors (if any)

If there are validation errors:
1. Click "Edit" on the row with errors
2. Correct the data
3. The system will re-validate and recalculate fees
4. Continue until all errors are resolved

### Step 4: Submit for Verification

Once all validation errors are fixed:
1. Review the total fees summary
2. Click "Submit for Verification"
3. The batch is created with a unique batch code
4. An accountant will verify and process the payment

## Validation Rules

### Email Validation
- Must be a valid email format
- Must be unique (not already in the system)

### Foreign Key Validation
- All ID fields must reference existing records
- System validates against master data tables

### Date Validation
- Dates must be in YYYY-MM-DD format
- Must be valid dates

### Gender Validation
- Must be M (Male), F (Female), or O (Other)

## Fee Calculation

Fees are calculated based on:
1. **Semester Batch**: Selected during upload
2. **Fee Plans**: Applicable fee plans for the batch
3. **School**: If student is assigned to a school
4. **Standard**: Student's academic standard
5. **Discounts**: Automatic application of school-level discounts

The system calculates:
- Subtotal from fee plans
- Discount total (if applicable)
- Total due (subtotal - discounts)

## Upload Batch Tracking

Each upload creates a batch record with:
- **Batch Code**: Unique identifier (format: BATCH-YYYYMMDD-XXXX)
- **Upload Date**: When the batch was created
- **Uploader**: User who created the batch
- **Status**: pending, approved, or rejected
- **Total Students**: Number of students in the batch
- **Total Fees**: Sum of all student fees

## Accountant Verification

After submission:
1. Batch appears in accountant's verification queue
2. Accountant reviews student list and fees
3. Accountant adds payment details
4. Accountant approves or rejects the batch
5. On approval, all students are converted to registered students

## Error Messages

### Common Errors

- **"Missing required columns"**: CSV is missing mandatory columns
- **"Maximum 500 students allowed"**: File has too many rows
- **"Email already exists"**: Email is already in the system
- **"Invalid email format"**: Email is not properly formatted
- **"Invalid Student Type ID"**: Student type doesn't exist
- **"Invalid Educational Board ID"**: Board doesn't exist
- **"Invalid Standard ID"**: Standard doesn't exist
- **"Invalid Taluka ID"**: Taluka doesn't exist
- **"Gender must be M, F, or O"**: Invalid gender value
- **"Invalid birthdate format"**: Date is not in YYYY-MM-DD format

## Best Practices

1. **Validate Data First**: Check your CSV data before uploading
2. **Use Sample Template**: Download and use the sample CSV format
3. **Check IDs**: Ensure all ID fields reference existing records
4. **Unique Emails**: Verify emails are unique before uploading
5. **Batch Size**: Keep batches under 500 students for better performance
6. **Review Preview**: Always review the preview before submitting
7. **Fix All Errors**: Don't submit until all validation errors are resolved

## Troubleshooting

### Upload Fails
- Check CSV file format
- Verify all required columns are present
- Ensure file size is under 10MB

### Validation Errors
- Review error messages for each row
- Check master data tables for valid IDs
- Verify email uniqueness

### Fee Calculation Issues
- Ensure semester batch has fee plans configured
- Check if school has specific fee plans
- Verify discount rules are properly configured

## Technical Details

### Database Tables

- `upload_batch`: Stores batch metadata
- `pending_student`: Stores student data awaiting verification
- `student`: Final student records after approval

### Workflow

1. CSV Upload → Validation
2. Fee Calculation → Preview
3. Edit (if needed) → Re-validation
4. Submit → Create Batch
5. Accountant Verification → Approval
6. Convert to Registered Students

### Session Storage

During the upload process, data is stored in session:
- `bulk_upload_data`: Student data with validation results
- `bulk_upload_sembatches_id`: Selected semester batch
- `bulk_upload_has_errors`: Error flag
- `bulk_upload_totals`: Calculated totals

Session data is cleared after successful submission.
