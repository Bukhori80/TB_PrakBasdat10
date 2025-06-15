-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 02, 2025 at 10:57 AM
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
-- Database: `reservasi_lapangan_futsal`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jam_mulai` time DEFAULT NULL,
  `durasi` int(11) DEFAULT NULL,
  `status` enum('pending','lunas','dibatalkan') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `lapangan_id` int(11) DEFAULT NULL,
  `bookings` datetime DEFAULT current_timestamp(),
  `total_bayar` int(11) DEFAULT 0,
  `nominal_bayar` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `tanggal`, `jam_mulai`, `durasi`, `status`, `created_at`, `lapangan_id`, `bookings`, `total_bayar`, `nominal_bayar`) VALUES
(31, 1, '2025-06-27', '15:31:00', 4, 'pending', '2025-06-02 08:26:35', 1, '2025-06-02 15:26:35', 600000, 100000);

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int(11) NOT NULL,
  `nama_lapangan` varchar(100) DEFAULT NULL,
  `lokasi` varchar(255) DEFAULT NULL,
  `harga_per_jam` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `nama_lapangan`, `lokasi`, `harga_per_jam`) VALUES
(1, 'Lapangan A', 'Jl. Garut No.10', 100000.00),
(2, 'Lapangan B', 'Jl. Garut No.12', 120000.00);

-- --------------------------------------------------------

--
-- Table structure for table `lapangan`
--

CREATE TABLE `lapangan` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `lokasi` text DEFAULT NULL,
  `harga_per_jam` decimal(10,2) DEFAULT NULL,
  `status` enum('aktif','tidak_aktif') DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lapangan`
--

INSERT INTO `lapangan` (`id`, `nama`, `jenis`, `lokasi`, `harga_per_jam`, `status`, `created_at`) VALUES
(1, 'Lapangan A', 'Sintetis', 'Lantai 1', 150000.00, 'aktif', '2025-05-30 09:23:18'),
(2, 'Lapangan B', 'Vinyl', 'Lantai 2', 125000.00, 'aktif', '2025-05-30 09:23:18');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `jumlah` decimal(10,2) DEFAULT NULL,
  `metode` varchar(50) DEFAULT NULL,
  `status_pembayaran` enum('belum_bayar','lunas') DEFAULT 'belum_bayar',
  `waktu_bayar` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin Futsal', 'admin@futsal.com', 'hashed_password_admin', 'admin', '2025-05-27 05:08:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_lapangan` (`lapangan_id`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lapangan`
--
ALTER TABLE `lapangan`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

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
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lapangan`
--
ALTER TABLE `lapangan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_lapangan` FOREIGN KEY (`lapangan_id`) REFERENCES `lapangan` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
