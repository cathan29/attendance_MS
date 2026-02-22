# 🏫 Attendance Monitoring System

Welcome to the team repository! This is a 2-tier system (Admin & Teacher) built with PHP, MySQL, Bootstrap, and XAMPP.

## 🚀 Initial Setup (Do this only ONCE)
1. Make sure **Apache** and **MySQL** are running in your XAMPP Control Panel.
2. Open your terminal inside your `C:\xampp\htdocs` folder and clone this repo: 
   `git clone https://github.com/cathan29/attendance_MS.git`
3. Open phpMyAdmin (`http://localhost/phpmyadmin`), create a database named `attendance_MS`, and import the `database/schema.sql` file.

---

## ☀️ Daily Workflow (Do this EVERY DAY)
**Rule #1: Always get the latest code from the Team Leader before you start typing to avoid code conflicts!**

### Step 1: Pull the latest master code
Open your terminal inside the `attendance_MS` folder and run:
`git checkout main`
`git pull origin main`

### Step 2: Go to your assigned branch
Switch to your safe workspace. 
`git checkout -b your-branch-name`
*(Note: If you already created your branch yesterday, just type `git checkout your-branch-name` without the `-b`)*

---

## 📋 Assigned Branches for the Team:
* **UI/UX & Navigation:** `feature-ui-layout`
* **Admin Dashboard:** `feature-admin-dashboard`
* **Teacher Dashboard:** `feature-teacher-dashboard`
* **Student Data Manager:** `feature-manage-students`
* **Attendance Viewer:** `feature-view-attendance`
* **Reporting & Export:** `feature-export-reports`
* **Teacher Account Manager:** `feature-manage-teachers`
* **Authentication & Security:** `feature-login-auth`
* **Bulk Attendance Engine:** `feature-take-attendance`

---

## 💾 How to Save & Submit Your Work:
When your assigned PHP file is working perfectly on your localhost, save it and send it to the Team Leader for review:
1. `git add .`
2. `git commit -m "Brief description of what you finished"`
3. `git push origin your-branch-name`

*Note: If you get a Git error or merge conflict, stop and message the Team Leader in the group chat!*