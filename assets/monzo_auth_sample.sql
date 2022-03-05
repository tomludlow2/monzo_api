-- phpMyAdmin SQL Dump
-- version 4.6.6deb5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Mar 05, 2022 at 10:56 AM
-- Server version: 10.3.31-MariaDB-0+deb10u1
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
-- Table structure for table `monzo_auth_sample`
--

CREATE TABLE `monzo_auth_sample` (
  `id` int(11) NOT NULL,
  `monzo_key` text NOT NULL,
  `monzo_val` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `monzo_auth_sample`
--

INSERT INTO `monzo_auth_sample` (`id`, `monzo_key`, `monzo_val`) VALUES
(1, 'test', 'value 1'),
(2, 'client_id', 'oauth2client_*your_oauth_client_id*'),
(3, 'redirect_uri', 'https://yourdomain.tld/path/oauth.php'),
(4, 'state', '*generate_random_state_token*'),
(5, 'client_secret', '*your_client_secret*'),
(7, 'temporary_code', 'auto_filled'),
(8, 'access_token', 'auto_filled'),
(9, 'expires_at', 'auto_filled'),
(10, 'refresh_token', 'auto_filled'),
(11, 'scope', 'auto_filled'),
(12, 'account_id', 'auto_filled'),
(13, 'account_number', 'auto_filled'),
(14, 'account_sort_code', 'auto_filled'),
(15, 'current_balance', 'auto_filled'),
(16, 'current_total_balance', 'auto_filled'),
(17, 'pots_list', 'auto_filled'),
(18, 'expires_at_human', 'auto_filled'),
(19, 'webhook_ids', 'auto_filled'),
(20, 'next_receipt_number', 'auto_filled'),
(21, 'account_created', 'auto_filled'),
(22, 'preferred_name', 'auto_filled'),
(23, 'first_name', 'auto_filled'),
(24, 'account_created_human', 'auto_filled');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `monzo_auth_sample`
--
ALTER TABLE `monzo_auth_sample`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `monzo_auth_sample`
--
ALTER TABLE `monzo_auth_sample`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
