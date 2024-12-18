-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Dec 17, 2024 at 03:52 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `student_managementdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `date_posted` datetime DEFAULT current_timestamp(),
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` enum('Present','Absent') DEFAULT NULL,
  `attendance_percentage` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `instructor_courses_id` int(11) NOT NULL,
  `student_courses_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `instructor_courses_id`, `student_courses_id`) VALUES
(1, 'Database Security Administration', 'This course is intended for system and network administrators, security personnel, auditors, and/ or consultants concerned with system security.', 0, 0),
(2, 'Multimedia Systems', 'Multimedia is a field related to computer-controlled integration of texts, graphics, drawings, audio, and animations.', 0, 0),
(3, 'Networking 2', 'The course covers the design, configuration, and deployment of switches in utilizing VLANs, trunking, and port aggregation.', 0, 0),
(4, 'System Integration And Architecture 1', 'The course focuses on how a proposed system will be integrated with other existing or planned systems.', 0, 0),
(5, 'Fundamental of Database Systems', 'This course introduces and defines the terminology, concepts, and skills you need to understand database objects, security requirements, graphical tools, T-SQL scripts, writing database queries, as well as executing stored procedures.', 0, 0),
(6, 'Web Systems and Technologies', 'This course is an in-depth study of the Internet, and the World Wide Web. It covers web technologies, information architecture, digital media, web development, vulnerabilities, social media, HTML/XHTML, Cascading Style Sheets, and JavaScript Code.', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `created_at`) VALUES
(1, 'DICT', '2024-12-01 01:17:09'),
(2, 'BME', '2024-12-01 01:17:09');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `enrollment_date` date DEFAULT curdate(),
  `status` enum('Enrolled','Pending','Waitlisted','Rejected') DEFAULT 'Pending',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grades`
--

CREATE TABLE `grades` (
  `grade_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `grade` enum('A','B','C','D','F') DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `instructors`
--

CREATE TABLE `instructors` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `assigned_courses` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructors`
--

INSERT INTO `instructors` (`id`, `instructor_id`, `first_name`, `last_name`, `email`, `created_at`, `assigned_courses`) VALUES
(1, 93, 'Kenneth', 'Gisalan', 'kenneth@gmail.com', '2024-12-09 10:07:16', '');

-- --------------------------------------------------------

--
-- Table structure for table `instructor_courses`
--

CREATE TABLE `instructor_courses` (
  `id` int(11) NOT NULL,
  `instructor_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `assigned_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `instructor_courses`
--

INSERT INTO `instructor_courses` (`id`, `instructor_id`, `course_id`, `assigned_date`) VALUES
(6, 93, 1, '2024-12-10 20:31:25');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `attendance_status` enum('Present','Absent','Late') DEFAULT 'Absent',
  `enrollment_status` enum('Enrolled','Graduated','Dropped') DEFAULT 'Enrolled',
  `grades` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `user_id`, `first_name`, `last_name`, `email`, `created_at`, `attendance_status`, `enrollment_status`, `grades`) VALUES
(22, 132, 'Rosielyn', 'Ripo', 'rosielynripo8@gmail.com', '2024-12-14 12:25:02', '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','instructor','student') NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('pending','approved') NOT NULL DEFAULT 'approved',
  `department` varchar(255) NOT NULL,
  `verification_token` varchar(64) DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `passwords` varchar(255) DEFAULT NULL,
  `token_expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `password`, `role`, `first_name`, `last_name`, `email`, `status`, `department`, `verification_token`, `email_verified`, `reset_token`, `profile_picture`, `passwords`, `token_expiry`) VALUES
(1, '$2y$10$FUGlW97I/0OgqXGFrESDDOaNGHg4emyCrVfKYiiIfawXALJBhD5S2', 'admin', 'Crisvin', 'Habitsuela', 'crisvinhabitsuela211@gmail.com', 'approved', '', NULL, 0, 'ae1d581a22d8494ec314480952fa483e74c9cb3127b53cd50264678df26e1153a9362ba4660bb0f4cd889655db48168ffaa7', 'profile_675ba471f3aa57.67983387.png', NULL, '0000-00-00 00:00:00'),
(51, '$2y$10$UeWTBzqfhhZthbUMbesiYOcY.wTIwNX21dGtEYdZq7QNVelGLX59m', 'student', 'Marc Andro', 'Levantino', 'levantinomarc@gmail.com', 'approved', 'DICT', '7aa43eee0c6707054eb92bdd73cfbdb1', 0, '918a3da8033998f933e5b571e8d9c7539228aecd4500e378322870e3b0f3d4a1bc2db551e296506cfa99a17ebed64b774226', NULL, NULL, '2024-12-11 04:15:30'),
(71, '$2y$10$OAyax5o9c9TnFpva9Y9lZOgWvMEOTLA2k.h7X5nrbKnuBrMk6HeQG', 'instructor', 'Norjan', 'Eneria', 'juan456rr@gmail.com', 'approved', 'DICT', 'f66ad263a3d333003f7fef17cf589c94', 0, NULL, NULL, NULL, '2024-12-11 04:15:30'),
(73, '$2y$10$y2VACSoPtCbx0LG9bizCKOvDm6/8mnkxl6XbgSMmofmPgX0Y1NOJq', 'instructor', 'Ruel', 'Grafia', 'ruel.grafia@gmail.com', 'approved', 'DICT', 'd951c609589701f53ca37856f5273a23', 0, NULL, 'profile_6758f63eafe700.89408601.jpg', NULL, '2024-12-11 04:15:30'),
(75, '$2y$10$668.pZN4CmconBv4QvUrquPVO4nQ2fe7N0Ydi.or0Dcp88WUkg0Ru', 'student', 'Juan', 'tamad', 'juan456@gmail.com', 'approved', 'DICT', '4f893e44efa5c85b374859659455d6a1', 0, NULL, 'profile_6756c2d135cce6.21486164.jpg', NULL, '2024-12-11 04:15:30'),
(93, '$2y$10$3BH/OMGEaCTV7RByq1aVnOg64sarQmZ2iC7XnQ.THGMXJU/UyKUUe', 'instructor', 'Kenneth', 'Gisalan', 'kenneth@gmail.com', 'approved', 'DICT', '42c18e0dbd596346dbf1e09ef2d2e856', 0, NULL, NULL, NULL, '2024-12-11 04:15:30'),
(132, '$2y$10$ypmHHccOotoNj.sg4d9CBe610FGhh8eHFFw3zxJkwUNWh50gHh2pC', 'student', 'Rosielyn', 'Ripo', 'rosielynripo8@gmail.com', 'approved', 'DICT', NULL, 0, NULL, NULL, NULL, '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_course_attendance` (`course_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `grades`
--
ALTER TABLE `grades`
  ADD PRIMARY KEY (`grade_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fk_course_grades` (`course_id`);

--
-- Indexes for table `instructors`
--
ALTER TABLE `instructors`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`instructor_id`);

--
-- Indexes for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `instructor_id` (`instructor_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grades`
--
ALTER TABLE `grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instructors`
--
ALTER TABLE `instructors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `attendance_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `fk_course_attendance` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `grades`
--
ALTER TABLE `grades`
  ADD CONSTRAINT `fk_course_grades` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `grades_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`);

--
-- Constraints for table `instructors`
--
ALTER TABLE `instructors`
  ADD CONSTRAINT `instructors_ibfk_1` FOREIGN KEY (`instructor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instructor_courses`
--
ALTER TABLE `instructor_courses`
  ADD CONSTRAINT `instructor_courses_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `instructor_courses_ibfk_2` FOREIGN KEY (`instructor_id`) REFERENCES `instructors` (`instructor_id`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
