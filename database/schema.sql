-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS `attendance_MS`;
USE `attendance_MS`;

-- --------------------------------------------------------
-- Table 1: users (Stores Admins and Teachers)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL, 
  `role` enum('admin','teacher') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table 2: students (Stores the Master Roster)
-- --------------------------------------------------------
-- UPDATE: Removed auto-increment. The actual student_id is now the Primary Key.
CREATE TABLE `students` (
  `student_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `section` varchar(50) NOT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table 3: attendance (Stores the Daily Records)
-- --------------------------------------------------------
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) NOT NULL,
  `teacher_id` varchar(50) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Late','Absent') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_student` (`student_id`),
  KEY `fk_teacher` (`teacher_id`),
  CONSTRAINT `fk_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`employee_id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_attendance_per_day` (`student_id`,`attendance_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;