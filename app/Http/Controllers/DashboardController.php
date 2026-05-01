<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Student;
use App\Models\SubjectModel;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('dashboard.admin', [
            'stats' => [
                'students' => Student::count(),
                'teachers' => User::where('role', 'teacher')->count(),
                'subjects' => SubjectModel::count(),
                'today' => Attendance::whereDate('attendance_date', today())->count(),
            ],
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
