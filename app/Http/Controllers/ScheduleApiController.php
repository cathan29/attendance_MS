<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\Attendance;
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
            // Get classes assigned to this teacher for today
            $classes = ClassAssignment::where('teacher_id', $user->id)
                ->with('subject')
                ->get();

            // Create schedule entries based on class count (simplified)
            foreach ($classes as $index => $class) {
                $schedules[] = [
                    'time' => sprintf('%02d:00 %s', 9 + ($index * 2), $index < 3 ? 'AM' : 'PM'),
                    'title' => $class->subject?->subject_name ?? 'Class ' . ($index + 1),
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
            // Get all classes assigned to this teacher
            $assignments = ClassAssignment::where('teacher_id', $user->id)
                ->with('subject')
                ->get();

            // Create sample upcoming class entries
            $daysAhead = 0;
            foreach ($assignments as $index => $assignment) {
                if ($index > 0 && $index % 3 === 0) $daysAhead++;
                
                $date = Carbon::now()->addDays($daysAhead);
                $classes[] = [
                    'subject' => $assignment->subject?->subject_name ?? 'Class',
                    'date' => $date->format('D, M d'),
                    'time' => sprintf('%02d:%02d %s', 9 + ($index % 6), 0, $index % 6 < 3 ? 'AM' : 'PM'),
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
}
