<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\ClassSchedule;
use App\Services\ExternalApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ScheduleApiController extends Controller
{
    /**
     * Get today's schedule for the authenticated user
     */
    public function todaySchedule(Request $request): JsonResponse
    {
        $user = auth()->user();
        $today = Carbon::today();
        
        $schedules = [];

        if ($user->role === 'teacher') {
            $classes = ClassSchedule::where('day_of_week', $today->dayOfWeekIso)
                ->whereHas('assignment', fn ($query) => $query->where('teacher_id', $user->id))
                ->with(['assignment.subject', 'assignment.strand'])
                ->orderBy('start_time')
                ->get();

            foreach ($classes as $class) {
                $schedules[] = [
                    'time' => Carbon::parse($class->start_time)->format('h:i A') . ' - ' . Carbon::parse($class->end_time)->format('h:i A'),
                    'title' => ($class->assignment->subject?->subject_name ?? 'Class') . ' / Grade ' . $class->assignment->year_level . ' ' . $class->assignment->strand->strand_name . '-' . $class->assignment->section . ' / Room ' . ($class->room ?: 'TBA'),
                ];
            }
        } elseif ($user->role === 'admin') {
            // Show sample schedule for admin
            $schedules = [
                ['time' => '09:00 AM', 'title' => 'Morning Assembly'],
                ['time' => '10:00 AM', 'title' => 'Administrative Review'],
                ['time' => '02:00 PM', 'title' => 'Afternoon Check-in'],
            ];
        }

        return response()->json([
            'schedules' => $schedules,
            'date' => $today->toDateString(),
        ]);
    }

    /**
     * Get upcoming classes for the authenticated user
     */
    public function upcomingClasses(Request $request): JsonResponse
    {
        $user = auth()->user();
        $classes = [];

        if ($user->role === 'teacher') {
            $assignments = ClassSchedule::query()
                ->whereHas('assignment', fn ($query) => $query->where('teacher_id', $user->id))
                ->with(['assignment.subject'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            foreach ($assignments as $assignment) {
                $date = Carbon::now()->next($assignment->day_of_week);
                $classes[] = [
                    'subject' => $assignment->assignment->subject?->subject_name ?? 'Class',
                    'date' => $date->format('D, M d'),
                    'time' => Carbon::parse($assignment->start_time)->format('h:i A'),
                ];
            }
        } elseif ($user->role === 'admin') {
            // Show upcoming system activities for admin
            $classes = [
                ['subject' => 'Attendance Report', 'date' => 'Today', 'time' => '03:00 PM'],
                ['subject' => 'Student Data Review', 'date' => 'Tomorrow', 'time' => '10:00 AM'],
                ['subject' => 'Teacher Meeting', 'date' => 'Friday', 'time' => '02:00 PM'],
            ];
        }

        return response()->json([
            'classes' => $classes,
        ]);
    }

    public function schoolCalendar(Request $request): JsonResponse
    {
        abort_unless(auth()->user()?->role === 'admin', 403);

        $today = Carbon::today();
        $holidays = collect(ExternalApiService::getSchoolHolidays())
            ->map(fn (array $holiday) => [
                'date' => $holiday['date'],
                'name' => $holiday['name'],
                'type' => $holiday['type'] ?? 'holiday',
            ])
            ->sortBy('date')
            ->values();
        $todayHoliday = $holidays->firstWhere('date', $today->toDateString());

        return response()->json([
            'date' => $today->toDateString(),
            'month' => $today->format('F Y'),
            'is_weekend' => $today->isWeekend(),
            'is_no_class_day' => $today->isWeekend() || $todayHoliday !== null,
            'today_label' => $todayHoliday['name'] ?? ($today->isWeekend() ? 'Weekend' : 'Regular school day'),
            'holidays' => $holidays,
            'upcoming' => $holidays
                ->filter(fn (array $holiday) => $holiday['date'] >= $today->toDateString())
                ->take(5)
                ->values(),
        ]);
    }
}
