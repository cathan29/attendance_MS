<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Section Attendance Report - {{ $classLabel }}</title>
    <link rel="stylesheet" href="{{ Vite::asset('resources/css/app.css') }}">
</head>
<body class="print-report-page">
<main class="print-report">
    <div class="print-actions"><button class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button></div>
    <header class="print-header">
        <div>
            <h1>Monthly Section Attendance Report</h1>
            <p>{{ $classLabel }} / {{ $dateFrom }} to {{ $dateTo }}</p>
        </div>
        <strong>Cipher Academy</strong>
    </header>
    <table class="print-table">
        <thead><tr><th>Student</th><th>ID</th><th>Total</th><th>Present</th><th>Late</th><th>Absent</th><th>Rate</th></tr></thead>
        <tbody>
        @forelse($students as $student)
            @php($studentRecords = $records->where('student_id', $student->student_id))
            @php($total = $studentRecords->count())
            @php($present = $studentRecords->where('status', 'Present')->count())
            @php($late = $studentRecords->where('status', 'Late')->count())
            @php($absent = $studentRecords->where('status', 'Absent')->count())
            @php($rate = $total > 0 ? round((($present + $late) / $total) * 100, 1) : 0)
            <tr>
                <td>{{ $student->last_name }}, {{ $student->first_name }}</td>
                <td>{{ $student->student_id }}</td>
                <td>{{ $total }}</td>
                <td>{{ $present }}</td>
                <td>{{ $late }}</td>
                <td>{{ $absent }}</td>
                <td>{{ $rate }}%</td>
            </tr>
        @empty
            <tr><td colspan="7">No students found.</td></tr>
        @endforelse
        </tbody>
    </table>
</main>
</body>
</html>
