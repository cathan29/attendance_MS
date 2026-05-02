@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1>Curriculum</h1>
        <p class="text-muted mb-0">Assign teachers using strand-specific SHS subjects, then check each class section in one place.</p>
    </div>
</div>

<section class="panel mb-4">
    <h2>Class Assignment</h2>
    <form method="POST" action="{{ route('admin.curriculum.store') }}" class="row">
        @csrf
        <div class="col-md-3">
            <label class="form-label">Teacher</label>
            <select class="form-select" name="teacher_id" required>
                @foreach($teachers as $teacher)
                    <option value="{{ $teacher->id }}">{{ $teacher->last_name }}, {{ $teacher->first_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Subject</label>
            <select class="form-select" name="subject_id" data-curriculum-subject required>
                @foreach($curriculumSubjects as $strandName => $grades)
                    @foreach($grades as $grade => $items)
                        @foreach($items as $subject)
                            @if($subject['id'])
                                <option value="{{ $subject['id'] }}" data-strand="{{ $strandName }}" data-grade="{{ $grade }}">{{ $subject['name'] }}</option>
                            @endif
                        @endforeach
                    @endforeach
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Strand</label>
            <select class="form-select" name="strand_id" data-curriculum-strand required>
                @foreach($strands as $strand)
                    <option value="{{ $strand->id }}" data-strand="{{ $strand->strand_name }}">{{ $strand->strand_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Grade</label>
            <select class="form-select" name="year_level" data-curriculum-grade required>
                <option>11</option>
                <option>12</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Section</label>
            <select class="form-select" name="section" required>
                @foreach($sections as $section)
                    <option value="{{ $section }}">{{ $section }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">School Year</label>
            <input class="form-control" name="school_year" value="{{ now()->year }}-{{ now()->addYear()->year }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Semester</label>
            <select class="form-select" name="semester" data-semester-select required>
                @foreach($semesters as $semesterName => $dates)
                    <option value="{{ $semesterName }}" data-start-date="{{ $dates['start_date'] ?? '' }}" data-end-date="{{ $dates['end_date'] ?? '' }}">{{ $semesterName }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Semester Start</label>
            <input class="form-control" type="date" name="semester_start_date" data-semester-start value="{{ $semesters['1st Semester']['start_date'] ?? '' }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Semester End</label>
            <input class="form-control" type="date" name="semester_end_date" data-semester-end value="{{ $semesters['1st Semester']['end_date'] ?? '' }}" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Save Assignment</button>
        </div>
    </form>
</section>

<section class="panel mb-4">
    <div class="section-title">
        <h2>Section View</h2>
        <span class="chip-light">Students grouped by strand, grade, and section</span>
    </div>
    <div class="live-search-control mb-3">
        <input class="form-control" placeholder="Live search section, strand, grade, student count, or load count" data-live-search data-live-search-target="#curriculumSections .section-tile">
    </div>
    <div class="section-card-grid" id="curriculumSections">
        @foreach($strands as $strand)
            @foreach(['11', '12'] as $grade)
                @foreach($sections as $section)
                    @php($key = $strand->strand_name . '|' . $grade . '|' . $section)
                    @php($modalId = 'section-' . md5($key))
                    @php($sectionStudents = $studentsBySection->get($key, collect()))
                    @php($sectionAssignments = $assignments->where('strand_id', $strand->id)->where('year_level', $grade)->where('section', $section))
                    @if($sectionStudents->isNotEmpty() || $sectionAssignments->isNotEmpty())
                        <button type="button" class="section-tile" data-modal-target="{{ $modalId }}">
                            <span>Grade {{ $grade }}</span>
                            <strong>{{ $strand->strand_name }}-{{ $section }}</strong>
                            <span class="section-tile-counts">
                                <small>{{ $sectionStudents->count() }} Students</small>
                                <small>{{ $sectionAssignments->count() }} Loads</small>
                            </span>
                        </button>

                        <div class="section-modal-backdrop" id="{{ $modalId }}" hidden>
                            <section class="section-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                                <div class="section-modal-head">
                                    <div>
                                        <span class="eyebrow">Section</span>
                                        <h2 id="{{ $modalId }}-title">Grade {{ $grade }} {{ $strand->strand_name }}-{{ $section }}</h2>
                                    </div>
                                    <button type="button" class="section-modal-close" data-modal-close aria-label="Close">x</button>
                                </div>

                                <div class="section-modal-body">
                                    <div class="section-summary">
                                        <span><strong>{{ $sectionStudents->count() }}</strong> Students</span>
                                        <span><strong>{{ $sectionAssignments->count() }}</strong> Teacher loads</span>
                                        <span><strong>{{ count($curriculum[$strand->strand_name][$grade] ?? []) }}</strong> Curriculum subjects</span>
                                    </div>

                                    <div class="section-tabs" role="tablist">
                                        <button type="button" class="section-tab is-active" data-section-tab="loads" role="tab">Teacher Loads</button>
                                        <button type="button" class="section-tab" data-section-tab="students" role="tab">Students</button>
                                    </div>

                                    <div class="section-tab-panel is-active" data-section-panel="loads">
                                        <div class="modal-list">
                                            @forelse($sectionAssignments as $assignment)
                                                <form method="POST" action="{{ route('admin.curriculum.update', $assignment) }}" class="modal-edit-row">
                                                    @csrf @method('PUT')
                                                    <input type="hidden" name="strand_id" value="{{ $strand->id }}">
                                                    <input type="hidden" name="year_level" value="{{ $grade }}">
                                                    <input type="hidden" name="section" value="{{ $section }}">
                                                    <div class="edit-row-title">
                                                        <strong>{{ $assignment->subject->subject_name }}</strong>
                                                        <span>{{ $assignment->teacher->last_name }}, {{ $assignment->teacher->first_name }}</span>
                                                    </div>
                                                    <label>
                                                        <span>Teacher</span>
                                                        <select class="form-select" name="teacher_id" required>
                                                            @foreach($allTeachers as $teacher)
                                                                <option value="{{ $teacher->id }}" @selected($assignment->teacher_id === $teacher->id)>
                                                                    {{ $teacher->last_name }}, {{ $teacher->first_name }}{{ $teacher->trashed() ? ' (Archived)' : ($teacher->status === 'inactive' ? ' (Inactive)' : '') }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span>Subject</span>
                                                        <select class="form-select" name="subject_id" required>
                                                            @foreach($curriculumSubjects[$strand->strand_name][$grade] ?? [] as $subject)
                                                                @if($subject['id'])
                                                                    <option value="{{ $subject['id'] }}" @selected($assignment->subject_id === $subject['id'])>{{ $subject['name'] }}</option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span>School Year</span>
                                                        <input class="form-control" name="school_year" value="{{ $assignment->school_year }}" required>
                                                    </label>
                                                    <label>
                                                        <span>Semester</span>
                                                        <select class="form-select" name="semester" data-semester-select required>
                                                            @foreach($semesters as $semesterName => $dates)
                                                                <option value="{{ $semesterName }}" data-start-date="{{ $dates['start_date'] ?? '' }}" data-end-date="{{ $dates['end_date'] ?? '' }}" @selected($assignment->semester === $semesterName)>{{ $semesterName }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span>Start</span>
                                                        <input class="form-control" type="date" name="semester_start_date" data-semester-start value="{{ optional($assignment->semester_start_date)->toDateString() }}" required>
                                                    </label>
                                                    <label>
                                                        <span>End</span>
                                                        <input class="form-control" type="date" name="semester_end_date" data-semester-end value="{{ optional($assignment->semester_end_date)->toDateString() }}" required>
                                                    </label>
                                                    <button class="btn btn-sm btn-primary">Save</button>
                                                </form>
                                            @empty
                                                <p class="empty-state">No teacher assigned yet.</p>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="section-tab-panel" data-section-panel="students">
                                        <div class="modal-list">
                                            @forelse($sectionStudents as $student)
                                                <form method="POST" action="{{ route('admin.students.update', $student) }}" class="modal-edit-row student-edit-row">
                                                    @csrf @method('PUT')
                                                    <div class="edit-row-title">
                                                        <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                                                        <span>{{ $student->student_id }}</span>
                                                    </div>
                                                    <label>
                                                        <span>First</span>
                                                        <input class="form-control" name="first_name" value="{{ $student->first_name }}" required>
                                                    </label>
                                                    <input type="hidden" name="middle_name" value="{{ $student->middle_name }}">
                                                    <label>
                                                        <span>Last</span>
                                                        <input class="form-control" name="last_name" value="{{ $student->last_name }}" required>
                                                    </label>
                                                    <label>
                                                        <span>Strand</span>
                                                        <select class="form-select" name="strand_id" required>
                                                            @foreach($strands as $studentStrand)
                                                                <option value="{{ $studentStrand->id }}" @selected($student->strand_id === $studentStrand->id)>{{ $studentStrand->strand_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span>Grade</span>
                                                        <select class="form-select" name="year_level" required>
                                                            <option @selected($student->year_level === '11')>11</option>
                                                            <option @selected($student->year_level === '12')>12</option>
                                                        </select>
                                                    </label>
                                                    <label>
                                                        <span>Section</span>
                                                        <select class="form-select" name="section" required>
                                                            @foreach($sections as $studentSection)
                                                                <option value="{{ $studentSection }}" @selected($student->section === $studentSection)>{{ $studentSection }}</option>
                                                            @endforeach
                                                        </select>
                                                    </label>
                                                    <button class="btn btn-sm btn-primary">Save</button>
                                                </form>
                                            @empty
                                                <p class="empty-state">No students in this section yet.</p>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    @endif
                @endforeach
            @endforeach
        @endforeach
    </div>
</section>

<section class="panel mb-4">
    <div class="section-title">
        <h2>Curriculum Subjects</h2>
        <span class="chip-light">Add, edit, or remove active curriculum subjects</span>
    </div>
    <form method="POST" action="{{ route('admin.curriculum.subjects.store') }}" class="row g-3 mb-4">
        @csrf
        <div class="col-md-3">
            <label class="form-label">Strand</label>
            <select class="form-select" name="strand_id" required>
                @foreach($strands as $strand)
                    <option value="{{ $strand->id }}">{{ $strand->strand_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Grade</label>
            <select class="form-select" name="year_level" required>
                <option>11</option>
                <option>12</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Subject</label>
            <input class="form-control" name="subject_name" list="subjectNameOptions" required>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-primary w-100">Add Subject</button>
        </div>
    </form>
    <datalist id="subjectNameOptions">
        @foreach($subjects as $subject)
            <option value="{{ $subject->subject_name }}"></option>
        @endforeach
    </datalist>

    <form method="GET" action="{{ route('admin.curriculum.index') }}" class="row g-3 mb-3">
        <div class="col-md-4">
            <label class="form-label">Filter by Strand</label>
            <select class="form-select" name="curriculum_strand_id">
                <option value="">All strands</option>
                @foreach($strands as $strand)
                    <option value="{{ $strand->id }}" @selected($curriculumFilters['strand_id'] === $strand->id)>{{ $strand->strand_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Filter by Grade</label>
            <select class="form-select" name="curriculum_grade">
                <option value="">All grades</option>
                <option value="11" @selected($curriculumFilters['grade'] === '11')>Grade 11</option>
                <option value="12" @selected($curriculumFilters['grade'] === '12')>Grade 12</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-outline-primary w-100">Apply Filter</button>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <a class="btn btn-outline-primary w-100" href="{{ route('admin.curriculum.index') }}">Clear</a>
        </div>
    </form>

    <p class="meta-line mb-3">Showing up to 10 curriculum subjects per page. Use filters to focus on ABM, STEM, TVL, HUMSS, or GAS.</p>

    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Strand</th><th>Grade</th><th>Subject</th><th></th></tr></thead>
            <tbody id="curriculumSubjectRows">
            @forelse($curriculumManageItems as $item)
                <tr>
                    <td>
                        <form id="curriculum-subject-{{ $item->id }}" method="POST" action="{{ route('admin.curriculum.subjects.update', $item) }}">
                            @csrf @method('PUT')
                            <select class="form-select form-select-sm" name="strand_id" required>
                                @foreach($strands as $strand)
                                    <option value="{{ $strand->id }}" @selected($item->strand_id === $strand->id)>{{ $strand->strand_name }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td>
                        <select class="form-select form-select-sm" name="year_level" form="curriculum-subject-{{ $item->id }}" required>
                            <option @selected($item->year_level === '11')>11</option>
                            <option @selected($item->year_level === '12')>12</option>
                        </select>
                    </td>
                    <td>
                        <input class="form-control form-control-sm" name="subject_name" form="curriculum-subject-{{ $item->id }}" list="subjectNameOptions" value="{{ $item->subject->subject_name }}" required>
                    </td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-primary" form="curriculum-subject-{{ $item->id }}">Save</button>
                        <form method="POST" action="{{ route('admin.curriculum.subjects.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Remove this subject from the active curriculum? Existing class loads and attendance records will remain.')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Remove from Curriculum</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center empty-state py-4">No curriculum subjects yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $curriculumManageItems->links() }}
</section>

<section class="panel mb-4">
    <div class="section-title">
        <h2>SHS Curriculum Guide</h2>
        <span class="chip-light">Different subjects per strand</span>
    </div>
    <div class="live-search-control mb-3">
        <input class="form-control" placeholder="Live search strand or subject" data-live-search data-live-search-target="#curriculumGuide .curriculum-card">
    </div>
    <div class="curriculum-grid" id="curriculumGuide">
        @foreach($curriculum as $strandName => $grades)
            <article class="curriculum-card">
                <h3>{{ $strandName }}</h3>
                @foreach($grades as $grade => $subjectNames)
                    <div class="curriculum-grade">
                        <strong>Grade {{ $grade }}</strong>
                        <ul>
                            @foreach($subjectNames as $subjectName)
                                <li>{{ $subjectName }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </article>
        @endforeach
    </div>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Teacher Loads</h2>
        <span class="chip-light">{{ $assignments->count() }} assignments</span>
    </div>
    <form method="GET" action="{{ route('admin.curriculum.index') }}" class="search-bar">
        <input class="form-control" name="q" value="{{ $search }}" placeholder="Live search teacher, subject, strand, section, school year, or semester" data-live-search data-live-search-target="#curriculumLoads tbody tr">
        <button class="btn btn-outline-primary">Search</button>
        @if($search !== '')
            <a class="btn btn-outline-primary" href="{{ route('admin.curriculum.index') }}">Clear</a>
        @endif
    </form>
    <div class="table-responsive">
        <table class="table align-middle" id="curriculumLoads">
            <thead><tr><th>Teacher</th><th>Subject</th><th>Class</th><th>Term</th><th></th></tr></thead>
            <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td><span class="record-name">{{ $assignment->teacher->last_name }}, {{ $assignment->teacher->first_name }}</span><span class="meta-line">{{ $assignment->teacher->employee_id }}</span></td>
                    <td>{{ $assignment->subject->subject_name }}</td>
                    <td>Grade {{ $assignment->year_level }} {{ $assignment->strand->strand_name }}-{{ $assignment->section }}</td>
                    <td>
                        {{ $assignment->school_year }} / {{ $assignment->semester }}
                        @if($assignment->semester_start_date && $assignment->semester_end_date)
                            <span class="meta-line">{{ $assignment->semester_start_date->format('M d, Y') }} - {{ $assignment->semester_end_date->format('M d, Y') }}</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.curriculum.destroy', $assignment) }}" onsubmit="return confirm('Remove this assignment?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Remove</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center empty-state py-4">No class assignments yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const strandSelect = document.querySelector('[data-curriculum-strand]');
        const gradeSelect = document.querySelector('[data-curriculum-grade]');
        const subjectSelect = document.querySelector('[data-curriculum-subject]');
        const subjectOptions = Array.from(subjectSelect.options);
        const syncSemesterDates = (select) => {
            const form = select.closest('form');
            const selected = select.selectedOptions[0];
            const startInput = form?.querySelector('[data-semester-start]');
            const endInput = form?.querySelector('[data-semester-end]');

            if (!selected || !startInput || !endInput) {
                return;
            }

            startInput.value = selected.dataset.startDate || startInput.value;
            endInput.value = selected.dataset.endDate || endInput.value;
        };

        const filterSubjects = () => {
            const strand = strandSelect.selectedOptions[0]?.dataset.strand;
            const grade = gradeSelect.value;
            let firstVisible = null;

            subjectOptions.forEach((option) => {
                const visible = option.dataset.strand === strand && option.dataset.grade === grade;
                option.hidden = !visible;
                option.disabled = !visible;

                if (visible && !firstVisible) {
                    firstVisible = option;
                }
            });

            if (firstVisible && subjectSelect.selectedOptions[0]?.disabled) {
                subjectSelect.value = firstVisible.value;
            }
        };

        document.querySelectorAll('[data-modal-target]').forEach((button) => {
            button.addEventListener('click', () => {
                const modal = document.getElementById(button.dataset.modalTarget);
                modal.hidden = false;
                document.body.classList.add('modal-open');
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('.section-modal-backdrop').hidden = true;
                document.body.classList.remove('modal-open');
            });
        });

        document.querySelectorAll('.section-modal').forEach((modal) => {
            const tabs = modal.querySelectorAll('[data-section-tab]');
            const panels = modal.querySelectorAll('[data-section-panel]');

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    tabs.forEach((item) => item.classList.remove('is-active'));
                    panels.forEach((panel) => panel.classList.remove('is-active'));
                    tab.classList.add('is-active');
                    modal.querySelector(`[data-section-panel="${tab.dataset.sectionTab}"]`)?.classList.add('is-active');
                });
            });
        });

        document.querySelectorAll('.section-modal-backdrop').forEach((backdrop) => {
            backdrop.addEventListener('click', (event) => {
                if (event.target === backdrop) {
                    backdrop.hidden = true;
                    document.body.classList.remove('modal-open');
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            document.querySelectorAll('.section-modal-backdrop:not([hidden])').forEach((modal) => {
                modal.hidden = true;
            });
            document.body.classList.remove('modal-open');
        });

        strandSelect.addEventListener('change', filterSubjects);
        gradeSelect.addEventListener('change', filterSubjects);
        document.querySelectorAll('[data-semester-select]').forEach((select) => {
            select.addEventListener('change', () => syncSemesterDates(select));

            if (!select.closest('form')?.querySelector('[data-semester-start]')?.value) {
                syncSemesterDates(select);
            }
        });
        filterSubjects();
    });
</script>
@endsection
