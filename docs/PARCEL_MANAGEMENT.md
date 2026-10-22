# Parcel Management Implementation

## Overview

This document describes the implementation of the Parcel Management system (Task 16) for the Pramukh Sankalp School Management System.

## Features Implemented

### 1. Parcel Tracking (Task 16.1)

#### ParcelController
- **Location**: `app/Http/Controllers/ParcelController.php`
- **Features**:
  - CRUD operations for parcels
  - Filtering by student, parcel company, and tracking number
  - Validation for mandatory fields (student, company, tracking number)
  - Integration with tracking URL generation

#### Parcel Model
- **Location**: `app/Models/Parcel.php`
- **Relationships**:
  - `belongsTo` Student
  - `belongsTo` ParcelCompany

#### Views
- **Index**: `resources/views/parcels/index.blade.php`
  - List all parcels with filters
  - Display tracking links when available
  - Pagination support
  
- **Create**: `resources/views/parcels/create.blade.php`
  - Form to add new parcel
  - Student and company dropdowns
  - Tracking number input
  - Optional notes field
  
- **Edit**: `resources/views/parcels/edit.blade.php`
  - Form to update existing parcel
  - Pre-filled with current data

### 2. Parcel Company Management (Task 16.2)

#### ParcelCompanyController
- **Location**: `app/Http/Controllers/ParcelCompanyController.php`
- **Features**:
  - CRUD operations for parcel companies
  - Inactive status management
  - Validation to prevent deletion of companies with associated parcels
  - Filtering by status and company name

#### ParcelCompany Model
- **Location**: `app/Models/ParcelCompany.php`
- **Features**:
  - `active()` scope for filtering active companies
  - `generateTrackingUrl()` method to create tracking URLs with placeholders
  - Relationship with Parcel model

#### Views
- **Index**: `resources/views/parcel-companies/index.blade.php`
  - List all parcel companies
  - Status badges (Active/Inactive)
  - Search and filter functionality
  
- **Create**: `resources/views/parcel-companies/create.blade.php`
  - Form to add new parcel company
  - Fields: company name, tracking URL, API endpoint, API key, support contact, delivery time estimate
  - Inactive checkbox
  
- **Edit**: `resources/views/parcel-companies/edit.blade.php`
  - Form to update existing company
  - Pre-filled with current data

## Database Schema

### Parcel Table
- `parcel_id` (Primary Key)
- `student_id` (Foreign Key to student)
- `parcelcom_id` (Foreign Key to parcelcompany)
- `trakingnumber` (VARCHAR 150)
- `notes` (VARCHAR 500, nullable)
- `create_at` (DATE)

### ParcelCompany Table
- `parcelcom_id` (Primary Key)
- `companyname` (VARCHAR 255)
- `trackingurl` (VARCHAR 500, nullable) - Supports {tracking_number} placeholder
- `apiendpoint` (VARCHAR 255, nullable)
- `apikey` (VARCHAR 255, nullable)
- `supportcontact` (VARCHAR 50, nullable)
- `deliverytimeestimate` (VARCHAR 50, nullable)
- `inactive` (BOOLEAN, default false)
- `create_at` (DATE)
- `update_at` (DATE, nullable)

## Routes

### Parcel Routes
- `GET /parcels` - List all parcels
- `GET /parcels/create` - Show create form
- `POST /parcels` - Store new parcel
- `GET /parcels/{parcel}/edit` - Show edit form
- `PUT /parcels/{parcel}` - Update parcel
- `DELETE /parcels/{parcel}` - Delete parcel

### Parcel Company Routes
- `GET /parcel-companies` - List all companies
- `GET /parcel-companies/create` - Show create form
- `POST /parcel-companies` - Store new company
- `GET /parcel-companies/{parcelCompany}/edit` - Show edit form
- `PUT /parcel-companies/{parcelCompany}` - Update company
- `DELETE /parcel-companies/{parcelCompany}` - Delete company

## Validation Rules

### Parcel Validation
- `student_id`: Required, must exist in student table
- `parcelcom_id`: Required, must exist in parcelcompany table
- `trakingnumber`: Required, max 150 characters
- `notes`: Optional, max 500 characters

### Parcel Company Validation
- `companyname`: Required, max 255 characters
- `trackingurl`: Optional, must be valid URL, max 500 characters
- `apiendpoint`: Optional, must be valid URL, max 255 characters
- `apikey`: Optional, max 255 characters
- `supportcontact`: Optional, max 50 characters
- `deliverytimeestimate`: Optional, max 50 characters
- `inactive`: Boolean

## Key Features

### Tracking URL Generation
The system supports dynamic tracking URL generation using placeholders:
- Format: `https://track.example.com/?id={tracking_number}`
- The `{tracking_number}` placeholder is replaced with the actual tracking number
- Tracking links are displayed in the parcel list with an external link icon

### Inactive Status Management
- Parcel companies can be marked as inactive
- Inactive companies are excluded from dropdown selections
- Active companies are shown with a green badge, inactive with a red badge

### Deletion Protection
- Parcel companies with associated parcels cannot be deleted
- Error message is displayed when attempting to delete a company in use

## UI Design

All views follow the Tailwind CSS design system with:
- Primary color: #00bde0
- Consistent card layouts with rounded corners and shadows
- Responsive design for mobile, tablet, and desktop
- Form validation with inline error messages
- Success/error flash messages
- Hover effects on interactive elements

## Requirements Satisfied

### Requirement 21: Parcel Management
- ✅ 21.1: Entry page for recording parcels with mandatory fields
- ✅ 21.2: Validation for student ID, parcel company, and tracking number
- ✅ 21.3: List page showing all parcels with student and company information
- ✅ 21.4: Editing capability for parcel information including notes
- ✅ 21.5: Clickable tracking links when company has tracking URL

### Requirement 22: Parcel Company Management
- ✅ 22.1: Entry page for adding parcel companies with all fields
- ✅ 22.2: Company name as mandatory field
- ✅ 22.3: Inactive status defaulting to 0 (active)
- ✅ 22.4: List page showing all parcel companies
- ✅ 22.5: Inactive companies excluded from selection dropdowns

## Access

To access the parcel management features:
1. Navigate to `/parcels` for parcel tracking
2. Navigate to `/parcel-companies` for company management

Note: These routes are protected by authentication middleware and require a logged-in user.
