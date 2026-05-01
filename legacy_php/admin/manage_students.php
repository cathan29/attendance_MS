<?php
require_once '../auth.check.php';
require_login(['admin']);
require_once '../db_connect.php'; 

$pageTitle = 'Manage Students';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $stmt = $pdo->prepare("
                INSERT INTO students (student_id, first_name, middle_name, last_name, strand_id, year_level, section)
                VALUES (:student_id, :first_name, :middle_name, :last_name, :strand_id, :year_level, :section)
                ON DUPLICATE KEY UPDATE
                    first_name = VALUES(first_name),
                    middle_name = VALUES(middle_name),
                    last_name = VALUES(last_name),
                    strand_id = VALUES(strand_id),
                    year_level = VALUES(year_level),
                    section = VALUES(section)
            ");
            $stmt->execute([
                'student_id' => trim($_POST['student_id'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'middle_name' => trim($_POST['middle_name'] ?? '') ?: null,
                'last_name' => trim($_POST['last_name'] ?? ''),
                'strand_id' => (int) ($_POST['strand_id'] ?? 0),
                'year_level' => $_POST['year_level'] ?? '11',
                'section' => trim($_POST['section'] ?? '') ?: null,
            ]);
            flash('success', 'Student saved successfully.');
        }

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = :student_id");
            $stmt->execute(['student_id' => $_POST['student_id'] ?? '']);
            flash('success', 'Student deleted.');
        }
    } catch (PDOException $e) {
        flash('danger', 'Unable to save student. Please check the form and try again.');
    }

    redirect('manage_students.php');
}

$strands = $pdo->query("SELECT * FROM strands ORDER BY strand_name")->fetchAll();
$students = $pdo->query("
    SELECT s.*, st.strand_name
    FROM students s
    JOIN strands st ON st.id = s.strand_id
    ORDER BY s.year_level, s.section, s.last_name
")->fetchAll();
$flash = flash();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="page-header">
    <div>
        <h1 class="h3 mb-1">Students</h1>
        <p class="text-muted mb-0">Add, update, and remove student records.</p>
    </div>
</div>

<?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

<section class="panel p-4 mb-4">
    <h2 class="h5 mb-3">Student Form</h2>
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <div class="col-md-3"><label class="form-label">Student ID</label><input class="form-control" name="student_id" required></div>
        <div class="col-md-3"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
        <div class="col-md-3"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name"></div>
        <div class="col-md-3"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
        <div class="col-md-3">
            <label class="form-label">Strand</label>
            <select class="form-select" name="strand_id" required>
                <?php foreach ($strands as $strand): ?><option value="<?= e($strand['id']) ?>"><?= e($strand['strand_name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2"><label class="form-label">Year</label><select class="form-select" name="year_level"><option>11</option><option>12</option></select></div>
        <div class="col-md-4"><label class="form-label">Section</label><input class="form-control" name="section" placeholder="e.g. 11-A"></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Save Student</button></div>
    </form>
</section>

<section class="panel p-4">
    <div class="table-responsive">
        <table class="table align-middle">
            <thead><tr><th>ID</th><th>Name</th><th>Strand</th><th>Year/Section</th><th></th></tr></thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= e($student['student_id']) ?></td>
                        <td><?= e($student['last_name'] . ', ' . $student['first_name']) ?></td>
                        <td><?= e($student['strand_name']) ?></td>
                        <td><?= e($student['year_level'] . ' - ' . $student['section']) ?></td>
                        <td class="text-end">
                            <form method="post" onsubmit="return confirm('Delete this student?')">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="student_id" value="<?= e($student['student_id']) ?>">
                                <button class="btn btn-sm btn-outline-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$students): ?><tr><td colspan="5" class="text-center text-muted py-4">No students yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include '../includes/footer.php'; ?>
