# API Documentation

## Overview

This document describes the API endpoints available in the Pramukh Sankalp School Management System. All endpoints require authentication unless specified otherwise.

## Authentication

### Login

**Endpoint:** `POST /login`

**Description:** Authenticate user with username and password or PIN

**Request Body:**
```json
{
  "username": "string (required)",
  "password": "string (optional)",
  "pin": "string (optional, 4 digits)",
  "remember": "boolean (optional)"
}
```

**Response:**
```json
{
  "success": true,
  "redirect": "/dashboard"
}
```

**Status Codes:**
- 200: Success
- 401: Invalid credentials
- 403: Account inactive

### Logout

**Endpoint:** `POST /logout`

**Description:** Log out current user

**Response:**
```json
{
  "success": true,
  "redirect": "/login"
}
```

## Password Reset

### Request Password Reset

**Endpoint:** `POST /forgot-password`

**Description:** Send password reset link to user email

**Request Body:**
```json
{
  "email": "string (required)"
}
```

**Response:**
```json
{
  "message": "Password reset link sent to your email"
}
```

### Reset Password

**Endpoint:** `POST /reset-password`

**Description:** Reset user password with token

**Request Body:**
```json
{
  "token": "string (required)",
  "email": "string (required)",
  "password": "string (required, min 8 chars)",
  "password_confirmation": "string (required)"
}
```

## PIN Reset

### Request PIN Reset

**Endpoint:** `POST /forgot-pin`

**Description:** Send PIN reset link to user email

**Request Body:**
```json
{
  "email": "string (required)"
}
```

### Reset PIN

**Endpoint:** `POST /reset-pin`

**Description:** Reset user PIN with token

**Request Body:**
```json
{
  "token": "string (required)",
  "email": "string (required)",
  "pin": "string (required, 4 digits)",
  "pin_confirmation": "string (required)"
}
```

## Profile Management

### Get Profile

**Endpoint:** `GET /profile`

**Description:** Get current user profile

**Response:**
```json
{
  "sysuser_id": 1,
  "username": "admin",
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "role": {
    "role_id": 1,
    "role_name": "Admin"
  }
}
```

### Update Profile

**Endpoint:** `PUT /profile`

**Description:** Update current user profile

**Request Body:**
```json
{
  "first_name": "string (required)",
  "last_name": "string (optional)",
  "email": "string (optional)",
  "phone": "string (optional)"
}
```

### Change Password

**Endpoint:** `PUT /profile/change-password`

**Description:** Change user password

**Request Body:**
```json
{
  "current_password": "string (required)",
  "password": "string (required, min 8 chars)",
  "password_confirmation": "string (required)"
}
```

### Change PIN

**Endpoint:** `PUT /profile/change-pin`

**Description:** Change user PIN

**Request Body:**
```json
{
  "current_pin": "string (required, 4 digits)",
  "pin": "string (required, 4 digits)",
  "pin_confirmation": "string (required)"
}
```

## User Management

**Permission Required:** 10003

### List Users

**Endpoint:** `GET /users`

**Description:** Get list of all users

**Query Parameters:**
- `role`: Filter by role ID
- `search`: Search by name or username
- `page`: Page number for pagination

**Response:**
```json
{
  "data": [
    {
      "sysuser_id": 1,
      "username": "admin",
      "first_name": "John",
      "role": {
        "role_name": "Admin"
      },
      "inactive": 0
    }
  ],
  "meta": {
    "current_page": 1,
    "total": 50
  }
}
```

### Create User

**Endpoint:** `POST /users`

**Description:** Create new user

**Request Body:**
```json
{
  "role_id": "integer (required)",
  "first_name": "string (required)",
  "last_name": "string (optional)",
  "username": "string (required, unique)",
  "password": "string (optional, min 8 chars)",
  "pin": "string (optional, 4 digits)",
  "email": "string (optional)",
  "inactive": "boolean (optional, default false)"
}
```

### Update User

**Endpoint:** `PUT /users/{id}`

**Description:** Update existing user

**Request Body:** Same as Create User

### Assign Schools to User

**Endpoint:** `POST /users/{id}/assign-schools`

**Description:** Assign schools to user

**Request Body:**
```json
{
  "schools": [1, 2, 3]
}
```

### Assign Students to User

**Endpoint:** `POST /users/{id}/assign-students`

**Description:** Assign students to user

**Request Body:**
```json
{
  "students": [1, 2, 3]
}
```

## Role Management

**Permission Required:** 10001

### List Roles

**Endpoint:** `GET /roles`

**Description:** Get list of all roles

### Create Role

**Endpoint:** `POST /roles`

**Description:** Create new role

**Request Body:**
```json
{
  "role_name": "string (required)",
  "predefined": "boolean (optional, default false)",
  "permissions": [1000, 1001, 1002]
}
```

### Update Role

**Endpoint:** `PUT /roles/{id}`

**Description:** Update existing role

### Delete Role

**Endpoint:** `DELETE /roles/{id}`

**Description:** Delete role (predefined roles cannot be deleted)

## Permission Management

**Permission Required:** 10002

### List Permissions

**Endpoint:** `GET /permissions`

**Description:** Get list of all permissions

### Create Permission

**Endpoint:** `POST /permissions`

**Description:** Create new permission

**Request Body:**
```json
{
  "permission_key": "integer (required, unique)",
  "permission_desc": "string (required)"
}
```

### Update Permission

**Endpoint:** `PUT /permissions/{id}`

**Description:** Update existing permission

## School Management

### List Schools

**Endpoint:** `GET /schools`

**Description:** Get list of schools

**Query Parameters:**
- `type`: Filter by school type
- `board`: Filter by educational board
- `status`: Filter by status (0=lead, 1=final)
- `search`: Search by name
- `page`: Page number

### Create School

**Endpoint:** `POST /schools`

**Description:** Create new school

**Request Body:**
```json
{
  "school_name": "string (required)",
  "schooltype_id": "integer (required)",
  "eductionalboard_id": "integer (required)",
  "admin_name": "string (required)",
  "admin_contact": "string (required, 10 digits)",
  "taluko_id": "integer (optional)",
  "status": "integer (optional, 0 or 1)",
  "inactive": "boolean (optional)"
}
```

### Update School

**Endpoint:** `PUT /schools/{id}`

**Description:** Update existing school

## Student Management

### List Students

**Endpoint:** `GET /students`

**Description:** Get list of students

**Query Parameters:**
- `school`: Filter by school ID
- `user`: Filter by assigned user ID
- `payment_status`: Filter by payment status
- `city`: Filter by city
- `standard`: Filter by standard ID
- `board`: Filter by educational board ID
- `type`: Filter by student type ID
- `search`: Search by name or email
- `page`: Page number

### Create Student

**Endpoint:** `POST /students`

**Description:** Create new student

**Request Body:**
```json
{
  "studenttype_id": "integer (required)",
  "eductionalboard_id": "integer (required)",
  "standard_id": "integer (required)",
  "taluko_id": "integer (required)",
  "first_name": "string (required)",
  "middle_name": "string (required)",
  "last_name": "string (required)",
  "email": "string (required, unique)",
  "gender": "string (optional, M/F/Other)",
  "birthdate": "date (optional, YYYY-MM-DD)",
  "phone": "string (optional, 10 digits)",
  "address": "string (optional)"
}
```

### Update Student

**Endpoint:** `PUT /students/{id}`

**Description:** Update existing student

### Bulk Upload Students

**Endpoint:** `POST /students/bulk-upload/process`

**Description:** Upload CSV file with multiple students

**Request Body:** (multipart/form-data)
```
file: CSV file (required)
sembatches_id: integer (required)
```

**Response:**
```json
{
  "success": true,
  "valid_count": 45,
  "invalid_count": 5,
  "errors": [
    {
      "row": 3,
      "errors": ["Email already exists"]
    }
  ],
  "preview_data": [...]
}
```

### Submit Bulk Upload for Verification

**Endpoint:** `POST /students/bulk-upload/submit`

**Description:** Submit validated bulk upload for accountant verification

**Request Body:**
```json
{
  "students": [...],
  "sembatches_id": 1,
  "total_fees": 50000
}
```

## Guardian Management

### List Guardians

**Endpoint:** `GET /guardians`

**Description:** Get list of guardians

### Create Guardian

**Endpoint:** `POST /guardians`

**Description:** Create new guardian

**Request Body:**
```json
{
  "relation_id": "integer (required)",
  "first_name": "string (required)",
  "middle_name": "string (required)",
  "last_name": "string (required)",
  "phone": "string (optional, 10 digits)",
  "student_id": "integer (required)"
}
```

### Update Guardian

**Endpoint:** `PUT /guardians/{id}`

**Description:** Update existing guardian

### Delete Guardian

**Endpoint:** `DELETE /guardians/{id}`

**Description:** Delete guardian

## Invoice Management

### List Invoices

**Endpoint:** `GET /invoices`

**Description:** Get list of invoices

**Query Parameters:**
- `student`: Filter by student ID
- `school`: Filter by school ID
- `status`: Filter by status (DRAFT, ISSUED, PARTIAL, PAID, VOID)
- `date_from`: Filter by date range start
- `date_to`: Filter by date range end
- `page`: Page number

### Create Invoice

**Endpoint:** `POST /invoices`

**Description:** Generate invoice for student

**Request Body:**
```json
{
  "student_id": "integer (required)",
  "sembatches_id": "integer (optional)"
}
```

**Response:**
```json
{
  "invoice_id": 1,
  "invoice_number": "INV-2025-001",
  "student_id": 1,
  "subtotal": 10000,
  "discount_total": 1000,
  "total_due": 9000,
  "status": "ISSUED",
  "items": [
    {
      "description": "Tuition Fee",
      "amount": 8000
    },
    {
      "description": "Materials",
      "amount": 2000
    }
  ]
}
```

### Get Invoice

**Endpoint:** `GET /invoices/{id}`

**Description:** Get invoice details

### Void Invoice

**Endpoint:** `POST /invoices/{id}/void`

**Description:** Void an invoice

### Recalculate Invoice

**Endpoint:** `POST /invoices/{id}/recalculate`

**Description:** Recalculate invoice with current fee plans and discounts

## Fee Plan Management

### List Fee Plans

**Endpoint:** `GET /fee-plans`

**Description:** Get list of fee plans

### Create Fee Plan

**Endpoint:** `POST /fee-plans`

**Description:** Create new fee plan

**Request Body:**
```json
{
  "school_id": "integer (optional)",
  "standard_id": "integer (optional)",
  "sembatches_id": "integer (optional)",
  "valid_from": "date (required)",
  "valid_to": "date (optional)",
  "items": [
    {
      "description": "Tuition Fee",
      "amount": 8000
    },
    {
      "description": "Materials",
      "amount": 2000
    }
  ]
}
```

### Update Fee Plan

**Endpoint:** `PUT /fee-plans/{id}`

**Description:** Update existing fee plan

### Delete Fee Plan

**Endpoint:** `DELETE /fee-plans/{id}`

**Description:** Delete fee plan

## Discount Rule Management

### List Discount Rules

**Endpoint:** `GET /discount-rules`

**Description:** Get list of discount rules

### Create Discount Rule

**Endpoint:** `POST /discount-rules`

**Description:** Create new discount rule

**Request Body:**
```json
{
  "scope": "string (required, SCHOOL or STUDENT)",
  "school_id": "integer (required if scope=SCHOOL)",
  "student_id": "integer (required if scope=STUDENT)",
  "discount_type": "string (required, FLAT or PERCENT)",
  "discount_value": "decimal (required)",
  "applies_to": "string (optional)",
  "valid_from": "date (required)",
  "valid_to": "date (optional)"
}
```

### Update Discount Rule

**Endpoint:** `PUT /discount-rules/{id}`

**Description:** Update existing discount rule

### Delete Discount Rule

**Endpoint:** `DELETE /discount-rules/{id}`

**Description:** Delete discount rule

## Payment Management

### List Payments

**Endpoint:** `GET /payments`

**Description:** Get list of payment logs

**Query Parameters:**
- `student`: Filter by student ID
- `status`: Filter by status
- `date_from`: Filter by date range start
- `date_to`: Filter by date range end
- `page`: Page number

### Create Payment

**Endpoint:** `POST /payments`

**Description:** Record new payment

**Request Body:**
```json
{
  "student_id": "integer (required)",
  "paymentmode_id": "integer (required)",
  "payment_datetime": "datetime (required)",
  "transaction_id": "string (required)",
  "amount": "decimal (required)",
  "notes": "string (optional)"
}
```

### Update Payment

**Endpoint:** `PUT /payments/{id}`

**Description:** Update payment details

### Update Payment Status

**Endpoint:** `POST /payments/{id}/update-status`

**Description:** Update payment status

**Request Body:**
```json
{
  "status": "string (required, PENDING/AUTHORIZED/PAID/FAILED/REFUNDED/CANCELLED)"
}
```

## Accountant Batch Verification

### List Upload Batches

**Endpoint:** `GET /accountant/batches`

**Description:** Get list of upload batches awaiting verification

**Query Parameters:**
- `status`: Filter by status (pending, approved, rejected)
- `sembatches`: Filter by semester batch ID
- `school`: Filter by school ID
- `date_from`: Filter by date range start
- `date_to`: Filter by date range end
- `page`: Page number

### Get Batch Details

**Endpoint:** `GET /accountant/batches/{id}`

**Description:** Get detailed information about upload batch

**Response:**
```json
{
  "batch_id": 1,
  "batch_code": "BATCH-2025-001",
  "sembatches_id": 1,
  "uploaded_by": 2,
  "upload_date": "2025-11-10 10:30:00",
  "total_students": 50,
  "fee_per_student": 10000,
  "total_fees": 500000,
  "status": "pending",
  "students": [...]
}
```

### Approve Batch

**Endpoint:** `POST /accountant/batches/{id}/approve`

**Description:** Approve upload batch and process payment

**Request Body:**
```json
{
  "paymentmode_id": "integer (required)",
  "transaction_id": "string (required)",
  "total_amount": "decimal (required)",
  "payment_date": "datetime (required)",
  "notes": "string (optional)"
}
```

### Reject Batch

**Endpoint:** `POST /accountant/batches/{id}/reject`

**Description:** Reject upload batch with reason

**Request Body:**
```json
{
  "rejection_reason": "string (required)"
}
```

## Parcel Management

### List Parcels

**Endpoint:** `GET /parcels`

**Description:** Get list of parcels

### Create Parcel

**Endpoint:** `POST /parcels`

**Description:** Record new parcel

**Request Body:**
```json
{
  "student_id": "integer (required)",
  "parcelcompany_id": "integer (required)",
  "tracking_number": "string (required)",
  "notes": "string (optional)"
}
```

### Update Parcel

**Endpoint:** `PUT /parcels/{id}`

**Description:** Update parcel information

### Delete Parcel

**Endpoint:** `DELETE /parcels/{id}`

**Description:** Delete parcel record

## Location Management

### Get States by Country

**Endpoint:** `GET /locations/api/states/{country_id}`

**Description:** Get list of states for a country

**Response:**
```json
[
  {
    "state_id": 1,
    "state_name": "Maharashtra"
  }
]
```

### Get Districts by State

**Endpoint:** `GET /locations/api/districts/{state_id}`

**Description:** Get list of districts for a state

### Get Talukos by District

**Endpoint:** `GET /locations/api/talukos/{district_id}`

**Description:** Get list of talukos for a district

## Search

### Global Search

**Endpoint:** `GET /search`

**Description:** Search across students, schools, and users

**Query Parameters:**
- `q`: Search query (required)
- `type`: Filter by type (students, schools, users)

**Response:**
```json
{
  "students": [
    {
      "student_id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    }
  ],
  "schools": [
    {
      "school_id": 1,
      "school_name": "ABC School"
    }
  ],
  "users": [
    {
      "sysuser_id": 1,
      "username": "admin",
      "name": "Admin User"
    }
  ]
}
```

## Audit Logs

**Permission Required:** 10001

### List Audit Logs

**Endpoint:** `GET /audit-logs`

**Description:** Get list of audit logs

**Query Parameters:**
- `user`: Filter by user ID
- `action`: Filter by action (created, updated, deleted)
- `entity_type`: Filter by entity type
- `date_from`: Filter by date range start
- `date_to`: Filter by date range end
- `page`: Page number

### Get Audit Log

**Endpoint:** `GET /audit-logs/{id}`

**Description:** Get detailed audit log entry

### Get Audit Logs for Model

**Endpoint:** `POST /audit-logs/for-model`

**Description:** Get audit logs for specific model instance

**Request Body:**
```json
{
  "entity_type": "string (required)",
  "entity_id": "integer (required)"
}
```

## Error Responses

All endpoints may return the following error responses:

### 400 Bad Request
```json
{
  "message": "Validation failed",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### 401 Unauthorized
```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden
```json
{
  "message": "You do not have permission to perform this action"
}
```

### 404 Not Found
```json
{
  "message": "Resource not found"
}
```

### 422 Unprocessable Entity
```json
{
  "message": "The given data was invalid",
  "errors": {
    "field": ["Error message"]
  }
}
```

### 500 Internal Server Error
```json
{
  "message": "An error occurred while processing your request"
}
```

## Rate Limiting

API endpoints are rate limited to prevent abuse:
- Authenticated requests: 60 requests per minute
- Guest requests: 10 requests per minute

Rate limit headers are included in responses:
- `X-RateLimit-Limit`: Maximum requests allowed
- `X-RateLimit-Remaining`: Remaining requests
- `X-RateLimit-Reset`: Time when limit resets (Unix timestamp)

## Pagination

List endpoints support pagination with the following parameters:
- `page`: Page number (default: 1)
- `per_page`: Items per page (default: 15, max: 100)

Paginated responses include:
```json
{
  "data": [...],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 10,
    "per_page": 15,
    "to": 15,
    "total": 150
  },
  "links": {
    "first": "url",
    "last": "url",
    "prev": null,
    "next": "url"
  }
}
```

---

*Last Updated: November 2025*
*Version: 1.0*
