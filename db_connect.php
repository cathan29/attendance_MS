<?php
/*
 * Attendance Monitoring System
 * Master Database Connection File
 */

$host = 'localhost';
$dbname = 'attendance_MS';
$username = 'root'; // Default XAMPP username
$password = '';     // Default XAMPP password is empty

try {
    // Note the DOUBLE QUOTES on the string below!
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}
?>