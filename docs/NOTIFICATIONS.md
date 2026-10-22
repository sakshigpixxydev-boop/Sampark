# Notification System Documentation

## Overview

The Pramukh Sankalp application includes a comprehensive notification system for both email notifications and in-app toast notifications. This system provides a consistent way to communicate with users about important events and actions.

## Email Notifications

### Configuration

Email notifications are configured in the `.env` file:

```env
# For development - logs emails to storage/logs/laravel.log
MAIL_MAILER=log

# For production - configure SMTP
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@pramukhsankalp.com"
MAIL_FROM_NAME="Pramukh Sankalp"
```

### Available Email Notifications

#### 1. Password Reset Notification
**Class:** `App\Notifications\ResetPasswordNotification`

Sent when a user requests a password reset.

```php
$user->notify(new ResetPasswordNotification($token));
```

#### 2. PIN Reset Notification
**Class:** `App\Notifications\ResetPinNotification`

Sent when a user requests a PIN reset.

```php
$user->notify(new ResetPinNotification($token));
```

#### 3. Batch Rejected Notification
**Class:** `App\Notifications\BatchRejectedNotification`

Sent when an accountant rejects a student upload batch.

```php
$uploader->notify(new BatchRejectedNotification($batch, $reason));
```

#### 4. Payment Confirmation Notification
**Class:** `App\Notifications\PaymentConfirmationNotification`

Sent when an accountant approves a batch payment.

```php
$uploader->notify(new PaymentConfirmationNotification($batch));
```

### Creating New Email Notifications

1. Create a new notification class:
```bash
php artisan make:notification YourNotificationName
```

2. Implement the notification:
```php
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class YourNotificationName extends Notification
{
    use Queueable;

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Subject')
            ->line('Your message content')
            ->action('Action Button', url('/'))
            ->line('Additional information');
    }
}
```

3. Send the notification:
```php
$user->notify(new YourNotificationName());
```

## In-App Notifications (Toast Messages)

### Usage in Controllers

The notification system provides several helper functions for displaying toast notifications:

#### Success Notifications
```php
// Using helper function
notify_success('Student created successfully!');

// Using NotificationHelper class
use App\Helpers\NotificationHelper;
NotificationHelper::success('Student created successfully!');

// Using session flash (legacy support)
return redirect()->back()->with('success', 'Student created successfully!');
```

#### Error Notifications
```php
notify_error('Failed to save student. Please try again.');
```

#### Warning Notifications
```php
notify_warning('This action cannot be undone.');
```

#### Info Notifications
```php
notify_info('Your changes have been saved as draft.');
```

### Multiple Notifications

To display multiple notifications at once:

```php
use App\Helpers\NotificationHelper;

NotificationHelper::add('success', 'Student created successfully!');
NotificationHelper::add('info', 'An email has been sent to the guardian.');
NotificationHelper::add('warning', 'Please complete the payment within 7 days.');
```

### JavaScript API

You can also trigger notifications from JavaScript:

```javascript
// Trigger a notification
window.notify('success', 'Action completed successfully!');
window.notify('error', 'Something went wrong!');
window.notify('warning', 'Please review your input.');
window.notify('info', 'Additional information available.');
```

### Notification Types and Styling

The notification system supports four types of notifications:

1. **Success** (Green)
   - Used for successful operations
   - Examples: "Student created", "Payment processed", "Data saved"

2. **Error** (Red)
   - Used for errors and failures
   - Examples: "Failed to save", "Invalid input", "Permission denied"

3. **Warning** (Yellow)
   - Used for warnings and cautions
   - Examples: "Action cannot be undone", "Incomplete data", "Approaching limit"

4. **Info** (Blue)
   - Used for informational messages
   - Examples: "Email sent", "Processing in background", "New feature available"

### Notification Behavior

- Notifications appear in the top-right corner of the screen
- They automatically dismiss after 5 seconds
- Users can manually dismiss notifications by clicking the close button
- Multiple notifications stack vertically
- Notifications slide in from the right with a smooth animation
- On mobile devices, notifications are responsive and adjust to screen size

## Components

### Notification Container Component

The main notification container is included in the layout:

```blade
<x-notification-container />
```

This component:
- Manages the notification queue
- Handles animations
- Processes session flash messages
- Listens for JavaScript notification events

### Alert Component (Inline Alerts)

For inline alerts within page content:

```blade
<x-alert type="success">
    Your changes have been saved successfully.
</x-alert>

<x-alert type="error" dismissible>
    There was an error processing your request.
</x-alert>
```

### Toast Component (Legacy)

The legacy toast component is still available but the notification container is preferred:

```blade
<x-toast type="success" message="Operation completed!" />
```

## Best Practices

1. **Use Appropriate Types**
   - Use success for completed actions
   - Use error for failures that require user attention
   - Use warning for potential issues
   - Use info for neutral information

2. **Keep Messages Concise**
   - Aim for one sentence
   - Be specific about what happened
   - Avoid technical jargon

3. **Provide Context**
   - Include relevant details (e.g., "Batch ABC123 approved")
   - Use action-oriented language

4. **Email Notifications**
   - Always check if user has an email before sending
   - Include relevant details in the email body
   - Provide action links when appropriate

5. **Error Handling**
   - Always wrap notification sending in try-catch blocks
   - Log errors if notification sending fails
   - Don't let notification failures break the main flow

## Examples

### Controller Example

```php
public function store(Request $request)
{
    try {
        $student = Student::create($request->validated());
        
        // Send email notification
        if ($student->email) {
            $student->notify(new StudentRegisteredNotification($student));
        }
        
        // Show success toast
        notify_success('Student registered successfully!');
        
        return redirect()->route('students.index');
    } catch (\Exception $e) {
        notify_error('Failed to register student: ' . $e->getMessage());
        return redirect()->back()->withInput();
    }
}
```

### Blade Template Example

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Inline alert for validation errors -->
    @if ($errors->any())
        <x-alert type="error" dismissible>
            <strong>Please correct the following errors:</strong>
            <ul class="mt-2 ml-4 list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif
    
    <!-- Your page content -->
</div>
@endsection
```

### JavaScript Example

```javascript
// After AJAX success
fetch('/api/students', {
    method: 'POST',
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(data => {
    window.notify('success', 'Student created successfully!');
})
.catch(error => {
    window.notify('error', 'Failed to create student.');
});
```

## Troubleshooting

### Notifications Not Appearing

1. Check that `<x-notification-container />` is included in your layout
2. Verify Alpine.js is loaded
3. Check browser console for JavaScript errors
4. Ensure session is working properly

### Email Notifications Not Sending

1. Check `.env` mail configuration
2. Verify MAIL_MAILER is set correctly
3. Check `storage/logs/laravel.log` for errors
4. Ensure user has a valid email address
5. For Gmail, use an app-specific password

### Multiple Notifications Overlapping

This is expected behavior. Notifications stack vertically and auto-dismiss after 5 seconds.

## Future Enhancements

Potential improvements for the notification system:

1. Database-backed notification center
2. Push notifications for mobile devices
3. SMS notifications for critical events
4. Notification preferences per user
5. Read/unread status tracking
6. Notification history and archive
