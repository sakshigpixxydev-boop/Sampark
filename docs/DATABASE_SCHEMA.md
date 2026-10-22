# Database Schema Documentation

## Overview

This document describes the database schema for the Pramukh Sankalp School Management System. The system uses MySQL 8.0+ as the database engine.

## Entity Relationship Diagram

```
┌─────────────┐       ┌──────────────┐       ┌─────────────┐
│   SYSUSER   │──────<│  USERSCHOOL  │>──────│   SCHOOL    │
└─────────────┘       └──────────────┘       └─────────────┘
      │                                              │
      │                                              │
      ├──────<┌──────────────┐>──────┐             │
      │       │ USERSTUDENT  │       │             │
      │       └──────────────┘       │             │
      │                              │             │
      ▼                              ▼             ▼
┌─────────────┐              ┌─────────────┐ ┌─────────────┐
│    ROLE     │              │   STUDENT   │ │  INVOICE    │
└─────────────┘              └─────────────┘ └─────────────┘
      │                              │             │
      │                              │             │
      ▼                              ▼             ▼
┌─────────────┐              ┌─────────────┐ ┌─────────────┐
│ PERMISSION  │              │  GUARDIAN   │ │INVOICE_ITEM │
└─────────────┘              └─────────────┘ └─────────────┘
```

## Core Tables

### sysuser

System users table storing all user accounts.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| sysuser_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique user identifier |
| role_id | INT | FOREIGN KEY → role(role_id) | User's role |
| usertype_id | INT | FOREIGN KEY → usertype(usertype_id), NULLABLE | User type |
| first_name | VARCHAR(100) | NOT NULL | User's first name |
| last_name | VARCHAR(100) | NULLABLE | User's last name |
| username | VARCHAR(255) | UNIQUE, NOT NULL | Login username |
| password | VARCHAR(255) | NULLABLE | Hashed password |
| pin | VARCHAR(255) | NULLABLE | Hashed 4-digit PIN |
| email | VARCHAR(255) | NULLABLE | Email address |
| phone | VARCHAR(20) | NULLABLE | Phone number |
| inactive | TINYINT(1) | DEFAULT 0 | Inactive status (0=active, 1=inactive) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (sysuser_id)
- UNIQUE KEY (username)
- INDEX (role_id)
- INDEX (inactive)

### role

User roles table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| role_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique role identifier |
| role_name | VARCHAR(100) | NOT NULL | Role name |
| predefined | TINYINT(1) | DEFAULT 0 | Predefined flag (1=cannot delete) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Predefined Roles:**
- Admin (role_id: 1)
- Initiator (role_id: 2)
- School (role_id: 3)
- Accountant (role_id: 4)

### permission

Permissions table with numeric keys.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| permission_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique permission identifier |
| permission_key | INT | UNIQUE, NOT NULL | Numeric permission key |
| permission_desc | VARCHAR(255) | NOT NULL | Permission description |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Common Permission Keys:**
- 10001: Role Management
- 10002: Permission Management
- 10003: User Management

### rolepermission

Junction table for role-permission relationships.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| rolepermission_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| role_id | INT | FOREIGN KEY → role(role_id) | Role reference |
| permission_id | INT | FOREIGN KEY → permission(permission_id) | Permission reference |

**Indexes:**
- UNIQUE KEY (role_id, permission_id)

## School Management Tables

### school

Schools table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| school_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique school identifier |
| school_name | VARCHAR(1000) | NOT NULL | School name |
| schooltype_id | INT | FOREIGN KEY → schooltype(schooltype_id) | School type |
| eductionalboard_id | INT | FOREIGN KEY → ms_eductionalboard(eductionalboard_id) | Educational board |
| admin_name | VARCHAR(200) | NOT NULL | Admin contact name |
| admin_contact | VARCHAR(20) | NOT NULL | Admin contact number |
| taluko_id | INT | FOREIGN KEY → ms_taluko(taluko_id), NULLABLE | Location |
| logo | VARCHAR(255) | NULLABLE | Logo file path |
| status | TINYINT(1) | DEFAULT 0 | Status (0=lead, 1=final) |
| inactive | TINYINT(1) | DEFAULT 0 | Inactive status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (school_id)
- INDEX (schooltype_id)
- INDEX (eductionalboard_id)
- INDEX (status)
- INDEX (inactive)

### schooltype

School types master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| schooltype_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| schooltype_name | VARCHAR(100) | NOT NULL | School type name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

## Student Management Tables

### student

Students table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| student_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique student identifier |
| school_id | INT | FOREIGN KEY → school(school_id), NULLABLE | Associated school |
| studenttype_id | INT | FOREIGN KEY → studenttype(studenttype_id) | Student type |
| eductionalboard_id | INT | FOREIGN KEY → ms_eductionalboard(eductionalboard_id) | Educational board |
| standard_id | INT | FOREIGN KEY → ms_standard(standard_id) | Academic standard |
| taluko_id | INT | FOREIGN KEY → ms_taluko(taluko_id) | Location |
| first_name | VARCHAR(100) | NOT NULL | First name |
| middle_name | VARCHAR(100) | NOT NULL | Middle name |
| last_name | VARCHAR(100) | NOT NULL | Last name |
| email | VARCHAR(255) | UNIQUE, NOT NULL | Email address |
| gender | VARCHAR(10) | NULLABLE | Gender (M/F/Other) |
| birthdate | DATE | NULLABLE | Date of birth |
| phone | VARCHAR(20) | NULLABLE | Phone number |
| mobile | VARCHAR(20) | NULLABLE | Mobile number |
| address | TEXT | NULLABLE | Address |
| city | VARCHAR(100) | NULLABLE | City |
| state | VARCHAR(100) | NULLABLE | State |
| pincode | VARCHAR(10) | NULLABLE | Postal code |
| payment_status | VARCHAR(20) | DEFAULT 'pending' | Payment status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (student_id)
- UNIQUE KEY (email)
- INDEX (school_id)
- INDEX (studenttype_id)
- INDEX (payment_status)

### studenttype

Student types master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| studenttype_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| studenttype_name | VARCHAR(100) | NOT NULL | Student type name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### guardian

Guardians table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| guardian_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique guardian identifier |
| student_id | INT | FOREIGN KEY → student(student_id) | Associated student |
| relation_id | INT | FOREIGN KEY → ms_relation(relation_id) | Relationship type |
| first_name | VARCHAR(100) | NOT NULL | First name |
| middle_name | VARCHAR(100) | NOT NULL | Middle name |
| last_name | VARCHAR(100) | NOT NULL | Last name |
| phone | VARCHAR(20) | NULLABLE | Phone number |
| email | VARCHAR(255) | NULLABLE | Email address |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (guardian_id)
- INDEX (student_id)

## Bulk Upload Tables

### upload_batch

Upload batches for bulk student uploads.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| batch_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique batch identifier |
| batch_code | VARCHAR(50) | UNIQUE, NOT NULL | Batch code |
| sembatches_id | INT | FOREIGN KEY → sembatches(sembatches_id) | Semester batch |
| uploaded_by | INT | FOREIGN KEY → sysuser(sysuser_id) | Uploader user |
| upload_date | DATETIME | NOT NULL | Upload timestamp |
| total_students | INT | NOT NULL | Total students in batch |
| fee_per_student | DECIMAL(10,2) | NOT NULL | Fee per student |
| total_fees | DECIMAL(12,2) | NOT NULL | Total fees for batch |
| status | ENUM | DEFAULT 'pending' | Status (pending/approved/rejected) |
| payment_verified | BOOLEAN | DEFAULT FALSE | Payment verification flag |
| paymentmode_id | INT | FOREIGN KEY → paymentmode(paymentmode_id), NULLABLE | Payment mode |
| transaction_id | VARCHAR(100) | NULLABLE | Transaction identifier |
| payment_date | DATETIME | NULLABLE | Payment date |
| verified_by | INT | FOREIGN KEY → sysuser(sysuser_id), NULLABLE | Verifier user |
| verified_at | DATETIME | NULLABLE | Verification timestamp |
| rejection_reason | TEXT | NULLABLE | Rejection reason |
| notes | TEXT | NULLABLE | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (batch_id)
- UNIQUE KEY (batch_code)
- INDEX (sembatches_id)
- INDEX (status)
- INDEX (uploaded_by)

### pending_student

Temporary storage for students awaiting verification.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| pending_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| batch_id | INT | FOREIGN KEY → upload_batch(batch_id) ON DELETE CASCADE | Associated batch |
| student_data | JSON | NOT NULL | Student information as JSON |
| calculated_fee | DECIMAL(10,2) | NOT NULL | Calculated fee |
| validation_errors | JSON | NULLABLE | Validation errors as JSON |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |

**Indexes:**
- PRIMARY KEY (pending_id)
- INDEX (batch_id)

## Financial Tables

### fee_plan

Fee plans table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| feeplan_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| school_id | INT | FOREIGN KEY → school(school_id), NULLABLE | Specific school |
| standard_id | INT | FOREIGN KEY → ms_standard(standard_id), NULLABLE | Specific standard |
| sembatches_id | INT | FOREIGN KEY → sembatches(sembatches_id), NULLABLE | Specific semester batch |
| valid_from | DATE | NOT NULL | Validity start date |
| valid_to | DATE | NULLABLE | Validity end date |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (feeplan_id)
- INDEX (school_id)
- INDEX (sembatches_id)

### discount_rule

Discount rules table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| discountrule_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| scope | ENUM | NOT NULL | Scope (SCHOOL/STUDENT) |
| school_id | INT | FOREIGN KEY → school(school_id), NULLABLE | School for SCHOOL scope |
| student_id | INT | FOREIGN KEY → student(student_id), NULLABLE | Student for STUDENT scope |
| discount_type | ENUM | NOT NULL | Type (FLAT/PERCENT) |
| discount_value | DECIMAL(10,2) | NOT NULL | Discount value |
| applies_to | VARCHAR(255) | NULLABLE | Fee components it applies to |
| valid_from | DATE | NOT NULL | Validity start date |
| valid_to | DATE | NULLABLE | Validity end date |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (discountrule_id)
- INDEX (school_id)
- INDEX (student_id)
- INDEX (scope)

### invoice

Invoices table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| invoice_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| invoice_number | VARCHAR(50) | UNIQUE, NOT NULL | Invoice number |
| student_id | INT | FOREIGN KEY → student(student_id) | Associated student |
| school_id | INT | FOREIGN KEY → school(school_id), NULLABLE | Associated school |
| subtotal | DECIMAL(10,2) | NOT NULL | Subtotal before discounts |
| discount_total | DECIMAL(10,2) | DEFAULT 0 | Total discounts |
| total_due | DECIMAL(10,2) | NOT NULL | Total amount due |
| status | ENUM | DEFAULT 'ISSUED' | Status (DRAFT/ISSUED/PARTIAL/PAID/VOID) |
| invoice_date | DATE | NOT NULL | Invoice date |
| due_date | DATE | NULLABLE | Payment due date |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (invoice_id)
- UNIQUE KEY (invoice_number)
- INDEX (student_id)
- INDEX (status)

### invoice_item

Invoice line items table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| invoiceitem_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| invoice_id | INT | FOREIGN KEY → invoice(invoice_id) ON DELETE CASCADE | Associated invoice |
| description | VARCHAR(255) | NOT NULL | Item description |
| amount | DECIMAL(10,2) | NOT NULL | Item amount |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (invoiceitem_id)
- INDEX (invoice_id)

### paymentlog

Payment transactions table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| paymentlog_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| student_id | INT | FOREIGN KEY → student(student_id) | Associated student |
| invoice_id | INT | FOREIGN KEY → invoice(invoice_id), NULLABLE | Associated invoice |
| paymentmode_id | INT | FOREIGN KEY → paymentmode(paymentmode_id) | Payment mode |
| payment_datetime | DATETIME | NOT NULL | Payment timestamp |
| transaction_id | VARCHAR(100) | NOT NULL | Transaction identifier |
| amount | DECIMAL(10,2) | NOT NULL | Payment amount |
| status | ENUM | DEFAULT 'PENDING' | Status (PENDING/AUTHORIZED/PAID/FAILED/REFUNDED/CANCELLED) |
| gateway_response | TEXT | NULLABLE | Payment gateway response |
| notes | TEXT | NULLABLE | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (paymentlog_id)
- INDEX (student_id)
- INDEX (status)
- INDEX (payment_datetime)

### paymentmode

Payment modes master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| paymentmode_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| paymentmode_name | VARCHAR(100) | NOT NULL | Payment mode name |
| inactive | TINYINT(1) | DEFAULT 0 | Inactive status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

## Parcel Management Tables

### parcel

Parcels table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| parcel_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| student_id | INT | FOREIGN KEY → student(student_id) | Associated student |
| parcelcompany_id | INT | FOREIGN KEY → parcelcompany(parcelcompany_id) | Parcel company |
| tracking_number | VARCHAR(100) | NOT NULL | Tracking number |
| notes | TEXT | NULLABLE | Additional notes |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- PRIMARY KEY (parcel_id)
- INDEX (student_id)
- INDEX (tracking_number)

### parcelcompany

Parcel companies master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| parcelcompany_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| company_name | VARCHAR(200) | NOT NULL | Company name |
| tracking_url | VARCHAR(500) | NULLABLE | Tracking URL template |
| api_endpoint | VARCHAR(500) | NULLABLE | API endpoint |
| delivery_time_estimate | VARCHAR(100) | NULLABLE | Delivery time estimate |
| inactive | TINYINT(1) | DEFAULT 0 | Inactive status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

## Master Data Tables

### ms_country

Countries master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| country_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| country_name | VARCHAR(100) | NOT NULL | Country name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### ms_state

States master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| state_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| country_id | INT | FOREIGN KEY → ms_country(country_id) | Parent country |
| state_name | VARCHAR(100) | NOT NULL | State name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### ms_dist

Districts master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| dist_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| state_id | INT | FOREIGN KEY → ms_state(state_id) | Parent state |
| dist_name | VARCHAR(100) | NOT NULL | District name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### ms_taluko

Talukas master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| taluko_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| dist_id | INT | FOREIGN KEY → ms_dist(dist_id) | Parent district |
| taluko_name | VARCHAR(100) | NOT NULL | Taluka name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### ms_eductionalboard

Educational boards master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| eductionalboard_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| eductionalboard_name | VARCHAR(100) | NOT NULL | Board name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### ms_standard

Academic standards master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| standard_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| standard_name | VARCHAR(50) | NOT NULL | Standard name |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### ms_relation

Guardian relations master data.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| relation_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| relation_name | VARCHAR(50) | NOT NULL | Relation name (Father, Mother, etc.) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

### sembatches

Semester batches table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| sembatches_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| batch_name | VARCHAR(100) | NOT NULL | Batch name |
| start_date | DATE | NOT NULL | Start date |
| end_date | DATE | NOT NULL | End date |
| capacity | INT | NULLABLE | Maximum capacity |
| status | VARCHAR(20) | DEFAULT 'active' | Status |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

## Audit Tables

### audit_logs

Audit trail table.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| id | BIGINT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| user_id | INT | FOREIGN KEY → sysuser(sysuser_id), NULLABLE | User who performed action |
| action | VARCHAR(50) | NOT NULL | Action type (created/updated/deleted) |
| entity_type | VARCHAR(100) | NOT NULL | Entity type (model name) |
| entity_id | INT | NOT NULL | Entity identifier |
| old_values | JSON | NULLABLE | Previous values |
| new_values | JSON | NULLABLE | New values |
| ip_address | VARCHAR(45) | NULLABLE | IP address |
| user_agent | TEXT | NULLABLE | User agent string |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Action timestamp |

**Indexes:**
- PRIMARY KEY (id)
- INDEX (user_id)
- INDEX (entity_type, entity_id)
- INDEX (action)
- INDEX (created_at)

## Junction Tables

### userschool

User-school assignments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| userschool_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| sysuser_id | INT | FOREIGN KEY → sysuser(sysuser_id) | User reference |
| school_id | INT | FOREIGN KEY → school(school_id) | School reference |

**Indexes:**
- UNIQUE KEY (sysuser_id, school_id)

### userstudent

User-student assignments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| userstudent_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| sysuser_id | INT | FOREIGN KEY → sysuser(sysuser_id) | User reference |
| student_id | INT | FOREIGN KEY → student(student_id) | Student reference |

**Indexes:**
- UNIQUE KEY (sysuser_id, student_id)

### student_sembatches

Student-semester batch assignments.

| Column | Type | Constraints | Description |
|--------|------|-------------|-------------|
| student_sembatches_id | INT | PRIMARY KEY, AUTO_INCREMENT | Unique identifier |
| student_id | INT | FOREIGN KEY → student(student_id) | Student reference |
| sembatches_id | INT | FOREIGN KEY → sembatches(sembatches_id) | Batch reference |
| status | VARCHAR(20) | DEFAULT 'Active' | Status (Active/Completed/Dropped/On Hold) |
| created_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | DEFAULT CURRENT_TIMESTAMP ON UPDATE | Last update timestamp |

**Indexes:**
- UNIQUE KEY (student_id, sembatches_id)

## Database Maintenance

### Backup Strategy

- **Full Backup**: Daily at 2:00 AM
- **Incremental Backup**: Every 6 hours
- **Retention**: 30 days for daily, 7 days for incremental
- **Location**: Secure off-site storage

### Optimization

- **Index Maintenance**: Weekly index rebuild
- **Statistics Update**: Daily statistics update
- **Query Optimization**: Monitor slow query log
- **Partitioning**: Consider partitioning audit_logs by date

### Security

- **Encryption**: Enable encryption at rest
- **Access Control**: Principle of least privilege
- **Audit**: Enable database audit logging
- **Backup Encryption**: Encrypt all backups

---

*Last Updated: November 2025*
*Version: 1.0*
