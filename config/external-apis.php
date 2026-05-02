<?php

/**
 * External APIs Configuration
 * 
 * This file contains configurations for third-party API integrations
 * Most of these are free APIs with either no auth required or free tier available
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Calendarific API
    |--------------------------------------------------------------------------
    | Free holidays API - 1,000 requests/month on free tier
    | Get your free key at: https://calendarific.com/
    */
    'calendarific_key' => env('CALENDARIFIC_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | OpenWeather API
    |--------------------------------------------------------------------------
    | Weather API - 60 calls/minute on free tier
    | Get your free key at: https://openweathermap.org/api
    */
    'openweather_key' => env('OPENWEATHER_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | TimezoneDB API
    |--------------------------------------------------------------------------
    | Timezone API - 1 request/second on free tier
    | Get your free key at: https://timezonedb.com/
    */
    'timezonedb_key' => env('TIMEZONEDB_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Google Calendar API
    |--------------------------------------------------------------------------
    | Calendar integration for class schedules
    | Setup: https://developers.google.com/calendar
    */
    'google_calendar' => [
        'client_id' => env('GOOGLE_CALENDAR_CLIENT_ID', ''),
        'client_secret' => env('GOOGLE_CALENDAR_CLIENT_SECRET', ''),
        'redirect_uri' => env('GOOGLE_CALENDAR_REDIRECT_URI', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | SendGrid Email API
    |--------------------------------------------------------------------------
    | Email notifications - 100 emails/day on free tier
    | Get your free key at: https://sendgrid.com/
    */
    'sendgrid_key' => env('SENDGRID_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Twilio SMS API
    |--------------------------------------------------------------------------
    | SMS notifications - Free trial with $15 credit
    | Get started at: https://www.twilio.com/
    */
    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID', ''),
        'auth_token' => env('TWILIO_AUTH_TOKEN', ''),
        'phone_number' => env('TWILIO_PHONE_NUMBER', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Geolocation APIs
    |--------------------------------------------------------------------------
    | For location-based features
    */
    'geolocation' => [
        // IP Geolocation - MaxMind GeoIP2 (free tier available)
        'geoip2_account_id' => env('GEOIP2_ACCOUNT_ID', ''),
        'geoip2_license_key' => env('GEOIP2_LICENSE_KEY', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | School Location (Default)
    |--------------------------------------------------------------------------
    | Default coordinates for the school (Manila, Philippines)
    | Used for weather and timezone calculations
    */
    'school_location' => [
        'latitude' => 14.5994,
        'longitude' => 120.9842,
        'timezone' => 'Asia/Manila',
    ],
];
