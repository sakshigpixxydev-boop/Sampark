# CSV Upload Format and Requirements

## Overview

The bulk student upload feature allows you to add multiple students at once using a CSV (Comma-Separated Values) file. This document describes the required format, fields, and validation rules.

## File Requirements

### File Format
- **Format**: CSV (Comma-Separated Values)
- **Encoding**: UTF-8
- **Maximum Rows**: 500 students per upload
- **File Extension**: .csv

### Column Headers

The CSV file must include the following column headers in the first row (case-sensitive):

## Required Columns

| Column Name | Description | Format | Example |
|------------|-------------|---------|---------|
| `student_type` | Type of student | Text | "Regular" |
| `education_board` | Educational board | Text | "CBSE" |
| `standard` | Academic standard/grade | Text | "10" |
| `taluka` | Taluka/location | Text | "Mumbai" |
| `first_name` | Student's first name | Text | "Raj" |
| `middle_name` | Student's middle name | Text | "Kumar" |
| `last_name` | Student's last name | Text | "Patel" |
| `email` | Student's email address | Valid email | "raj.patel@example.com" |

## Optional Columns

| Column Name | Description | Format | Example |
|------------|-------------|---------|---------|
| `gender` | Student's gender | M/F/Other | "M" |
| `birthdate` | Date of birth | YYYY-MM-DD | "2005-06-15" |
| `address` | Residential address | Text | "123 Main St" |
| `city` | City | Text | "Mumbai" |
| `state` | State | Text | "Maharashtra" |
| `pincode` | Postal code | 6 digits | "400001" |
| `phone` | Phone number | 10 digits | "9876543210" |
| `mobile` | Mobile number | 10 digits | "9876543210" |
| `guardian_first_name` | Guardian's first name | Text | "Suresh" |
| `guardian_middle_name` | Guardian's middle name | Text | "Kumar" |
| `guardian_last_name` | Guardian's last name | Text | "Patel" |
| `guardian_relation` | Relation to student | Text | "Father" |
| `guardian_phone` | Guardian's phone | 10 digits | "9876543210" |

## Sample CSV Template

```csv
student_type,education_board,standard,taluka,first_name,middle_name,last_name,email,gender,birthdate,phone,guardian_first_name,guardian_middle_name,guardian_last_name,guardian_relation,guardian_phone
Regular,CBSE,10,Mumbai,Raj,Kumar,Patel,raj.patel@example.com,M,2005-06-15,9876543210,Suresh,Kumar,Patel,Father,9876543211
Regular,CBSE,10,Mumbai,Priya,Devi,Sharma,priya.sharma@example.com,F,2005-08-20,9876543212,Ramesh,Lal,Sharma,Father,9876543213
Regular,State Board,9,Pune,Amit,Singh,Verma,amit.verma@example.com,M,2006-03-10,9876543214,Vijay,Singh,Verma,Father,9876543215
```

## Validation Rules

### Required Field Validation

All required columns must:
- Be present in the CSV file
- Have values for every row
- Not be empty or contain only whitespace

### Email Validation

- Must be a valid email format (e.g., user@domain.com)
- Must be unique across all students in the system
- Cannot be duplicate within the CSV file
- Maximum 255 characters

### Name Validation

- First name, middle name, last name: Maximum 100 characters each
- Cannot contain special characters except hyphens and apostrophes
- Cannot be empty for required fields

### Phone Number Validation

- Must be exactly 10 digits
- Only numeric characters allowed
- No spaces, dashes, or other formatting

### Date Validation

- Birthdate must be in YYYY-MM-DD format
- Must be a valid date
- Student should be between 5 and 25 years old (reasonable range)

### Gender Validation

- Accepted values: M, F, Male, Female, Other
- Case-insensitive

### Pincode Validation

- Must be exactly 6 digits
- Only numeric characters allowed

## Data Lookup Validation

The following fields must match existing records in the system:

### Student Type
- Must exist in the student types master data
- Contact administrator if type doesn't exist

### Educational Board
- Must exist in the educational boards master data
- Common values: CBSE, ICSE, State Board, IB
- Contact administrator if board doesn't exist

### Standard
- Must exist in the standards master data
- Common values: 1-12, KG, Nursery
- Contact administrator if standard doesn't exist

### Taluka
- Must exist in the location master data
- Includes country → state → district → taluka hierarchy
- Contact administrator if location doesn't exist

## Upload Process

### Step 1: Prepare CSV File

1. Download the CSV template from the system
2. Fill in student data following the format above
3. Verify all required fields are filled
4. Check for duplicate emails
5. Ensure data types match requirements
6. Save file as CSV format

### Step 2: Upload File

1. Navigate to **Students** → **Bulk Upload**
2. Select **Semester Batch** (required)
3. Click **Choose File** and select your CSV
4. Click **Upload**

### Step 3: Review Validation

The system will validate your CSV and show:
- ✓ Valid rows (green)
- ✗ Invalid rows (red with error messages)
- Total students count
- Validation error summary

### Step 4: Preview and Edit

1. Review the preview table
2. Check calculated fees for each student
3. Edit individual records if needed (click Edit icon)
4. Fix any validation errors
5. Verify total fees summary

### Step 5: Submit for Verification

1. Once all errors are resolved
2. Review total students and total fees
3. Click **Submit for Verification**
4. Batch is sent to accountant for approval

### Step 6: Accountant Approval

1. Accountant reviews the batch
2. Accountant verifies payment
3. Accountant approves or rejects batch
4. If approved: Students are registered
5. If rejected: You receive email with reason

## Common Validation Errors

### Error: "Email already exists"
**Cause**: Email is already used by another student
**Solution**: Use a unique email address for each student

### Error: "Invalid email format"
**Cause**: Email doesn't match standard format
**Solution**: Use format like user@domain.com

### Error: "Student type not found"
**Cause**: Student type doesn't exist in master data
**Solution**: Contact administrator to add the type or use existing type

### Error: "Educational board not found"
**Cause**: Board doesn't exist in master data
**Solution**: Contact administrator to add the board or use existing board

### Error: "Standard not found"
**Cause**: Standard doesn't exist in master data
**Solution**: Contact administrator to add the standard or use existing standard

### Error: "Taluka not found"
**Cause**: Location doesn't exist in master data
**Solution**: Contact administrator to add the location or use existing location

### Error: "Invalid phone number"
**Cause**: Phone number is not 10 digits
**Solution**: Use exactly 10 digits, no spaces or formatting

### Error: "Invalid date format"
**Cause**: Birthdate is not in YYYY-MM-DD format
**Solution**: Use format like 2005-06-15

### Error: "Required field missing"
**Cause**: A required column is empty
**Solution**: Fill in all required fields for every row

### Error: "Maximum rows exceeded"
**Cause**: CSV has more than 500 students
**Solution**: Split into multiple files of 500 or fewer students

## Best Practices

### Data Preparation

1. **Use Template**: Always start with the system-provided template
2. **Verify Master Data**: Check that types, boards, standards, and locations exist
3. **Unique Emails**: Ensure each student has a unique email
4. **Consistent Format**: Use consistent date and phone formats
5. **Clean Data**: Remove extra spaces, special characters

### File Management

1. **Backup**: Keep a backup of your original CSV
2. **Version Control**: Name files with dates (e.g., students_2025-11-10.csv)
3. **Small Batches**: Upload in smaller batches for easier error handling
4. **Test First**: Test with a few rows before uploading large files

### Error Handling

1. **Read Errors Carefully**: Each error message indicates the specific issue
2. **Fix in Spreadsheet**: Correct errors in your spreadsheet application
3. **Re-upload**: Upload the corrected CSV file
4. **Incremental Fixes**: Fix a few errors at a time if there are many

### Communication

1. **Coordinate with Admin**: Ensure master data is up to date
2. **Notify Accountant**: Let accountant know when batch is ready
3. **Document Issues**: Keep notes on common errors for future uploads

## Fee Calculation

### How Fees Are Calculated

When you upload a CSV and select a semester batch:

1. System fetches fee plan for the selected batch
2. Fee plan may be specific to:
   - School
   - Standard
   - Semester batch
3. System calculates fees per student based on fee plan
4. Discounts are applied if applicable:
   - School-level discounts (applied first)
   - Student-level discounts (applied second)
5. Total fees = (Fee per student × Number of students) - Total discounts

### Fee Preview

The preview page shows:
- Fee per student
- Total students
- Total fees for entire batch
- Individual student fees (if different due to discounts)

### Fee Verification

Before submitting:
- Verify fee per student is correct
- Check if discounts are applied correctly
- Confirm total fees match expectations
- Contact administrator if fees seem incorrect

## Troubleshooting

### CSV Won't Upload

**Check:**
- File is in CSV format (not Excel .xlsx)
- File size is reasonable (< 10MB)
- File encoding is UTF-8
- No special characters in filename

### All Rows Show Errors

**Check:**
- Column headers match exactly (case-sensitive)
- Required columns are present
- File is not corrupted
- Commas are used as delimiters

### Some Rows Valid, Some Invalid

**Check:**
- Error messages for each invalid row
- Data types match requirements
- Master data exists for lookup fields
- No duplicate emails

### Fees Not Calculating

**Check:**
- Semester batch is selected
- Fee plan exists for the batch
- Fee plan is active and valid
- Contact administrator if fee plan is missing

## Support

For assistance with CSV uploads:
- Review this document thoroughly
- Check the [Bulk Upload Guide](BULK_UPLOAD_GUIDE.md)
- Review the [FAQ](FAQ.md)
- Contact your administrator
- Email support with sample CSV (remove sensitive data)

## Appendix: Excel Tips

### Creating CSV from Excel

1. Enter data in Excel spreadsheet
2. Click **File** → **Save As**
3. Choose **CSV (Comma delimited) (*.csv)**
4. Click **Save**
5. If prompted about features, click **Yes**

### Handling Special Characters

- Avoid special characters in names
- Use plain text, no formatting
- Remove formulas before saving as CSV
- Check for hidden characters

### Date Formatting in Excel

1. Select date column
2. Right-click → **Format Cells**
3. Choose **Custom**
4. Enter format: `yyyy-mm-dd`
5. Click **OK**

---

*Last Updated: November 2025*
*Version: 1.0*
