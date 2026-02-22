<?php
// Start the session so we can remember who logged in
session_start();
require_once 'db_connect.php';

$error = '';

// Check if the user is already logged in, redirect them if they are
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/admin_dashboard.php");
    } else {
        header("Location: teacher/teacher_dashboard.php");
    }
    exit();
}

// Process the form when the user clicks "Log In"
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $employee_id = trim($_POST['employee_id']);
    $password = $_POST['password'];

    if (empty($employee_id) || empty($password)) {
        $error = 'Please enter both Employee ID and Password.';
    } else {
        // Prepare the SQL statement to prevent SQL Injection hackers
        $stmt = $pdo->prepare("SELECT * FROM users WHERE employee_id = :employee_id AND status = 'active'");
        $stmt->execute(['employee_id' => $employee_id]);
        $user = $stmt->fetch();

        // Check if user exists AND the hashed password matches
        if ($user && password_verify($password, $user['password'])) {
            
            // Set the secure session variables
            $_SESSION['user_id'] = $user['employee_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];

            // The Routing Engine: Send them to the correct dashboard
            if ($user['role'] === 'admin') {
                header("Location: admin/admin_dashboard.php");
            } else {
                header("Location: teacher/teacher_dashboard.php");
            }
            exit();
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
    <style>
        /* A little extra CSS just to center the login box perfectly */
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: var(--bg-body); 
        }
        .login-card {
            width: 100%;
            max-width: 400px;
        }
    </style>
</head>
<body>

<div class="login-card content-card">
    <div class="text-center mb-4">
        <h3 class="text-gradient fw-bold">System Login</h3>
        <p class="text-muted">Attendance Monitoring System</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label for="employee_id" class="form-label fw-bold">Employee ID mhjhjhjghjghjg</label>
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