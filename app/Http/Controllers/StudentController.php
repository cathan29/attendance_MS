<?php

namespace App\Http\Controllers;

use App\Models\Strand;
use App\Models\Student;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    private const SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $students = Student::with('strand')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('student_id', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('year_level', 'like', "%{$search}%")
                    ->orWhere('section', 'like', "%{$search}%")
                    ->orWhereHas('strand', fn ($strand) => $strand->where('strand_name', 'like', "%{$search}%"));
            }))
            ->orderBy('year_level')
            ->orderBy('section')
            ->orderBy('last_name')
            ->get();

        return view('students.index', [
            'students' => $students,
            'studentsBySection' => $students->groupBy(fn (Student $student) => $student->strand->strand_name . '|' . $student->year_level . '|' . $student->section),
            'strands' => Strand::orderBy('strand_name')->get(),
            'sections' => self::SECTIONS,
            'search' => $search,
            'nextStudentId' => $this->nextStudentId(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
        ]);

        $data['student_id'] = $this->nextStudentId();

        $student = Student::create($data);
        AuditLog::record('student_created', "Created student {$student->student_id}: {$student->last_name}, {$student->first_name}", $student, null, $student->toArray());

        return back()->with('success', 'Student saved successfully.');
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'middle_name' => ['nullable', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
        ]);

        $oldValues = $student->only(['first_name', 'middle_name', 'last_name', 'strand_id', 'year_level', 'section']);
        $student->update($data);
        AuditLog::record('student_updated', "Updated student {$student->student_id}: {$student->last_name}, {$student->first_name}", $student, $oldValues, $student->only(array_keys($oldValues)));

        return back()->with('success', 'Student updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $oldValues = $student->toArray();
        AuditLog::record('student_deleted', "Deleted student {$student->student_id}: {$student->last_name}, {$student->first_name}", $student, $oldValues, null);
        $student->delete();

        return back()->with('success', 'Student deleted.');
    }

    private function nextStudentId(): string
    {
        $year = (string) now()->year;
        $lastSequence = Student::query()
            ->where('student_id', 'like', "{$year}%")
            ->pluck('student_id')
            ->filter(fn (string $studentId) => ctype_digit($studentId) && str_starts_with($studentId, $year))
            ->map(fn (string $studentId) => (int) substr($studentId, 4))
            ->max() ?? 0;

        do {
            $lastSequence++;
            $studentId = $year . str_pad((string) $lastSequence, 3, '0', STR_PAD_LEFT);
        } while (Student::whereKey($studentId)->exists());

        return $studentId;
    }
}
