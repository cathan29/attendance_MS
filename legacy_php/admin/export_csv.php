<?php
require_once '../auth.check.php';
require_login(['admin']);
require_once '../db_connect.php';

$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$subjectId = (int) ($_GET['subject_id'] ?? 0);

$where = ['a.attendance_date BETWEEN :date_from AND :date_to'];
$params = ['date_from' => $dateFrom, 'date_to' => $dateTo];
if (in_array($status, ['Present', 'Late', 'Absent'], true)) {
    $where[] = 'a.status = :status';
    $params['status'] = $status;
}
if ($subjectId > 0) {
    $where[] = 'a.subject_id = :subject_id';
    $params['subject_id'] = $subjectId;
}

$stmt = $pdo->prepare("
    SELECT a.attendance_date, s.student_id, s.last_name, s.first_name, s.year_level, s.section,
           sub.subject_name, u.employee_id AS teacher_id, u.last_name AS teacher_last, u.first_name AS teacher_first,
           a.status, a.remarks
    FROM attendance a
    JOIN students s ON s.student_id = a.student_id
    JOIN subjects sub ON sub.id = a.subject_id
    JOIN users u ON u.employee_id = a.teacher_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY a.attendance_date DESC, s.last_name, s.first_name
");
$stmt->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=attendance_' . date('Ymd_His') . '.csv');

$out = fopen('php://output', 'w');
fputcsv($out, ['Date', 'Student ID', 'Last Name', 'First Name', 'Year', 'Section', 'Subject', 'Teacher ID', 'Teacher Name', 'Status', 'Remarks']);
while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['attendance_date'],
        $row['student_id'],
        $row['last_name'],
        $row['first_name'],
        $row['year_level'],
        $row['section'],
        $row['subject_name'],
        $row['teacher_id'],
        $row['teacher_last'] . ', ' . $row['teacher_first'],
        $row['status'],
        $row['remarks'],
    ]);
}
fclose($out);
exit();
