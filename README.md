# Cipher Academy Attendance Management System

Cipher Academy Attendance Management System is a Laravel-based web application for managing senior high school students, teachers, class assignments, schedules, attendance records, reports, and audit logs.

The system supports two main user roles: **Admin** and **Teacher**. Admin users manage the school setup and monitor records, while teacher users submit attendance for their assigned classes.

## Main Features

- Admin and teacher authentication
- Role-based access control
- Student enrollment and section management
- Teacher account management
- Strand and subject-based curriculum assignment
- Editable curriculum subject list
- Class scheduling with conflict checking
- Teacher attendance submission
- Attendance review and CSV export
- Dashboard summaries for admins and teachers
- Printable student and section reports
- Audit trail for important system actions
- Configurable sections and semester date ranges

## System Flow

### 1. Login Flow

Users access the system through the login page.

The system accepts either an employee ID or an email address. After a successful login, the user is redirected based on their role:

- Admin users are redirected to the admin dashboard.
- Teacher users are redirected to the teacher dashboard.

Only active accounts can log in. Role middleware prevents teachers from accessing admin pages and prevents admins from using teacher-only attendance submission pages.

### 2. Admin Setup Flow

The admin prepares the school data before teachers can submit attendance.

The usual admin setup flow is:

1. Create or manage teacher accounts.
2. Enroll students.
3. Assign students to a strand, grade level, and section.
4. Create class assignments by connecting a teacher, subject, strand, grade level, section, school year, and semester.
5. Add class schedules for each class assignment.
6. Monitor attendance submissions, reports, and audit logs.

### 3. Student Management Flow

Admins can enroll students into the system.

Each student record contains:

- Student ID
- First name, middle name, and last name
- Strand
- Grade level
- Section

Student IDs are generated based on the current year. Students are grouped by strand, grade level, and section, such as `Grade 11 STEM-A`.

Sections are configurable in `config/school.php`, so the system can support more sections when enrollment grows.

### 4. Teacher Management Flow

Admins can create teacher accounts.

When a teacher is created, the system generates:

- Employee ID
- Temporary password

Teachers can be marked as active or inactive. Admins can also reset a teacher password when needed. Teacher account actions are recorded in the audit trail.

### 5. Curriculum and Class Assignment Flow

Admins can manage the active curriculum subject list before assigning teachers to classes.

The curriculum subject list supports:

- Adding a new subject to a strand and grade level
- Editing an existing curriculum subject entry
- Removing a subject from the active curriculum

Removing a subject from the active curriculum does not delete old class assignments, attendance records, or reports. It only prevents the removed subject from being used in new curriculum assignments.

Class assignments define which teacher handles a specific class.

A class assignment connects:

- Teacher
- Subject
- Strand
- Grade level
- Section
- School year
- Semester
- Semester start date
- Semester end date

Example:

`Maria Santos teaches General Mathematics to Grade 11 STEM-A for 1st Semester, School Year 2026-2027.`

Teachers can only take attendance for classes assigned to them.

### 6. Semester Flow

Semester defaults are configured in `config/school.php`.

Each semester has:

- Start date
- End date

When an admin selects a semester in the curriculum form, the system automatically fills in the default start and end dates. These dates can still be adjusted per class assignment if needed.

This allows the system to clearly identify the coverage period of the 1st Semester and 2nd Semester.

### 7. Schedule Management Flow

Admins can create schedules for class assignments.

Each schedule contains:

- Class assignment
- Day of the week
- Start time
- End time
- Room

The system checks for schedule conflicts. It prevents overlapping schedules for the same teacher or the same room.

### 8. Teacher Attendance Flow

Teachers submit attendance through the teacher attendance page.

The teacher attendance flow is:

1. The teacher logs in.
2. The teacher selects one of their assigned classes.
3. The teacher selects an attendance date.
4. The system loads the students who match the selected class assignment.
5. The teacher marks each student as Present, Late, or Absent.
6. The teacher may add remarks.
7. The teacher saves the attendance.

The system only loads students who match the class assignment's strand, grade level, and section.

Each student can only have one attendance record per date and subject. If the teacher submits attendance again for the same date and subject, the existing record is updated instead of creating a duplicate.

### 9. Attendance Records Flow

Saved attendance records are linked to:

- Student
- Teacher
- Subject
- Attendance date
- Status
- Remarks
- Class schedule, when available

Admins can view attendance records, filter them, search them, and export them as a CSV file.

### 10. Dashboard Flow

The admin dashboard provides a summary of system activity.

It shows:

- Total students
- Total teachers
- Total subjects
- Attendance submitted today
- Missing attendance submissions
- Watchlist students
- Absent students today
- Late students today
- Recent attendance records
- Recent audit logs

The teacher dashboard shows the teacher's own attendance summary and recent submission history.

### 11. Reports Flow

The reports module uses attendance data to generate summaries.

Reports include:

- Attendance totals
- Present, late, and absent counts
- Attendance rate
- Daily attendance trends
- Subject breakdown
- Class breakdown
- Teacher breakdown
- At-risk students
- Printable student attendance report
- Printable section attendance report

Reports are computed from actual attendance records.

### 12. Audit Trail Flow

Important system actions are recorded in the audit trail.

Logged actions include:

- Student creation, update, and deletion
- Teacher creation, deletion, and password reset
- Curriculum assignment creation, update, and removal
- Schedule creation and deletion
- Attendance creation and update

The audit trail helps admins track who made changes and when they happened.

## Data Relationship Overview

The main data flow is:

`Teacher + Subject + Strand + Grade + Section + Semester` creates a class assignment.

`Class Assignment + Day + Time + Room` creates a class schedule.

`Class Assignment + Students` allows a teacher to submit attendance.

`Attendance Records` power the dashboards, reports, exports, and audit monitoring.

## Setup

1. Start MySQL in XAMPP.
2. Create the database if it does not exist:

```sql
CREATE DATABASE attendance_ms_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

3. Install PHP dependencies:

```bash
composer install
```

4. Install JavaScript dependencies:

```bash
npm install
```

5. Run migrations and seed the default data:

```bash
php artisan migrate:fresh --seed
```

6. Build frontend assets:

```bash
npm run build
```

7. Start the Laravel development server:

```bash
php artisan serve
```

8. Open the application:

```text
http://127.0.0.1:8000
```

## Default Login Credentials

The login form accepts either an email address or an employee ID.

### Admin Login

```text
Employee ID: ADMIN-001
Email: admin@cipheracademy.edu
Password: Admin@123
```

Change the default password after first login.

### Seeded Teacher Logins

Seeded teacher accounts use the following sample credentials:

```text
Employee ID: CA-T-001
Email: maria.santos@cipheracademy.edu
Password: Cipher@1001

Employee ID: CA-T-002
Email: jose.reyes@cipheracademy.edu
Password: Cipher@1002

Employee ID: CA-T-003
Email: ana.cruz@cipheracademy.edu
Password: Cipher@1003

Employee ID: CA-T-004
Email: ramon.garcia@cipheracademy.edu
Password: Cipher@1004

Employee ID: CA-T-005
Email: leah.flores@cipheracademy.edu
Password: Cipher@1005

Employee ID: CA-T-006
Email: paolo.navarro@cipheracademy.edu
Password: Cipher@1006

Employee ID: CA-T-007
Email: clarissa.mendoza@cipheracademy.edu
Password: Cipher@1007

Employee ID: CA-T-008
Email: victor.torres@cipheracademy.edu
Password: Cipher@1008
```

Teachers are required to update their password after receiving temporary credentials.

## Configuration Notes

Sections and semester date defaults are located in:

```text
config/school.php
```

Use this file to update available sections and semester start/end dates.
