@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Students</h1>
        <p class="text-muted mb-0">Enroll students, place them into sections, and keep records easy to scan.</p>
    </div>
</div>

<div class="student-dashboard">
    <section class="student-form-panel">
        <div class="student-form-header">
            <div>
                <span class="eyebrow">New student</span>
                <h2>Enrollment</h2>
            </div>
            <span class="student-id-preview">{{ $nextStudentId }}</span>
        </div>
        <form method="POST" action="{{ route('admin.students.store') }}" class="student-form-grid">
            @csrf
            <label>
                <span>Student ID</span>
                <input class="form-control" name="student_id" value="{{ $nextStudentId }}" readonly>
            </label>
            <label>
                <span>First Name</span>
                <input class="form-control" name="first_name" required>
            </label>
            <label>
                <span>Middle Name</span>
                <input class="form-control" name="middle_name">
            </label>
            <label>
                <span>Last Name</span>
                <input class="form-control" name="last_name" required>
            </label>
            <label>
                <span>Strand</span>
                <select class="form-select" name="strand_id" required>
                    @foreach($strands as $strand)
                        <option value="{{ $strand->id }}">{{ $strand->strand_name }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span>Grade</span>
                <select class="form-select" name="year_level">
                    <option>11</option>
                    <option>12</option>
                </select>
            </label>
            <label>
                <span>Section</span>
                <select class="form-select" name="section" required>
                    @foreach($sections as $section)
                        <option value="{{ $section }}">{{ $section }}</option>
                    @endforeach
                </select>
            </label>
            <button class="btn btn-primary">Save Student</button>
        </form>
    </section>

    <section class="student-summary-grid">
        <article><span>Total Students</span><strong>{{ $students->total() }}</strong></article>
        <article><span>Active Sections</span><strong>{{ $studentsBySection->count() }}</strong></article>
        <article><span>Next ID</span><strong>{{ $nextStudentId }}</strong></article>
    </section>
</div>

<section class="panel">
    <div class="student-list-header">
        <div>
            <h2>Student Records</h2>
            <p class="text-muted mb-0">{{ $students->total() }} records found</p>
        </div>
        <form method="GET" action="{{ route('admin.students.index') }}" class="student-search">
            <input class="form-control" name="q" value="{{ $search }}" placeholder="Live search ID, name, strand, grade, or section" data-live-search data-live-search-target="#studentSections article, #studentRecords tbody tr">
            <button class="btn btn-outline-primary">Search</button>
            @if($search !== '')
                <a class="btn btn-outline-primary" href="{{ route('admin.students.index') }}">Clear</a>
            @endif
        </form>
    </div>

    <div class="student-section-strip" id="studentSections">
        @forelse($studentsBySection as $key => $sectionStudents)
            @php([$strandName, $grade, $section] = explode('|', $key))
            @php($modalId = 'student-section-' . md5($key))
            <article role="button" tabindex="0" data-modal-target="{{ $modalId }}">
                <strong>{{ $strandName }}-{{ $section }}</strong>
                <span>Grade {{ $grade }}</span>
                <em>{{ $sectionStudents->count() }}</em>
            </article>
            <div class="section-modal-backdrop" id="{{ $modalId }}" hidden>
                <section class="section-modal student-section-modal" role="dialog" aria-modal="true" aria-labelledby="{{ $modalId }}-title">
                    <div class="section-modal-head">
                        <div>
                            <span class="eyebrow">Student Section</span>
                            <h2 id="{{ $modalId }}-title">Grade {{ $grade }} {{ $strandName }}-{{ $section }}</h2>
                        </div>
                        <button type="button" class="section-modal-close" data-modal-close aria-label="Close">x</button>
                    </div>
                    <div class="section-modal-body">
                        <div class="section-summary">
                            <span><strong>{{ $sectionStudents->count() }}</strong> Students</span>
                            <span><strong>{{ $strandName }}</strong> Strand</span>
                            <span><strong>{{ $section }}</strong> Section</span>
                        </div>
                        <div class="live-search-control mb-3">
                            <input class="form-control" placeholder="Live search students in this section" data-live-search data-live-search-target="#{{ $modalId }} .student-section-list tr">
                        </div>
                        <div class="table-responsive">
                            <table class="table align-middle student-section-list">
                                <thead><tr><th>Student</th><th>ID</th><th>Grade</th><th>Section</th></tr></thead>
                                <tbody>
                                @foreach($sectionStudents as $student)
                                    <tr>
                                        <td><span class="record-name">{{ $student->last_name }}, {{ $student->first_name }}</span></td>
                                        <td>{{ $student->student_id }}</td>
                                        <td>{{ $student->year_level }}</td>
                                        <td>{{ $student->strand->strand_name }}-{{ $student->section }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        @empty
            <p class="empty-state">No sections yet.</p>
        @endforelse
    </div>

    <div class="table-responsive" id="studentRecords">
        <table class="table align-middle student-table">
            <thead><tr><th>Student</th><th>Class</th><th>Section</th><th></th></tr></thead>
            <tbody>
            @forelse($students as $student)
                <tr>
                    <td>
                        <span class="record-name">{{ $student->last_name }}, {{ $student->first_name }}</span>
                        <span class="meta-line">{{ $student->student_id }}</span>
                    </td>
                    <td><span class="badge text-bg-secondary">{{ $student->strand->strand_name }}</span></td>
                    <td>Grade {{ $student->year_level }} - {{ $student->section }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" data-confirm="Delete this student?">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center empty-state py-4">No students yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $students->links() }}
</section>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-modal-target]').forEach((trigger) => {
            const open = () => {
                const modal = document.getElementById(trigger.dataset.modalTarget);
                if (!modal) {
                    return;
                }

                document.body.appendChild(modal);
                modal.hidden = false;
                document.body.classList.add('modal-open');
                modal.querySelector('input, button')?.focus();
            };

            trigger.addEventListener('click', open);
            trigger.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    open();
                }
            });
        });

        document.querySelectorAll('[data-modal-close]').forEach((button) => {
            button.addEventListener('click', () => {
                button.closest('.section-modal-backdrop').hidden = true;
                document.body.classList.remove('modal-open');
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
    });
</script>
@endsection
