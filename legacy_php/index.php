<?php
require_once 'auth.check.php';

if (!empty($_SESSION['user_id'])) {
    redirect(($_SESSION['role'] ?? '') === 'admin' ? 'admin/admin_dashboard.php' : 'teacher/teacher_dashboard.php');
}

redirect('login.php');
