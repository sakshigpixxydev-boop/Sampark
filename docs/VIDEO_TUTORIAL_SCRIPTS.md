# Video Tutorial Scripts

This document contains scripts for creating video tutorials for key workflows in the Pramukh Sankalp School Management System.

## Tutorial 1: Getting Started - Login and Dashboard (3-4 minutes)

### Target Audience
All users

### Script

**[INTRO - 0:00-0:15]**
"Welcome to Pramukh Sankalp School Management System. In this tutorial, we'll show you how to log in and navigate your dashboard."

**[LOGIN - 0:15-0:45]**
"To log in, navigate to the login page. Enter your username. You can choose to log in with either your password or your 4-digit PIN. Click Login."

*[Show both password and PIN login options]*

"If you forget your password or PIN, click the 'Forgot Password' or 'Forgot PIN' link and follow the email instructions."

**[DASHBOARD OVERVIEW - 0:45-2:00]**
"After logging in, you'll see your dashboard. The dashboard shows different information based on your role."

*[Show Admin dashboard]*
"Admins see total students, schools, and initiators across the entire system."

*[Show Initiator dashboard]*
"Initiators see only their assigned students and schools."

*[Show School dashboard]*
"School users see students enrolled in their school."

"Click any overview card to navigate to the detailed list page."

**[NAVIGATION - 2:00-2:45]**
"Use the sidebar menu to navigate between different sections. On mobile, tap the hamburger icon to open the menu."

"The search bar at the top lets you quickly find students, schools, or users. Just start typing and select from the results."

**[PROFILE - 2:45-3:30]**
"Click your name in the top right to access your profile. Here you can update your information, change your password, or change your PIN."

**[OUTRO - 3:30-3:45]**
"That's the basics of logging in and navigating the system. Check out our other tutorials for specific features."

---

## Tutorial 2: Adding Individual Students (4-5 minutes)

### Target Audience
Admin, Initiator, School users

### Script

**[INTRO - 0:00-0:15]**
"In this tutorial, we'll show you how to add a student to the system individually."

**[NAVIGATION - 0:15-0:30]**
"Navigate to Students, then click Create Student."

**[REQUIRED FIELDS - 0:30-2:00]**
"Let's fill in the required fields first."

*[Fill each field while explaining]*
- "Select the Student Type from the dropdown"
- "Choose the Educational Board - like CBSE, ICSE, or State Board"
- "Select the Standard or grade level"
- "Choose the Taluka or location"
- "Enter the student's First Name, Middle Name, and Last Name"
- "Enter a unique email address - this cannot be used by any other student"

**[OPTIONAL FIELDS - 2:00-3:00]**
"Now let's add some optional information that helps keep complete records."

*[Show optional fields]*
- "Select gender if applicable"
- "Enter birthdate in the date picker"
- "Add address and contact information"
- "Enter phone and mobile numbers - these should be 10 digits"

**[GUARDIAN INFO - 3:00-4:00]**
"You can also add guardian information right here, or add it separately later."

*[Fill guardian fields]*
- "Select the relation - Father, Mother, or Guardian"
- "Enter guardian's full name"
- "Add guardian contact information"

**[SAVE - 4:00-4:30]**
"Review all the information to make sure it's correct. Then click Save."

"You'll see a success message, and the student will appear in your student list."

**[OUTRO - 4:30-4:45]**
"That's how you add a student individually. For adding multiple students at once, check out our Bulk Upload tutorial."

---

## Tutorial 3: Bulk Student Upload (7-8 minutes)

### Target Audience
Admin, Initiator users

### Script

**[INTRO - 0:00-0:20]**
"In this tutorial, we'll show you how to upload multiple students at once using a CSV file. This is much faster than adding students one by one."

**[DOWNLOAD TEMPLATE - 0:20-0:45]**
"First, navigate to Students, then Bulk Upload. Download the CSV template by clicking the Download Template button."

**[PREPARE CSV - 0:45-2:30]**
"Open the template in Excel or Google Sheets. You'll see columns for all the student information."

*[Show template in spreadsheet]*

"The required columns are marked with asterisks:
- Student Type
- Educational Board
- Standard
- Taluka
- First Name, Middle Name, Last Name
- Email"

"Fill in your student data. Remember:
- Each email must be unique
- Phone numbers should be 10 digits
- Dates should be in YYYY-MM-DD format
- Maximum 500 students per file"

**[SAVE CSV - 2:30-2:45]**
"When you're done, save the file as CSV format. In Excel, go to File, Save As, and choose CSV."

**[UPLOAD - 2:45-3:30]**
"Back in the system, select the Semester Batch from the dropdown. This is important because fees are calculated based on the batch."

"Click Choose File and select your CSV. Then click Upload."

**[VALIDATION - 3:30-4:30]**
"The system will validate your data. Valid rows show in green, invalid rows show in red with error messages."

*[Show validation results]*

"Common errors include:
- Duplicate emails
- Missing required fields
- Invalid data formats
- Master data that doesn't exist"

"Fix any errors in your spreadsheet and re-upload."

**[PREVIEW - 4:30-5:30]**
"Once validation passes, you'll see the preview page. This shows:
- All students with their information
- Calculated fees for each student
- Total students and total fees"

*[Show preview page]*

"You can edit individual student records by clicking the Edit icon. Make any necessary corrections."

**[SUBMIT - 5:30-6:30]**
"When everything looks good, click Submit for Verification."

"Your batch is now sent to the accountant for payment verification. You'll receive an email when it's approved or if corrections are needed."

**[BATCH STATUS - 6:30-7:30]**
"You can check the status of your batch in the Upload Batches section. Statuses include:
- Pending: Awaiting accountant review
- Approved: Payment verified, students registered
- Rejected: Needs corrections"

"If rejected, you'll receive an email explaining what needs to be fixed. Correct the issues and re-upload."

**[OUTRO - 7:30-7:45]**
"That's how you upload multiple students at once. This saves time and ensures consistent data entry."

---

## Tutorial 4: Accountant Batch Verification (5-6 minutes)

### Target Audience
Accountant users

### Script

**[INTRO - 0:00-0:20]**
"In this tutorial, we'll show accountants how to verify and approve student upload batches."

**[VIEW BATCHES - 0:20-1:00]**
"Navigate to Accountant, then Upload Batches. You'll see all batches awaiting verification."

*[Show batch list]*

"Each batch shows:
- Batch code and upload date
- Who uploaded it
- Semester batch
- Total students
- Fee per student
- Total fees"

**[REVIEW BATCH - 1:00-2:30]**
"Click on a batch to review the details. You'll see the complete list of students with all their information."

*[Show batch detail page]*

"Verify:
- Student information is complete and accurate
- Emails are valid and unique
- Fees are calculated correctly
- No validation errors exist"

"Scroll through all students to ensure data quality."

**[APPROVE - 2:30-4:00]**
"If everything looks good, click Approve Payment."

*[Show approval form]*

"Enter the payment details:
- Payment Mode: Cash, Credit Card, Check, etc.
- Transaction ID: Unique identifier from payment system
- Total Amount: Should match the batch total
- Payment Date: When payment was received"

"Double-check all payment information, then click Confirm Approval."

**[AFTER APPROVAL - 4:00-4:45]**
"After approval:
- All students are marked as paid
- Students are registered in the system
- Invoices are generated
- Uploader receives confirmation
- Batch status changes to Approved"

**[REJECT - 4:45-5:30]**
"If there are issues with the batch, click Reject Batch instead."

*[Show rejection form]*

"Enter a detailed rejection reason explaining what needs to be corrected. Be specific so the uploader knows exactly what to fix."

"Click Confirm Rejection. The uploader will receive an email with your notes and can correct and re-submit."

**[OUTRO - 5:30-5:45]**
"That's how you verify and approve upload batches. This ensures payment is received before students are registered."

---

## Tutorial 5: Creating and Managing Invoices (5-6 minutes)

### Target Audience
Admin, Accountant users

### Script

**[INTRO - 0:00-0:20]**
"In this tutorial, we'll show you how to create and manage student invoices."

**[FEE PLANS - 0:20-1:20]**
"Before creating invoices, make sure fee plans are set up. Navigate to Fee Plans."

*[Show fee plan list]*

"Fee plans define the charges for:
- Specific schools
- Specific standards
- Specific semester batches"

"Each fee plan includes line items like tuition, materials, activities, etc."

**[DISCOUNT RULES - 1:20-2:20]**
"You can also set up discount rules. Navigate to Discount Rules."

*[Show discount rules]*

"Discounts can apply to:
- Entire schools (all students get the discount)
- Individual students (specific student only)"

"Discount types:
- FLAT: Fixed amount off (e.g., ₹500 off)
- PERCENT: Percentage off (e.g., 10% off)"

**[CREATE INVOICE - 2:20-3:30]**
"To create an invoice, navigate to Invoices, then Create Invoice."

*[Show invoice creation]*

"Select the student. The system automatically:
- Fetches the applicable fee plan
- Calculates the subtotal
- Applies any discount rules
- Calculates the total due"

"Review the invoice details and click Generate."

**[INVOICE STATUS - 3:30-4:30]**
"Invoices have different statuses:
- DRAFT: Not yet finalized
- ISSUED: Sent to student
- PARTIAL: Partially paid
- PAID: Fully paid
- VOID: Cancelled"

*[Show status transitions]*

"Update the status as payments are received."

**[VIEW INVOICES - 4:30-5:30]**
"To view invoices, navigate to the Invoices list. Filter by:
- Student
- School
- Status
- Date range"

"Click on an invoice to see full details including:
- Fee breakdown
- Discounts applied
- Payment history
- Balance remaining"

**[OUTRO - 5:30-5:45]**
"That's how you create and manage invoices. The system handles all the calculations automatically."

---

## Tutorial 6: Recording Payments (4-5 minutes)

### Target Audience
Admin, Accountant users

### Script

**[INTRO - 0:00-0:15]**
"In this tutorial, we'll show you how to record student payments."

**[PAYMENT MODES - 0:15-0:45]**
"First, make sure payment modes are set up. Navigate to Payment Modes."

*[Show payment modes]*

"Common modes include Cash, Credit Card, Debit Card, Check, and Bank Transfer."

**[CREATE PAYMENT - 0:45-2:30]**
"To record a payment, navigate to Payments, then Create Payment."

*[Show payment form]*

"Fill in the required fields:
- Student: Select the student making payment
- Payment Mode: How they paid
- Payment Date: When payment was received
- Transaction ID: Unique identifier
- Amount: Payment amount"

"You can also add notes for additional context."

**[PAYMENT STATUS - 2:30-3:30]**
"Payments have different statuses:
- PENDING: Awaiting processing
- AUTHORIZED: Approved but not completed
- PAID: Successfully completed
- FAILED: Transaction failed
- REFUNDED: Payment returned
- CANCELLED: Transaction cancelled"

*[Show status flow]*

"Update the status as the payment is processed."

**[VIEW PAYMENTS - 3:30-4:15]**
"To view payment history, navigate to the Payments list. Filter by:
- Date range
- Student
- Payment mode
- Status"

"Click on a payment to see full details including gateway response and audit trail."

**[OUTRO - 4:15-4:30]**
"That's how you record and track payments. All transactions are logged for audit purposes."

---

## Tutorial 7: Using Search and Filters (3-4 minutes)

### Target Audience
All users

### Script

**[INTRO - 0:00-0:15]**
"In this tutorial, we'll show you how to quickly find information using search and filters."

**[GLOBAL SEARCH - 0:15-1:15]**
"The global search bar at the top lets you search across the entire system."

*[Demonstrate search]*

"Click the search icon or press Ctrl+K (Cmd+K on Mac). Start typing a student name, email, school name, or username."

"Results appear instantly. Click on a result to navigate to that record."

"Search results are filtered based on your role - you'll only see records you have access to."

**[LIST FILTERS - 1:15-2:45]**
"Most list pages have filter panels for more specific searches."

*[Show student list filters]*

"For example, on the student list you can filter by:
- School
- Assigned User
- Payment Status
- City
- Standard
- Educational Board
- Student Type"

"Select multiple filters to narrow down results. The list updates automatically."

**[DATE FILTERS - 2:45-3:15]**
"Many pages also have date range filters. Click the date filter, select start and end dates, and apply."

**[CLEAR FILTERS - 3:15-3:30]**
"To reset, click Clear All Filters to see the complete list again."

**[OUTRO - 3:30-3:45]**
"That's how you use search and filters to quickly find what you need."

---

## Tutorial 8: Managing Your Profile (3-4 minutes)

### Target Audience
All users

### Script

**[INTRO - 0:00-0:15]**
"In this tutorial, we'll show you how to manage your profile and security settings."

**[VIEW PROFILE - 0:15-0:45]**
"Click your name in the top right corner and select Profile. Here you can see your current information."

**[EDIT PROFILE - 0:45-1:30]**
"Click Edit Profile to update your information. You can change:
- Your name
- Email address
- Phone number
- Other contact details"

"Make your changes and click Save."

**[CHANGE PASSWORD - 1:30-2:15]**
"To change your password, go to Profile, then Change Password."

*[Show password change form]*

"Enter your current password, then your new password twice. Click Update."

"Your new password must be at least 8 characters long."

**[CHANGE PIN - 2:15-2:45]**
"To change your PIN, go to Profile, then Change PIN."

*[Show PIN change form]*

"Enter your current PIN, then your new 4-digit PIN twice. Click Update."

**[SECURITY TIPS - 2:45-3:30]**
"For security:
- Change your password every 90 days
- Don't share your password or PIN
- Use a strong, unique password
- Log out when using shared computers"

**[OUTRO - 3:30-3:45]**
"That's how you manage your profile and keep your account secure."

---

## Production Notes

### General Guidelines

1. **Screen Recording**: Use 1920x1080 resolution
2. **Cursor**: Use cursor highlighting for visibility
3. **Pace**: Speak clearly and not too fast
4. **Pauses**: Add 1-2 second pauses between major steps
5. **Annotations**: Use arrows or highlights for important elements
6. **Audio**: Use clear microphone, minimize background noise
7. **Music**: Optional light background music at low volume
8. **Branding**: Include system logo and branding

### Editing Checklist

- [ ] Remove mistakes and long pauses
- [ ] Add intro/outro graphics
- [ ] Add text overlays for key points
- [ ] Ensure audio levels are consistent
- [ ] Add chapter markers for longer videos
- [ ] Export in 1080p MP4 format
- [ ] Add closed captions/subtitles
- [ ] Test playback before publishing

### Publishing

- Upload to organization's video platform
- Create playlist for all tutorials
- Add video descriptions with timestamps
- Link to relevant documentation
- Make available in system help section

---

*Last Updated: November 2025*
*Version: 1.0*
