-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 13, 2022 at 10:04 PM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `samstudio`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_profile`
--

CREATE TABLE `admin_profile` (
  `id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `telephone` varchar(12) NOT NULL,
  `bio` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `admin_user`
--

CREATE TABLE `admin_user` (
  `id` varchar(32) NOT NULL DEFAULT 'john',
  `firstname` varchar(32) NOT NULL DEFAULT 'doe',
  `lastname` varchar(32) NOT NULL,
  `password` varchar(200) NOT NULL,
  `email` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `id` varchar(32) NOT NULL,
  `content` text NOT NULL,
  `seen` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `package`
--

CREATE TABLE `package` (
  `id` varchar(32) NOT NULL,
  `name` varchar(32) NOT NULL,
  `event` varchar(32) NOT NULL,
  `options` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`options`)),
  `price` decimal(32,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `id` varchar(32) NOT NULL,
  `event` varchar(200) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `session`
--

CREATE TABLE `session` (
  `id` varchar(32) NOT NULL,
  `data` varchar(2000) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE current_timestamp()
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `team`
--

CREATE TABLE `team` (
  `id` varchar(32) NOT NULL,
  `project_id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `transaction`
--

CREATE TABLE `transaction` (
  `id` varchar(32) NOT NULL,
  `amount` decimal(32,2) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `telephone` varchar(12) NOT NULL,
  `success` tinyint(1) NOT NULL,
  `transaction_desc` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_privilege`
--

CREATE TABLE `user_privilege` (
  `id` varchar(32) NOT NULL,
  `user_id` varchar(32) NOT NULL,
  `add_user` varchar(4) NOT NULL,
  `delete_user` varchar(4) NOT NULL,
  `edit` varchar(4) NOT NULL,
  `delete` varchar(4) NOT NULL,
  `create` varchar(4) NOT NULL,
  `special` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_slot`
--

CREATE TABLE `user_slot` (
  `id` varchar(32) NOT NULL,
  `token` varchar(200) NOT NULL,
  `user_id` varchar(32) DEFAULT NULL,
  `email` varchar(32) NOT NULL,
  `privilege` varchar(60) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admin_user`
--
ALTER TABLE `admin_user`
  ADD PRIMARY KEY (`id`) USING HASH;

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaction`
--
ALTER TABLE `transaction`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_privilege`
--
ALTER TABLE `user_privilege`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_privilege` (`user_id`);

--
-- Indexes for table `user_slot`
--
ALTER TABLE `user_slot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk` (`user_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_profile`
--
ALTER TABLE `admin_profile`
  ADD CONSTRAINT `frk` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_privilege`
--
ALTER TABLE `user_privilege`
  ADD CONSTRAINT `user_privilege` FOREIGN KEY (`user_id`) REFERENCES `admin_user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
