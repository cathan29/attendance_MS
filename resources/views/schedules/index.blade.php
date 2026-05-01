@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1>Class Schedules</h1>
        <p class="text-muted mb-0">Set real class days, time slots, and rooms for each curriculum load.</p>
    </div>
</div>

<section class="panel mb-4">
    <h2>Schedule Form</h2>
    <form method="POST" action="{{ route('admin.schedules.store') }}" class="row">
        @csrf
        <div class="col-md-4">
            <label class="form-label">Class Load</label>
            <select class="form-select" name="class_assignment_id" required>
                @foreach($assignments as $assignment)
                    <option value="{{ $assignment->id }}">
                        {{ $assignment->subject->subject_name }} / Grade {{ $assignment->year_level }} {{ $assignment->strand->strand_name }}-{{ $assignment->section }} / {{ $assignment->teacher->last_name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Day</label>
            <select class="form-select" name="day_of_week" required>
                @foreach($days as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Start</label><input type="time" class="form-control" name="start_time" value="08:00" required></div>
        <div class="col-md-2"><label class="form-label">End</label><input type="time" class="form-control" name="end_time" value="09:00" required></div>
        <div class="col-md-1"><label class="form-label">Room</label><input class="form-control" name="room" placeholder="201"></div>
        <div class="col-md-1 d-flex align-items-end"><button class="btn btn-primary w-100">Save</button></div>
    </form>
</section>

<section class="panel">
    <div class="section-title">
        <h2>Weekly Schedule</h2>
        <span class="chip-light">{{ $schedules->count() }} slots</span>
    </div>
    <form method="GET" action="{{ route('admin.schedules.index') }}" class="search-bar">
        <input class="form-control" name="q" value="{{ $search }}" placeholder="Live search teacher, subject, class, room" data-live-search data-live-search-target="#scheduleTable tbody tr">
        <button class="btn btn-outline-primary">Search</button>
        @if($search !== '')
            <a class="btn btn-outline-primary" href="{{ route('admin.schedules.index') }}">Clear</a>
        @endif
    </form>
    <div class="table-responsive">
        <table class="table align-middle" id="scheduleTable">
            <thead><tr><th>Day</th><th>Time</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Room</th><th></th></tr></thead>
            <tbody>
            @forelse($schedules as $schedule)
                <tr>
                    <td>{{ $days[$schedule->day_of_week] }}</td>
                    <td>{{ \Carbon\Carbon::parse($schedule->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('h:i A') }}</td>
                    <td>Grade {{ $schedule->assignment->year_level }} {{ $schedule->assignment->strand->strand_name }}-{{ $schedule->assignment->section }}</td>
                    <td>{{ $schedule->assignment->subject->subject_name }}</td>
                    <td>{{ $schedule->assignment->teacher->last_name }}, {{ $schedule->assignment->teacher->first_name }}</td>
                    <td>{{ $schedule->room ?: 'TBA' }}</td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('admin.schedules.destroy', $schedule) }}" onsubmit="return confirm('Delete this schedule?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center empty-state py-4">No schedules yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
