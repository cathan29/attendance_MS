<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

/**
 * Service for integrating free third-party APIs
 * This service manages integrations with free APIs for calendar, weather, holidays, and more
 */
class ExternalApiService
{
    /**
     * Get holiday information for a specific date
     * Uses Calendarific API (free tier: 1,000 requests/month)
     * 
     * @param string $date Date in YYYY-MM-DD format
     * @param string $country Country code (e.g., 'US', 'PH')
     * @return array
     */
    public static function getHolidays(string $date = null, string $country = 'PH'): array
    {
        $apiKey = config('external-apis.calendarific_key');
        if (!$apiKey) {
            return [];
        }

        try {
            $date = $date ? Carbon::parse($date) : Carbon::now();
            $year = $date->year;

            $response = Http::timeout(5)->get('https://calendarific.com/api/v2/holidays', [
                'api_key' => $apiKey,
                'country' => $country,
                'year' => $year,
            ]);

            if ($response->successful()) {
                return $response->json('holidays', []);
            }
        } catch (\Exception $e) {
            \Log::warning('Calendarific API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get weather information
     * Uses Open-Meteo API (completely free, no key needed)
     * 
     * @param float $latitude
     * @param float $longitude
     * @return array
     */
    public static function getWeather(float $latitude = 14.5994, float $longitude = 120.9842): array
    {
        try {
            $response = Http::timeout(5)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'current' => 'temperature_2m,relative_humidity_2m,weather_code',
                'temperature_unit' => 'celsius',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'temperature' => $data['current']['temperature_2m'] ?? null,
                    'humidity' => $data['current']['relative_humidity_2m'] ?? null,
                    'weather_code' => $data['current']['weather_code'] ?? null,
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('Open-Meteo API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get timezone information
     * Uses Timezonedb API (free tier: 1 request/second)
     * 
     * @param float $latitude
     * @param float $longitude
     * @return string
     */
    public static function getTimezone(float $latitude = 14.5994, float $longitude = 120.9842): string
    {
        $apiKey = config('external-apis.timezonedb_key');
        if (!$apiKey) {
            return 'Asia/Manila'; // Default Philippines timezone
        }

        try {
            $response = Http::timeout(5)->get('http://api.timezonedb.com/v2.1/get-time-zone', [
                'key' => $apiKey,
                'format' => 'json',
                'by' => 'position',
                'lat' => $latitude,
                'lng' => $longitude,
            ]);

            if ($response->successful()) {
                return $response->json('zoneName', 'Asia/Manila');
            }
        } catch (\Exception $e) {
            \Log::warning('TimezoneDB API error: ' . $e->getMessage());
        }

        return 'Asia/Manila';
    }

    /**
     * Get public holidays list for the school year
     * This uses a simple hard-coded list that can be enhanced with API integration
     * 
     * @return array
     */
    public static function getSchoolHolidays(): array
    {
        // Philippine school holidays 2026
        return [
            ['date' => '2026-01-01', 'name' => 'New Year\'s Day', 'type' => 'national'],
            ['date' => '2026-02-10', 'name' => 'EDSA Revolution Anniversary', 'type' => 'national'],
            ['date' => '2026-02-25', 'name' => 'EDSA People Power Revolution', 'type' => 'national'],
            ['date' => '2026-03-28', 'name' => 'Maundy Thursday', 'type' => 'religious'],
            ['date' => '2026-03-29', 'name' => 'Good Friday', 'type' => 'religious'],
            ['date' => '2026-03-30', 'name' => 'Black Saturday', 'type' => 'religious'],
            ['date' => '2026-04-09', 'name' => 'Day of Valor', 'type' => 'national'],
            ['date' => '2026-05-01', 'name' => 'Labor Day', 'type' => 'national'],
            ['date' => '2026-06-12', 'name' => 'Independence Day', 'type' => 'national'],
            ['date' => '2026-08-21', 'name' => 'Ninoy Aquino Day', 'type' => 'national'],
            ['date' => '2026-11-01', 'name' => 'All Saints\' Day', 'type' => 'national'],
            ['date' => '2026-11-02', 'name' => 'All Souls\' Day', 'type' => 'religious'],
            ['date' => '2026-11-30', 'name' => 'Bonifacio Day', 'type' => 'national'],
            ['date' => '2026-12-08', 'name' => 'Feast of the Immaculate Conception', 'type' => 'religious'],
            ['date' => '2026-12-25', 'name' => 'Christmas Day', 'type' => 'religious'],
            ['date' => '2026-12-30', 'name' => 'Rizal Day', 'type' => 'national'],
            ['date' => '2026-12-31', 'name' => 'New Year\'s Eve', 'type' => 'observance'],
        ];
    }

    /**
     * Check if a date is a school holiday
     * 
     * @param string $date Date in YYYY-MM-DD format
     * @return array|null
     */
    public static function isSchoolHoliday(string $date): ?array
    {
        $holidays = static::getSchoolHolidays();
        
        foreach ($holidays as $holiday) {
            if ($holiday['date'] === $date) {
                return $holiday;
            }
        }

        return null;
    }

    /**
     * Get a list of school days (excluding weekends and holidays)
     * 
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @return array
     */
    public static function getSchoolDays(Carbon $startDate, Carbon $endDate): array
    {
        $schoolDays = [];
        $current = $startDate->clone();

        while ($current <= $endDate) {
            // Skip weekends
            if (!in_array($current->dayOfWeek, [6, 0])) { // 6 = Saturday, 0 = Sunday
                // Skip holidays
                if (!static::isSchoolHoliday($current->format('Y-m-d'))) {
                    $schoolDays[] = $current->format('Y-m-d');
                }
            }
            $current->addDay();
        }

        return $schoolDays;
    }
}
