@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1>Reports</h1>
        <p class="text-muted mb-0">Review attendance health, spot trends, and identify students who need follow-up.</p>
    </div>
    <a class="btn btn-outline-primary" href="{{ route('admin.attendance.export', [
        'date_from' => $filters['dateFrom'],
        'date_to' => $filters['dateTo'],
        'subject_id' => $filters['subjectId'],
    ]) }}">Export Current Data</a>
</div>

<section class="panel mb-4">
    <form method="GET" action="{{ route('admin.reports.index') }}" class="action-bar">
        <div>
            <label class="form-label">From</label>
            <input type="date" class="form-control" name="date_from" value="{{ $filters['dateFrom'] }}">
        </div>
        <div>
            <label class="form-label">To</label>
            <input type="date" class="form-control" name="date_to" value="{{ $filters['dateTo'] }}">
        </div>
        <div>
            <label class="form-label">Subject</label>
            <select class="form-select" name="subject_id">
                <option value="">All subjects</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" @selected($filters['subjectId'] === $subject->id)>{{ $subject->subject_name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-primary">Apply Filters</button>
    </form>
</section>

<div class="stat-grid">
    <div class="stat-card success"><span>Attendance Rate</span><strong>{{ $summary['attendanceRate'] }}%</strong></div>
    <div class="stat-card"><span>Total Records</span><strong>{{ $summary['total'] }}</strong></div>
    <div class="stat-card success"><span>Present</span><strong>{{ $summary['present'] }}</strong></div>
    <div class="stat-card warning"><span>Late</span><strong>{{ $summary['late'] }}</strong></div>
    <div class="stat-card danger"><span>Absent</span><strong>{{ $summary['absent'] }}</strong></div>
</div>

<div class="report-grid">
    <section class="panel report-card">
        <div class="section-title">
            <h2>Status Breakdown</h2>
            <span class="chip-light">{{ $summary['total'] }} records</span>
        </div>
        @foreach([
            'Present' => ['count' => $summary['present'], 'class' => 'success'],
            'Late' => ['count' => $summary['late'], 'class' => 'warning'],
            'Absent' => ['count' => $summary['absent'], 'class' => 'danger'],
        ] as $label => $item)
            @php($percent = $summary['total'] > 0 ? round(($item['count'] / $summary['total']) * 100) : 0)
            <div class="metric-row">
                <div>
                    <strong>{{ $label }}</strong>
                    <span>{{ $item['count'] }} records</span>
                </div>
                <div class="bar-track"><span class="bar-fill {{ $item['class'] }}" style="width: {{ $percent }}%"></span></div>
                <b>{{ $percent }}%</b>
            </div>
        @endforeach
    </section>

    <section class="panel report-card">
        <div class="section-title">
            <h2>Daily Volume</h2>
        </div>
        <div class="mini-chart">
            @forelse($dailyTrend as $day)
                @php($maxDay = max(1, $dailyTrend->max('total')))
                <div class="mini-bar" title="{{ $day->attendance_date }}: {{ $day->total }}">
                    <span style="height: {{ max(8, round(($day->total / $maxDay) * 100)) }}%"></span>
                    <small>{{ \Carbon\Carbon::parse($day->attendance_date)->format('d') }}</small>
                </div>
            @empty
                <p class="empty-state">No attendance records in this range.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="report-grid">
    <section class="panel">
        <div class="section-title">
            <h2>Top Subjects</h2>
        </div>
        <div class="rank-list">
            @forelse($subjectBreakdown as $subject)
                @php($maxSubject = max(1, $subjectBreakdown->max('total')))
                <div class="rank-item">
                    <div><strong>{{ $subject->subject_name }}</strong><span>{{ $subject->total }} records</span></div>
                    <div class="bar-track"><span class="bar-fill" style="width: {{ round(($subject->total / $maxSubject) * 100) }}%"></span></div>
                </div>
            @empty
                <p class="empty-state">No subject data yet.</p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Class Activity</h2>
        </div>
        <div class="rank-list">
            @forelse($classBreakdown as $class)
                @php($maxClass = max(1, $classBreakdown->max('total')))
                <div class="rank-item">
                    <div><strong>Grade {{ $class->year_level }} - {{ $class->section ?: 'No Section' }}</strong><span>{{ $class->total }} records</span></div>
                    <div class="bar-track"><span class="bar-fill success" style="width: {{ round(($class->total / $maxClass) * 100) }}%"></span></div>
                </div>
            @empty
                <p class="empty-state">No class activity yet.</p>
            @endforelse
        </div>
    </section>
</div>

<div class="report-grid">
    <section class="panel">
        <div class="section-title">
            <h2>Teacher Submissions</h2>
        </div>
        <div class="rank-list">
            @forelse($teacherBreakdown as $teacher)
                @php($maxTeacher = max(1, $teacherBreakdown->max('total')))
                <div class="rank-item">
                    <div><strong>{{ $teacher->last_name }}, {{ $teacher->first_name }}</strong><span>{{ $teacher->total }} records</span></div>
                    <div class="bar-track"><span class="bar-fill" style="width: {{ round(($teacher->total / $maxTeacher) * 100) }}%"></span></div>
                </div>
            @empty
                <p class="empty-state">No teacher activity yet.</p>
            @endforelse
        </div>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Weekday Pattern</h2>
        </div>
        <div class="rank-list">
            @forelse($weekdayBreakdown as $weekday)
                @php($maxWeekday = max(1, $weekdayBreakdown->max('total')))
                <div class="rank-item">
                    <div><strong>{{ $weekday->weekday }}</strong><span>{{ $weekday->total }} records</span></div>
                    <div class="bar-track"><span class="bar-fill success" style="width: {{ round(($weekday->total / $maxWeekday) * 100) }}%"></span></div>
                </div>
            @empty
                <p class="empty-state">No weekday data yet.</p>
            @endforelse
        </div>
    </section>
</div>

<section class="panel">
    <div class="section-title">
        <h2>Needs Follow-Up</h2>
        <span class="chip-light">Absent or repeatedly late</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Student</th><th>Class</th><th>Absent</th><th>Late</th><th>Total Records</th></tr></thead>
            <tbody>
            @forelse($atRiskStudents as $student)
                <tr>
                    <td><span class="record-name">{{ $student->last_name }}, {{ $student->first_name }}</span><span class="meta-line">{{ $student->student_id }}</span></td>
                    <td>Grade {{ $student->year_level }} - {{ $student->section }}</td>
                    <td><span class="badge badge-absent">{{ $student->absent_count }}</span></td>
                    <td><span class="badge badge-late">{{ $student->late_count }}</span></td>
                    <td>{{ $student->total }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center empty-state py-4">No follow-up concerns in this date range.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
