-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:redacted
-- Generation Time: redacted
-- Server version: redacted
-- PHP Version: 7.3.31-1~deb10u1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `money`
--

-- --------------------------------------------------------

--
-- Table structure for table `monzo_auth`
--

CREATE TABLE `monzo_auth` (
  `id` int(11) NOT NULL,
  `monzo_key` text NOT NULL,
  `monzo_val` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monzo_daily_balances`
--

CREATE TABLE `monzo_daily_balances` (
  `id` int(11) NOT NULL,
  `account_id` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `balance` int(11) NOT NULL,
  `total_balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monzo_pots_daily_balances`
--

CREATE TABLE `monzo_pots_daily_balances` (
  `id` int(11) NOT NULL,
  `account_id` varchar(255) NOT NULL,
  `pot_id` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `balance` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monzo_receipts`
--

CREATE TABLE `monzo_receipts` (
  `id` int(11) NOT NULL,
  `receipt_id` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `content` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monzo_transactions`
--

CREATE TABLE `monzo_transactions` (
  `id` int(11) NOT NULL,
  `account_id` varchar(255) NOT NULL,
  `date_created` datetime NOT NULL,
  `date_settled` datetime NOT NULL,
  `amount` int(11) NOT NULL,
  `description` text NOT NULL,
  `merchant_id` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `notes` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `monzo_webhooks`
--

CREATE TABLE `monzo_webhooks` (
  `id` int(11) NOT NULL,
  `date` datetime NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `monzo_auth`
--
ALTER TABLE `monzo_auth`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monzo_daily_balances`
--
ALTER TABLE `monzo_daily_balances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monzo_pots_daily_balances`
--
ALTER TABLE `monzo_pots_daily_balances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `monzo_receipts`
--
ALTER TABLE `monzo_receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_id` (`receipt_id`);

--
-- Indexes for table `monzo_transactions`
--
ALTER TABLE `monzo_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `monzo_webhooks`
--
ALTER TABLE `monzo_webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `monzo_auth`
--
ALTER TABLE `monzo_auth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT for table `monzo_daily_balances`
--
ALTER TABLE `monzo_daily_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `monzo_pots_daily_balances`
--
ALTER TABLE `monzo_pots_daily_balances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;
--
-- AUTO_INCREMENT for table `monzo_receipts`
--
ALTER TABLE `monzo_receipts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
--
-- AUTO_INCREMENT for table `monzo_transactions`
--
ALTER TABLE `monzo_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18008;
--
-- AUTO_INCREMENT for table `monzo_webhooks`
--
ALTER TABLE `monzo_webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
