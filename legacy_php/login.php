<?php
session_start();
require_once 'db_connect.php';
require_once 'auth.check.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    redirect($_SESSION['role'] === 'admin' ? 'admin/admin_dashboard.php' : 'teacher/teacher_dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    verify_csrf();
    $employee_id = trim($_POST['employee_id'] ?? '');
    $password = $_POST['password'];

    if (empty($employee_id) || empty($password)) {
        $error = 'Please enter both Employee ID and Password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE employee_id = :employee_id AND status = 'active'");
        $stmt->execute(['employee_id' => $employee_id]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            redirect($user['role'] === 'admin' ? 'admin/admin_dashboard.php' : 'teacher/teacher_dashboard.php');
        } else {
            $error = 'Invalid Employee ID or Password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance Monitoring System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">

<div class="login-card content-card">
    <div class="text-center mb-4">
        <h3 class="text-gradient fw-bold">System Login</h3>
        <p class="text-muted">Attendance Monitoring System</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= e($error) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <?= csrf_field() ?>
        <div class="mb-3">
            <label for="employee_id" class="form-label fw-bold">Employee ID</label>
            <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="e.g. T-1029" required>
        </div>
        <div class="mb-4">
            <label for="password" class="form-label fw-bold">Password</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn btn-primary w-100 fw-bold">Log In</button>
    </form>
</div>

</body>
</html>
