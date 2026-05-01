<?php
$activePage = basename($_SERVER['PHP_SELF']);
$adminLinks = [
    ['admin_dashboard.php', 'Dashboard'],
    ['manage_students.php', 'Students'],
    ['manage_teachers.php', 'Teachers'],
    ['view_attendance.php', 'Attendance'],
    ['export_csv.php', 'Export CSV'],
];
$teacherLinks = [
    ['teacher_dashboard.php', 'Dashboard'],
    ['take_attendance.php', 'Take Attendance'],
];
$links = ($_SESSION['role'] ?? '') === 'admin' ? $adminLinks : $teacherLinks;
$prefix = ($_SESSION['role'] ?? '') === 'admin' ? '/attendance_MS/admin/' : '/attendance_MS/teacher/';
$nameParts = preg_split('/\s+/', trim($_SESSION['name'] ?? 'User'));
$initials = strtoupper(substr($nameParts[0] ?? 'U', 0, 1) . substr($nameParts[1] ?? '', 0, 1));
?>
<aside class="sidebar">
    <a class="brand" href="<?= $prefix . ($links[0][0] ?? '') ?>">
        <span class="brand-mark">AMS</span>
        <div>
            <strong>Attendance MS</strong>
            <small><?= e(ucfirst($_SESSION['role'] ?? 'user')) ?> workspace</small>
        </div>
    </a>

    <div class="sidebar-note">
        <span class="chip">Live attendance</span>
        <p><?= e(($_SESSION['role'] ?? '') === 'admin' ? 'A focused command center for records, people, and exports.' : 'Fast class roll call with filters, status controls, and notes.') ?></p>
    </div>

    <nav class="nav-list">
        <?php foreach ($links as [$file, $label]): ?>
            <a class="<?= $activePage === $file ? 'active' : '' ?>" href="<?= $prefix . $file ?>"><?= e($label) ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="sidebar-user">
        <span class="avatar"><?= e($initials) ?></span>
        <span class="user-copy">
            <strong><?= e($_SESSION['name'] ?? 'User') ?></strong>
            <small><?= e(ucfirst($_SESSION['role'] ?? 'Member')) ?></small>
        </span>
        <span class="role-chip"><?= e(ucfirst($_SESSION['role'] ?? 'User')) ?></span>
        <a class="logout-button" href="/attendance_MS/logout.php">Logout</a>
    </div>
</aside>
<main class="main-content">
