<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\ClassAssignment;
use App\Models\Student;
use App\Models\SubjectModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        $today = today();
        $assignments = ClassAssignment::with(['teacher', 'subject', 'strand'])->get();
        $isSchoolDay = !$today->isWeekend();
        $missingSubmissions = $isSchoolDay
            ? $assignments->filter(function (ClassAssignment $assignment) use ($today) {
                return !Attendance::query()
                    ->where('teacher_id', $assignment->teacher_id)
                    ->where('subject_id', $assignment->subject_id)
                    ->whereDate('attendance_date', $today)
                    ->whereHas('student', fn ($student) => $student
                        ->where('strand_id', $assignment->strand_id)
                        ->where('year_level', $assignment->year_level)
                        ->where('section', $assignment->section))
                    ->exists();
            })->values()
            : collect();
        $submittedToday = $assignments->count() - $missingSubmissions->count();

        $studentRisk = Attendance::query()
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->join('strands', 'strands.id', '=', 'students.strand_id')
            ->whereDate('attendance_date', '>=', now()->subDays(30)->toDateString())
            ->select(
                'students.student_id',
                'students.first_name',
                'students.last_name',
                'students.year_level',
                'students.section',
                'strands.strand_name',
                DB::raw("SUM(CASE WHEN attendances.status = 'Absent' THEN 1 ELSE 0 END) as absent_count"),
                DB::raw("SUM(CASE WHEN attendances.status = 'Late' THEN 1 ELSE 0 END) as late_count"),
                DB::raw("COUNT(*) as total_records"),
                DB::raw("(SUM(CASE WHEN attendances.status = 'Absent' THEN 1 ELSE 0 END) * 3) + SUM(CASE WHEN attendances.status = 'Late' THEN 1 ELSE 0 END) as risk_score")
            )
            ->groupBy('students.student_id', 'students.first_name', 'students.last_name', 'students.year_level', 'students.section', 'strands.strand_name')
            ->orderByDesc('risk_score')
            ->limit(8)
            ->get()
            ->map(function ($student) {
                $student->risk_level = match (true) {
                    $student->risk_score >= 12 => 'Critical',
                    $student->risk_score >= 6 => 'Watchlist',
                    default => 'Good',
                };

                return $student;
            });

        $absentToday = Attendance::with(['student.strand', 'subject'])
            ->whereDate('attendance_date', $today)
            ->where('status', 'Absent')
            ->latest('updated_at')
            ->limit(8)
            ->get();
        $lateToday = Attendance::with(['student.strand', 'subject'])
            ->whereDate('attendance_date', $today)
            ->where('status', 'Late')
            ->latest('updated_at')
            ->limit(8)
            ->get();

        return view('dashboard.admin', [
            'stats' => [
                'students' => Student::count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'subjects' => SubjectModel::count(),
                'today' => Attendance::whereDate('attendance_date', $today)->count(),
                'missing' => $missingSubmissions->count(),
                'watchlist' => $studentRisk->whereIn('risk_level', ['Watchlist', 'Critical'])->count(),
            ],
            'submissionStats' => [
                'total' => $assignments->count(),
                'submitted' => $submittedToday,
                'missing' => $missingSubmissions->count(),
                'rate' => $assignments->isNotEmpty() ? round(($submittedToday / $assignments->count()) * 100, 1) : 0,
                'isSchoolDay' => $isSchoolDay,
            ],
            'missingSubmissions' => $missingSubmissions->take(10),
            'studentRisk' => $studentRisk,
            'absentToday' => $absentToday,
            'lateToday' => $lateToday,
            'auditLogs' => AuditLog::with('user')->latest()->limit(6)->get(),
            'recent' => Attendance::with(['student', 'subject', 'teacher'])
                ->latest('updated_at')
                ->limit(8)
                ->get(),
        ]);
    }

    public function teacher(): View
    {
        $teacher = Auth::user();
        $todayRecords = Attendance::where('teacher_id', $teacher->id)
            ->whereDate('attendance_date', today())
            ->get();

        return view('dashboard.teacher', [
            'today' => [
                'total' => $todayRecords->count(),
                'present' => $todayRecords->where('status', 'Present')->count(),
                'late' => $todayRecords->where('status', 'Late')->count(),
                'absent' => $todayRecords->where('status', 'Absent')->count(),
            ],
            'history' => Attendance::query()
                ->selectRaw('attendance_date, subject_id, count(*) as records')
                ->with('subject')
                ->where('teacher_id', $teacher->id)
                ->groupBy('attendance_date', 'subject_id')
                ->orderByDesc('attendance_date')
                ->limit(10)
                ->get(),
        ]);
    }
}
