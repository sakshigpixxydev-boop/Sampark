# Browser Tests

This directory is for Laravel Dusk browser tests.

## Setup

To run browser tests, you need to install Laravel Dusk:

```bash
composer require --dev laravel/dusk
php artisan dusk:install
```

## Running Tests

```bash
php artisan dusk
```

## Test Coverage

Browser tests should cover:

1. **Complete Student Registration Flow**
   - Navigate to student creation page
   - Fill out form with valid data
   - Submit and verify student is created
   - Verify redirect to student list

2. **Bulk Upload to Payment Verification Flow**
   - Upload CSV file
   - Select semester batch
   - Preview students with fees
   - Submit for verification
   - Login as accountant
   - Approve batch payment
   - Verify students are registered

3. **Invoice Generation to Payment Flow**
   - Create invoice for student
   - Verify fee calculation
   - Verify discount application
   - Record payment
   - Verify invoice status changes to PAID

4. **Dashboard Navigation and Metrics**
   - Login as different roles (Admin, Initiator, School)
   - Verify correct dashboard displays
   - Verify metrics are accurate
   - Click on overview cards
   - Verify navigation to filtered lists

## Example Test Structure

```php
<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudentRegistrationTest extends DuskTestCase
{
    public function test_complete_student_registration_flow()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($user)
                    ->visit('/students/create')
                    ->type('fname', 'John')
                    ->type('mname', 'Michael')
                    ->type('lname', 'Doe')
                    ->type('email', 'john.doe@example.com')
                    ->select('studenttype_id', 1)
                    ->select('eduboard_id', 1)
                    ->select('std_id', 1)
                    ->select('takula_id', 1)
                    ->press('Save')
                    ->assertPathIs('/students')
                    ->assertSee('John Doe');
        });
    }
}
```

## Notes

- Browser tests are slower than feature tests
- They require a running web server
- They test the actual user interface
- They catch JavaScript errors and UI issues
- Use them for critical user flows only
