# Cipher Academy

This project is now a Laravel attendance management system for Cipher Academy.

The old plain-PHP/XAMPP version was preserved in `legacy_php` so the previous work is still available for reference.

## Features

* Admin and teacher login with Laravel authentication.
* Role-based access for admin and teacher dashboards.
* Student management.
* Teacher account management.
* Attendance taking by subject, date, strand, year level, and section.
* Attendance review and CSV export.
* Laravel migrations, seeders, controllers, models, middleware, and Blade views.

## Setup

1. Start MySQL in XAMPP.
2. Create the database if it does not exist:
   `CREATE DATABASE attendance_ms_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
3. Install PHP dependencies if needed:
   `composer install`
4. Run migrations and seed the default data:
   `php artisan migrate:fresh --seed`
5. Start the app:
   `php artisan serve`
6. Open `http://127.0.0.1:8000`.

## Default Login

* Employee ID: `ADMIN-001`
* Password: `Admin@123`

Change the default password after first login.
