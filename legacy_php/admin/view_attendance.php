<?php
require_once '../auth.check.php';
require_login(['admin']);
require_once '../db_connect.php'; 

$pageTitle = 'View Attendance';
$dateFrom = $_GET['date_from'] ?? date('Y-m-01');
$dateTo = $_GET['date_to'] ?? date('Y-m-d');
$status = $_GET['status'] ?? '';
$subjectId = (int) ($_GET['subject_id'] ?? 0);

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();
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
    SELECT a.*, s.first_name, s.last_name, s.year_level, s.section, sub.subject_name, u.first_name AS teacher_first, u.last_name AS teacher_last
    FROM attendance a
    JOIN students s ON s.student_id = a.student_id
    JOIN subjects sub ON sub.id = a.subject_id
    JOIN users u ON u.employee_id = a.teacher_id
    WHERE " . implode(' AND ', $where) . "
    ORDER BY a.attendance_date DESC, s.last_name, s.first_name
");
$stmt->execute($params);
$records = $stmt->fetchAll();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="page-header">
    <div>
        <h1 class="h3 mb-1">Attendance Records</h1>
        <p class="text-muted mb-0">Search and review submitted attendance.</p>
    </div>
    <a class="btn btn-outline-primary" href="export_csv.php?<?= e(http_build_query($_GET)) ?>">Export CSV</a>
</div>

<section class="panel p-4 mb-4">
    <form method="get" class="action-bar">
        <div><label class="form-label">From</label><input type="date" class="form-control" name="date_from" value="<?= e($dateFrom) ?>"></div>
        <div><label class="form-label">To</label><input type="date" class="form-control" name="date_to" value="<?= e($dateTo) ?>"></div>
        <div><label class="form-label">Status</label><select class="form-select" name="status"><option value="">All</option><?php foreach (['Present', 'Late', 'Absent'] as $opt): ?><option <?= $status === $opt ? 'selected' : '' ?>><?= e($opt) ?></option><?php endforeach; ?></select></div>
        <div><label class="form-label">Subject</label><select class="form-select" name="subject_id"><option value="0">All</option><?php foreach ($subjects as $subject): ?><option value="<?= e($subject['id']) ?>" <?= $subjectId === (int) $subject['id'] ? 'selected' : '' ?>><?= e($subject['subject_name']) ?></option><?php endforeach; ?></select></div>
        <button class="btn btn-primary">Filter</button>
    </form>
</section>

<section class="panel p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>Date</th><th>Student</th><th>Class</th><th>Subject</th><th>Teacher</th><th>Status</th><th>Remarks</th></tr></thead>
            <tbody>
                <?php foreach ($records as $row): ?>
                    <tr>
                        <td><?= e($row['attendance_date']) ?></td>
                        <td><?= e($row['last_name'] . ', ' . $row['first_name']) ?></td>
                        <td><?= e($row['year_level'] . '-' . $row['section']) ?></td>
                        <td><?= e($row['subject_name']) ?></td>
                        <td><?= e($row['teacher_last'] . ', ' . $row['teacher_first']) ?></td>
                        <td><span class="badge text-bg-secondary"><?= e($row['status']) ?></span></td>
                        <td><?= e($row['remarks']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$records): ?><tr><td colspan="7" class="text-center text-muted py-4">No records found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
