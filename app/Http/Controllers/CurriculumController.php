<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\AuditLog;
use App\Models\Strand;
use App\Models\Student;
use App\Models\SubjectModel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    private const SECTIONS = ['A', 'B', 'C', 'D', 'E', 'F'];

    public function index(): View
    {
        $search = trim((string) request()->query('q', ''));
        $curriculum = config('curriculum');

        $this->ensureCurriculumSubjectsExist($curriculum);

        $strands = Strand::orderBy('strand_name')->get();
        $subjects = SubjectModel::orderBy('subject_name')->get();
        $subjectIdsByName = $subjects->pluck('id', 'subject_name');
        $curriculumSubjects = collect($curriculum)->map(function (array $grades) use ($subjectIdsByName) {
            return collect($grades)->map(fn (array $subjectNames) => collect($subjectNames)
                ->map(fn (string $subjectName) => [
                    'id' => $subjectIdsByName[$subjectName] ?? null,
                    'name' => $subjectName,
                ])
                ->values()
                ->all())
                ->all();
        })->all();

        $studentsBySection = Student::with('strand')
            ->orderBy('year_level')
            ->orderBy('section')
            ->orderBy('last_name')
            ->get()
            ->groupBy(fn (Student $student) => $student->strand->strand_name . '|' . $student->year_level . '|' . $student->section);

        return view('curriculum.index', [
            'assignments' => ClassAssignment::with(['teacher', 'subject', 'strand'])
                ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                    $query->where('year_level', 'like', "%{$search}%")
                        ->orWhere('section', 'like', "%{$search}%")
                        ->orWhere('school_year', 'like', "%{$search}%")
                        ->orWhere('semester', 'like', "%{$search}%")
                        ->orWhereHas('teacher', fn ($teacher) => $teacher
                            ->where('employee_id', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%"))
                        ->orWhereHas('subject', fn ($subject) => $subject->where('subject_name', 'like', "%{$search}%"))
                        ->orWhereHas('strand', fn ($strand) => $strand->where('strand_name', 'like', "%{$search}%"));
                }))
                ->orderBy('year_level')
                ->orderBy('section')
                ->latest()
                ->get(),
            'teachers' => User::where('role', 'teacher')->where('status', 'active')->orderBy('last_name')->get(),
            'subjects' => $subjects,
            'strands' => $strands,
            'sections' => self::SECTIONS,
            'search' => $search,
            'curriculum' => $curriculum,
            'curriculumSubjects' => $curriculumSubjects,
            'studentsBySection' => $studentsBySection,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
            'school_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:20'],
        ]);

        $strand = Strand::findOrFail($data['strand_id']);
        $subject = SubjectModel::findOrFail($data['subject_id']);
        $allowedSubjects = Arr::get(config('curriculum'), $strand->strand_name . '.' . $data['year_level'], []);

        if (!in_array($subject->subject_name, $allowedSubjects, true)) {
            return back()
                ->withErrors(['subject_id' => "{$subject->subject_name} is not part of Grade {$data['year_level']} {$strand->strand_name} curriculum."])
                ->withInput();
        }

        $assignment = ClassAssignment::firstOrCreate($data);
        AuditLog::record('curriculum_assigned', "Assigned class load #{$assignment->id}", $assignment, null, $assignment->toArray());

        return back()->with('success', 'Class assignment saved.');
    }

    public function update(Request $request, ClassAssignment $curriculum): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:users,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'section' => ['required', 'in:' . implode(',', self::SECTIONS)],
            'school_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'string', 'max:20'],
        ]);

        $strand = Strand::findOrFail($data['strand_id']);
        $subject = SubjectModel::findOrFail($data['subject_id']);
        $allowedSubjects = Arr::get(config('curriculum'), $strand->strand_name . '.' . $data['year_level'], []);

        if (!in_array($subject->subject_name, $allowedSubjects, true)) {
            return back()
                ->withErrors(['subject_id' => "{$subject->subject_name} is not part of Grade {$data['year_level']} {$strand->strand_name} curriculum."])
                ->withInput();
        }

        $oldValues = $curriculum->only(['teacher_id', 'subject_id', 'strand_id', 'year_level', 'section', 'school_year', 'semester']);
        $curriculum->update($data);
        AuditLog::record('curriculum_updated', "Updated class load #{$curriculum->id}", $curriculum, $oldValues, $curriculum->only(array_keys($oldValues)));

        return back()->with('success', 'Class assignment updated.');
    }

    public function destroy(ClassAssignment $curriculum): RedirectResponse
    {
        $oldValues = $curriculum->toArray();
        AuditLog::record('curriculum_removed', "Removed class load #{$curriculum->id}", $curriculum, $oldValues, null);
        $curriculum->delete();

        return back()->with('success', 'Class assignment removed.');
    }

    private function ensureCurriculumSubjectsExist(array $curriculum): void
    {
        collect($curriculum)
            ->flatMap(fn (array $grades) => collect($grades)->flatten())
            ->unique()
            ->each(fn (string $subjectName) => SubjectModel::firstOrCreate(['subject_name' => $subjectName]));
    }
}
