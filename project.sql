-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 28, 2025 at 11:00 PM
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
-- Database: `project`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`) VALUES
(1, 'Main Campus');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `university_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `name`, `university_id`, `department_id`) VALUES
(1, 'Introduction to Programming', 1, 1),
(2, 'Data Structures & Algorithms', 1, 1),
(3, 'Database Management Systems', 1, 1),
(4, 'Operating Systems', 1, 1),
(5, 'Computer Networks', 1, 1),
(6, 'BCA', 1, 2),
(7, 'MCA', 1, 3),
(8, 'BBA', 1, 4),
(9, 'B.Tech', 1, 5),
(10, 'Other', 1, 10),
(11, 'CSE', NULL, 11),
(12, 'b.com', NULL, 12),
(15, 'b.come', 2, 12),
(18, 'MBBS', 4, 14);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `branch_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `university_id`, `name`, `branch_id`) VALUES
(1, 1, 'Computer Science', 1),
(2, 1, 'B.C.A.', 1),
(3, 1, 'M.C.A.', 1),
(4, 1, 'B.B.A.', 1),
(5, 1, 'B.Tech.', 1),
(6, 1, 'B.Com.', 1),
(7, 1, 'B.Sc.', 1),
(8, 1, 'M.Sc.', 1),
(9, 1, 'B.Arch.', 1),
(10, 1, 'Other Programs', 1),
(11, 2, 'B.TECH', NULL),
(12, 2, 'b.com', NULL),
(13, 3, 'MCA', NULL),
(14, 4, 'medical', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `university_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `branch_for` varchar(100) NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `course_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `user_course_name` varchar(255) DEFAULT NULL,
  `user_subject_name` varchar(255) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `upload_group_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `title`, `description`, `type`, `file_path`, `university_id`, `department_id`, `branch_for`, `upload_date`, `course_id`, `subject`, `user_id`, `subject_id`, `user_course_name`, `user_subject_name`, `branch_id`, `semester`, `upload_group_id`) VALUES
(1, 'oops', 'pyq ', NULL, 'uploads/material_68d961a12c9ea_Dbms_PYQ_module wise.pdf', NULL, 11, '', '2025-09-28 16:26:09', 11, NULL, 1, 20, NULL, NULL, NULL, 5, NULL),
(3, 'ss', 'vvio', NULL, 'uploads/material_68d9666195e2e_pyq.pdf', 2, 12, '', '2025-09-28 16:46:25', 15, NULL, 3, 22, NULL, NULL, NULL, 5, NULL),
(4, 'physic', 'unit 1 ,2 ,3', NULL, 'uploads/material_68d97525ac10a7.52502883.pdf', 2, 11, '', '2025-09-28 14:19:25', 11, NULL, 3, 23, NULL, NULL, NULL, 2, NULL),
(5, 'physic', 'unit 1 ,2 ,3', NULL, 'uploads/material_68d97525cd44d7.36535143.pdf', 2, 11, '', '2025-09-28 14:19:25', 11, NULL, 3, 23, NULL, NULL, NULL, 2, NULL),
(6, 'physic', 'unit 1 ,2 ,3', NULL, 'uploads/material_68d97525dd80e7.27126820.pdf', 2, 11, '', '2025-09-28 14:19:25', 11, NULL, 3, 23, NULL, NULL, NULL, 2, NULL),
(7, 'chemistry ', 'unit 1 ,2 ', NULL, 'uploads/material_68d97be05fad42.55349300.pdf', 2, 5, '', '2025-09-28 14:48:08', 11, NULL, 3, 24, NULL, NULL, NULL, 5, 'upload_68d97be0531345.14629239'),
(8, 'COMPUTER SCIENCE', 'NOTIC', NULL, 'uploads/material_68d99af1657f78.12355953.pdf', 3, 13, '', '2025-09-28 17:00:41', 10, NULL, 4, 25, NULL, NULL, NULL, 1, 'upload_68d99af15155f7.14877516'),
(9, 'notic', 'medical AIMS', NULL, 'uploads/material_68d9a0dd2ab1f9.07875730.pdf', 4, 14, '', '2025-09-28 17:25:57', 18, NULL, 5, 26, NULL, NULL, NULL, 12, 'upload_68d9a0dd096de2.28809717');

-- --------------------------------------------------------

--
-- Table structure for table `material_favorites`
--

CREATE TABLE `material_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `favorited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_favorites`
--

INSERT INTO `material_favorites` (`id`, `user_id`, `material_id`, `created_at`, `favorited_at`) VALUES
(17, 3, 6, '2025-09-28 19:58:56', '2025-09-28 19:58:56'),
(18, 3, 5, '2025-09-28 19:58:57', '2025-09-28 19:58:57'),
(23, 1, 7, '2025-09-28 20:13:23', '2025-09-28 20:13:23'),
(24, 4, 3, '2025-09-28 20:29:23', '2025-09-28 20:29:23'),
(25, 5, 4, '2025-09-28 20:54:54', '2025-09-28 20:54:54');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `course_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `semester` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `name`, `course_id`, `department_id`, `semester`) VALUES
(1, 'C++ Programming', 1, 1, 1),
(2, 'Algorithms', 2, 1, 2),
(3, 'TCP/IP', 3, 1, 3),
(4, 'Memory Management', 4, 1, 4),
(5, 'SQL & Relational DBs', 5, 1, 5),
(6, 'DBMS', 6, 2, 1),
(7, 'DSA', 6, 2, 2),
(8, 'COA', 6, 2, 3),
(9, 'OS', 6, 2, 4),
(10, 'DAA', 6, 2, 5),
(11, 'Data Mining', 7, 3, 1),
(12, 'Cloud Computing', 7, 3, 2),
(13, 'Network Security', 7, 3, 3),
(14, 'Marketing', 8, 4, 1),
(15, 'Finance', 8, 4, 2),
(16, 'HR Management', 8, 4, 3),
(17, 'Fluid Mechanics', 9, 5, 1),
(18, 'Engineering Drawing', 9, 5, 2),
(19, 'Other', 10, 10, 1),
(20, 'OOPS', 11, 11, 0),
(21, 'dbma', 12, 12, 0),
(22, 'ss', 15, 12, 0),
(23, 'physic', 11, 11, 0),
(24, 'chemistry', 11, 5, 0),
(25, 'NOTIC', 10, 13, 0),
(26, 'DENTAL', 18, 14, 0);

-- --------------------------------------------------------

--
-- Table structure for table `universities`
--

CREATE TABLE `universities` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `universities`
--

INSERT INTO `universities` (`id`, `name`) VALUES
(2, 'aravali collage'),
(4, 'echelon institute of technology'),
(3, 'goldentgate'),
(1, 'Sample University of Technology');

-- --------------------------------------------------------

--
-- Table structure for table `university_favorites`
--

CREATE TABLE `university_favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `university_favorites`
--

INSERT INTO `university_favorites` (`id`, `user_id`, `university_id`, `created_at`) VALUES
(3, 3, 1, '2025-09-28 19:49:38'),
(5, 3, 2, '2025-09-28 19:59:00'),
(7, 1, 2, '2025-09-28 20:13:40'),
(8, 4, 2, '2025-09-28 20:29:26'),
(9, 4, 3, '2025-09-28 20:41:54'),
(10, 5, 2, '2025-09-28 20:56:23'),
(11, 5, 4, '2025-09-28 20:56:25'),
(12, 5, 3, '2025-09-28 20:56:26'),
(13, 5, 1, '2025-09-28 20:56:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gmail` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` enum('student','teacher') NOT NULL,
  `university_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `roll_number` varchar(50) DEFAULT NULL,
  `branch` varchar(100) DEFAULT NULL,
  `year` int(4) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `gmail`, `password`, `user_type`, `university_id`, `department_id`, `branch_id`, `roll_number`, `branch`, `year`, `contact`, `address`) VALUES
(1, 'sumit', 'rishav@gmail', '$2y$10$778IZ3ha2x6e8pM5nuuMPOD2GSPeiznE1y4R/BPlX0h7X3NY2j7Rm', 'teacher', 2, 11, NULL, NULL, NULL, NULL, '8976542319', 'KATHMANDU'),
(3, 'sumit', 'sumit@gmail', '$2y$10$eNLRcZAOGJIrULgGkG6wM.PaQ8n2ZUE9dM4y9h.IO1Mov56ZuULPu', 'teacher', 2, 12, NULL, NULL, NULL, NULL, '8976542319', 'haryana'),
(4, 'sumit', 'chaurasiya@gmail.com', '$2y$10$f50rh4OpMahaMoV991gEu.z09.bxWkTkIhcK.CpVdSzWpQV.IqhPS', 'student', 3, 13, NULL, '150', 'cse', 2022, '8976542319', 'haryana'),
(5, 'haresh', 'harsh@gmail.com', '$2y$10$HOg60FDQIdqOxNCBx89IWeqsOAy80aKWcI4WkTjpNw5Tjwusvur..', 'student', 4, 14, NULL, '98', 'medical', 2025, '0', '');

-- --------------------------------------------------------

--
-- Table structure for table `user_favorites`
--

CREATE TABLE `user_favorites` (
  `user_id` int(11) NOT NULL,
  `university_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `fk_course_uni` (`university_id`),
  ADD KEY `fk_course_dept` (`department_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_dept_per_uni` (`university_id`,`name`),
  ADD KEY `fk_department_branch` (`branch_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `material_favorites`
--
ALTER TABLE `material_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_material_unique` (`user_id`,`material_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `fk_subject_department` (`department_id`);

--
-- Indexes for table `universities`
--
ALTER TABLE `universities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `university_favorites`
--
ALTER TABLE `university_favorites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_university_unique` (`user_id`,`university_id`),
  ADD KEY `university_id` (`university_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gmail` (`gmail`),
  ADD UNIQUE KEY `roll_number` (`roll_number`),
  ADD KEY `university_id` (`university_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`user_id`,`university_id`),
  ADD KEY `university_id` (`university_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `material_favorites`
--
ALTER TABLE `material_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `universities`
--
ALTER TABLE `universities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `university_favorites`
--
ALTER TABLE `university_favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `fk_course_dept` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_course_uni` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_department_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`);

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `materials_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `material_favorites`
--
ALTER TABLE `material_favorites`
  ADD CONSTRAINT `material_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_favorites_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `fk_subject_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `subjects_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `university_favorites`
--
ALTER TABLE `university_favorites`
  ADD CONSTRAINT `university_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `university_favorites_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`university_id`) REFERENCES `universities` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
