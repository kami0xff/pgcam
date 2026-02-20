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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
    ],

    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'indexnow' => [
        'key' => env('INDEXNOW_KEY', '9382709b6acf468c8e4e61cab366dfe7'),
    ],

    'prelander' => [
        'allowed_origins' => env('PRELANDER_ALLOWED_ORIGINS', ''),
    ],

    'affiliates' => [
        'stripchat' => [
            'base_url' => 'https://stripchat.com',
            'campaign_id' => env('STRIPCHAT_CAMPAIGN_ID', ''),
            // URL format: https://stripchat.com/{username}?utm_campaign={campaign_id}
        ],
        'xlovecam' => [
            'base_url' => 'https://www.xlovecam.com/chat',
            'affiliate_id' => env('XLOVECAM_AFFILIATE_ID', '333'),
            // URL format: https://www.xlovecam.com/chat/{username}/?id_affilie={affiliate_id}
        ],
        'bongacams' => [
            'track_url' => env('BONGACAMS_TRACK_URL', 'https://bongacams11.com/track'),
            'campaign_id' => env('BONGACAMS_CAMPAIGN_ID', '833673'),
            'site_url' => env('BONGACAMS_SITE_URL', 'https://bongacams.com'),
            // Chat room: track?c={id}&csurl=https://bongacams.com/{username}
            // Profile:   track?c={id}&csurl=https://bongacams.com/profile/{username}
        ],
    ],

];
