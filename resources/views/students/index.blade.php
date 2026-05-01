@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Students</h1>
        <p class="text-muted mb-0">Add, update, and remove student records.</p>
    </div>
</div>
<section class="panel mb-4">
    <h2>Student Form</h2>
    <form method="POST" action="{{ route('admin.students.store') }}" class="row g-3">
        @csrf
        <div class="col-md-3"><label class="form-label">Student ID</label><input class="form-control" name="student_id" value="{{ old('student_id') }}" required></div>
        <div class="col-md-3"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
        <div class="col-md-3"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name"></div>
        <div class="col-md-3"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
        <div class="col-md-3"><label class="form-label">Strand</label><select class="form-select" name="strand_id" required>@foreach($strands as $strand)<option value="{{ $strand->id }}">{{ $strand->strand_name }}</option>@endforeach</select></div>
        <div class="col-md-2"><label class="form-label">Year</label><select class="form-select" name="year_level"><option>11</option><option>12</option></select></div>
        <div class="col-md-4">
            <label class="form-label">Section</label>
            <select class="form-select" name="section" required>
                @foreach($sections as $section)
                    <option value="{{ $section }}">{{ $section }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Save Student</button></div>
    </form>
</section>
<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Name</th><th>Strand</th><th>Year/Section</th><th></th></tr></thead>
            <tbody>
            @forelse($students as $student)
                <tr>
                    <td>{{ $student->student_id }}</td>
                    <td><span class="record-name">{{ $student->last_name }}, {{ $student->first_name }}</span></td>
                    <td>{{ $student->strand->strand_name }}</td>
                    <td>{{ $student->year_level }} - {{ $student->section }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.students.destroy', $student) }}" onsubmit="return confirm('Delete this student?')">
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
</section>
@endsection
