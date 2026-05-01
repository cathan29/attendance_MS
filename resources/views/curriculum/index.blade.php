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
            <select class="form-select" name="semester" required>
                <option>1st Semester</option>
                <option>2nd Semester</option>
            </select>
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
                                                            @foreach($teachers as $teacher)
                                                                <option value="{{ $teacher->id }}" @selected($assignment->teacher_id === $teacher->id)>{{ $teacher->last_name }}, {{ $teacher->first_name }}</option>
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
                                                        <select class="form-select" name="semester" required>
                                                            <option @selected($assignment->semester === '1st Semester')>1st Semester</option>
                                                            <option @selected($assignment->semester === '2nd Semester')>2nd Semester</option>
                                                        </select>
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
                    <td>{{ $assignment->school_year }} / {{ $assignment->semester }}</td>
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
        filterSubjects();
    });
</script>
@endsection
