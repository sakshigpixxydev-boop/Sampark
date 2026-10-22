# Search and Filtering Implementation

## Overview

This document describes the global search and advanced filtering features implemented in the Pramukh Sankalp School Management System.

## Features Implemented

### 1. Global Search (Task 18.1)

A comprehensive global search feature that allows users to quickly find students, schools, and users from anywhere in the application.

#### Components

**SearchService** (`app/Services/SearchService.php`)
- Handles search logic across multiple models
- Applies role-based filtering (Admin, Initiator, School)
- Returns formatted results with highlighting
- Searches across:
  - Students: name, email, contact
  - Schools: school name, admin name, admin contact
  - Users: name, username, email (Admin/Initiator only)

**SearchController** (`app/Http/Controllers/SearchController.php`)
- Handles search API requests
- Returns JSON responses for AJAX calls

**Global Search Component** (`resources/views/components/global-search.blade.php`)
- Alpine.js-powered search interface
- Debounced search (300ms delay)
- Keyboard navigation (arrow keys, enter, escape)
- Real-time results display
- Grouped results by type (Students, Schools, Users)
- Highlighted matching text

#### Usage

The global search bar is located in the top navigation bar (visible on desktop only). Users can:

1. Type at least 2 characters to trigger search
2. View results grouped by type
3. Navigate results with arrow keys
4. Press Enter to select highlighted result
5. Click on any result to navigate to that record

#### API Endpoint

```
GET /search?q={query}
```

**Response Format:**
```json
{
  "students": [
    {
      "id": 1,
      "title": "John Doe",
      "subtitle": "john@example.com",
      "meta": "ABC School",
      "url": "/students/1/edit",
      "type": "student",
      "highlight": "<mark>John</mark> Doe"
    }
  ],
  "schools": [...],
  "users": [...]
}
```

### 2. Advanced Filtering (Task 18.2)

A flexible filtering system with session persistence, multi-select capabilities, and date range filters.

#### Components

**FilterService** (`app/Services/FilterService.php`)
- Manages filter persistence in session
- Applies filters to query builders
- Supports multiple filter types:
  - `exact`: Exact match
  - `like`: Partial match
  - `in`: Multiple values (multi-select)
  - `date_range`: Date range filtering
  - `relation`: Filter through relationships
  - `custom`: Custom callback functions

**Filter Panel Component** (`resources/views/components/filter-panel.blade.php`)
- Collapsible filter interface
- Shows active filter count
- Clear all filters functionality
- Session-aware (remembers last applied filters)

**Multi-Select Component** (`resources/views/components/multi-select.blade.php`)
- Alpine.js-powered multi-select dropdown
- Search within options
- Visual tags for selected items
- Keyboard navigation

**Date Range Filter Component** (`resources/views/components/date-range-filter.blade.php`)
- From/To date inputs
- HTML5 date picker

#### Usage in Controllers

```php
use App\Services\FilterService;

class StudentController extends Controller
{
    protected $filterService;

    public function __construct(FilterService $filterService)
    {
        $this->filterService = $filterService;
    }

    public function index(Request $request)
    {
        // Merge request filters with session filters
        $filters = $this->filterService->mergeFilters($request, 'students');

        // Define filter map
        $filterMap = [
            'school_id' => ['type' => 'exact', 'column' => 'school_id'],
            'std_id' => ['type' => 'in', 'column' => 'std_id'],
            'search' => [
                'type' => 'custom',
                'callback' => function ($query, $value) {
                    $query->where('fname', 'like', "%{$value}%");
                }
            ],
        ];

        // Apply filters
        $query = $this->filterService->applyFilters($query, $filters, $filterMap);
        
        // Return view with filters
        return view('students.index', compact('filters'));
    }
}
```

#### Usage in Views

**Basic Filter Panel:**
```blade
<x-filter-panel action="{{ route('students.index') }}">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Text Input -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
        </div>

        <!-- Single Select -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">School</label>
            <select name="school_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                <option value="">All Schools</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" 
                            {{ ($filters['school_id'] ?? '') == $school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</x-filter-panel>
```

**Multi-Select Filter:**
```blade
<x-multi-select 
    name="std_id"
    label="Standard"
    :options="$standards"
    :selected="$filters['std_id'] ?? []"
    valueKey="std_id"
    labelKey="stdname"
    placeholder="Select standards..."
/>
```

**Date Range Filter:**
```blade
<x-date-range-filter 
    label="Registration Date"
    nameFrom="date_from"
    nameTo="date_to"
    :valueFrom="$filters['date_from'] ?? ''"
    :valueTo="$filters['date_to'] ?? ''"
/>
```

## Filter Persistence

Filters are automatically saved to the session when applied. This means:

1. Users can navigate away and return to see the same filters
2. Filters persist across page refreshes
3. Clicking "Clear All" removes filters from session
4. Each list page has its own filter session key

## Role-Based Filtering

Both search and filters respect role-based access:

- **Admin**: Can see all records
- **Initiator**: Can only see assigned schools and students
- **School**: Can only see their own school's students
- **Accountant**: Can see all records for verification

## Mobile Responsiveness

- Global search is hidden on mobile devices (< 768px)
- Filter panel is collapsible on all devices
- Multi-select dropdowns are touch-friendly
- Date pickers use native mobile date inputs

## Performance Considerations

1. **Search Debouncing**: 300ms delay prevents excessive API calls
2. **Result Limiting**: Maximum 10 results per category
3. **Eager Loading**: Related models are eager loaded to prevent N+1 queries
4. **Query Optimization**: Filters are applied at database level
5. **Session Storage**: Filters stored in session, not database

## Browser Compatibility

- Modern browsers (Chrome, Firefox, Safari, Edge)
- Requires JavaScript enabled
- Uses Alpine.js for interactivity
- Graceful degradation for older browsers

## Future Enhancements

Potential improvements for future versions:

1. Saved filter presets
2. Export filtered results
3. Advanced search operators (AND, OR, NOT)
4. Search history
5. Autocomplete suggestions
6. Full-text search with Elasticsearch/Meilisearch
7. Filter sharing via URL parameters

## Testing

To test the search and filtering features:

1. **Global Search**:
   - Type in the search bar in the navigation
   - Verify results appear for students, schools, users
   - Test keyboard navigation
   - Verify role-based filtering

2. **Advanced Filtering**:
   - Apply multiple filters on student list
   - Navigate away and return (filters should persist)
   - Test multi-select filters
   - Test clear all functionality
   - Verify filter count badge

## Troubleshooting

**Search not working:**
- Check JavaScript console for errors
- Verify Alpine.js is loaded
- Check search route is registered

**Filters not persisting:**
- Verify session is configured correctly
- Check session driver in `.env`
- Clear browser cache

**Multi-select not displaying:**
- Check Alpine.js is loaded
- Verify component syntax
- Check browser console for errors

## Related Files

- `app/Services/SearchService.php`
- `app/Services/FilterService.php`
- `app/Http/Controllers/SearchController.php`
- `resources/views/components/global-search.blade.php`
- `resources/views/components/filter-panel.blade.php`
- `resources/views/components/multi-select.blade.php`
- `resources/views/components/date-range-filter.blade.php`
- `resources/views/layouts/app.blade.php`
- `routes/web.php`
