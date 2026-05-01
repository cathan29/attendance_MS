<?php
require_once '../auth.check.php';
require_login(['teacher']);
require_once '../db_connect.php'; 

$pageTitle = 'Teacher Dashboard';
$teacherId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT
        COUNT(*) AS total,
        SUM(status = 'Present') AS present_count,
        SUM(status = 'Late') AS late_count,
        SUM(status = 'Absent') AS absent_count
    FROM attendance
    WHERE teacher_id = :teacher_id AND attendance_date = CURDATE()
");
$stmt->execute(['teacher_id' => $teacherId]);
$today = $stmt->fetch() ?: ['total' => 0, 'present_count' => 0, 'late_count' => 0, 'absent_count' => 0];
$stmt = $pdo->prepare("
    SELECT a.attendance_date, sub.subject_name, COUNT(*) AS records
    FROM attendance a
    JOIN subjects sub ON sub.id = a.subject_id
    WHERE a.teacher_id = :teacher_id
    GROUP BY a.attendance_date, sub.subject_name
    ORDER BY a.attendance_date DESC
    LIMIT 10
");
$stmt->execute(['teacher_id' => $teacherId]);
$history = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="page-header">
    <div>
        <h1 class="h3 mb-1">Teacher Dashboard</h1>
        <p class="text-muted mb-0">Welcome, <?= e($_SESSION['name'] ?? 'Teacher') ?>.</p>
    </div>
    <a class="btn btn-primary" href="take_attendance.php">Take Attendance</a>
</div>

<div class="stat-grid">
    <div class="stat-card"><span>Today Total</span><strong><?= e($today['total'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Present</span><strong><?= e($today['present_count'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Late</span><strong><?= e($today['late_count'] ?? 0) ?></strong></div>
    <div class="stat-card"><span>Absent</span><strong><?= e($today['absent_count'] ?? 0) ?></strong></div>
</div>

<section class="panel p-4">
    <h2 class="h5 mb-3">Recent Submissions</h2>
    <table class="table align-middle">
        <thead><tr><th>Date</th><th>Subject</th><th>Records</th></tr></thead>
        <tbody>
            <?php foreach ($history as $row): ?>
                <tr><td><?= e($row['attendance_date']) ?></td><td><?= e($row['subject_name']) ?></td><td><?= e($row['records']) ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$history): ?>
                <tr><td colspan="3" class="text-center text-muted py-4">No submissions yet.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
