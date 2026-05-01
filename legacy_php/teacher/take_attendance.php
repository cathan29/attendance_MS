<?php
require_once '../auth.check.php';
require_login(['teacher']);
require_once '../db_connect.php';

$pageTitle = 'Take Attendance';
$teacherId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $attendanceDate = $_POST['attendance_date'] ?? date('Y-m-d');
    $subjectId = (int) ($_POST['subject_id'] ?? 0);
    $statuses = $_POST['status'] ?? [];
    $remarks = $_POST['remarks'] ?? [];

    try {
        $pdo->beginTransaction();
        $stmt = $pdo->prepare("
            INSERT INTO attendance (student_id, teacher_id, subject_id, attendance_date, status, remarks)
            VALUES (:student_id, :teacher_id, :subject_id, :attendance_date, :status, :remarks)
            ON DUPLICATE KEY UPDATE
                teacher_id = VALUES(teacher_id),
                status = VALUES(status),
                remarks = VALUES(remarks)
        ");

        foreach ($statuses as $studentId => $status) {
            if (!in_array($status, ['Present', 'Late', 'Absent'], true)) {
                continue;
            }

            $stmt->execute([
                'student_id' => $studentId,
                'teacher_id' => $teacherId,
                'subject_id' => $subjectId,
                'attendance_date' => $attendanceDate,
                'status' => $status,
                'remarks' => trim($remarks[$studentId] ?? '') ?: null,
            ]);
        }

        $pdo->commit();
        flash('success', 'Attendance saved successfully.');
    } catch (PDOException $e) {
        $pdo->rollBack();
        flash('danger', 'Attendance was not saved. Please choose a subject and try again.');
    }

    redirect('take_attendance.php?subject_id=' . $subjectId . '&attendance_date=' . urlencode($attendanceDate));
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY subject_name")->fetchAll();
$strands = $pdo->query("SELECT * FROM strands ORDER BY strand_name")->fetchAll();

$subjectId = (int) ($_GET['subject_id'] ?? ($subjects[0]['id'] ?? 0));
$attendanceDate = $_GET['attendance_date'] ?? date('Y-m-d');
$strandId = (int) ($_GET['strand_id'] ?? 0);
$yearLevel = $_GET['year_level'] ?? '';
$section = trim($_GET['section'] ?? '');

$where = [];
$params = [];
if ($strandId > 0) {
    $where[] = 's.strand_id = :strand_id';
    $params['strand_id'] = $strandId;
}
if (in_array($yearLevel, ['11', '12'], true)) {
    $where[] = 's.year_level = :year_level';
    $params['year_level'] = $yearLevel;
}
if ($section !== '') {
    $where[] = 's.section = :section';
    $params['section'] = $section;
}

$sql = "
    SELECT s.*, a.status AS saved_status, a.remarks AS saved_remarks
    FROM students s
    LEFT JOIN attendance a
        ON a.student_id = s.student_id
        AND a.subject_id = :subject_id
        AND a.attendance_date = :attendance_date
";
$params['subject_id'] = $subjectId;
$params['attendance_date'] = $attendanceDate;
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY s.year_level, s.section, s.last_name, s.first_name';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
$flash = flash();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="page-header">
    <div>
        <h1 class="h3 mb-1">Take Attendance</h1>
        <p class="text-muted mb-0">Filter a class, choose a subject, then save one record per student.</p>
    </div>
</div>

<?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

<section class="panel p-4 mb-4">
    <form method="get" class="action-bar">
        <div>
            <label class="form-label">Subject</label>
            <select class="form-select" name="subject_id" required>
                <?php foreach ($subjects as $subject): ?>
                    <option value="<?= e($subject['id']) ?>" <?= $subjectId === (int) $subject['id'] ? 'selected' : '' ?>><?= e($subject['subject_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><label class="form-label">Date</label><input type="date" class="form-control" name="attendance_date" value="<?= e($attendanceDate) ?>"></div>
        <div>
            <label class="form-label">Strand</label>
            <select class="form-select" name="strand_id">
                <option value="0">All</option>
                <?php foreach ($strands as $strand): ?><option value="<?= e($strand['id']) ?>" <?= $strandId === (int) $strand['id'] ? 'selected' : '' ?>><?= e($strand['strand_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div><label class="form-label">Year</label><select class="form-select" name="year_level"><option value="">All</option><option <?= $yearLevel === '11' ? 'selected' : '' ?>>11</option><option <?= $yearLevel === '12' ? 'selected' : '' ?>>12</option></select></div>
        <div><label class="form-label">Section</label><input class="form-control" name="section" value="<?= e($section) ?>"></div>
        <button class="btn btn-outline-primary">Load Class</button>
    </form>
</section>

<section class="panel p-4">
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="subject_id" value="<?= e($subjectId) ?>">
        <input type="hidden" name="attendance_date" value="<?= e($attendanceDate) ?>">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Student</th><th>Present</th><th>Late</th><th>Absent</th><th>Remarks</th></tr></thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <?php $status = $student['saved_status'] ?: 'Present'; ?>
                        <tr>
                            <td><strong><?= e($student['last_name'] . ', ' . $student['first_name']) ?></strong><br><span class="text-muted"><?= e($student['student_id'] . ' / ' . $student['year_level'] . '-' . $student['section']) ?></span></td>
                            <?php foreach (['Present', 'Late', 'Absent'] as $option): ?>
                                <td><input class="form-check-input" type="radio" name="status[<?= e($student['student_id']) ?>]" value="<?= e($option) ?>" <?= $status === $option ? 'checked' : '' ?>></td>
                            <?php endforeach; ?>
                            <td><input class="form-control" name="remarks[<?= e($student['student_id']) ?>]" value="<?= e($student['saved_remarks']) ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$students): ?><tr><td colspan="5" class="text-center text-muted py-4">No students match this filter.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
        <button class="btn btn-primary" <?= (!$students || !$subjectId) ? 'disabled' : '' ?>>Save Attendance</button>
    </form>
</section>

<?php include '../includes/footer.php'; ?>
