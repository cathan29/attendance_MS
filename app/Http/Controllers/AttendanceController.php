<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ClassAssignment;
use App\Models\Strand;
use App\Models\Student;
use App\Models\SubjectModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttendanceController extends Controller
{
    public function create(Request $request): View
    {
        $assignments = ClassAssignment::with(['subject', 'strand'])
            ->where('teacher_id', Auth::id())
            ->orderBy('year_level')
            ->orderBy('section')
            ->get();
        $assignmentId = (int) $request->query('assignment_id', $assignments->first()->id ?? 0);
        $assignment = $assignments->firstWhere('id', $assignmentId);
        $subjectId = (int) ($assignment->subject_id ?? 0);
        $attendanceDate = $request->query('attendance_date', today()->toDateString());

        $students = Student::query()
            ->with(['strand', 'attendances' => function ($query) use ($subjectId, $attendanceDate) {
                $query->where('subject_id', $subjectId)->whereDate('attendance_date', $attendanceDate);
            }])
            ->when($assignment, fn ($query) => $query
                ->where('strand_id', $assignment->strand_id)
                ->where('year_level', $assignment->year_level)
                ->where('section', $assignment->section))
            ->when(!$assignment, fn ($query) => $query->whereRaw('1 = 0'))
            ->orderBy('year_level')
            ->orderBy('section')
            ->orderBy('last_name')
            ->get();

        return view('attendance.take', compact('assignments', 'assignment', 'assignmentId', 'students', 'subjectId', 'attendanceDate'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'assignment_id' => ['required', 'exists:class_assignments,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'attendance_date' => ['required', 'date'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:Present,Late,Absent'],
            'remarks' => ['nullable', 'array'],
        ]);

        $assignment = ClassAssignment::where('id', $data['assignment_id'])
            ->where('teacher_id', Auth::id())
            ->where('subject_id', $data['subject_id'])
            ->firstOrFail();

        $allowedStudentIds = Student::query()
            ->where('strand_id', $assignment->strand_id)
            ->where('year_level', $assignment->year_level)
            ->where('section', $assignment->section)
            ->pluck('student_id')
            ->all();

        DB::transaction(function () use ($data, $allowedStudentIds) {
            foreach ($data['status'] as $studentId => $status) {
                if (!in_array($studentId, $allowedStudentIds, true)) {
                    continue;
                }

                Attendance::updateOrCreate([
                    'student_id' => $studentId,
                    'attendance_date' => $data['attendance_date'],
                    'subject_id' => $data['subject_id'],
                ], [
                    'teacher_id' => Auth::id(),
                    'status' => $status,
                    'remarks' => $data['remarks'][$studentId] ?? null,
                ]);
            }
        });

        return redirect()
            ->route('teacher.attendance.create', ['assignment_id' => $data['assignment_id'], 'attendance_date' => $data['attendance_date']])
            ->with('success', 'Attendance saved successfully.');
    }

    public function index(Request $request): View
    {
        $subjects = SubjectModel::orderBy('subject_name')->get();
        $records = $this->filteredAttendance($request)
            ->with(['student', 'subject', 'teacher'])
            ->orderByDesc('attendance_date')
            ->orderBy('student_id')
            ->paginate(25)
            ->withQueryString();

        return view('attendance.index', compact('subjects', 'records'));
    }

    public function export(Request $request): StreamedResponse
    {
        $records = $this->filteredAttendance($request)
            ->with(['student', 'subject', 'teacher'])
            ->orderByDesc('attendance_date')
            ->get();

        return response()->streamDownload(function () use ($records) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Date', 'Student ID', 'Student', 'Class', 'Subject', 'Teacher', 'Status', 'Remarks']);
            foreach ($records as $record) {
                fputcsv($out, [
                    $record->attendance_date->toDateString(),
                    $record->student_id,
                    $record->student->last_name . ', ' . $record->student->first_name,
                    $record->student->year_level . '-' . $record->student->section,
                    $record->subject->subject_name,
                    $record->teacher->last_name . ', ' . $record->teacher->first_name,
                    $record->status,
                    $record->remarks,
                ]);
            }
            fclose($out);
        }, 'attendance_' . now()->format('Ymd_His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    private function filteredAttendance(Request $request)
    {
        return Attendance::query()
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('attendance_date', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('attendance_date', '<=', $request->query('date_to')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('subject_id'), fn ($query) => $query->where('subject_id', $request->integer('subject_id')));
    }
}
