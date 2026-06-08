-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Gazdă: 127.0.0.1
-- Timp de generare: mai 29, 2026 la 06:17 PM
-- Versiune server: 10.4.32-MariaDB
-- Versiune PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Bază de date: `kim_manager`
--

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `activities_history`
--

CREATE TABLE `activities_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `activity_type` enum('access','purchase','session','payment') DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `activities_history`
--

INSERT INTO `activities_history` (`id`, `user_id`, `activity_type`, `description`, `amount`, `created_at`) VALUES
(1, 3, 'access', 'Intrare', 0.00, '2026-04-23 11:20:57'),
(2, 3, 'purchase', 'Apa plata', 10.00, '2026-04-23 11:20:57'),
(3, 3, 'session', 'Kinetoterapie', 500.00, '2026-04-23 11:23:34'),
(4, 3, 'payment', 'Abonament - Tip 1', 550.00, '2026-04-23 11:35:32'),
(5, 3, 'payment', 'Abonament - Forta', 250.00, '2026-04-30 11:14:06'),
(6, 3, 'payment', 'Abonament - Tip 1', 550.00, '2026-04-30 11:18:18'),
(7, 3, 'payment', 'Abonament - Tip 1', 550.00, '2026-04-30 11:22:51'),
(8, 3, '', 'Suspendare abonament', 0.00, '2026-04-30 11:49:53'),
(9, 3, '', 'Reactivare abonament', 0.00, '2026-04-30 11:50:06'),
(10, 3, 'payment', 'Abonament - Fitness', 250.00, '2026-04-30 11:56:32'),
(11, 3, '', 'Suspendare abonament', 0.00, '2026-04-30 11:59:22'),
(12, 3, '', 'Reactivare abonament', 0.00, '2026-04-30 12:09:12'),
(13, 3, '', 'Suspendare abonament', 0.00, '2026-05-07 09:45:36'),
(14, 3, '', 'Reactivare abonament', 0.00, '2026-05-07 09:45:44'),
(15, 3, '', 'Suspendare abonament', 0.00, '2026-05-07 09:47:20'),
(16, 3, '', 'Reactivare abonament', 0.00, '2026-05-07 09:47:24'),
(17, 3, 'payment', 'Abonament Confirmat - MEMBRU - Forta', 0.00, '2026-05-07 10:23:35'),
(18, 3, 'payment', 'Abonament Confirmat - MEMBRU - Fitness', 0.00, '2026-05-07 11:47:40'),
(19, 3, 'payment', 'Abonament Confirmat - PREMIUM - Tip 1', 250.00, '2026-05-07 12:30:02'),
(20, 3, 'payment', 'Abonament Confirmat - MEMBRU - Fitness', 0.00, '2026-05-07 12:40:10'),
(21, 3, '', 'Suspendare abonament', 0.00, '2026-05-07 12:58:50'),
(22, 3, '', 'Reactivare abonament', 0.00, '2026-05-07 12:58:53'),
(23, 3, '', 'Suspendare abonament', 0.00, '2026-05-14 20:06:25'),
(24, 3, '', 'Reactivare abonament', 0.00, '2026-05-14 20:06:36'),
(25, 3, 'payment', 'Abonament Confirmat - MEMBRU - Kineto', 0.00, '2026-05-18 06:53:28'),
(26, 3, '', 'Suspendare abonament', 0.00, '2026-05-18 06:54:01'),
(27, 3, '', 'Reactivare abonament', 0.00, '2026-05-18 06:54:11'),
(28, 3, 'payment', 'Abonament Confirmat - PREMIUM - Tip 2', 250.00, '2026-05-19 10:35:41'),
(29, 3, '', 'Suspendare abonament', 0.00, '2026-05-19 10:35:51'),
(30, 3, '', 'Reactivare abonament', 0.00, '2026-05-19 10:35:59'),
(31, 8, 'payment', 'Abonament Confirmat - MEMBRU - Forta', 250.00, '2026-05-19 12:01:27'),
(32, 8, '', 'Suspendare abonament', 0.00, '2026-05-19 12:01:53'),
(33, 9, 'payment', 'Abonament Confirmat - MEMBRU - Kineto', 250.00, '2026-05-19 15:18:42'),
(34, 3, 'session', 'Sesiune de Evaluare in Sala Kinetoterapie 1', 0.00, '2026-05-21 11:28:47'),
(35, 3, 'session', 'Personal Training in Sala de Fitness 1', 0.00, '2026-05-21 18:07:45'),
(36, 3, 'session', 'Kinetoterapeutul ți-a programat o nouă procedură pe data de 2026-05-27 la ora 13:00.', 0.00, '2026-05-26 12:35:11'),
(37, 9, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 0.00, '2026-05-26 12:39:51'),
(38, 8, '', 'Reactivare abonament', 0.00, '2026-05-26 12:49:03'),
(39, 8, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 0.00, '2026-05-26 13:24:37'),
(40, 8, 'payment', 'Abonament Confirmat - PREMIUM - Tip 2', 250.00, '2026-05-28 20:35:24'),
(41, 8, 'payment', 'Abonament Confirmat - MEMBRU - Forta', 0.00, '2026-05-28 22:08:15'),
(42, 8, 'session', 'Sesiune de Evaluare in Sala Kinetoterapie 1', 0.00, '2026-05-28 22:30:27'),
(43, 8, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 0.00, '2026-05-28 22:41:27'),
(44, 9, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 0.00, '2026-05-28 22:42:37'),
(45, 9, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 0.00, '2026-05-29 15:14:45'),
(46, 9, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 0.00, '2026-05-29 15:21:10'),
(47, 9, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 175.00, '2026-05-29 15:40:45'),
(49, 9, 'session', 'Masaj de relaxare in Sala Kinetoterapie 1', 175.00, '2026-05-29 15:48:41');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `session_type_id` int(11) DEFAULT NULL,
  `room_id` int(11) DEFAULT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `booking_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `status` enum('pending','approved','rejected','cancelled','rescheduled') DEFAULT 'pending',
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `appointments`
--

INSERT INTO `appointments` (`id`, `user_id`, `session_type_id`, `room_id`, `staff_id`, `booking_date`, `start_time`, `end_time`, `status`, `notes`) VALUES
(5, 3, 6, 3, 4, '2026-05-15', '10:00:00', '11:00:00', 'rescheduled', NULL),
(6, 3, 6, 1, 4, '2026-05-20', '10:00:00', '11:00:00', 'approved', NULL),
(7, 3, 6, 2, 4, '2026-05-20', '11:00:00', '12:00:00', 'cancelled', NULL),
(8, 3, 6, 1, 4, '2026-05-20', '11:00:00', '12:00:00', 'approved', NULL),
(9, 9, 6, 2, 4, '2026-05-20', '12:00:00', '13:00:00', 'approved', NULL),
(10, 3, 7, 4, 5, '2026-05-22', '09:00:00', NULL, 'approved', NULL),
(11, 3, 6, 2, 4, '2026-05-22', '10:00:00', NULL, 'approved', NULL),
(12, 3, 3, 5, 5, '2026-05-27', '11:00:00', NULL, 'rescheduled', NULL),
(13, 9, 1, 4, 5, '2026-05-27', '09:00:00', NULL, 'rescheduled', NULL),
(14, 8, 1, 4, 5, '2026-05-27', '10:00:00', NULL, 'approved', NULL),
(15, 8, 6, NULL, 4, '2026-05-29', '10:00:00', NULL, 'pending', NULL),
(16, 8, 7, 4, 5, '2026-05-29', '12:00:00', NULL, 'cancelled', NULL),
(17, 8, 7, NULL, 5, '2026-05-29', '11:00:00', NULL, 'cancelled', NULL),
(18, 8, 1, 4, 5, '2026-05-29', '09:00:00', NULL, 'approved', NULL),
(19, 9, 1, 4, 5, '2026-05-29', '10:00:00', NULL, 'approved', NULL),
(20, 9, 1, 4, 5, '2026-05-30', '14:00:00', NULL, 'cancelled', NULL),
(21, 9, 1, 4, 5, '2026-05-30', '11:00:00', NULL, 'approved', NULL),
(23, 9, 1, 4, 5, '2026-05-30', '12:00:00', NULL, 'approved', NULL);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `patient_medical_records`
--

CREATE TABLE `patient_medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `therapist_id` int(11) NOT NULL,
  `diagnosis` text DEFAULT NULL,
  `therapist_notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `patient_medical_records`
--

INSERT INTO `patient_medical_records` (`id`, `patient_id`, `therapist_id`, `diagnosis`, `therapist_notes`, `updated_at`) VALUES
(2, 3, 5, 'Traiect de fractura fara deplasare la nivelul extremitatii proximale metacarpian 1', 'se recomanda consult chirurgie plastica', '2026-05-26 12:33:24');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `pending_upgrades`
--

CREATE TABLE `pending_upgrades` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `new_tier` enum('membru','premium','vip') DEFAULT NULL,
  `has_fitness` tinyint(1) DEFAULT 0,
  `has_forta` tinyint(1) DEFAULT 0,
  `has_kineto` tinyint(1) DEFAULT 0,
  `has_vip_perks` tinyint(1) DEFAULT 0,
  `amount_to_pay` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `pending_upgrades`
--

INSERT INTO `pending_upgrades` (`id`, `user_id`, `new_tier`, `has_fitness`, `has_forta`, `has_kineto`, `has_vip_perks`, `amount_to_pay`, `status`, `created_at`) VALUES
(1, 3, 'membru', 0, 1, 0, 0, 0.00, 'approved', '2026-05-07 10:22:52'),
(2, 3, 'membru', 1, 0, 0, 0, 0.00, 'rejected', '2026-05-07 10:27:49'),
(3, 3, 'membru', 1, 0, 0, 0, 0.00, 'rejected', '2026-05-07 10:40:48'),
(4, 3, 'membru', 1, 0, 0, 0, 0.00, 'rejected', '2026-05-07 11:02:10'),
(5, 3, 'membru', 1, 0, 0, 0, 0.00, 'approved', '2026-05-07 11:46:54'),
(6, 3, 'premium', 1, 1, 0, 0, 250.00, 'approved', '2026-05-07 12:29:52'),
(7, 3, 'membru', 1, 0, 0, 0, 0.00, 'rejected', '2026-05-07 12:30:29'),
(8, 3, 'membru', 1, 0, 0, 0, 0.00, 'rejected', '2026-05-07 12:39:25'),
(9, 3, 'membru', 1, 0, 0, 0, 0.00, 'approved', '2026-05-07 12:40:05'),
(10, 3, 'premium', 1, 1, 0, 0, 250.00, 'rejected', '2026-05-07 12:59:56'),
(11, 3, 'membru', 0, 0, 1, 0, 0.00, 'approved', '2026-05-18 06:52:39'),
(12, 3, 'premium', 1, 0, 1, 0, 250.00, 'rejected', '2026-05-18 06:53:38'),
(13, 3, 'premium', 1, 0, 1, 0, 250.00, 'approved', '2026-05-19 10:35:30'),
(14, 8, 'membru', 0, 1, 0, 0, 250.00, 'approved', '2026-05-19 12:00:27'),
(15, 9, 'membru', 0, 0, 1, 0, 250.00, 'approved', '2026-05-19 14:18:55'),
(16, 3, 'vip', 1, 1, 1, 1, 500.00, 'rejected', '2026-05-21 18:04:50'),
(17, 8, 'premium', 1, 0, 1, 0, 250.00, 'approved', '2026-05-28 20:35:14'),
(18, 8, 'membru', 0, 1, 0, 0, 0.00, 'approved', '2026-05-28 22:08:04');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `rooms`
--

CREATE TABLE `rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `type` enum('fitness','kineto','exterior') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `rooms`
--

INSERT INTO `rooms` (`id`, `name`, `capacity`, `type`) VALUES
(1, 'Sala de Aparate', 30, 'fitness'),
(2, 'Sala de Fitness 1', 15, 'fitness'),
(3, 'Sala de Fitness 2', 15, 'fitness'),
(4, 'Sala Kinetoterapie 1', 2, 'kineto'),
(5, 'Sala Kinetoterapie 2', 2, 'kineto'),
(6, 'Teren Exterior', 50, 'exterior');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `session_types`
--

CREATE TABLE `session_types` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `category` enum('kineto_examen','fitness_group','personal_training','kineto','kineto_masaj','kineto_recuperare') DEFAULT NULL,
  `location` enum('sala_aparate','sala_fitness','sala_kineto','exterior') DEFAULT NULL,
  `required_role` enum('kineto','trainer') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `session_types`
--

INSERT INTO `session_types` (`id`, `name`, `category`, `location`, `required_role`) VALUES
(1, 'Masaj de relaxare', 'kineto_masaj', 'sala_kineto', 'kineto'),
(2, 'Masaj medical', 'kineto', 'sala_kineto', 'kineto'),
(3, 'Kinetoterapie recuperare', 'kineto_recuperare', 'sala_kineto', 'kineto'),
(4, 'Workout de grup', 'fitness_group', 'sala_fitness', 'trainer'),
(5, 'Antrenament în aer liber', 'fitness_group', 'exterior', 'trainer'),
(6, 'Personal Training', 'personal_training', 'sala_aparate', 'trainer'),
(7, 'Sesiune de Evaluare', 'kineto_examen', 'sala_kineto', 'kineto');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `staff_availability`
--

CREATE TABLE `staff_availability` (
  `id` int(11) NOT NULL,
  `trainer_id` int(11) DEFAULT NULL,
  `available_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `is_booked` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `staff_availability`
--

INSERT INTO `staff_availability` (`id`, `trainer_id`, `available_date`, `start_time`, `end_time`, `is_booked`) VALUES
(1, 4, '2026-05-20', '09:00:00', '14:00:00', 0),
(2, 4, '2026-05-21', '09:00:00', '14:00:00', 0),
(6, 5, '2026-05-20', '08:00:00', '12:00:00', 0),
(7, 5, '2026-05-22', '09:00:00', '12:00:00', 0),
(8, 4, '2026-05-22', '09:00:00', '11:00:00', 0),
(9, 5, '2026-05-27', '09:00:00', '12:00:00', 0),
(10, 4, '2026-05-28', '12:53:00', '21:00:00', 0),
(11, 4, '2026-05-29', '08:00:00', '14:00:00', 0),
(12, 5, '2026-05-29', '08:00:00', '14:00:00', 0),
(13, 5, '2026-05-30', '10:00:00', '15:00:00', 0);

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `tier` enum('membru','premium','vip') DEFAULT 'membru',
  `has_fitness` tinyint(1) DEFAULT 0,
  `has_forta` tinyint(1) DEFAULT 0,
  `has_kineto` tinyint(1) DEFAULT 0,
  `has_vip_perks` tinyint(1) DEFAULT 0,
  `is_suspended` tinyint(1) NOT NULL DEFAULT 0,
  `start_date` date DEFAULT NULL,
  `expires_at` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `tier`, `has_fitness`, `has_forta`, `has_kineto`, `has_vip_perks`, `is_suspended`, `start_date`, `expires_at`) VALUES
(3, 3, 'premium', 1, 0, 1, 0, 0, '2026-04-30', '2026-06-19'),
(10, 8, 'membru', 0, 1, 0, 0, 0, NULL, '2026-06-29'),
(11, 9, 'membru', 0, 0, 1, 0, 0, NULL, '2026-06-19');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) DEFAULT NULL,
  `role` enum('admin','trainer','member','kineto') NOT NULL DEFAULT 'member',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(2, 'admin_kim', 'admin@kim.ro', '$2y$10$YM4gUyyXZGEb9jWxauNiROJXEI7oD0/Q6ZttgWH3NXQWsVGQ8NqyS', 'admin', '2026-04-02 12:40:57'),
(3, 'AndreiSGD', 'isandrei@gmail.com', '$2y$10$NLIgIR/yTN7yPLhTP3odperpf1f8U638cVfsiRgVmNAVh2Kv4kU7q', 'member', '2026-04-21 10:24:42'),
(4, 'alexia_25', 'alecom@gmail.com', '$2y$10$ZgTmGqSRihAbe9IxbT3WhuM5kNePxiqtuFbGQtzs6205AYKHh6Fxq', 'trainer', '2026-04-21 11:45:37'),
(5, 'raisa', 'raisa@email.com', '$2y$10$pHp3LhAGZOiW0DcvwvwMruG.R87KEgLMEUWzt1DIvaGOraNqT7Xom', 'kineto', '2026-05-14 12:06:49'),
(8, 'andrei', 'andrei@gmail.com', '$2y$10$SLqxpjExWbO9caY2836AdewMeGAsrhumBC7jWXn2aHkGh.mqWE7gW', 'member', '2026-05-19 11:50:03'),
(9, 'roxana26', 'totosicarmelia@gmail.com', '$2y$10$JtADT9RSxelBiuTypMQJBeoyIUtrZ7N3D50/ZCBZ3U8W9DZHYNtCu', 'member', '2026-05-19 14:10:46');

-- --------------------------------------------------------

--
-- Structură tabel pentru tabel `user_details`
--

CREATE TABLE `user_details` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nume` varchar(100) DEFAULT NULL,
  `prenume` varchar(100) DEFAULT NULL,
  `data_nasterii` date DEFAULT NULL,
  `judet` varchar(50) DEFAULT NULL,
  `oras` varchar(50) DEFAULT NULL,
  `adresa` varchar(255) DEFAULT NULL,
  `telefon` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Eliminarea datelor din tabel `user_details`
--

INSERT INTO `user_details` (`id`, `user_id`, `nume`, `prenume`, `data_nasterii`, `judet`, `oras`, `adresa`, `telefon`) VALUES
(1, 3, 'Popescu', 'Andrei', '2003-04-01', 'Iasi', 'Iasi', 'adresa ', '0737613871'),
(2, 4, 'Coman', 'Alexia', '2006-01-01', 'Iasi', 'Iasi', 'adresa mea', '0729374613'),
(3, 5, 'Moldoveanu', 'Raisa', '2005-05-11', 'Iasi', 'Iasi', 'Str Lalelelor Nr 5', '0746392745'),
(5, 8, 'Andrei', 'Andrei', '2002-06-01', 'Vrancea', 'Focsani', 'Str Albinelor Nr 6', '0739639465'),
(6, 9, 'Roxana', 'Roxana', '2002-01-16', 'Vaslui', 'Vaslui', 'Blv 1 Mai, Nr 35', '0794364827');

--
-- Indexuri pentru tabele eliminate
--

--
-- Indexuri pentru tabele `activities_history`
--
ALTER TABLE `activities_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexuri pentru tabele `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `session_type_id` (`session_type_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `room_id` (`room_id`);

--
-- Indexuri pentru tabele `patient_medical_records`
--
ALTER TABLE `patient_medical_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_patient_therapist` (`patient_id`,`therapist_id`),
  ADD KEY `therapist_id` (`therapist_id`);

--
-- Indexuri pentru tabele `pending_upgrades`
--
ALTER TABLE `pending_upgrades`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexuri pentru tabele `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `session_types`
--
ALTER TABLE `session_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexuri pentru tabele `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Indexuri pentru tabele `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexuri pentru tabele `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexuri pentru tabele `user_details`
--
ALTER TABLE `user_details`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT pentru tabele eliminate
--

--
-- AUTO_INCREMENT pentru tabele `activities_history`
--
ALTER TABLE `activities_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pentru tabele `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT pentru tabele `patient_medical_records`
--
ALTER TABLE `patient_medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pentru tabele `pending_upgrades`
--
ALTER TABLE `pending_upgrades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pentru tabele `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pentru tabele `session_types`
--
ALTER TABLE `session_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pentru tabele `staff_availability`
--
ALTER TABLE `staff_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pentru tabele `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pentru tabele `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pentru tabele `user_details`
--
ALTER TABLE `user_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constrângeri pentru tabele eliminate
--

--
-- Constrângeri pentru tabele `activities_history`
--
ALTER TABLE `activities_history`
  ADD CONSTRAINT `activities_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constrângeri pentru tabele `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`session_type_id`) REFERENCES `session_types` (`id`),
  ADD CONSTRAINT `appointments_ibfk_3` FOREIGN KEY (`staff_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_4` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`);

--
-- Constrângeri pentru tabele `patient_medical_records`
--
ALTER TABLE `patient_medical_records`
  ADD CONSTRAINT `patient_medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `patient_medical_records_ibfk_2` FOREIGN KEY (`therapist_id`) REFERENCES `users` (`id`);

--
-- Constrângeri pentru tabele `pending_upgrades`
--
ALTER TABLE `pending_upgrades`
  ADD CONSTRAINT `pending_upgrades_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constrângeri pentru tabele `staff_availability`
--
ALTER TABLE `staff_availability`
  ADD CONSTRAINT `staff_availability_ibfk_1` FOREIGN KEY (`trainer_id`) REFERENCES `users` (`id`);

--
-- Constrângeri pentru tabele `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constrângeri pentru tabele `user_details`
--
ALTER TABLE `user_details`
  ADD CONSTRAINT `user_details_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
