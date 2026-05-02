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
    <div class="stat-card warning"><span>Missing Submissions</span><strong>{{ $stats['missing'] }}</strong></div>
    <div class="stat-card danger"><span>Watchlist Students</span><strong>{{ $stats['watchlist'] }}</strong></div>
</div>

<div class="monitor-grid">
    <section class="panel">
        <div class="section-title">
            <h2>Teacher Submission Monitor</h2>
            <span class="chip-light">{{ $submissionStats['isSchoolDay'] ? $submissionStats['rate'] . '% submitted' : 'No class day' }}</span>
        </div>
        <div class="submission-meter">
            <span style="width: {{ $submissionStats['rate'] }}%"></span>
        </div>
        <div class="monitor-summary">
            <span><strong>{{ $submissionStats['submitted'] }}</strong> Submitted</span>
            <span><strong>{{ $submissionStats['missing'] }}</strong> Missing</span>
            <span><strong>{{ $submissionStats['total'] }}</strong> Total Loads</span>
        </div>
        <div class="compact-list">
            @forelse($missingSubmissions->take(4) as $assignment)
                <div>
                    <strong>{{ $assignment->teacher->last_name }}, {{ $assignment->teacher->first_name }}</strong>
                    <span>{{ $assignment->subject->subject_name }} / Grade {{ $assignment->year_level }} {{ $assignment->strand->strand_name }}-{{ $assignment->section }}</span>
                </div>
            @empty
                <p class="empty-state">{{ $submissionStats['isSchoolDay'] ? 'All teacher loads have submissions today.' : 'No submission monitoring for weekends.' }}</p>
            @endforelse
        </div>
        @if($missingSubmissions->count() > 4)
            <button type="button" class="btn btn-outline-primary btn-sm monitor-more-btn" data-modal-target="missingSubmissionsModal">More</button>
        @endif
    </section>

    <section class="panel">
        <div class="section-title">
            <h2>Attendance Alerts Today</h2>
            <span class="chip-light">{{ $absentToday->count() + $lateToday->count() }} alerts</span>
        </div>
        <div class="alert-columns">
            <div>
                <h3>Absent</h3>
                <div class="compact-list">
                    @forelse($absentToday->take(4) as $record)
                        <div>
                            <strong>{{ $record->student->last_name }}, {{ $record->student->first_name }}</strong>
                            <span>{{ $record->student->strand->strand_name }}-{{ $record->student->section }} / {{ $record->subject->subject_name }}</span>
                        </div>
                    @empty
                        <p class="empty-state">No absences today.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h3>Late</h3>
                <div class="compact-list">
                    @forelse($lateToday->take(4) as $record)
                        <div>
                            <strong>{{ $record->student->last_name }}, {{ $record->student->first_name }}</strong>
                            <span>{{ $record->student->strand->strand_name }}-{{ $record->student->section }} / {{ $record->subject->subject_name }}</span>
                        </div>
                    @empty
                        <p class="empty-state">No late students today.</p>
                    @endforelse
                </div>
            </div>
        </div>
        @if($absentToday->count() > 4 || $lateToday->count() > 4)
            <button type="button" class="btn btn-outline-primary btn-sm monitor-more-btn" data-modal-target="attendanceAlertsModal">More</button>
        @endif
    </section>
</div>

<section class="panel mb-4">
    <div class="section-title">
        <h2>Student Risk Scoring</h2>
        <span class="chip-light">Last 30 days</span>
    </div>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Student</th><th>Class</th><th>Absent</th><th>Late</th><th>Score</th><th>Level</th></tr></thead>
            <tbody>
            @forelse($studentRisk as $student)
                <tr>
                    <td><span class="record-name">{{ $student->last_name }}, {{ $student->first_name }}</span><span class="meta-line">{{ $student->student_id }}</span></td>
                    <td>Grade {{ $student->year_level }} {{ $student->strand_name }}-{{ $student->section }}</td>
                    <td><span class="badge badge-absent">{{ $student->absent_count }}</span></td>
                    <td><span class="badge badge-late">{{ $student->late_count }}</span></td>
                    <td>{{ $student->risk_score }}</td>
                    <td><span class="risk-pill {{ strtolower($student->risk_level) }}">{{ $student->risk_level }}</span></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center empty-state py-4">No risk data yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="section-modal-backdrop" id="missingSubmissionsModal" hidden>
    <section class="section-modal dashboard-more-modal" role="dialog" aria-modal="true" aria-labelledby="missingSubmissionsTitle">
        <div class="section-modal-head">
            <div>
                <span class="eyebrow">Teacher Monitor</span>
                <h2 id="missingSubmissionsTitle">Missing Submissions</h2>
            </div>
            <button type="button" class="section-modal-close" data-modal-close aria-label="Close">x</button>
        </div>
        <div class="section-modal-body">
            <div class="modal-paged-list" data-page-size="10">
                @forelse($missingSubmissions as $assignment)
                    <div class="modal-page-item">
                        <strong>{{ $assignment->teacher->last_name }}, {{ $assignment->teacher->first_name }}</strong>
                        <span>{{ $assignment->subject->subject_name }} / Grade {{ $assignment->year_level }} {{ $assignment->strand->strand_name }}-{{ $assignment->section }}</span>
                    </div>
                @empty
                    <p class="empty-state">No missing submissions.</p>
                @endforelse
            </div>
            <div class="modal-pagination" data-modal-pagination></div>
        </div>
    </section>
</div>

<div class="section-modal-backdrop" id="attendanceAlertsModal" hidden>
    <section class="section-modal dashboard-more-modal" role="dialog" aria-modal="true" aria-labelledby="attendanceAlertsTitle">
        <div class="section-modal-head">
            <div>
                <span class="eyebrow">Attendance Alerts</span>
                <h2 id="attendanceAlertsTitle">Absent and Late Today</h2>
            </div>
            <button type="button" class="section-modal-close" data-modal-close aria-label="Close">x</button>
        </div>
        <div class="section-modal-body">
            <div class="modal-paged-list" data-page-size="10">
                @foreach($absentToday as $record)
                    <div class="modal-page-item">
                        <strong>{{ $record->student->last_name }}, {{ $record->student->first_name }}</strong>
                        <span>Absent / {{ $record->student->strand->strand_name }}-{{ $record->student->section }} / {{ $record->subject->subject_name }}</span>
                    </div>
                @endforeach
                @foreach($lateToday as $record)
                    <div class="modal-page-item">
                        <strong>{{ $record->student->last_name }}, {{ $record->student->first_name }}</strong>
                        <span>Late / {{ $record->student->strand->strand_name }}-{{ $record->student->section }} / {{ $record->subject->subject_name }}</span>
                    </div>
                @endforeach
                @if($absentToday->isEmpty() && $lateToday->isEmpty())
                    <p class="empty-state">No attendance alerts today.</p>
                @endif
            </div>
            <div class="modal-pagination" data-modal-pagination></div>
        </div>
    </section>
</div>

<section class="panel mb-4">
    <div class="section-title">
        <h2>Recent Audit Trail</h2>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.audit.index') }}">View All</a>
    </div>
    <div class="compact-list audit-compact-list">
        @forelse($auditLogs as $log)
            <div>
                <strong>{{ str_replace('_', ' ', $log->action) }}</strong>
                <span>{{ $log->description }} / {{ $log->user?->name ?? 'System' }} / {{ $log->created_at->format('M d h:i A') }}</span>
            </div>
        @empty
            <p class="empty-state">No audit logs yet.</p>
        @endforelse
    </div>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Recent Attendance</h2>
        <a class="btn btn-outline-primary btn-sm" href="{{ route('admin.attendance.index') }}">View All</a>
    </div>
    <div class="live-search-control mb-3">
        <input class="form-control" placeholder="Live search recent attendance" data-live-search data-live-search-target="#recentAttendance tbody tr">
    </div>
    <div class="table-responsive">
        <table class="table align-middle" id="recentAttendance">
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const openModal = (id) => {
            const modal = document.getElementById(id);
            if (!modal) {
                return;
            }

            modal.hidden = false;
            document.body.classList.add('modal-open');
        };

        document.querySelectorAll('[data-modal-target]').forEach((button) => {
            button.addEventListener('click', () => openModal(button.dataset.modalTarget));
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

        document.querySelectorAll('.modal-paged-list').forEach((list) => {
            const items = Array.from(list.querySelectorAll('.modal-page-item'));
            const pageSize = Number(list.dataset.pageSize || 10);
            const pagination = list.parentElement.querySelector('[data-modal-pagination]');
            let page = 1;
            const pages = Math.max(1, Math.ceil(items.length / pageSize));

            const render = () => {
                items.forEach((item, index) => {
                    item.hidden = index < (page - 1) * pageSize || index >= page * pageSize;
                });

                if (!pagination || pages <= 1) {
                    return;
                }

                pagination.innerHTML = `
                    <button type="button" class="btn btn-outline-primary btn-sm" ${page === 1 ? 'disabled' : ''} data-page-prev>Previous</button>
                    <span>Page ${page} of ${pages}</span>
                    <button type="button" class="btn btn-outline-primary btn-sm" ${page === pages ? 'disabled' : ''} data-page-next>Next</button>
                `;

                pagination.querySelector('[data-page-prev]')?.addEventListener('click', () => {
                    page = Math.max(1, page - 1);
                    render();
                });
                pagination.querySelector('[data-page-next]')?.addEventListener('click', () => {
                    page = Math.min(pages, page + 1);
                    render();
                });
            };

            render();
        });
    });
</script>
@endsection
