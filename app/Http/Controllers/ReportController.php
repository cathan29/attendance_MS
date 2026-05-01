<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\SubjectModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());
        $subjectId = $request->integer('subject_id') ?: null;

        $base = Attendance::query()
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId));

        $total = (clone $base)->count();
        $statusCounts = (clone $base)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $present = (int) ($statusCounts['Present'] ?? 0);
        $late = (int) ($statusCounts['Late'] ?? 0);
        $absent = (int) ($statusCounts['Absent'] ?? 0);
        $attendanceRate = $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0;

        $dailyTrend = (clone $base)
            ->select('attendance_date', DB::raw('count(*) as total'))
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();

        $subjectBreakdown = (clone $base)
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->select('subjects.subject_name', DB::raw('count(*) as total'))
            ->groupBy('subjects.subject_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $classBreakdown = (clone $base)
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->select('students.year_level', 'students.section', DB::raw('count(*) as total'))
            ->groupBy('students.year_level', 'students.section')
            ->orderBy('students.year_level')
            ->orderBy('students.section')
            ->limit(10)
            ->get();

        $teacherBreakdown = (clone $base)
            ->join('users', 'users.id', '=', 'attendances.teacher_id')
            ->select('users.first_name', 'users.last_name', DB::raw('count(*) as total'))
            ->groupBy('users.first_name', 'users.last_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        $weekdayBreakdown = (clone $base)
            ->selectRaw('DAYNAME(attendance_date) as weekday, WEEKDAY(attendance_date) as weekday_index, count(*) as total')
            ->groupBy('weekday', 'weekday_index')
            ->orderBy('weekday_index')
            ->get();

        $atRiskStudents = (clone $base)
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->select(
                'students.student_id',
                'students.first_name',
                'students.last_name',
                'students.year_level',
                'students.section',
                DB::raw("SUM(attendances.status = 'Absent') as absent_count"),
                DB::raw("SUM(attendances.status = 'Late') as late_count"),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy('students.student_id', 'students.first_name', 'students.last_name', 'students.year_level', 'students.section')
            ->havingRaw("SUM(attendances.status = 'Absent') > 0 OR SUM(attendances.status = 'Late') > 1")
            ->orderByDesc('absent_count')
            ->orderByDesc('late_count')
            ->limit(10)
            ->get();

        return view('reports.index', [
            'subjects' => SubjectModel::orderBy('subject_name')->get(),
            'filters' => compact('dateFrom', 'dateTo', 'subjectId'),
            'summary' => compact('total', 'present', 'late', 'absent', 'attendanceRate'),
            'dailyTrend' => $dailyTrend,
            'subjectBreakdown' => $subjectBreakdown,
            'classBreakdown' => $classBreakdown,
            'teacherBreakdown' => $teacherBreakdown,
            'weekdayBreakdown' => $weekdayBreakdown,
            'atRiskStudents' => $atRiskStudents,
        ]);
    }
}
