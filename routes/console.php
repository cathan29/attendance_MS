<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('attendance:simulate
    {--teacher_id= : User id of a teacher (optional)}
    {--assignment_id= : ClassAssignment id (optional)}
    {--date= : Attendance date YYYY-MM-DD (default: today)}
    {--student_limit=2 : Number of students to include}
', function () {
    $date = $this->option('date') ?: now()->toDateString();
    $studentLimit = (int) ($this->option('student_limit') ?: 2);
    if ($studentLimit < 1) {
        $this->error('student_limit must be >= 1');
        return 1;
    }

    $teacherQuery = \App\Models\User::query()->where('role', 'teacher');
    if ($this->option('teacher_id')) {
        $teacherQuery->where('id', (int) $this->option('teacher_id'));
    }
    $teacher = $teacherQuery->orderBy('id')->first();
    if (!$teacher) {
        $this->error('No teacher found.');
        return 1;
    }

    $assignmentQuery = \App\Models\ClassAssignment::query()->where('teacher_id', $teacher->id);
    if ($this->option('assignment_id')) {
        $assignmentQuery->where('id', (int) $this->option('assignment_id'));
    }
    $assignment = $assignmentQuery->orderBy('id')->first();
    if (!$assignment) {
        $this->error('No class assignment found for that teacher.');
        return 1;
    }

    $students = \App\Models\Student::query()
        ->where('strand_id', $assignment->strand_id)
        ->where('year_level', $assignment->year_level)
        ->where('section', $assignment->section)
        ->orderBy('student_id')
        ->limit($studentLimit)
        ->pluck('student_id')
        ->toArray();

    if (count($students) < $studentLimit) {
        $this->error('Not enough students found for the assignment (check strand/year/section).');
        $this->line('Found: ' . count($students) . ', requested: ' . $studentLimit);
        return 1;
    }

    $status = [];
    $remarks = [];
    foreach ($students as $i => $studentId) {
        $status[$studentId] = $i === 0 ? 'Present' : 'Late';
        if ($i === 1) {
            $remarks[$studentId] = 'Simulated save from artisan command';
        }
    }

    $payload = [
        'assignment_id' => $assignment->id,
        'subject_id' => $assignment->subject_id,
        'attendance_date' => $date,
        'status' => $status,
        'remarks' => $remarks,
    ];

    Auth::login($teacher);

    $request = Request::create('/teacher/attendance/take', 'POST', $payload);
    $request->headers->set('Accept', 'application/json');
    $request->headers->set('X-Requested-With', 'XMLHttpRequest');
    $request->setUserResolver(fn () => Auth::user());

    $controller = app(\App\Http\Controllers\AttendanceController::class);
    $response = $controller->store($request);

    $this->info('Teacher: #' . $teacher->id . ' (' . ($teacher->employee_id ?? $teacher->email ?? 'unknown') . ')');
    $this->info('Assignment: #' . $assignment->id . ' | Subject: ' . $assignment->subject_id . ' | Date: ' . $date);
    $this->info('HTTP: ' . $response->getStatusCode());

    $count = \App\Models\Attendance::query()
        ->where('teacher_id', $teacher->id)
        ->where('subject_id', $assignment->subject_id)
        ->whereDate('attendance_date', $date)
        ->count();

    $this->info('Saved rows (teacher+subject+date): ' . $count);

    $last = \App\Models\Attendance::query()
        ->where('teacher_id', $teacher->id)
        ->where('subject_id', $assignment->subject_id)
        ->whereDate('attendance_date', $date)
        ->orderByDesc('updated_at')
        ->first();

    if ($last) {
        $this->info('Last saved at (PH): ' . $last->updated_at->timezone(config('app.timezone'))->format('Y-m-d h:i A'));
    }

    return 0;
})->purpose('Simulate teacher saving attendance (dev helper)');
