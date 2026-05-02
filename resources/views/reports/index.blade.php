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

<section class="panel mb-4">
    <div class="section-title">
        <h2>PDF-Ready Reports</h2>
        <span class="chip-light">Open then Print / Save as PDF</span>
    </div>
    <div class="report-export-grid">
        <form method="GET" action="" data-report-form data-report-type="student">
            <h3>Per Student Report</h3>
            <label class="form-label">Student</label>
            <select class="form-select" name="student_id" required>
                @foreach($students as $student)
                    <option value="{{ $student->student_id }}">{{ $student->last_name }}, {{ $student->first_name }} / {{ $student->student_id }} / {{ $student->strand->strand_name }}-{{ $student->section }}</option>
                @endforeach
            </select>
            <input type="hidden" name="date_from" value="{{ $filters['dateFrom'] }}">
            <input type="hidden" name="date_to" value="{{ $filters['dateTo'] }}">
            <button class="btn btn-outline-primary">Open Student PDF View</button>
        </form>
        <form method="GET" action="{{ route('admin.reports.section.print') }}" target="_blank">
            <h3>Per Section Monthly Report</h3>
            <div class="report-export-fields">
                <label><span>Strand</span><select class="form-select" name="strand_id">@foreach($strands as $strand)<option value="{{ $strand->id }}">{{ $strand->strand_name }}</option>@endforeach</select></label>
                <label><span>Grade</span><select class="form-select" name="year_level"><option>11</option><option>12</option></select></label>
                <label><span>Section</span><select class="form-select" name="section">@foreach($sections as $section)<option>{{ $section }}</option>@endforeach</select></label>
            </div>
            <input type="hidden" name="date_from" value="{{ $filters['dateFrom'] }}">
            <input type="hidden" name="date_to" value="{{ $filters['dateTo'] }}">
            <button class="btn btn-outline-primary">Open Section PDF View</button>
        </form>
    </div>
</section>

<section class="panel mb-4">
    <div class="section-title">
        <h2>Live Search</h2>
        <span class="chip-light">Filters report lists below</span>
    </div>
    <input class="form-control" placeholder="Live search subjects, classes, teachers, weekdays, or follow-up students" data-live-search data-live-search-target=".rank-list .rank-item, .report-health-table tbody tr, #followUpTable tbody tr">
</section>

<div class="report-hero-grid">
    <section class="report-hero-card">
        <span>Attendance Health</span>
        <strong>{{ $summary['attendanceRate'] }}%</strong>
        <p>
            @if($summary['attendanceRateChange'] > 0)
                Up {{ abs($summary['attendanceRateChange']) }} pts vs previous {{ $summary['periodDays'] }} days.
            @elseif($summary['attendanceRateChange'] < 0)
                Down {{ abs($summary['attendanceRateChange']) }} pts vs previous {{ $summary['periodDays'] }} days.
            @else
                No change vs previous {{ $summary['periodDays'] }} days.
            @endif
        </p>
    </section>
    <section class="report-kpi-grid">
        <article><span>Total Records</span><strong>{{ $summary['total'] }}</strong></article>
        <article><span>On-Time Rate</span><strong>{{ $summary['onTimeRate'] }}%</strong></article>
        <article><span>Concern Rate</span><strong>{{ $summary['concernRate'] }}%</strong></article>
        <article><span>Avg Daily Records</span><strong>{{ $summary['averageDailyRecords'] }}</strong></article>
        <article><span>Perfect Students</span><strong>{{ $summary['perfectStudents'] }}</strong></article>
        <article><span>Critical Students</span><strong>{{ $summary['criticalStudents'] }}</strong></article>
    </section>
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
            <h2>Daily Health</h2>
        </div>
        <div class="mini-chart">
            @forelse($dailyHealth as $day)
                <div class="mini-bar" title="{{ $day->attendance_date }}: {{ $day->attendance_rate }}% attendance">
                    <span class="{{ $day->attendance_rate < 85 ? 'danger' : ($day->attendance_rate < 95 ? 'warning' : 'success') }}" style="height: {{ max(8, round($day->attendance_rate)) }}%"></span>
                    <small>{{ \Carbon\Carbon::parse($day->attendance_date)->format('d') }}</small>
                </div>
            @empty
                <p class="empty-state">No attendance records in this range.</p>
            @endforelse
        </div>
        <div class="insight-strip">
            <span>Best: <strong>{{ $bestAttendanceDay ? \Carbon\Carbon::parse($bestAttendanceDay->attendance_date)->format('M d') . ' / ' . $bestAttendanceDay->attendance_rate . '%' : 'No data' }}</strong></span>
            <span>Lowest: <strong>{{ $lowestAttendanceDay ? \Carbon\Carbon::parse($lowestAttendanceDay->attendance_date)->format('M d') . ' / ' . $lowestAttendanceDay->attendance_rate . '%' : 'No data' }}</strong></span>
        </div>
    </section>
</div>

<div class="report-grid">
    <section class="panel">
        <div class="section-title">
            <h2>Subject Health</h2>
            <span class="chip-light">Lowest rates first</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle report-health-table">
                <thead><tr><th>Subject</th><th>Rate</th><th>Present</th><th>Late</th><th>Absent</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($subjectHealth as $subject)
                    <tr>
                        <td><span class="record-name">{{ $subject->subject_name }}</span></td>
                        <td><span class="health-pill {{ $subject->attendance_rate < 85 ? 'danger' : ($subject->attendance_rate < 95 ? 'warning' : 'success') }}">{{ $subject->attendance_rate }}%</span></td>
                        <td>{{ $subject->present_count }}</td>
                        <td>{{ $subject->late_count }}</td>
                        <td>{{ $subject->absent_count }}</td>
                        <td>{{ $subject->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center empty-state py-4">No subject health data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Class Health</h2>
            <span class="chip-light">Sections needing attention</span>
        </div>
        <div class="table-responsive">
            <table class="table align-middle report-health-table">
                <thead><tr><th>Class</th><th>Rate</th><th>Present</th><th>Late</th><th>Absent</th><th>Total</th></tr></thead>
                <tbody>
                @forelse($classHealth as $class)
                    <tr>
                        <td><span class="record-name">Grade {{ $class->year_level }} {{ $class->strand_name }}-{{ $class->section ?: 'No Section' }}</span></td>
                        <td><span class="health-pill {{ $class->attendance_rate < 85 ? 'danger' : ($class->attendance_rate < 95 ? 'warning' : 'success') }}">{{ $class->attendance_rate }}%</span></td>
                        <td>{{ $class->present_count }}</td>
                        <td>{{ $class->late_count }}</td>
                        <td>{{ $class->absent_count }}</td>
                        <td>{{ $class->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center empty-state py-4">No class health data yet.</td></tr>
                @endforelse
                </tbody>
            </table>
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
        <table class="table align-middle" id="followUpTable">
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-report-form][data-report-type="student"]').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const studentId = form.querySelector('[name="student_id"]').value;
                const params = new URLSearchParams(new FormData(form));
                params.delete('student_id');
                window.open(`{{ url('/admin/reports/student') }}/${studentId}/print?${params.toString()}`, '_blank');
            });
        });
    });
</script>
@endsection
