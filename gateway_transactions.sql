-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 23, 2018 at 01:25 PM
-- Server version: 5.6.35
-- PHP Version: 7.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `laravel`
--

-- --------------------------------------------------------

--
-- Table structure for table `gateway_transactions`
--

CREATE TABLE `gateway_transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `OrderId` int(11) NOT NULL,
  `Amount` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `SystemTraceNo` text COLLATE utf8mb4_unicode_ci,
  `RetrivalRefNo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ResCode` text COLLATE utf8mb4_unicode_ci,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `gateway_transactions`
--
ALTER TABLE `gateway_transactions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `gateway_transactions`
--
ALTER TABLE `gateway_transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;