@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Attendance Records</h1>
        <p class="text-muted mb-0">Search, review, and export attendance submissions.</p>
    </div>
    <a class="btn btn-outline-primary" href="{{ route('admin.attendance.export', request()->query()) }}">Export CSV</a>
</div>
<section class="panel mb-4">
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="action-bar">
        <div><label class="form-label">From</label><input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}"></div>
        <div><label class="form-label">To</label><input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}"></div>
        <div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option>@foreach(['Present', 'Late', 'Absent'] as $status)<option @selected(request('status') === $status)>{{ $status }}</option>@endforeach</select></div>
        <div><label class="form-label">Subject</label><select class="form-select" name="subject_id"><option value="">All</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected(request('subject_id') == $subject->id)>{{ $subject->subject_name }}</option>@endforeach</select></div>
        <button class="btn btn-primary">Filter</button>
    </form>
</section>
<section class="panel">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>
            @forelse($records as $record)
                <tr>
                    <td>{{ $record->attendance_date->toDateString() }}</td>
                    <td><span class="record-name">{{ $record->student->last_name }}, {{ $record->student->first_name }}</span><span class="meta-line">{{ $record->student_id }}</span></td>
                    <td>{{ $record->student->year_level }}-{{ $record->student->section }}</td>
                    <td>{{ $record->subject->subject_name }}</td>
                    <td>{{ $record->teacher->last_name }}, {{ $record->teacher->first_name }}</td>
                    <td><span class="badge badge-{{ strtolower($record->status) }}">{{ $record->status }}</span></td>
                    <td>{{ $record->remarks }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center empty-state py-4">No records found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $records->links() }}
</section>
@endsection
