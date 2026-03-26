-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 01:37 PM
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
-- Database: `user_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(150) NOT NULL,
  `last_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(150) NOT NULL,
  `gender` enum('Male','Female','Other') NOT NULL,
  `role` enum('admin','user') NOT NULL,
  `address` varchar(150) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `login_attempts` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `gender`, `role`, `address`, `status`, `login_attempts`, `created_at`) VALUES
(1, 'JAY-AR', 'DE GUZMAN', 'jrsaturno66@gmail.com', '$2y$10$luKlX5cqKQ1rH4vXC7dCNeqwQ7LvRTfSUtP1Zl8yYUsUNfp1fB/GS', 'Male', 'admin', '35 Camia, Villa Nati, Muñoz, Nueva Ecija', 'active', 0, '2026-02-16 07:31:36'),
(3, 'Kurt Tristan San Luiz', 'Adaoag', 'kurttsladaoag@gmail.com', '$2y$10$rAEefnXME40wJXPWnCTqlewvuq5Sgv6Fc2Fk9PBOed84R7JuWcdHm', 'Other', 'user', 'Medico Nampicuan Nueva Ecija', 'active', 0, '2026-02-16 08:11:42'),
(4, 'Frinz', 'De Guzman', 'frinzcambaliza@gmail.com', '$2y$10$zOgquP85kGt4P0PUHRBmluvcrJpgQJLBrwZWvj03WaubX7uk0PkZO', 'Male', 'user', 'Llanera Nueva Ecija', 'active', 0, '2026-02-16 08:14:53'),
(5, 'Jay-ar', 'De Guzman', 'jay-ar.deguzman@gmail.com', '$2y$10$yqCRa1B1Bh3/Vab7ZlH.wedyYOl7mGryZzzNDh2UoXWjOo8u.yfQq', 'Male', 'user', 'Muñoz, Nueva Ecija', 'active', 0, '2026-02-16 08:53:25'),
(6, 'Jay', 'Saturno', '_jayxx.saturno@gmail.com', '$2y$10$wUKORYzhDLTQ7neRGI6R/u9VVwNk2FmtNcbyKSFqqP41NEiP2fbSC', 'Male', 'user', 'Muñoz, Nueva Ecija', 'active', 0, '2026-02-16 08:54:08'),
(7, 'Jay-ar', 'Saturno', '_jaysaturno@gmail.com', '$2y$10$tcSPh0EeOYVeOme1e/5nSOB9Jbnu6h8zz772wfUFGoR8FhH7Svy.S', 'Male', 'user', 'Muñoz, Nueva Ecija', 'active', 0, '2026-02-16 08:55:21'),
(8, 'Mel', 'Velasco', 'akosimel@gmail.com', '$2y$10$gSEc8EAZJg5x5xCXe5APcOv5n/KZR8FqkwbTJLx8v1GZoerzwTCDW', 'Male', 'admin', 'sapangsapang', 'active', 0, '2026-02-23 02:01:12'),
(10, 'Jay', 'De Guzman', '_jay.dgzmn123@gmail.com', '$2y$10$Pa14ePiLGEeMhjMef8yPPOVfEZnM6wETnYtRDgrWvnngDp49IRdOC', 'Male', 'admin', 'Pantabangan, Nueva Ecija', 'active', 0, '2026-02-23 12:33:43');

--
-- Indexes for dumped tables
--

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
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
