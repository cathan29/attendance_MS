@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1>Curriculum</h1>
        <p class="text-muted mb-0">Assign teachers to a subject, strand, grade level, and section like a real SHS class load.</p>
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
            <select class="form-select" name="subject_id" required>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}">{{ $subject->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
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

<section class="panel">
    <div class="section-title">
        <h2>Teacher Loads</h2>
        <span class="chip-light">{{ $assignments->count() }} assignments</span>
    </div>
    <form method="GET" action="{{ route('admin.curriculum.index') }}" class="search-bar">
        <input class="form-control" name="q" value="{{ $search }}" placeholder="Search teacher, subject, strand, section, school year, or semester">
        <button class="btn btn-outline-primary">Search</button>
        @if($search !== '')
            <a class="btn btn-outline-primary" href="{{ route('admin.curriculum.index') }}">Clear</a>
        @endif
    </form>
    <div class="table-responsive">
        <table class="table align-middle">
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
@endsection
