@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Take Attendance</h1>
        <p class="text-muted mb-0">Filter a class, choose a subject, then save one record per student.</p>
    </div>
</div>
<section class="panel mb-4">
    <form method="GET" action="{{ route('teacher.attendance.create') }}" class="action-bar">
        <div><label class="form-label">Subject</label><select class="form-select" name="subject_id">@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected($subjectId === $subject->id)>{{ $subject->subject_name }}</option>@endforeach</select></div>
        <div><label class="form-label">Date</label><input type="date" class="form-control" name="attendance_date" value="{{ $attendanceDate }}"></div>
        <div><label class="form-label">Strand</label><select class="form-select" name="strand_id"><option value="">All</option>@foreach($strands as $strand)<option value="{{ $strand->id }}" @selected(request('strand_id') == $strand->id)>{{ $strand->strand_name }}</option>@endforeach</select></div>
        <div><label class="form-label">Year</label><select class="form-select" name="year_level"><option value="">All</option><option @selected(request('year_level') === '11')>11</option><option @selected(request('year_level') === '12')>12</option></select></div>
        <div><label class="form-label">Section</label><input class="form-control" name="section" value="{{ request('section') }}"></div>
        <button class="btn btn-outline-primary">Load Class</button>
    </form>
</section>
<section class="panel">
    <form method="POST" action="{{ route('teacher.attendance.store') }}">
        @csrf
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Student</th><th class="attendance-options">Present</th><th class="attendance-options">Late</th><th class="attendance-options">Absent</th><th class="remarks-cell">Remarks</th></tr></thead>
                <tbody>
                @forelse($students as $student)
                    @php($saved = $student->attendances->first())
                    @php($status = $saved->status ?? 'Present')
                    <tr>
                        <td><span class="student-name">{{ $student->last_name }}, {{ $student->first_name }}</span><span class="meta-line">{{ $student->student_id }} / {{ $student->year_level }}-{{ $student->section }}</span></td>
                        @foreach(['Present', 'Late', 'Absent'] as $option)
                            <td><input class="form-check-input" type="radio" name="status[{{ $student->student_id }}]" value="{{ $option }}" @checked($status === $option)></td>
                        @endforeach
                        <td><input class="form-control" name="remarks[{{ $student->student_id }}]" value="{{ $saved->remarks ?? '' }}"></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center empty-state py-4">No students match this filter.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary" @disabled($students->isEmpty() || !$subjectId)>Save Attendance</button>
    </form>
</section>
@endsection
