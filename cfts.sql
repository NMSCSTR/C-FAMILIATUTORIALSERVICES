-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 07:22 AM
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
-- Database: `cfamiliats`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `category` enum('General','Urgent','Event','Academic') DEFAULT 'General',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `category`, `created_at`) VALUES
(1, 'Payment Update', 'Please pay your remaining balance at Saturday.', 'General', '2026-03-28 17:27:47');

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `program_type` varchar(100) DEFAULT 'Criminology Review',
  `status` enum('pending','enrolled','completed') DEFAULT 'pending',
  `enrolled_at` varchar(100) NOT NULL COMMENT 'REVIEW CENTER LOCATION WHERE STUDENTS ENROLLED\r\n',
  `enrollment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `batch` varchar(50) DEFAULT NULL,
  `insured` tinyint(1) NOT NULL COMMENT 'INSURANCE FOR STUDENTS IF THEY ARE BEING LISTED TO INSURANCE',
  `total_fee` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `user_id`, `program_type`, `status`, `enrolled_at`, `enrollment_date`, `created_at`, `batch`, `insured`, `total_fee`) VALUES
(1, 2, 'Criminology Review', 'pending', '', '2026-03-28', '2026-03-28 08:27:01', 'Batch 2026-A (April - June)', 1, 5000.00),
(2, 3, 'Criminology Review', 'enrolled', 'Oroqueta', '2026-03-28', '2026-03-28 10:27:48', 'Batch 2026-A (April - June)', 0, 5500.00),
(3, 4, 'Criminology Review', 'pending', 'Ozamis', '2026-04-14', '2026-04-14 08:55:19', 'Batch 2026-A (Morning Session)', 0, 5500.00),
(4, 5, 'Criminology Review', 'enrolled', 'Tubod', '2026-04-27', '2026-04-26 20:02:34', 'Batch 2026-A (Morning Session)', 1, 5500.00);

-- --------------------------------------------------------

--
-- Table structure for table `exam_result`
--

CREATE TABLE `exam_result` (
  `exam_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `diagnostic_exam` int(11) NOT NULL,
  `preboard_exam` int(11) NOT NULL,
  `compre_exam` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passers`
--

CREATE TABLE `passers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `program` varchar(100) NOT NULL,
  `batch` varchar(50) NOT NULL,
  `rating` decimal(4,2) DEFAULT NULL,
  `photo` varchar(255) DEFAULT 'default_user.jpg',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passers`
--

INSERT INTO `passers` (`id`, `name`, `program`, `batch`, `rating`, `photo`, `created_at`) VALUES
(3, 'RHONDEL PAGOBO', 'CRIMINOLOGY', '2026', 96.00, '1774692728_481875975_1693045591626922_5649588926967639802_n.jpg', '2026-04-29 00:17:54');

-- --------------------------------------------------------

--
-- Table structure for table `gallery_images`
--

CREATE TABLE `gallery_images` (
  `id` int(11) NOT NULL,
  `caption` varchar(255) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `status` enum('paid','pending','failed') DEFAULT 'pending',
  `reference_number` varchar(100) DEFAULT NULL,
  `receipt` varchar(255) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `payment_type` enum('full','installment') DEFAULT 'full'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `user_id`, `amount`, `payment_method`, `status`, `reference_number`, `receipt`, `payment_date`, `created_at`, `payment_type`) VALUES
(1, 2, 5000.00, 'GCash', 'pending', 'saddasfwrg', '1774689474_FhbtNbUWYAEbVCV.jpeg', '2026-03-28', '2026-03-28 09:17:54', 'full'),
(2, 3, 1500.00, 'GCash', 'paid', 'TARA831979', '1774972065_Snapchat_330830312.jpg', '2026-03-31', '2026-03-31 15:47:45', 'full'),
(3, 3, 250.00, 'GCash', 'paid', 'sampleref', '1775654790_FhbtNbUWYAEbVCV.jpeg', NULL, '2026-04-08 13:26:30', 'installment'),
(5, 5, 3000.00, 'Walk-in Cash', 'paid', 'OR-0001', NULL, '2026-04-27', '2026-04-26 21:14:11', 'installment');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(100) DEFAULT 'Admin',
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `title`, `content`, `author`, `file_path`, `created_at`) VALUES
(2, 'SAMPLE CV', 'PLEASE CREATE A SAMPLE CV LIKE THIS FORMAT.', 'Admin', '1775655752_RHONDELPAGOBOCV (2).pdf', '2026-04-08 21:42:32');

-- --------------------------------------------------------

--
-- Table structure for table `testimonials`
--

CREATE TABLE `testimonials` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `testimonials`
--

INSERT INTO `testimonials` (`id`, `user_id`, `content`, `created_at`) VALUES
(1, 5, 'C-Familia is more than just a review center; it\'s a family. The lecturers genuinely care about our success and break down complex concepts into terms I can finally understand. I owe my license to their dedication.', '2026-04-28 16:36:16');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') DEFAULT 'student',
  `profile_pic` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `email`, `password`, `role`, `profile_pic`, `created_at`) VALUES
(2, 'SHIELA MARIS', 'GWAPA', 'CUEVAS', 'admin@cfamilia.com', '$2y$10$CJtl6AjNJxe69F52XbhkYuYDkJHI70sT6ioTd0Up9KMU7/bWVX5x.', 'admin', NULL, '2026-03-28 08:17:28'),
(3, 'RHONDEL', 'MEPARANUM', 'PAGOBO', 'rhondelpagobo19@gmail.com', '$2y$10$Apiso9PAv5YQ0J/VKR8YMOBY4f/Mai6yBzUOqKqiMNVN52oeH8Jli', 'student', '1774692728_481875975_1693045591626922_5649588926967639802_n.jpg', '2026-03-28 10:04:59'),
(4, 'RODRIGO', 'ROA', 'DUTERTE', 'rodrigoroa@gmail.com', '$2y$10$l9ULzzNjGPKrXAw9hScm8u1hdL6g5QvI3Qb38b8vOVEFpSw99RNQa', 'student', NULL, '2026-04-14 08:53:49'),
(5, 'Aldril', 'Remitar', 'Catigum', 'aldrilrcatigum@gmail.com', '$2y$10$6IDigpSSnMxV4/dPNUkxBO5yevLVNaViYwSq6uhHI6A3HiVw5RRvq', 'student', '1777394943_CICT_LOGO.png', '2026-04-26 19:34:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD PRIMARY KEY (`exam_id`),
  ADD KEY `examinee_id_exam_result` (`user_id`);

--
-- Indexes for table `passers`
--
ALTER TABLE `passers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `gallery_images`
--
ALTER TABLE `gallery_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `caption` (`caption`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD PRIMARY KEY (`id`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `exam_result`
--
ALTER TABLE `exam_result`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passers`
--
ALTER TABLE `passers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gallery_images`
--
ALTER TABLE `gallery_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `testimonials`
--
ALTER TABLE `testimonials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_result`
--
ALTER TABLE `exam_result`
  ADD CONSTRAINT `examinee_id_exam_result` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `testimonials`
--
ALTER TABLE `testimonials`
  ADD CONSTRAINT `testimonials_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
