<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'razorpay' => [
        'key' => env('RAZORPAY_KEY_ID', 'rzp_live_9t8o2rcuMlGIoB'),
        'secret' => env('RAZORPAY_KEY_SECRET', 'rnwqsuB98xbvbnkovjTwVZj4'),
    ],

    /*
     * Shared secret for the public enquiry intake endpoint (POST api/enquiries).
     * Used only by that route's api.key middleware. The static default lets the
     * endpoint work without any .env setup; ENQUIRY_API_KEY still overrides it
     * per environment.
     */
    'enquiry_api' => [
        'key' => env('ENQUIRY_API_KEY', 'admission_live_jKfSEHbhebQ2I7kbdrYviRAQTGhjzpco'),
    ],

];
