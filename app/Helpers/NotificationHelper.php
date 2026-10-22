<?php

namespace App\Helpers;

class NotificationHelper
{
    /**
     * Flash a success notification
     */
    public static function success($message)
    {
        session()->flash('notification', [
            'type' => 'success',
            'message' => $message
        ]);
    }

    /**
     * Flash an error notification
     */
    public static function error($message)
    {
        session()->flash('notification', [
            'type' => 'error',
            'message' => $message
        ]);
    }

    /**
     * Flash a warning notification
     */
    public static function warning($message)
    {
        session()->flash('notification', [
            'type' => 'warning',
            'message' => $message
        ]);
    }

    /**
     * Flash an info notification
     */
    public static function info($message)
    {
        session()->flash('notification', [
            'type' => 'info',
            'message' => $message
        ]);
    }

    /**
     * Add a notification to the session array (for multiple notifications)
     */
    public static function add($type, $message)
    {
        $notifications = session()->get('notifications', []);
        $notifications[] = [
            'type' => $type,
            'message' => $message
        ];
        session()->put('notifications', $notifications);
    }
}
