-- Create the database
CREATE DATABASE IF NOT EXISTS `attendance_MS`
DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `attendance_MS`;

-- --------------------------------------------------------
-- Table 1: users (Admins and Teachers)
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL, 
  `role` enum('admin','teacher') NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_id` (`employee_id`),
  KEY `idx_role_status` (`role`, `status`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 2: strands
-- --------------------------------------------------------
CREATE TABLE `strands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `strand_name` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `strand_name` (`strand_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 3: subjects
-- --------------------------------------------------------
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subject_name` (`subject_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 4: students
-- --------------------------------------------------------
CREATE TABLE `students` (
  `student_id` varchar(50) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,

  `strand_id` int(11) NOT NULL,
  `year_level` enum('11','12') NOT NULL,
  `section` varchar(50) DEFAULT NULL,

  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`student_id`),

  KEY `fk_strand` (`strand_id`),
  CONSTRAINT `fk_strand`
    FOREIGN KEY (`strand_id`)
    REFERENCES `strands` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table 5: attendance
-- --------------------------------------------------------
CREATE TABLE `attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` varchar(50) NOT NULL,
  `teacher_id` varchar(50) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `attendance_date` date NOT NULL,
  `status` enum('Present','Late','Absent') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  PRIMARY KEY (`id`),

  -- Prevent duplicate per subject per day
  UNIQUE KEY `unique_attendance_per_day`
    (`student_id`,`attendance_date`,`subject_id`),

  -- Indexes
  KEY `fk_student` (`student_id`),
  KEY `fk_teacher` (`teacher_id`),
  KEY `fk_subject` (`subject_id`),
  KEY `idx_date_status` (`attendance_date`, `status`),

  -- Foreign Keys
  CONSTRAINT `fk_student`
    FOREIGN KEY (`student_id`)
    REFERENCES `students` (`student_id`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_teacher`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `users` (`employee_id`)
    ON DELETE CASCADE,

  CONSTRAINT `fk_subject`
    FOREIGN KEY (`subject_id`)
    REFERENCES `subjects` (`id`)
    ON DELETE CASCADE

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Starter data for a fresh install.
INSERT INTO `users` (`employee_id`, `first_name`, `last_name`, `password`, `role`, `status`)
VALUES
('ADMIN-001', 'System', 'Administrator', '$2y$10$iFZQH28eXXeaqv.UlVrYX.WzoyAlgp5NRIEnbqqxzOxZGTBHCN4Cm', 'admin', 'active')
ON DUPLICATE KEY UPDATE `employee_id` = VALUES(`employee_id`);

INSERT INTO `strands` (`strand_name`) VALUES
('ABM'), ('GAS'), ('HUMSS'), ('STEM'), ('TVL')
ON DUPLICATE KEY UPDATE `strand_name` = VALUES(`strand_name`);

INSERT INTO `subjects` (`subject_name`) VALUES
('English'), ('Filipino'), ('Mathematics'), ('Science'), ('Practical Research')
ON DUPLICATE KEY UPDATE `subject_name` = VALUES(`subject_name`);
