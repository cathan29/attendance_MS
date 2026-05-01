<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ClassAssignment;
use App\Models\ClassSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('schedules.index', [
            'assignments' => ClassAssignment::with(['teacher', 'subject', 'strand'])
                ->orderBy('year_level')
                ->orderBy('section')
                ->get(),
            'schedules' => ClassSchedule::with(['assignment.teacher', 'assignment.subject', 'assignment.strand'])
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('room', 'like', "%{$search}%")
                        ->orWhereHas('assignment.teacher', fn ($teacher) => $teacher
                            ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('assignment.subject', fn ($subject) => $subject->where('subject_name', 'like', "%{$search}%"))
                        ->orWhereHas('assignment.strand', fn ($strand) => $strand->where('strand_name', 'like', "%{$search}%"));
                }))
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get(),
            'days' => $this->days(),
            'search' => $search,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_assignment_id' => ['required', 'exists:class_assignments,id'],
            'day_of_week' => ['required', 'integer', 'between:1,7'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
        ]);

        $schedule = ClassSchedule::updateOrCreate([
            'class_assignment_id' => $data['class_assignment_id'],
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
        ], $data);

        AuditLog::record('schedule_saved', "Saved schedule #{$schedule->id}", $schedule, null, $schedule->toArray());

        return back()->with('success', 'Class schedule saved.');
    }

    public function destroy(ClassSchedule $schedule): RedirectResponse
    {
        $oldValues = $schedule->toArray();
        AuditLog::record('schedule_deleted', "Deleted schedule #{$schedule->id}", $schedule, $oldValues, null);
        $schedule->delete();

        return back()->with('success', 'Class schedule deleted.');
    }

    private function days(): array
    {
        return [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];
    }
}
