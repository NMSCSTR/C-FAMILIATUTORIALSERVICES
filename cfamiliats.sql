-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2026 at 09:25 PM
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
(1, 2, 'Criminology Review', 'pending', '', '2026-03-28', '2026-03-28 08:27:01', 'Batch 2026-A (April - June)', 0, 5000.00),
(2, 3, 'Criminology Review', 'enrolled', 'Oroqueta', '2026-03-28', '2026-03-28 10:27:48', 'Batch 2026-A (April - June)', 0, 5500.00),
(3, 4, 'Criminology Review', 'pending', 'Ozamis', '2026-04-14', '2026-04-14 08:55:19', 'Batch 2026-A (Morning Session)', 0, 5500.00);

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
  `photo` varchar(255) DEFAULT 'default_user.jpg',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `passers`
--

INSERT INTO `passers` (`id`, `name`, `program`, `batch`, `photo`, `created_at`) VALUES
(1, 'RHONDEL M. PAGOBO', 'CRIMINOLOGY', '2025', '1774690913_481928148_1011155720936283_6075998069416443900_n.jpg', '2026-03-28 17:41:53'),
(2, 'JESSEL VE ALJAS', 'CRIMINOLOGY', '2025', 'default_user.jpg', '2026-04-14 16:44:03');

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
(3, 3, 250.00, 'GCash', 'paid', 'sampleref', '1775654790_FhbtNbUWYAEbVCV.jpeg', NULL, '2026-04-08 13:26:30', 'installment');

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
(1, 'PASSING PERCENTAGE', 'This passing percentage is a manifestation of the hard work and dedication of our lecturers. Some may say that our lecturers are “purely local,” but the results speak for themselves.\r\nSuccess does not depend on where the lecturers come from, but on their genuine intention to share their knowledge and sincerely help the reviewees succeed. Today, we see the fruit of that commitment and dedication.\r\nDaghang salamant\r\nJose Cuevas Jr. Edmar Daniel Kris Tality EL Mie Teopisto Yray Culanag Jr. Bernaflor Canape Sagi Ttario Mat Jumawan Heyrosa Aldrin Tactacon Fiona Ivana Detalla Xians Estares Reynold Jay Bahunsua Reymark Labitad Reymark Ragmac  Rey Niño Buenaventura Abucay Ricky Jean Alegrado Angelyn Pacatang Ar-jay Patalinghug Jerry Besiohan Alizter Singidas Llorong', 'Admin', '1774690342_J.jpg', '2026-03-28 17:32:22'),
(2, 'SAMPLE CV', 'PLEASE CREATE A SAMPLE CV LIKE THIS FORMAT.', 'Admin', '1775655752_RHONDELPAGOBOCV (2).pdf', '2026-04-08 21:42:32');

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
(3, 'RHONDEL M. PAGOBO', 'MEPARANUM', 'PAGOBO', 'rhondelpagobo19@gmail.com', '$2y$10$Apiso9PAv5YQ0J/VKR8YMOBY4f/Mai6yBzUOqKqiMNVN52oeH8Jli', 'student', '1774692728_481875975_1693045591626922_5649588926967639802_n.jpg', '2026-03-28 10:04:59'),
(4, 'RODRIGO', 'ROA', 'DUTERTE', 'rodrigoroa@gmail.com', '$2y$10$l9ULzzNjGPKrXAw9hScm8u1hdL6g5QvI3Qb38b8vOVEFpSw99RNQa', 'student', NULL, '2026-04-14 08:53:49');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `exam_result`
--
ALTER TABLE `exam_result`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `passers`
--
ALTER TABLE `passers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
