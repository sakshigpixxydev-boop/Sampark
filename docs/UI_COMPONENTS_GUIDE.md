# UI Components Guide

This guide documents all reusable UI components available in the Pramukh Sankalp School Management System.

## Design System

### Colors
- **Primary**: #00bde0 (cyan)
- **Primary Dark**: #0099b8
- **Success**: #10b981 (green)
- **Warning**: #f59e0b (amber)
- **Error/Danger**: #ef4444 (red)
- **Info**: #3b82f6 (blue)

### Breakpoints
- **Mobile**: < 640px (sm)
- **Tablet**: 640px - 1024px (md, lg)
- **Desktop**: > 1024px (xl, 2xl)

## Layout Components

### Base Layout (`layouts/app.blade.php`)

The main application layout with sidebar navigation and responsive header.

**Features:**
- Collapsible sidebar with navigation links
- Responsive hamburger menu for mobile
- User profile dropdown
- Success/error message display
- Mobile overlay for sidebar

**Usage:**
```blade
@extends('layouts.app')

@section('title', 'Page Title')
@section('page-title', 'Dashboard')

@section('content')
    <!-- Your content here -->
@endsection
```

## Form Components

### Button (`components/button.blade.php`)

Reusable button component with multiple variants.

**Props:**
- `type`: button|submit|reset (default: 'button')
- `variant`: primary|secondary|danger|success|outline (default: 'primary')

**Usage:**
```blade
<x-button variant="primary" type="submit">
    Save Changes
</x-button>

<x-button variant="danger" @click="deleteItem()">
    Delete
</x-button>
```

### Input (`components/input.blade.php`)

Text input field with label and error display.

**Props:**
- `label`: Field label text
- `name`: Input name attribute
- `type`: Input type (default: 'text')
- `required`: Boolean for required field
- `error`: Error message to display

**Usage:**
```blade
<x-input 
    label="Email Address" 
    name="email" 
    type="email" 
    :required="true"
    :error="$errors->first('email')"
    value="{{ old('email') }}"
/>
```

### Select (`components/select.blade.php`)

Dropdown select field with label and error display.

**Props:**
- `label`: Field label text
- `name`: Select name attribute
- `required`: Boolean for required field
- `error`: Error message to display

**Usage:**
```blade
<x-select label="School Type" name="school_type_id" :required="true">
    <option value="">Select Type</option>
    @foreach($schoolTypes as $type)
        <option value="{{ $type->id }}">{{ $type->name }}</option>
    @endforeach
</x-select>
```

### Textarea (`components/textarea.blade.php`)

Multi-line text input with label and error display.

**Props:**
- `label`: Field label text
- `name`: Textarea name attribute
- `required`: Boolean for required field
- `error`: Error message to display
- `rows`: Number of rows (default: 3)

**Usage:**
```blade
<x-textarea 
    label="Description" 
    name="description" 
    :rows="5"
    :error="$errors->first('description')"
>{{ old('description') }}</x-textarea>
```

## Display Components

### Card (`components/card.blade.php`)

Container component with consistent styling.

**Props:**
- `padding`: Boolean to include padding (default: true)

**Usage:**
```blade
<x-card>
    <h3 class="text-lg font-semibold mb-4">Card Title</h3>
    <p>Card content goes here</p>
</x-card>

<x-card :padding="false">
    <!-- Custom padding content -->
</x-card>
```

### Badge (`components/badge.blade.php`)

Small status indicator component.

**Props:**
- `variant`: success|error|warning|info|primary|default

**Usage:**
```blade
<x-badge variant="success">Active</x-badge>
<x-badge variant="error">Inactive</x-badge>
<x-badge variant="warning">Pending</x-badge>
```

### Alert (`components/alert.blade.php`)

Alert message component with icon.

**Props:**
- `type`: success|error|warning|info (default: 'info')
- `dismissible`: Boolean to show close button

**Usage:**
```blade
<x-alert type="success" :dismissible="true">
    Your changes have been saved successfully!
</x-alert>

<x-alert type="error">
    An error occurred while processing your request.
</x-alert>
```

### Table (`components/table.blade.php`)

Responsive table component.

**Props:**
- `headers`: Array of header labels

**Usage:**
```blade
<x-table :headers="['ID', 'Name', 'Email', 'Status', 'Actions']">
    @foreach($users as $user)
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4">{{ $user->id }}</td>
            <td class="px-6 py-4">{{ $user->name }}</td>
            <td class="px-6 py-4">{{ $user->email }}</td>
            <td class="px-6 py-4">
                <x-badge :variant="$user->active ? 'success' : 'error'">
                    {{ $user->active ? 'Active' : 'Inactive' }}
                </x-badge>
            </td>
            <td class="px-6 py-4">
                <x-button variant="secondary" size="sm">Edit</x-button>
            </td>
        </tr>
    @endforeach
</x-table>
```

## Interactive Components

### Modal (`components/modal.blade.php`)

Modal dialog component with Alpine.js.

**Props:**
- `name`: Unique modal identifier
- `title`: Modal title
- `maxWidth`: sm|md|lg|xl|2xl|3xl|4xl (default: 'md')

**Usage:**
```blade
<!-- Trigger Button -->
<x-button @click="$dispatch('open-modal', 'delete-confirmation')">
    Delete Item
</x-button>

<!-- Modal Definition -->
<x-modal name="delete-confirmation" title="Confirm Deletion" maxWidth="md">
    <p class="text-gray-600 mb-4">Are you sure you want to delete this item?</p>
    <div class="flex justify-end space-x-3">
        <x-button variant="secondary" @click="$dispatch('close-modal', 'delete-confirmation')">
            Cancel
        </x-button>
        <x-button variant="danger">
            Delete
        </x-button>
    </div>
</x-modal>
```

### Toast (`components/toast.blade.php`)

Toast notification component (auto-dismisses after 5 seconds).

**Props:**
- `type`: success|error|warning|info (default: 'success')
- `message`: Notification message

**Usage:**
```blade
@if(session('success'))
    <x-toast type="success" :message="session('success')" />
@endif

<x-toast type="error">
    An error occurred!
</x-toast>
```

## Dashboard Components

### Stat Card (`components/dashboard/stat-card.blade.php`)

Dashboard statistics card with icon.

**Props:**
- `title`: Card title
- `value`: Statistic value
- `icon`: SVG icon HTML
- `href`: Link URL (default: '#')
- `color`: primary|success|warning|danger|info (default: 'primary')

**Usage:**
```blade
<x-dashboard.stat-card 
    title="Total Students" 
    :value="number_format($totalStudents)"
    href="{{ route('students.index') }}"
    color="primary"
>
    <x-slot:icon>
        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
        </svg>
    </x-slot:icon>
</x-dashboard.stat-card>
```

### Section Header (`components/dashboard/section-header.blade.php`)

Section header with title and optional actions.

**Props:**
- `title`: Section title
- `subtitle`: Optional subtitle

**Usage:**
```blade
<x-dashboard.section-header 
    title="Pending Payments" 
    subtitle="Recent invoices awaiting payment"
>
    <x-slot:actions>
        <x-button variant="outline" size="sm">View All</x-button>
    </x-slot:actions>
</x-dashboard.section-header>
```

### Activity Item (`components/dashboard/activity-item.blade.php`)

Activity list item component.

**Props:**
- `icon`: SVG icon HTML
- `title`: Activity title
- `description`: Activity description
- `time`: Timestamp
- `href`: Link URL (default: '#')

**Usage:**
```blade
<x-dashboard.activity-item 
    title="New student registered"
    description="John Doe enrolled in Semester 1"
    time="2 hours ago"
    href="{{ route('students.show', $student->id) }}"
>
    <x-slot:icon>
        <svg class="w-5 h-5 text-[#00bde0]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
        </svg>
    </x-slot:icon>
</x-dashboard.activity-item>
```

### Empty State (`components/dashboard/empty-state.blade.php`)

Empty state component for tables.

**Props:**
- `icon`: Custom SVG icon HTML
- `message`: Empty state message
- `colspan`: Table colspan value

**Usage:**
```blade
<x-table :headers="['ID', 'Name', 'Status']">
    @forelse($items as $item)
        <tr>
            <td>{{ $item->id }}</td>
            <td>{{ $item->name }}</td>
            <td>{{ $item->status }}</td>
        </tr>
    @empty
        <x-dashboard.empty-state 
            :colspan="3" 
            message="No items found"
        >
            <x-slot:action>
                <x-button variant="primary" href="{{ route('items.create') }}">
                    Add New Item
                </x-button>
            </x-slot:action>
        </x-dashboard.empty-state>
    @endforelse
</x-table>
```

## Responsive Components

### Responsive Grid (`components/responsive-grid.blade.php`)

Responsive grid layout component.

**Props:**
- `cols`: Number of columns (1|2|3|4, default: 3)

**Usage:**
```blade
<x-responsive-grid :cols="3">
    <x-card>Card 1</x-card>
    <x-card>Card 2</x-card>
    <x-card>Card 3</x-card>
</x-responsive-grid>
```

### Responsive Table Wrapper (`components/responsive-table-wrapper.blade.php`)

Wrapper for horizontal scrolling tables on mobile.

**Props:**
- `mobileCards`: Boolean to show as cards on mobile

**Usage:**
```blade
<x-responsive-table-wrapper>
    <x-table :headers="['ID', 'Name', 'Email', 'Status']">
        <!-- Table rows -->
    </x-table>
</x-responsive-table-wrapper>
```

### Mobile Filter Sheet (`components/mobile-filter-sheet.blade.php`)

Bottom sheet for filters on mobile devices.

**Props:**
- `title`: Sheet title (default: 'Filters')

**Usage:**
```blade
<x-mobile-filter-sheet title="Filter Students">
    <div class="space-y-4">
        <x-select label="School" name="school_id">
            <option value="">All Schools</option>
            @foreach($schools as $school)
                <option value="{{ $school->id }}">{{ $school->name }}</option>
            @endforeach
        </x-select>
        
        <x-select label="Status" name="status">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
        </x-select>
    </div>
    
    <x-slot:actions>
        <div class="flex space-x-3">
            <x-button variant="secondary" class="flex-1">Clear</x-button>
            <x-button variant="primary" class="flex-1">Apply</x-button>
        </div>
    </x-slot:actions>
</x-mobile-filter-sheet>
```

### Page Header (`components/page-header.blade.php`)

Page header with title and optional actions.

**Props:**
- `title`: Page title
- `subtitle`: Optional subtitle

**Usage:**
```blade
<x-page-header 
    title="Students" 
    subtitle="Manage student records and enrollments"
>
    <x-slot:actions>
        <x-button variant="primary" href="{{ route('students.create') }}">
            Add New Student
        </x-button>
    </x-slot:actions>
</x-page-header>
```

## Responsive Design Guidelines

### Mobile Optimization

1. **Navigation**: Sidebar collapses to hamburger menu on mobile
2. **Tables**: Horizontal scroll enabled on mobile devices
3. **Forms**: Full-width inputs with optimized spacing
4. **Cards**: Stack vertically on mobile, grid on desktop
5. **Filters**: Bottom sheet on mobile, sidebar on desktop

### Breakpoint Usage

```blade
<!-- Hide on mobile, show on desktop -->
<div class="hidden lg:block">Desktop only content</div>

<!-- Show on mobile, hide on desktop -->
<div class="lg:hidden">Mobile only content</div>

<!-- Responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Grid items -->
</div>

<!-- Responsive padding -->
<div class="px-4 sm:px-6 lg:px-8">
    <!-- Content -->
</div>
```

### Testing Checklist

- [ ] Test on mobile (< 640px)
- [ ] Test on tablet (640px - 1024px)
- [ ] Test on desktop (> 1024px)
- [ ] Verify sidebar collapse/expand
- [ ] Check table horizontal scroll
- [ ] Test form inputs on mobile
- [ ] Verify modal responsiveness
- [ ] Check filter sheet on mobile

## Best Practices

1. **Consistency**: Use components consistently across the application
2. **Accessibility**: Ensure proper labels and ARIA attributes
3. **Performance**: Lazy load images and defer non-critical scripts
4. **Mobile First**: Design for mobile, enhance for desktop
5. **Touch Targets**: Ensure buttons are at least 44x44px on mobile
6. **Spacing**: Use consistent spacing (4px, 8px, 16px, 24px, 32px)
7. **Typography**: Use consistent font sizes and weights
8. **Colors**: Stick to the defined color palette

## Examples

### Complete Form Example

```blade
<x-card>
    <form method="POST" action="{{ route('students.store') }}">
        @csrf
        
        <div class="space-y-4">
            <x-input 
                label="First Name" 
                name="fname" 
                :required="true"
                :error="$errors->first('fname')"
                value="{{ old('fname') }}"
            />
            
            <x-input 
                label="Email" 
                name="email" 
                type="email"
                :required="true"
                :error="$errors->first('email')"
                value="{{ old('email') }}"
            />
            
            <x-select 
                label="School" 
                name="school_id" 
                :required="true"
                :error="$errors->first('school_id')"
            >
                <option value="">Select School</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                @endforeach
            </x-select>
            
            <x-textarea 
                label="Notes" 
                name="notes"
                :rows="4"
            >{{ old('notes') }}</x-textarea>
        </div>
        
        <div class="flex justify-end space-x-3 mt-6">
            <x-button variant="secondary" type="button" onclick="history.back()">
                Cancel
            </x-button>
            <x-button variant="primary" type="submit">
                Save Student
            </x-button>
        </div>
    </form>
</x-card>
```

### Complete Dashboard Example

```blade
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <x-page-header 
            title="Dashboard" 
            subtitle="Overview of system metrics"
        />
        
        <x-responsive-grid :cols="3">
            <x-dashboard.stat-card 
                title="Total Students" 
                :value="number_format($totalStudents)"
                href="{{ route('students.index') }}"
            >
                <x-slot:icon>
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </x-slot:icon>
            </x-dashboard.stat-card>
            
            <!-- More stat cards -->
        </x-responsive-grid>
        
        <x-card class="mt-8" :padding="false">
            <x-dashboard.section-header 
                title="Recent Activity" 
                subtitle="Latest system updates"
            />
            
            <div class="divide-y divide-gray-200">
                <x-dashboard.activity-item 
                    title="New student registered"
                    description="John Doe enrolled in Semester 1"
                    time="2 hours ago"
                />
                <!-- More activity items -->
            </div>
        </x-card>
    </div>
</div>
@endsection
```
