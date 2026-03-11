-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 11, 2026 at 08:35 PM
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
-- Database: `aquaprime_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `chat_messages`
--

CREATE TABLE `chat_messages` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sender` enum('customer','admin') NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('Processing','Delivering','Completed','Cancelled','Pending') DEFAULT 'Pending',
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_hidden` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_time` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('Empty','Purified','Alkaline','Distilled','NonElectricDispenser','ElectricDispenser','5Galloon','Icecubes','Stickers') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_path` varchar(255) DEFAULT 'default_bottle.png',
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `image_path`, `description`) VALUES
(1, 'Empty Container', 'Empty', 200.00, 'EmptyContainer.PNG', 'Reusable round container for refill (WITH PRINTED NAME)'),
(2, '1-pc Refilled Water Container', 'Purified', 30.00, '1pcs.PNG\r\n', 'Standard refilled round container for water dispensers'),
(3, '3-pcs Refilled Water Container', 'Purified', 90.00, '3pcs.PNG', 'Standard refilled round container for water dispensers.'),
(4, '5-pcs Refilled Water Container', 'Purified', 150.00, '5pcs.PNG', 'Standard refilled round container for water dispensers'),
(5, '6-pcs Premium Bottled Water', 'Alkaline', 90.00, '6pcsbottle.PNG', 'Premium Alkaline Bottled Water'),
(6, '12-pcs Premium Bottled Water', 'Alkaline', 175.00, '12pcsbottle.PNG', 'Premium Alkaline Bottled Water'),
(7, '24-pcs Premium Bottled Water', 'Alkaline', 350.00, '24pcsbottle.PNG', 'Premium Alkaline Bottled Water'),
(8, 'Water Dispenser', 'NonElectricDispenser', 250.00, 'nonelectricdispenser.PNG', 'Non-Electric Water Dispenser'),
(9, 'Water Dispenser', 'ElectricDispenser', 7500.00, 'electricdispenser.PNG', 'Electric Water Dispenser'),
(10, '2kg Pack Ice Cubes', 'Icecubes', 50.00, 'icecubes.PNG', '100% Purified Premium Ice Cubes'),
(11, 'Aqua Prime Stickers', 'Stickers', 10.00, 'stickers.PNG', 'High-grip stickers designed for maximum control');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `email`, `password`, `phone`, `address`, `role`, `created_at`) VALUES
(2, 'franzin', 'franzin@gmail.com', '$2y$10$oevj81HmMOlvQl7F3xXk6eBKIu8vouvBMPfEmV/V8sfKyAd/vb6Qa', '', 'San Carlos City, Negros Occidental', 'admin', '2026-03-08 21:00:10'),
(3, 'fritz', 'fritz@gmail.com', '$2y$10$OF5F6lfc4q6ezr.C6u3gLupNM/UqXKY/nJwZgnqkbo15KZaSl/QRC', '', 'San Carlos City, Negros Occidental', 'admin', '2026-03-08 21:03:30'),
(4, 'user', 'user@gmail.com', '$2y$10$QHKKTNYUPM6riNYibdqF7.Zp1fQQQmvNJBw40woag2zWglrsAHfxy', '09513213213', 'San Carlos City, Negros Occidental', 'customer', '2026-03-09 19:17:21'),
(5, 'admin', 'admin@gmail.com', '$2y$10$b2mjVlffxVGZAaVQ..bFPOB73kxsfEAkotDM1yPRF3nlmsk6spxju', '09529513856', '', 'admin', '2026-03-09 19:18:05');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `chat_messages`
--
ALTER TABLE `chat_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
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
-- AUTO_INCREMENT for table `chat_messages`
--
ALTER TABLE `chat_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
