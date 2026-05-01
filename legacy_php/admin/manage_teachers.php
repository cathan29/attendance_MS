<?php
require_once '../auth.check.php';
require_login(['admin']);
require_once '../db_connect.php'; 

$pageTitle = 'Manage Teachers';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'save') {
            $passwordSql = !empty($_POST['password']) ? ', password = :password' : '';
            $sql = "
                INSERT INTO users (employee_id, first_name, middle_name, last_name, password, role, status)
                VALUES (:employee_id, :first_name, :middle_name, :last_name, :insert_password, 'teacher', :status)
                ON DUPLICATE KEY UPDATE
                    first_name = VALUES(first_name),
                    middle_name = VALUES(middle_name),
                    last_name = VALUES(last_name),
                    status = VALUES(status)
                    $passwordSql
            ";
            $params = [
                'employee_id' => trim($_POST['employee_id'] ?? ''),
                'first_name' => trim($_POST['first_name'] ?? ''),
                'middle_name' => trim($_POST['middle_name'] ?? '') ?: null,
                'last_name' => trim($_POST['last_name'] ?? ''),
                'insert_password' => password_hash($_POST['password'] ?: 'Teacher@123', PASSWORD_DEFAULT),
                'status' => $_POST['status'] ?? 'active',
            ];
            if (!empty($_POST['password'])) {
                $params['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            flash('success', 'Teacher saved successfully.');
        }

        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM users WHERE employee_id = :employee_id AND role = 'teacher'");
            $stmt->execute(['employee_id' => $_POST['employee_id'] ?? '']);
            flash('success', 'Teacher deleted.');
        }
    } catch (PDOException $e) {
        flash('danger', 'Unable to save teacher. Employee ID may already be used.');
    }

    redirect('manage_teachers.php');
}

$teachers = $pdo->query("SELECT * FROM users WHERE role = 'teacher' ORDER BY last_name, first_name")->fetchAll();
$flash = flash();

include '../includes/header.php';
include '../includes/sidebar.php';
?>
<div class="page-header">
    <div>
        <h1 class="h3 mb-1">Teachers</h1>
        <p class="text-muted mb-0">Create teacher accounts and manage access.</p>
    </div>
</div>

<?php if ($flash): ?><div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div><?php endif; ?>

<section class="panel p-4 mb-4">
    <h2 class="h5 mb-3">Teacher Form</h2>
    <form method="post" class="row g-3">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="save">
        <div class="col-md-3"><label class="form-label">Employee ID</label><input class="form-control" name="employee_id" required></div>
        <div class="col-md-3"><label class="form-label">First Name</label><input class="form-control" name="first_name" required></div>
        <div class="col-md-3"><label class="form-label">Middle Name</label><input class="form-control" name="middle_name"></div>
        <div class="col-md-3"><label class="form-label">Last Name</label><input class="form-control" name="last_name" required></div>
        <div class="col-md-4"><label class="form-label">Password</label><input type="password" class="form-control" name="password" placeholder="Leave blank to keep existing"></div>
        <div class="col-md-3"><label class="form-label">Status</label><select class="form-select" name="status"><option value="active">Active</option><option value="inactive">Inactive</option></select></div>
        <div class="col-md-3 d-flex align-items-end"><button class="btn btn-primary w-100">Save Teacher</button></div>
    </form>
</section>

<section class="panel p-4">
    <table class="table align-middle">
        <thead><tr><th>Employee ID</th><th>Name</th><th>Status</th><th></th></tr></thead>
        <tbody>
            <?php foreach ($teachers as $teacher): ?>
                <tr>
                    <td><?= e($teacher['employee_id']) ?></td>
                    <td><?= e($teacher['last_name'] . ', ' . $teacher['first_name']) ?></td>
                    <td><span class="badge text-bg-<?= $teacher['status'] === 'active' ? 'success' : 'secondary' ?>"><?= e($teacher['status']) ?></span></td>
                    <td class="text-end">
                        <form method="post" onsubmit="return confirm('Delete this teacher?')">
                            <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="employee_id" value="<?= e($teacher['employee_id']) ?>">
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$teachers): ?><tr><td colspan="4" class="text-center text-muted py-4">No teachers yet.</td></tr><?php endif; ?>
        </tbody>
    </table>
</section>

<?php include '../includes/footer.php'; ?>
