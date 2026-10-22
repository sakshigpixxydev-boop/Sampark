<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Razorpay API Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Razorpay API credentials here.
    | Get your credentials from: https://dashboard.razorpay.com/app/keys
    |
    */

    'key_id' => env('RAZORPAY_KEY_ID', 'rzp_live_9t8o2rcuMlGIoB'),
    'key_secret' => env('RAZORPAY_KEY_SECRET', 'rnwqsuB98xbvbnkovjTwVZj4'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET', '#LY?sL8Rawr02hIc8l&o'),

    /*
    |--------------------------------------------------------------------------
    | Test Mode
    |--------------------------------------------------------------------------
    |
    | Set to true to use test mode credentials
    |
    */
    'test_mode' => env('RAZORPAY_TEST_MODE', true),
];
