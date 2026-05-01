<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\Strand;
use App\Models\SubjectModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
        ]);
        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();
        $subjectId = isset($validated['subject_id']) ? (int) $validated['subject_id'] : null;
        $fromDate = Carbon::parse($dateFrom)->startOfDay();
        $toDate = Carbon::parse($dateTo)->startOfDay();
        $periodDays = max(1, $fromDate->diffInDays($toDate) + 1);
        $previousTo = $fromDate->copy()->subDay();
        $previousFrom = $previousTo->copy()->subDays($periodDays - 1);

        $base = Attendance::query()
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->when($subjectId, fn ($query) => $query->where('subject_id', $subjectId));
        $previousBase = Attendance::query()
            ->whereBetween('attendance_date', [$previousFrom->toDateString(), $previousTo->toDateString()])
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
        $onTimeRate = $total > 0 ? round(($present / $total) * 100, 1) : 0;
        $concernRate = $total > 0 ? round((($late + $absent) / $total) * 100, 1) : 0;
        $averageDailyRecords = round($total / $periodDays, 1);

        $previousStatusCounts = (clone $previousBase)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
        $previousTotal = (clone $previousBase)->count();
        $previousPresent = (int) ($previousStatusCounts['Present'] ?? 0);
        $previousLate = (int) ($previousStatusCounts['Late'] ?? 0);
        $previousAttendanceRate = $previousTotal > 0 ? round((($previousPresent + $previousLate) / $previousTotal) * 100, 1) : 0;
        $attendanceRateChange = round($attendanceRate - $previousAttendanceRate, 1);

        $dailyTrend = (clone $base)
            ->select('attendance_date', DB::raw('count(*) as total'))
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();
        $dailyHealth = (clone $base)
            ->select(
                'attendance_date',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(status = 'Present') as present_count"),
                DB::raw("SUM(status = 'Late') as late_count"),
                DB::raw("SUM(status = 'Absent') as absent_count"),
                DB::raw("ROUND(((SUM(status = 'Present') + SUM(status = 'Late')) / COUNT(*)) * 100, 1) as attendance_rate")
            )
            ->groupBy('attendance_date')
            ->orderBy('attendance_date')
            ->get();
        $bestAttendanceDay = $dailyHealth->sortByDesc('attendance_rate')->first();
        $lowestAttendanceDay = $dailyHealth->sortBy('attendance_rate')->first();

        $subjectBreakdown = (clone $base)
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->select('subjects.subject_name', DB::raw('count(*) as total'))
            ->groupBy('subjects.subject_name')
            ->orderByDesc('total')
            ->limit(8)
            ->get();
        $subjectHealth = (clone $base)
            ->join('subjects', 'subjects.id', '=', 'attendances.subject_id')
            ->select(
                'subjects.subject_name',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(attendances.status = 'Present') as present_count"),
                DB::raw("SUM(attendances.status = 'Late') as late_count"),
                DB::raw("SUM(attendances.status = 'Absent') as absent_count"),
                DB::raw("ROUND(((SUM(attendances.status = 'Present') + SUM(attendances.status = 'Late')) / COUNT(*)) * 100, 1) as attendance_rate")
            )
            ->groupBy('subjects.subject_name')
            ->orderBy('attendance_rate')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $classBreakdown = (clone $base)
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->select('students.year_level', 'students.section', DB::raw('count(*) as total'))
            ->groupBy('students.year_level', 'students.section')
            ->orderBy('students.year_level')
            ->orderBy('students.section')
            ->limit(10)
            ->get();
        $classHealth = (clone $base)
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->join('strands', 'strands.id', '=', 'students.strand_id')
            ->select(
                'strands.strand_name',
                'students.year_level',
                'students.section',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(attendances.status = 'Present') as present_count"),
                DB::raw("SUM(attendances.status = 'Late') as late_count"),
                DB::raw("SUM(attendances.status = 'Absent') as absent_count"),
                DB::raw("ROUND(((SUM(attendances.status = 'Present') + SUM(attendances.status = 'Late')) / COUNT(*)) * 100, 1) as attendance_rate")
            )
            ->groupBy('strands.strand_name', 'students.year_level', 'students.section')
            ->orderBy('attendance_rate')
            ->orderByDesc('total')
            ->limit(12)
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
        $perfectStudents = (clone $base)
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->select('students.student_id')
            ->groupBy('students.student_id')
            ->havingRaw("SUM(attendances.status = 'Absent') = 0 AND SUM(attendances.status = 'Late') = 0")
            ->get()
            ->count();
        $criticalStudents = (clone $base)
            ->join('students', 'students.student_id', '=', 'attendances.student_id')
            ->select('students.student_id')
            ->groupBy('students.student_id')
            ->havingRaw("SUM(attendances.status = 'Absent') >= 2 OR SUM(attendances.status = 'Late') >= 3")
            ->get()
            ->count();

        return view('reports.index', [
            'subjects' => SubjectModel::orderBy('subject_name')->get(),
            'students' => Student::with('strand')->orderBy('last_name')->orderBy('first_name')->get(),
            'strands' => Strand::orderBy('strand_name')->get(),
            'sections' => ['A', 'B', 'C', 'D', 'E', 'F'],
            'filters' => compact('dateFrom', 'dateTo', 'subjectId'),
            'summary' => compact(
                'total',
                'present',
                'late',
                'absent',
                'attendanceRate',
                'onTimeRate',
                'concernRate',
                'averageDailyRecords',
                'previousAttendanceRate',
                'attendanceRateChange',
                'perfectStudents',
                'criticalStudents',
                'periodDays'
            ),
            'dailyTrend' => $dailyTrend,
            'dailyHealth' => $dailyHealth,
            'bestAttendanceDay' => $bestAttendanceDay,
            'lowestAttendanceDay' => $lowestAttendanceDay,
            'subjectBreakdown' => $subjectBreakdown,
            'subjectHealth' => $subjectHealth,
            'classBreakdown' => $classBreakdown,
            'classHealth' => $classHealth,
            'teacherBreakdown' => $teacherBreakdown,
            'weekdayBreakdown' => $weekdayBreakdown,
            'atRiskStudents' => $atRiskStudents,
        ]);
    }

    public function studentPrint(Student $student, Request $request): View
    {
        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->toDateString());
        $records = Attendance::with(['subject', 'teacher', 'schedule'])
            ->where('student_id', $student->student_id)
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->orderBy('attendance_date')
            ->get();

        return view('reports.print.student', [
            'student' => $student->load('strand'),
            'records' => $records,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'summary' => $this->summaryFromRecords($records),
        ]);
    }

    public function sectionPrint(Request $request): View
    {
        $data = $request->validate([
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);
        $dateFrom = $data['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $data['date_to'] ?? now()->toDateString();
        $students = Student::with('strand')
            ->where('strand_id', $data['strand_id'])
            ->where('year_level', $data['year_level'])
            ->where('section', $data['section'])
            ->orderBy('last_name')
            ->get();
        $records = Attendance::with(['student', 'subject'])
            ->whereIn('student_id', $students->pluck('student_id'))
            ->whereBetween('attendance_date', [$dateFrom, $dateTo])
            ->get();

        return view('reports.print.section', [
            'students' => $students,
            'records' => $records,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'classLabel' => 'Grade ' . $data['year_level'] . ' ' . ($students->first()?->strand->strand_name ?? 'Class') . '-' . $data['section'],
        ]);
    }

    private function summaryFromRecords($records): array
    {
        $total = $records->count();
        $present = $records->where('status', 'Present')->count();
        $late = $records->where('status', 'Late')->count();
        $absent = $records->where('status', 'Absent')->count();

        return [
            'total' => $total,
            'present' => $present,
            'late' => $late,
            'absent' => $absent,
            'attendanceRate' => $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0,
        ];
    }
}
