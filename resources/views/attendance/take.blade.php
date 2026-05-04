@extends('layouts.app')
@section('content')
<div class="page-header">
    <div>
        <h1>Take Attendance</h1>
        <p class="text-muted mb-0">Filter a class, choose a subject, then save one record per student.</p>
    </div>
</div>
<section class="panel mb-4 teacher-filter-panel" data-attendance-filter-panel>
    <form method="GET" action="{{ route('teacher.attendance.create') }}" class="action-bar" data-attendance-load-form>
        <div class="col-md-6">
            <label class="form-label">Assigned Class</label>
            <select class="form-select" name="assignment_id" required data-attendance-autoload>
                @foreach($assignments as $item)
                    @php($doneAt = $assignmentCompletion[$item->id] ?? null)
                    <option value="{{ $item->id }}" @selected($assignmentId === $item->id)>
                        @if($doneAt)
                            ✓
                        @endif
                        {{ $item->subject->subject_name }} / Grade {{ $item->year_level }} {{ $item->strand->strand_name }}-{{ $item->section }}
                        @if($doneAt)
                            ({{ $doneAt->timezone(config('app.timezone'))->format('h:i A') }})
                        @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div><label class="form-label">Date</label><input type="date" class="form-control" name="attendance_date" value="{{ $attendanceDate }}" data-attendance-autoload></div>
        @if($schedules->isNotEmpty())
            <div class="col-md-3">
                <label class="form-label">Schedule</label>
                <select class="form-select" name="class_schedule_id" data-attendance-autoload>
                    @foreach($schedules as $item)
                        <option value="{{ $item->id }}" @selected($schedule?->id === $item->id)>
                            {{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }} / {{ $item->room ?: 'TBA' }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
        <button class="btn btn-outline-primary" data-attendance-load-button>Load Class</button>
    </form>
</section>
<section class="panel teacher-attendance-panel" data-attendance-panel>
    <form method="POST" action="{{ route('teacher.attendance.store') }}" data-attendance-save-form>
        @csrf
        <input type="hidden" name="assignment_id" value="{{ $assignmentId }}">
        <input type="hidden" name="subject_id" value="{{ $subjectId }}">
        <input type="hidden" name="class_schedule_id" value="{{ $schedule?->id }}">
        <input type="hidden" name="attendance_date" value="{{ $attendanceDate }}">
        @if($assignment)
            <div class="section-title">
                <h2>{{ $assignment->subject->subject_name }}</h2>
                <span class="chip-light">
                    Grade {{ $assignment->year_level }} {{ $assignment->strand->strand_name }}-{{ $assignment->section }}
                    @if($schedule)
                        / {{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} / Room {{ $schedule->room ?: 'TBA' }}
                    @endif
                </span>
            </div>
        @endif
        <div class="live-search-control mb-3">
            <input class="form-control" placeholder="Live search student name or ID" data-live-search data-live-search-target="#attendanceStudents tbody tr, #attendanceStudentCards .attendance-card">
        </div>
        <div class="teacher-attendance-cards" id="attendanceStudentCards">
            @forelse($students as $student)
                @php($saved = $student->attendances->first())
                @php($status = $saved->status ?? 'Present')
                <input type="hidden" name="status[{{ $student->student_id }}]" value="{{ $status }}" data-attendance-status-value="{{ $student->student_id }}">
                <article class="attendance-card">
                    <div class="attendance-card-head">
                        <div>
                            <strong>{{ $student->last_name }}, {{ $student->first_name }}</strong>
                            <span>{{ $student->student_id }} / {{ $student->year_level }}-{{ $student->section }}</span>
                        </div>
                    </div>
                    <div class="attendance-segment">
                        @foreach(['Present', 'Late', 'Absent'] as $option)
                            <label class="{{ strtolower($option) }}">
                                <input type="radio" name="visual_status_card[{{ $student->student_id }}]" value="{{ $option }}" data-attendance-status-option="{{ $student->student_id }}" @checked($saved && $status === $option)>
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                    <input class="form-control" name="remarks[{{ $student->student_id }}]" value="{{ $saved->remarks ?? '' }}" placeholder="Remarks">
                </article>
            @empty
                <p class="text-center empty-state py-4">No assigned students found. Ask the admin to set your curriculum load first.</p>
            @endforelse
        </div>
        <div class="table-responsive teacher-attendance-table">
            <table class="table align-middle" id="attendanceStudents">
                <thead><tr><th>Student</th><th class="attendance-options">Present</th><th class="attendance-options">Late</th><th class="attendance-options">Absent</th><th class="remarks-cell">Remarks</th></tr></thead>
                <tbody>
                @forelse($students as $student)
                    @php($saved = $student->attendances->first())
                    @php($status = $saved->status ?? 'Present')
                    <tr>
                        <td><span class="student-name">{{ $student->last_name }}, {{ $student->first_name }}</span><span class="meta-line">{{ $student->student_id }} / {{ $student->year_level }}-{{ $student->section }}</span></td>
                        @foreach(['Present', 'Late', 'Absent'] as $option)
                            <td><input class="form-check-input" type="radio" name="visual_status_table[{{ $student->student_id }}]" value="{{ $option }}" data-attendance-status-option="{{ $student->student_id }}" @checked($saved && $status === $option)></td>
                        @endforeach
                        <td><input class="form-control" name="remarks[{{ $student->student_id }}]" value="{{ $saved->remarks ?? '' }}"></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center empty-state py-4">No assigned students found. Ask the admin to set your curriculum load first.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="teacher-save-bar">
            <button type="submit" class="btn btn-primary" @disabled($students->isEmpty() || !$subjectId || !$assignment)>Save Attendance</button>
        </div>
    </form>
</section>
@endsection
