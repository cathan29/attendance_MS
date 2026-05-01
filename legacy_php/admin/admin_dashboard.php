<?php
require_once '../auth.check.php';
require_login(['admin']);
require_once '../db_connect.php'; 

$pageTitle = 'Admin Dashboard';
$stats = [
    'students' => $pdo->query("SELECT COUNT(*) AS total FROM students")->fetch()['total'] ?? 0,
    'teachers' => $pdo->query("SELECT COUNT(*) AS total FROM users WHERE role = 'teacher'")->fetch()['total'] ?? 0,
    'subjects' => $pdo->query("SELECT COUNT(*) AS total FROM subjects")->fetch()['total'] ?? 0,
    'today' => $pdo->query("SELECT COUNT(*) AS total FROM attendance WHERE attendance_date = CURDATE()")->fetch()['total'] ?? 0,
];
$recent = $pdo->query("
    SELECT a.attendance_date, a.status, s.student_id, s.first_name, s.last_name, sub.subject_name
    FROM attendance a
    JOIN students s ON s.student_id = a.student_id
    JOIN subjects sub ON sub.id = a.subject_id
    ORDER BY a.updated_at DESC
    LIMIT 8
")->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="page-header">
    <div>
        <h1 class="h3 mb-1">Admin Dashboard</h1>
        <p class="text-muted mb-0">Monitor students, teachers, subjects, and daily attendance activity.</p>
    </div>
</div>

<div class="stat-grid">
    <div class="stat-card"><span>Students</span><strong><?= e($stats['students']) ?></strong></div>
    <div class="stat-card"><span>Teachers</span><strong><?= e($stats['teachers']) ?></strong></div>
    <div class="stat-card"><span>Subjects</span><strong><?= e($stats['subjects']) ?></strong></div>
    <div class="stat-card"><span>Records Today</span><strong><?= e($stats['today']) ?></strong></div>
</div>

<section class="panel p-4">
    <h2 class="h5 mb-3">Recent Attendance</h2>
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Date</th><th>Student</th><th>Subject</th><th>Status</th></tr></thead>
            <tbody>
                <?php foreach ($recent as $row): ?>
                    <tr>
                        <td><?= e($row['attendance_date']) ?></td>
                        <td><?= e($row['last_name'] . ', ' . $row['first_name']) ?> <span class="text-muted"><?= e($row['student_id']) ?></span></td>
                        <td><?= e($row['subject_name']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= e($row['status']) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$recent): ?>
                    <tr><td colspan="4" class="text-center text-muted py-4">No attendance records yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
