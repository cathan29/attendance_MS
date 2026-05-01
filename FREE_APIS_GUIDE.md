# Free APIs Integration Guide

This document explains the free third-party APIs integrated into the Cipher Academy Attendance Management System and how to use them.

## Overview of Integrated APIs

### 1. **Open-Meteo Weather API** ✅ (No Key Required)
- **Purpose**: Display current weather information
- **Tier**: Completely free
- **Requests**: Unlimited
- **Documentation**: https://open-meteo.com/
- **Usage**: 
  ```php
  use App\Services\ExternalApiService;
  $weather = ExternalApiService::getWeather(14.5994, 120.9842);
  // Returns: ['temperature' => 25, 'humidity' => 65, 'weather_code' => 2]
  ```

### 2. **Calendarific Holidays API** ✅ (Free Tier)
- **Purpose**: Get national and religious holidays for the school
- **Tier**: Free (1,000 requests/month)
- **Requests**: 1,000 per month
- **Documentation**: https://calendarific.com/
- **Setup**:
  1. Register at https://calendarific.com/
  2. Get your free API key
  3. Add to `.env`: `CALENDARIFIC_API_KEY=your_key_here`
- **Usage**:
  ```php
  $holidays = ExternalApiService::getHolidays('2026-05-01', 'PH');
  $isHoliday = ExternalApiService::isSchoolHoliday('2026-12-25');
  ```

### 3. **TimezoneDB API** ✅ (Free Tier)
- **Purpose**: Get accurate timezone information based on location
- **Tier**: Free (1 request/second)
- **Documentation**: https://timezonedb.com/
- **Setup**:
  1. Register at https://timezonedb.com/
  2. Get your free API key
  3. Add to `.env`: `TIMEZONEDB_API_KEY=your_key_here`
- **Usage**:
  ```php
  $timezone = ExternalApiService::getTimezone(14.5994, 120.9842);
  // Returns: "Asia/Manila"
  ```

## Recommended Free APIs to Add

### 4. **Google Calendar API**
- **Purpose**: Sync school calendar with class schedules
- **Tier**: Free (500 requests/100 seconds)
- **Setup**: https://developers.google.com/calendar/api
- **Benefits**:
  - Import school events into the system
  - Export class schedules to students' Google Calendars
  - Automatic reminder notifications

### 5. **SendGrid Email API**
- **Purpose**: Send attendance notifications and reports
- **Tier**: Free (100 emails/day)
- **Setup**: https://sendgrid.com/
- **Benefits**:
  - Automated attendance reminders to parents
  - Daily/weekly attendance reports via email
  - Teacher notifications about class schedules

### 6. **Twilio SMS API**
- **Purpose**: Send SMS notifications to parents
- **Tier**: Free trial with $15 credit (approximately 100-300 SMS depending on region)
- **Setup**: https://www.twilio.com/
- **Benefits**:
  - SMS reminders for absent students
  - Quick notifications to parents
  - Two-factor authentication for accounts

### 7. **GeoJSON / Geolocation APIs**
- **Purpose**: Map school locations and distance tracking
- **Options**:
  - **Nominatim (OpenStreetMap)**: Free, unlimited (though rate-limited)
  - **Google Maps API**: Free tier available
- **Benefits**:
  - Geofencing for teacher check-in
  - Distance calculations for student verification

### 8. **QR Code Generation API**
- **Purpose**: Generate QR codes for attendance check-in
- **Tier**: Free, no key required
- **Options**:
  - **QR Server**: Free
  - **GoQR**: Free
  - Built-in Laravel libraries
- **Usage**: https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=http://example.com

## How to Enable New APIs

### Step 1: Add Configuration
Add the API key to your `.env` file:
```env
CALENDARIFIC_API_KEY=your_key
TIMEZONEDB_API_KEY=your_key
OPENWEATHER_API_KEY=your_key
```

### Step 2: Update Config
The config is already in `config/external-apis.php`. No changes needed.

### Step 3: Use in Code
```php
use App\Services\ExternalApiService;

// Get holidays
$holidays = ExternalApiService::getHolidays(date: '2026-05-01', country: 'PH');

// Get weather
$weather = ExternalApiService::getWeather();

// Check school days
$schoolDays = ExternalApiService::getSchoolDays(
    Carbon::now(),
    Carbon::now()->addMonth()
);
```

## Best Practices

1. **Error Handling**: Always wrap API calls in try-catch blocks
2. **Rate Limiting**: Cache results when possible to avoid exceeding rate limits
3. **Fallback Data**: Provide default values when APIs are unavailable
4. **Testing**: Test API integrations in development before deploying
5. **Monitoring**: Log API failures for troubleshooting

## Environment Variables

Add these to your `.env` file:

```env
# Weather
OPENWEATHER_API_KEY=

# Holidays
CALENDARIFIC_API_KEY=

# Timezone
TIMEZONEDB_API_KEY=

# Email Notifications
SENDGRID_API_KEY=

# SMS Notifications
TWILIO_ACCOUNT_SID=
TWILIO_AUTH_TOKEN=
TWILIO_PHONE_NUMBER=+1234567890

# Google Calendar
GOOGLE_CALENDAR_CLIENT_ID=
GOOGLE_CALENDAR_CLIENT_SECRET=
GOOGLE_CALENDAR_REDIRECT_URI=

# Geolocation
GEOIP2_ACCOUNT_ID=
GEOIP2_LICENSE_KEY=
```

## Current Features Using APIs

### Right Sidebar
- **Calendar Widget**: Shows current month calendar (client-side, no API)
- **Schedule Widget**: Displays today's classes using `/api/schedules/today`
- **Upcoming Classes**: Shows upcoming classes using `/api/classes/upcoming`

### Dashboard Features
- Can be enhanced with:
  - Weather widget for outdoor activities
  - Holiday calendar highlighting school-free days
  - Timezone-aware time displays
  - Holiday reminders

## Support and Resources

- **Open-Meteo**: https://open-meteo.com/
- **Calendarific**: https://calendarific.com/
- **TimezoneDB**: https://timezonedb.com/
- **Google APIs**: https://developers.google.com/
- **SendGrid**: https://sendgrid.com/
- **Twilio**: https://www.twilio.com/

## Troubleshooting

### API Calls Failing
1. Check if API key is correctly set in `.env`
2. Verify rate limits haven't been exceeded
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test API endpoint manually with cURL

### Rate Limit Exceeded
- Implement caching: `Cache::remember('holidays', 3600, fn() => ...)`
- Reduce request frequency
- Upgrade to paid tier if needed

### Missing Configuration
- Ensure `config/external-apis.php` is present
- Run `php artisan config:cache` after adding new config
- Check `.env` file has all required keys
