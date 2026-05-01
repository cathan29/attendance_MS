# Attendance Monitoring System

This is a 2-tier Admin and Teacher attendance system built with PHP, MySQL, Bootstrap, and XAMPP.

## Current Features

* Secure admin/teacher login with hashed passwords and CSRF-protected forms.
* Admin dashboard with student, teacher, subject, and attendance totals.
* Student management: add, update, list, and delete students.
* Teacher management: add, update, activate/deactivate, and delete teacher accounts.
* Teacher dashboard with daily attendance summary.
* Attendance taking by subject, date, strand, year level, and section.
* Attendance viewer with filters and CSV export.

## Initial Setup

1. Start Apache and MySQL in XAMPP.
2. Open phpMyAdmin at `http://localhost/phpmyadmin`.
3. Import `database/schema.sql`.
4. Visit `http://localhost/attendance_MS`.

## Default Fresh-Install Login

* Employee ID: `ADMIN-001`
* Password: `Admin@123`

Change this password after first login.

## Daily Workflow

1. `git checkout main`
2. `git pull origin main`
3. `git checkout -b your-branch-name`
4. Make and test your changes.
5. `git add .`
6. `git commit -m "Brief description of what you finished"`
7. `git push origin your-branch-name`

## Laravel Upgrade Note

The Laravel version now lives in `laravel_app`.

### Run The Laravel Version

1. Start MySQL in XAMPP.
2. Create and seed the Laravel database:
   `cd laravel_app`
   `php artisan migrate:fresh --seed`
3. Start Laravel:
   `php artisan serve`
4. Open `http://127.0.0.1:8000`.

Laravel default login:

* Employee ID: `ADMIN-001`
* Password: `Admin@123`
