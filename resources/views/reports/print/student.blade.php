<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Attendance Report - {{ $student->student_id }}</title>
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/app.css') }}">
</head>
<body class="print-report-page">
<main class="print-report">
    <div class="print-actions"><button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button></div>
    <header class="print-header">
        <div>
            <h1>Parent Attendance Summary</h1>
            <p>{{ $dateFrom }} to {{ $dateTo }}</p>
        </div>
        <strong>Cipher Academy</strong>
    </header>
    <section class="print-profile">
        <div><span>Student</span><strong>{{ $student->last_name }}, {{ $student->first_name }}</strong></div>
        <div><span>Student ID</span><strong>{{ $student->student_id }}</strong></div>
        <div><span>Class</span><strong>Grade {{ $student->year_level }} {{ $student->strand->strand_name }}-{{ $student->section }}</strong></div>
        <div><span>Attendance Rate</span><strong>{{ $summary['attendanceRate'] }}%</strong></div>
    </section>
    <section class="print-summary">
        <article><span>Total</span><strong>{{ $summary['total'] }}</strong></article>
        <article><span>Present</span><strong>{{ $summary['present'] }}</strong></article>
        <article><span>Late</span><strong>{{ $summary['late'] }}</strong></article>
        <article><span>Absent</span><strong>{{ $summary['absent'] }}</strong></article>
    </section>
    <table class="print-table">
        <thead><tr><th>Date</th><th>Subject</th><th>Schedule</th><th>Teacher</th><th>Status</th><th>Remarks</th></tr></thead>
        <tbody>
        @forelse($records as $record)
            <tr>
                <td>{{ $record->attendance_date->toDateString() }}</td>
                <td>{{ $record->subject->subject_name }}</td>
                <td>{{ $record->schedule ? \Carbon\Carbon::parse($record->schedule->start_time)->format('h:i A') . ' / ' . ($record->schedule->room ?: 'TBA') : 'N/A' }}</td>
                <td>{{ $record->teacher->last_name }}, {{ $record->teacher->first_name }}</td>
                <td>{{ $record->status }}</td>
                <td>{{ $record->remarks }}</td>
            </tr>
        @empty
            <tr><td colspan="6">No records found.</td></tr>
        @endforelse
        </tbody>
    </table>
</main>
</body>
</html>
