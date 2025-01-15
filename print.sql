-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 23, 2024 at 10:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `print`
--

-- --------------------------------------------------------

--
-- Table structure for table `koperasibranch`
--

CREATE TABLE `koperasibranch` (
  `branch_id` varchar(50) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `branch_address` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `loginsession`
--

CREATE TABLE `loginsession` (
  `session_id` varchar(50) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `login_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `logout_time` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `membershipcard`
--

CREATE TABLE `membershipcard` (
  `Membership_Card_id` varchar(50) NOT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `Card_Number` varchar(50) NOT NULL,
  `Issue_Date` date NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `Expiry_Date` date NOT NULL,
  `card_status` varchar(20) DEFAULT NULL,
  `QR_Code` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order`
--

CREATE TABLE `order` (
  `order_id` varchar(50) NOT NULL,
  `branch_id` varchar(50) DEFAULT NULL,
  `user_id` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `Order_Tax` decimal(10,2) DEFAULT 0.00,
  `Order_Grand_Total` decimal(10,2) NOT NULL,
  `Order_Point` int(11) DEFAULT 0,
  `card_status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `printingpackage`
--

CREATE TABLE `printingpackage` (
  `package_id` varchar(50) NOT NULL,
  `branch_id` varchar(50) DEFAULT NULL,
  `package_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','suspended') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `user_id` varchar(50) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_role` enum('admin','staff','customer') NOT NULL,
  `student_card` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `User_Phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`user_id`, `user_name`, `user_email`, `user_password`, `user_role`, `student_card`, `status`, `created_at`, `User_Phone`) VALUES
('', 'Turki ', 'alkathrys300@gmail.com', '12345678', 'admin', NULL, 'active', '2024-12-23 09:06:18', '0183209437'),
('ADMIN001', 'Administrator', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL, 'active', '2024-12-22 17:39:13', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `koperasibranch`
--
ALTER TABLE `koperasibranch`
  ADD PRIMARY KEY (`branch_id`);

--
-- Indexes for table `loginsession`
--
ALTER TABLE `loginsession`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_login_user` (`user_id`);

--
-- Indexes for table `membershipcard`
--
ALTER TABLE `membershipcard`
  ADD PRIMARY KEY (`Membership_Card_id`),
  ADD KEY `idx_membership_user` (`user_id`);

--
-- Indexes for table `order`
--
ALTER TABLE `order`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_branch` (`branch_id`),
  ADD KEY `idx_order_user` (`user_id`);

--
-- Indexes for table `printingpackage`
--
ALTER TABLE `printingpackage`
  ADD PRIMARY KEY (`package_id`),
  ADD KEY `idx_package_branch` (`branch_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD KEY `idx_user_email` (`user_email`),
  ADD KEY `idx_user_role` (`user_role`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loginsession`
--
ALTER TABLE `loginsession`
  ADD CONSTRAINT `loginsession_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `membershipcard`
--
ALTER TABLE `membershipcard`
  ADD CONSTRAINT `membershipcard_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `order`
--
ALTER TABLE `order`
  ADD CONSTRAINT `order_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `koperasibranch` (`branch_id`),
  ADD CONSTRAINT `order_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`);

--
-- Constraints for table `printingpackage`
--
ALTER TABLE `printingpackage`
  ADD CONSTRAINT `printingpackage_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `koperasibranch` (`branch_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
