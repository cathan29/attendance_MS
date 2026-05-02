<?php

namespace App\Http\Controllers;

use App\Models\ClassAssignment;
use App\Models\CurriculumSubject;
use App\Models\AuditLog;
use App\Models\Strand;
use App\Models\Student;
use App\Models\SubjectModel;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function index(): View
    {
        $search = trim((string) request()->query('q', ''));
        $this->ensureCurriculumSubjectsExist(config('curriculum'));

        $strands = Strand::orderBy('strand_name')->get();
        $subjects = SubjectModel::orderBy('subject_name')->get();
        $curriculumStrandId = request()->integer('curriculum_strand_id') ?: null;
        $curriculumGrade = request()->query('curriculum_grade');
        $curriculumItems = CurriculumSubject::with(['strand', 'subject'])
            ->orderBy('year_level')
            ->get()
            ->sortBy(fn (CurriculumSubject $item) => $item->strand->strand_name . '|' . $item->year_level . '|' . $item->subject->subject_name);
        $curriculumManageItems = CurriculumSubject::with(['strand', 'subject'])
            ->when($curriculumStrandId, fn ($query) => $query->where('strand_id', $curriculumStrandId))
            ->when(in_array($curriculumGrade, ['11', '12'], true), fn ($query) => $query->where('year_level', $curriculumGrade))
            ->whereHas('strand')
            ->whereHas('subject')
            ->orderBy('strand_id')
            ->orderBy('year_level')
            ->orderBy('subject_id')
            ->paginate(10, ['*'], 'curriculum_page')
            ->withQueryString();
        $curriculum = $this->curriculumMap($curriculumItems);
        $curriculumSubjects = $this->curriculumSubjectMap($curriculumItems);

        $studentsBySection = Student::with('strand')
            ->orderBy('year_level')
            ->orderBy('section')
            ->orderBy('last_name')
            ->get()
            ->groupBy(fn (Student $student) => $student->strand->strand_name . '|' . $student->year_level . '|' . $student->section);

        $assignmentsQuery = ClassAssignment::with(['teacher', 'subject', 'strand'])
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
            ->latest();

        return view('curriculum.index', [
            'assignments' => (clone $assignmentsQuery)
                ->paginate(10, ['*'], 'loads_page')
                ->withQueryString(),
            'assignmentsForSections' => (clone $assignmentsQuery)->get(),
            'teachers' => User::where('role', 'teacher')->where('status', 'active')->orderBy('last_name')->get(),
            'allTeachers' => User::withTrashed()->where('role', 'teacher')->orderBy('last_name')->get(),
            'subjects' => $subjects,
            'strands' => $strands,
            'sections' => $this->sections(),
            'semesters' => config('school.semesters', []),
            'search' => $search,
            'curriculum' => $curriculum,
            'curriculumSubjects' => $curriculumSubjects,
            'curriculumItems' => $curriculumItems,
            'curriculumManageItems' => $curriculumManageItems,
            'curriculumFilters' => [
                'strand_id' => $curriculumStrandId,
                'grade' => $curriculumGrade,
            ],
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
            'section' => ['required', Rule::in($this->sections())],
            'school_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(config('school.semesters', [])))],
            'semester_start_date' => ['required', 'date'],
            'semester_end_date' => ['required', 'date', 'after_or_equal:semester_start_date'],
        ]);
        $data['section'] = strtoupper(trim($data['section']));
        $data['school_year'] = trim($data['school_year']);
        $data['semester'] = trim($data['semester']);

        $strand = Strand::findOrFail($data['strand_id']);
        $subject = SubjectModel::findOrFail($data['subject_id']);
        if (!$this->subjectIsInCurriculum((int) $data['strand_id'], (int) $data['subject_id'], $data['year_level'])) {
            return back()
                ->withErrors(['subject_id' => "{$subject->subject_name} is not part of Grade {$data['year_level']} {$strand->strand_name} curriculum."])
                ->withInput();
        }

        $uniqueData = Arr::only($data, ['teacher_id', 'subject_id', 'strand_id', 'year_level', 'section', 'school_year', 'semester']);
        $duplicate = ClassAssignment::where($uniqueData)->exists();
        if ($duplicate) {
            return back()
                ->withErrors(['subject_id' => 'This exact class assignment already exists.'])
                ->withInput();
        }

        $assignment = ClassAssignment::create($data);
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
            'section' => ['required', Rule::in($this->sections())],
            'school_year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(array_keys(config('school.semesters', [])))],
            'semester_start_date' => ['required', 'date'],
            'semester_end_date' => ['required', 'date', 'after_or_equal:semester_start_date'],
        ]);
        $data['section'] = strtoupper(trim($data['section']));
        $data['school_year'] = trim($data['school_year']);
        $data['semester'] = trim($data['semester']);

        $strand = Strand::findOrFail($data['strand_id']);
        $subject = SubjectModel::findOrFail($data['subject_id']);
        if (!$this->subjectIsInCurriculum((int) $data['strand_id'], (int) $data['subject_id'], $data['year_level'])) {
            return back()
                ->withErrors(['subject_id' => "{$subject->subject_name} is not part of Grade {$data['year_level']} {$strand->strand_name} curriculum."])
                ->withInput();
        }

        $uniqueData = Arr::only($data, ['teacher_id', 'subject_id', 'strand_id', 'year_level', 'section', 'school_year', 'semester']);
        $duplicate = ClassAssignment::where('id', '!=', $curriculum->id)
            ->where($uniqueData)
            ->exists();
        if ($duplicate) {
            return back()
                ->withErrors(['subject_id' => 'Another identical class assignment already exists.'])
                ->withInput();
        }

        $oldValues = $curriculum->only(['teacher_id', 'subject_id', 'strand_id', 'year_level', 'section', 'school_year', 'semester', 'semester_start_date', 'semester_end_date']);
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

    public function storeSubject(Request $request): RedirectResponse
    {
        $request->merge([
            'subject_name' => trim((string) $request->input('subject_name')),
        ]);

        $data = $request->validate([
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'subject_name' => ['required', 'string', 'max:100'],
        ]);

        $subject = SubjectModel::firstOrCreate([
            'subject_name' => trim($data['subject_name']),
        ]);

        $curriculumSubject = CurriculumSubject::withTrashed()->firstOrNew([
            'strand_id' => $data['strand_id'],
            'subject_id' => $subject->id,
            'year_level' => $data['year_level'],
        ]);

        if ($curriculumSubject->exists && !$curriculumSubject->trashed()) {
            return back()
                ->withErrors(['subject_name' => 'This subject is already in this curriculum.'])
                ->withInput();
        }

        if ($curriculumSubject->exists && $curriculumSubject->trashed()) {
            $curriculumSubject->restore();
        }

        $curriculumSubject->fill([
            'strand_id' => $data['strand_id'],
            'subject_id' => $subject->id,
            'year_level' => $data['year_level'],
        ])->save();

        AuditLog::record('curriculum_subject_added', "Added {$subject->subject_name} to curriculum", $curriculumSubject, null, $curriculumSubject->toArray());

        return back()->with('success', 'Curriculum subject saved.');
    }

    public function updateSubject(Request $request, CurriculumSubject $curriculumSubject): RedirectResponse
    {
        $request->merge([
            'subject_name' => trim((string) $request->input('subject_name')),
        ]);

        $data = $request->validate([
            'strand_id' => ['required', 'exists:strands,id'],
            'year_level' => ['required', 'in:11,12'],
            'subject_name' => ['required', 'string', 'max:100'],
        ]);

        $subject = SubjectModel::firstOrCreate([
            'subject_name' => trim($data['subject_name']),
        ]);

        $duplicate = CurriculumSubject::withTrashed()
            ->where('id', '!=', $curriculumSubject->id)
            ->where('strand_id', $data['strand_id'])
            ->where('subject_id', $subject->id)
            ->where('year_level', $data['year_level'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['subject_name' => 'Another curriculum entry already uses this subject, strand, and grade.'])
                ->withInput();
        }

        $oldValues = $curriculumSubject->only(['strand_id', 'subject_id', 'year_level']);
        $curriculumSubject->update([
            'strand_id' => $data['strand_id'],
            'subject_id' => $subject->id,
            'year_level' => $data['year_level'],
        ]);

        AuditLog::record('curriculum_subject_updated', "Updated curriculum subject #{$curriculumSubject->id}", $curriculumSubject, $oldValues, $curriculumSubject->only(array_keys($oldValues)));

        return back()->with('success', 'Curriculum subject updated.');
    }

    public function destroySubject(CurriculumSubject $curriculumSubject): RedirectResponse
    {
        $oldValues = $curriculumSubject->load(['strand', 'subject'])->toArray();
        $curriculumSubject->delete();

        AuditLog::record('curriculum_subject_removed', "Removed {$curriculumSubject->subject->subject_name} from active curriculum", $curriculumSubject, $oldValues, null);

        return back()->with('success', 'Curriculum subject removed from active curriculum. Existing attendance and class records remain intact.');
    }

    private function ensureCurriculumSubjectsExist(array $curriculum): void
    {
        if (CurriculumSubject::withTrashed()->exists()) {
            return;
        }

        collect($curriculum)->each(function (array $grades, string $strandName) {
            $strand = Strand::firstOrCreate(['strand_name' => $strandName]);

            collect($grades)->each(function (array $subjectNames, string $yearLevel) use ($strand) {
                collect($subjectNames)->each(function (string $subjectName) use ($strand, $yearLevel) {
                    $subject = SubjectModel::firstOrCreate(['subject_name' => $subjectName]);

                    CurriculumSubject::withTrashed()->firstOrCreate([
                        'strand_id' => $strand->id,
                        'subject_id' => $subject->id,
                        'year_level' => $yearLevel,
                    ])->restore();
                });
            });
        });
    }

    private function sections(): array
    {
        return config('school.sections', ['A', 'B', 'C', 'D', 'E', 'F']);
    }

    private function subjectIsInCurriculum(int $strandId, int $subjectId, string $yearLevel): bool
    {
        return CurriculumSubject::where('strand_id', $strandId)
            ->where('subject_id', $subjectId)
            ->where('year_level', $yearLevel)
            ->exists();
    }

    private function curriculumMap($curriculumItems): array
    {
        return $curriculumItems
            ->groupBy(fn (CurriculumSubject $item) => $item->strand->strand_name)
            ->map(fn ($strandItems) => $strandItems
                ->groupBy('year_level')
                ->map(fn ($gradeItems) => $gradeItems
                    ->pluck('subject.subject_name')
                    ->values()
                    ->all())
                ->all())
            ->all();
    }

    private function curriculumSubjectMap($curriculumItems): array
    {
        return $curriculumItems
            ->groupBy(fn (CurriculumSubject $item) => $item->strand->strand_name)
            ->map(fn ($strandItems) => $strandItems
                ->groupBy('year_level')
                ->map(fn ($gradeItems) => $gradeItems
                    ->map(fn (CurriculumSubject $item) => [
                        'id' => $item->subject_id,
                        'name' => $item->subject->subject_name,
                    ])
                    ->values()
                    ->all())
                ->all())
            ->all();
    }
}
