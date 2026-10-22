<?php

use App\Helpers\NotificationHelper;

if (!function_exists('notify')) {
    /**
     * Flash a notification message
     *
     * @param string $type The notification type (success, error, warning, info)
     * @param string $message The notification message
     * @return void
     */
    function notify($type, $message)
    {
        NotificationHelper::{$type}($message);
    }
}

if (!function_exists('notify_success')) {
    /**
     * Flash a success notification
     *
     * @param string $message
     * @return void
     */
    function notify_success($message)
    {
        NotificationHelper::success($message);
    }
}

if (!function_exists('notify_error')) {
    /**
     * Flash an error notification
     *
     * @param string $message
     * @return void
     */
    function notify_error($message)
    {
        NotificationHelper::error($message);
    }
}

if (!function_exists('notify_warning')) {
    /**
     * Flash a warning notification
     *
     * @param string $message
     * @return void
     */
    function notify_warning($message)
    {
        NotificationHelper::warning($message);
    }
}

if (!function_exists('notify_info')) {
    /**
     * Flash an info notification
     *
     * @param string $message
     * @return void
     */
    function notify_info($message)
    {
        NotificationHelper::info($message);
    }
}
