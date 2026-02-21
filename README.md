# 🏫 Attendance Monitoring System

Welcome to the team repository! This is a 2-tier system (Admin & Teacher) built with PHP, MySQL, Bootstrap, and XAMPP.

## 🚀 Getting Started (Daily Workflow)
1. Make sure **Apache** and **MySQL** are running in your XAMPP Control Panel.
2. Open your terminal inside your `C:\xampp\htdocs` folder and clone this repo: 
   `git clone https://github.com/cathan29/attendance_MS.git`
3. Open phpMyAdmin (`http://localhost/phpmyadmin`), create a database named `attendance_MS`, and import the `database/schema.sql` file.

## 🌿 Git Branching Rules
**DO NOT PUSH DIRECTLY TO MAIN.** Always work in your assigned branch to prevent code conflicts. 

To start working, open your VS Code terminal inside the `attendance_MS` folder and type:
`git checkout -b your-branch-name`

### 📋 Assigned Branches for the Team:
* **UI/UX & Navigation:** `git checkout -b feature-ui-layout`
* **Admin Dashboard:** `git checkout -b feature-admin-dashboard`
* **Teacher Dashboard:** `git checkout -b feature-teacher-dashboard`
* **Student Data Manager:** `git checkout -b feature-manage-students`
* **Attendance Viewer:** `git checkout -b feature-view-attendance`
* **Reporting & Export:** `git checkout -b feature-export-reports`
* **Teacher Account Manager:** `git checkout -b feature-manage-teachers`
* **Authentication & Security:** `git checkout -b feature-login-auth`
* **Bulk Attendance Engine:** `git checkout -b feature-take-attendance`

### 💾 How to Save & Push Your Work:
When your assigned PHP file is working perfectly on your localhost, save it and send it to the Team Leader for review:
1. `git add .`
2. `git commit -m "Brief description of what you finished"`
3. `git push origin your-branch-name`

*Note: If you get a Git error or merge conflict, stop and message the Team Leader in the group chat!*