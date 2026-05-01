@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Admin Dashboard</h1>
        <p class="text-muted mb-0">Monitor students, teachers, subjects, and attendance activity.</p>
    </div>
</div>
<div class="stat-grid">
    <div class="stat-card"><span>Students</span><strong>{{ $stats['students'] }}</strong></div>
    <div class="stat-card"><span>Teachers</span><strong>{{ $stats['teachers'] }}</strong></div>
    <div class="stat-card"><span>Subjects</span><strong>{{ $stats['subjects'] }}</strong></div>
    <div class="stat-card success"><span>Records Today</span><strong>{{ $stats['today'] }}</strong></div>
</div>
<section class="panel">
    <div class="section-title">
        <h2>Recent Attendance</h2>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.attendance.index') }}">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Date</th><th>Student</th><th>Subject</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($recent as $row)
                <tr>
                    <td>{{ $row->attendance_date->toDateString() }}</td>
                    <td><span class="record-name">{{ $row->student->last_name }}, {{ $row->student->first_name }}</span><span class="meta-line">{{ $row->student_id }}</span></td>
                    <td>{{ $row->subject->subject_name }}</td>
                    <td><span class="badge badge-{{ strtolower($row->status) }}">{{ $row->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center empty-state py-4">No attendance records yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
