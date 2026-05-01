@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Teacher Dashboard</h1>
        <p class="text-muted mb-0">Welcome, {{ auth()->user()->name }}.</p>
    </div>
    <a class="btn btn-primary" href="{{ route('teacher.attendance.create') }}">Take Attendance</a>
</div>
<div class="stat-grid teacher-stat-grid">
    <div class="stat-card"><span>Today Total</span><strong>{{ $today['total'] }}</strong></div>
    <div class="stat-card success"><span>Present</span><strong>{{ $today['present'] }}</strong></div>
    <div class="stat-card warning"><span>Late</span><strong>{{ $today['late'] }}</strong></div>
    <div class="stat-card danger"><span>Absent</span><strong>{{ $today['absent'] }}</strong></div>
</div>
<section class="panel">
    <div class="section-title">
        <h2>Recent Submissions</h2>
    </div>
    <div class="live-search-control mb-3">
        <input class="form-control" placeholder="Live search submissions" data-live-search data-live-search-target="#recentSubmissions tbody tr">
    </div>
    <div class="table-responsive teacher-history-table">
        <table class="table align-middle" id="recentSubmissions">
            <thead><tr><th>Date</th><th>Subject</th><th>Records</th></tr></thead>
            <tbody>
            @forelse($history as $row)
                <tr><td>{{ $row->attendance_date }}</td><td>{{ $row->subject->subject_name }}</td><td>{{ $row->records }}</td></tr>
            @empty
                <tr><td colspan="3" class="text-center empty-state py-4">No submissions yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
