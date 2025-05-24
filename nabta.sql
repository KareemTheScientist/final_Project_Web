-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 10, 2025 at 02:25 PM
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
-- Database: `nabta`
--

-- --------------------------------------------------------

--
-- Table structure for table `about_us`
--

CREATE TABLE `about_us` (
  `id` int(11) NOT NULL,
  `content` text NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `about_us`
--

INSERT INTO `about_us` (`id`, `content`, `updated_at`) VALUES
(1, 'Welcome to Nabta! We are passionate about plant care, smart gardening, and sustainable indoor environments.', '2025-04-12 02:51:49');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

CREATE TABLE `blogs` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `created_at`, `updated_at`) VALUES
(4, 2, '2025-05-10 10:11:16', '2025-05-10 10:11:16'),
(5, 1, '2025-05-10 12:20:18', '2025-05-10 12:20:18');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `item_type` enum('plant','product') NOT NULL DEFAULT 'plant'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `first_name`, `last_name`, `email`, `address`, `city`, `country`, `payment_method`, `total_amount`, `created_at`) VALUES
(1, 2, 'kareem', 'refaat', 'test@gewewfq.com', 'cairo egypt', '6th of october', 'CA', 'paypal', 107.49, '2025-05-10 11:34:46'),
(2, 2, 'kareem', 'refaat', 'test@gewewfq.com', 'cairo egypt', '6th of october', 'US', 'credit_card', 67.23, '2025-05-10 11:35:15'),
(3, 2, 'kareem', 'refaat', 'kareemrefaat8008@gmail.com', 'cairo egypt', '6th of october', 'UK', 'credit_card', 17.25, '2025-05-10 11:37:45'),
(4, 2, 'kareem', 'refaat', 'kareemrefaat8008@gmail.com', 'cairo egypt', '6th of october', 'US', 'credit_card', 17.25, '2025-05-10 11:39:30'),
(5, 2, 'kareem', 'refaat', 'kareemrefaat8008@gmail.com', 'cairo egypt', '6th of october', 'US', 'credit_card', 9.75, '2025-05-10 11:41:25'),
(6, 2, 'kareem', 'refaat', 'kareemrefaat8008@gmail.com', 'cairo egypt', '6th of october', 'US', 'cash_on_delivery', 152.98, '2025-05-10 12:18:52'),
(7, 1, 'kareem', 'refaat', 'kareemrefaat8008@gmail.com', 'cairo egypt', '6th of october', 'US', 'cash_on_delivery', 89.99, '2025-05-10 12:20:45');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_type` enum('plant','product') NOT NULL,
  `plant_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_type`, `plant_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 'plant', 34, NULL, 9, 10.50),
(2, 1, 'plant', 21, NULL, 1, 12.99),
(3, 2, 'product', NULL, 6, 1, 29.99),
(4, 2, 'product', NULL, 5, 1, 19.99),
(5, 2, 'plant', 18, NULL, 1, 7.50),
(6, 2, 'plant', 19, NULL, 1, 9.75),
(7, 3, 'plant', 18, NULL, 1, 7.50),
(8, 3, 'plant', 19, NULL, 1, 9.75),
(9, 4, 'plant', 18, NULL, 1, 7.50),
(10, 4, 'plant', 19, NULL, 1, 9.75),
(11, 5, 'plant', 19, NULL, 1, 9.75),
(12, 6, 'plant', 19, NULL, 1, 9.75),
(13, 6, 'plant', 32, NULL, 1, 10.75),
(14, 6, 'plant', 18, NULL, 1, 7.50),
(15, 6, 'product', NULL, 2, 1, 89.99),
(16, 6, 'product', NULL, 3, 1, 34.99),
(17, 7, 'product', NULL, 2, 1, 89.99);

-- --------------------------------------------------------

--
-- Table structure for table `plants`
--

CREATE TABLE `plants` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) NOT NULL,
  `short_description` text NOT NULL,
  `long_description` text DEFAULT NULL,
  `category` enum('herbs','vegetables','flowers') NOT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_new` tinyint(1) DEFAULT 0,
  `active` tinyint(1) DEFAULT 1,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `plants`
--

INSERT INTO `plants` (`id`, `name`, `price`, `image_url`, `short_description`, `long_description`, `category`, `is_featured`, `is_new`, `active`, `date_added`) VALUES
(17, 'Basil', 8.99, '/FinalProject/final_Project_Web/img/basil.jpg', 'Aromatic basil perfect for Italian dishes', 'This versatile herb thrives in warm conditions and needs plenty of sunlight. Great for pesto, salads, and garnishing.', 'herbs', 1, 0, 1, '2025-04-30 21:40:49'),
(18, 'Mint', 7.50, '/FinalProject/final_Project_Web/img/mint.jpg', 'Refreshing mint ideal for teas and cocktails', 'Fast-growing mint with a bright flavor. Keep in containers as it can spread aggressively in gardens.', 'herbs', 1, 1, 1, '2025-04-30 21:40:49'),
(19, 'Rosemary', 9.75, '/FinalProject/final_Project_Web/img/rosemary.jpg', 'Fragrant woody herb for roasting', 'Drought-tolerant perennial with needle-like leaves. Excellent with roasted meats and potatoes.', 'herbs', 1, 0, 1, '2025-04-30 21:40:49'),
(20, 'Parsley', 6.25, '/FinalProject/final_Project_Web/img/parsley.jpg', 'Classic garnish with mild flavor', 'Biennial herb rich in vitamins. Flat-leaf variety has more flavor than curly types.', 'herbs', 0, 0, 1, '2025-04-30 21:40:49'),
(21, 'Cherry Tomato', 12.99, '/FinalProject/final_Project_Web/img/tomato.jpg', 'Sweet bite-sized tomatoes', 'Compact variety perfect for containers. Produces abundant clusters of sweet, juicy tomatoes all season.', 'vegetables', 1, 0, 1, '2025-04-30 21:40:49'),
(23, 'Lettuce Mix', 7.25, '/FinalProject/final_Project_Web/img/lettuce.jpg', 'Assorted salad greens', 'Fast-growing blend of tender lettuces. Harvest leaves as needed for continuous production.', 'vegetables', 0, 0, 1, '2025-04-30 21:40:49'),
(24, 'Dwarf Carrot', 8.75, '/FinalProject/final_Project_Web/img/Dwarf-Carrots.png', 'Sweet baby carrots', 'Short-rooted variety ideal for containers. Sweet flavor develops best in cool weather.', 'vegetables', 0, 1, 1, '2025-04-30 21:40:49'),
(25, 'Orchid', 24.99, '/FinalProject/final_Project_Web/img/orchid.jpg', 'Elegant flowering plant', 'Exotic blooms that last for months. Prefers bright indirect light and weekly watering.', 'flowers', 1, 0, 1, '2025-04-30 21:40:49'),
(26, 'Peace Lily', 18.50, '/FinalProject/final_Project_Web/img/peace-lily.png', 'Air-purifying white blooms', 'Low-maintenance plant that thrives in low light. Dramatic white flowers appear periodically.', 'flowers', 1, 1, 1, '2025-04-30 21:40:49'),
(27, 'African Violet', 14.75, '/FinalProject/final_Project_Web/img/african-violet.png', 'Velvety purple flowers', 'Compact flowering plant that does well in indoor conditions. Blooms repeatedly with proper care.', 'flowers', 0, 0, 1, '2025-04-30 21:40:49'),
(28, 'Succulent Assortment', 15.99, '/FinalProject/final_Project_Web/img/Succulent Assortment.png', 'Drought-resistant plants', 'Collection of 3-5 small succulents in decorative arrangement. Very low maintenance.', 'flowers', 0, 1, 1, '2025-04-30 21:40:49'),
(29, 'Aloe Vera', 11.25, '/FinalProject/final_Project_Web/img/aloe.jpg', 'Healing succulent plant', 'Medicinal gel inside leaves soothes burns. Requires minimal watering and bright light.', 'herbs', 0, 1, 1, '2025-04-30 21:40:49'),
(30, 'Spider Plant', 9.99, '/FinalProject/final_Project_Web/img/Spider Plant.png', 'Easy-care hanging plant', 'Produces baby plantlets on long stems. Excellent for purifying indoor air.', 'flowers', 0, 0, 1, '2025-04-30 21:40:49'),
(31, 'English Ivy', 8.50, '/FinalProject/final_Project_Web/img/English Ivy.png', 'Classic trailing vine', 'Versatile plant for hanging baskets or as ground cover. Prefers cool temperatures.', 'flowers', 0, 0, 1, '2025-04-30 21:40:49'),
(32, 'Lavender', 10.75, '/FinalProject/final_Project_Web/img/lavender.jpg', 'Fragrant purple herb', 'Beautiful flowers with calming scent. Needs full sun and well-drained soil.', 'herbs', 1, 0, 1, '2025-04-30 21:40:49'),
(33, 'Chili Pepper', 9.99, '/FinalProject/final_Project_Web/img/chili.jpg', 'Spicy chili peppers for heat lovers', 'Medium-hot chili peppers perfect for salsas, stir-fries, and hot sauces. Grows well in containers.', 'vegetables', 1, 1, 1, '2025-04-30 21:53:19'),
(34, 'Bell Pepper', 10.50, '/FinalProject/final_Project_Web/img/bellpepper.png', 'Sweet colorful peppers', 'Produces green peppers that mature to red, yellow or orange. High in vitamin C and versatile in cooking.', 'vegetables', 1, 0, 1, '2025-04-30 21:53:19');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `category` enum('pot','sensor','utility') NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `stock` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `description`, `price`, `image_url`, `stock`, `created_at`) VALUES
(1, 'smart garden 9', 'pot', '6\" self-watering pot with moisture sensor and reservoir', 24.99, '/FinalProject/final_Project_Web/img/smart-garden-9.jpg', 42, '2025-05-03 19:36:28'),
(2, 'smart garden 3', 'pot', 'Complete hydroponic system with LED grow lights and pump', 89.99, '/FinalProject/final_Project_Web/img/smart-garden-3.jpg', 0, '2025-05-03 19:36:28'),
(3, 'smart garden mini', 'pot', '8\" ceramic pot with integrated soil sensor', 34.99, '/FinalProject/final_Project_Web/img/smart-garden-mini.jpg', 20, '2025-05-03 19:36:28'),
(5, 'Soil Moisture Sensor', 'sensor', 'Wireless sensor for accurate soil moisture measurement', 19.99, '/FinalProject/final_Project_Web/img/soil-sensor.png', 93, '2025-05-03 19:36:28'),
(6, 'Plant Health Monitor', 'sensor', 'Tracks light, temperature, humidity and soil nutrients', 29.99, '/FinalProject/final_Project_Web/img/health-monitor.png', 57, '2025-05-03 19:36:28'),
(7, 'Water Level Sensor', 'sensor', 'Alerts when water reservoir needs refilling', 14.99, '/FinalProject/final_Project_Web/img/water-sensor.jpg', 50, '2025-05-03 19:36:28'),
(9, 'plant-food', 'utility', 'Essential tools for plant care (pruner, trowel, spray bottle)', 24.99, '/FinalProject/final_Project_Web/img/plant-food.png', 77, '2025-05-03 19:36:28'),
(10, 'pet-plants', 'utility', 'Slow-release fertilizer for indoor plants (3 months supply)', 12.99, '/FinalProject/final_Project_Web/img/pet-plants.png', 116, '2025-05-03 19:36:28'),
(11, 'Decorative Pebbles', 'utility', '1kg bag of natural pebbles for plant decoration', 8.99, '/FinalProject/final_Project_Web/img/pebbles.jpg', 193, '2025-05-03 19:36:28'),
(12, 'rare-plants', 'utility', 'Premium glass mister for tropical plants', 14.99, '/FinalProject/final_Project_Web/img/rare-plants.png', 86, '2025-05-03 19:36:28'),
(13, 'grow-domes', 'utility', 'Full spectrum LED bulb for plant growth', 18.99, '/FinalProject/final_Project_Web/img/grow-domes.png', 64, '2025-05-03 19:36:28'),
(14, 'led-panel', 'utility', '50 reusable plant markers with pen', 6.99, '/FinalProject/final_Project_Web/img/led-panel.png', 146, '2025-05-03 19:36:28');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `plan_name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_months` int(11) NOT NULL,
  `start_date` date DEFAULT curdate(),
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remember_token` varchar(64) DEFAULT NULL,
  `token_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `created_at`, `remember_token`, `token_expires`) VALUES
(1, 'kareem', 'starsbrawl365@gmail.com', '$2y$10$sFmEQ0FZEUMuOcTPH7hJ8uGi4RaxeCz7QXF0lwE/lsbmkSekl9TTG', '2025-04-12 19:46:27', NULL, NULL),
(2, 'ahmed', 'ahmed@gmail.com', '$2y$10$zPS3DG6TDWyrG6dvIz2GjunBGfY2RYkFV6nfRXFjvqoPgt.V5Lu5y', '2025-04-12 22:28:26', 'c59600d598f213cac62039591b85a55ab7a68783bcaeae05d3ae8ea0c9e8278e', '2025-06-02 21:40:35'),
(3, 'test', 'test@gewewfq.com', '$2y$10$hNlqZGf9MUMLDX4uXmjObOkpFneqObVLEzd3PYeQooQO4xRRz.Ju6', '2025-05-05 08:57:42', NULL, NULL),
(4, 'noha', 'noha@gmail.com', '$2y$10$vnskny5yyL7QUr0fJwc/jeUgmnKOM9EVj0hzA9V4nZz4sfG39cihC', '2025-05-05 11:17:51', NULL, NULL),
(5, 'ola', 'ola@gmail.com', '$2y$10$Ax78zLZMcpp37kt43NbTn.Jmm/yap//wd2RDkD5ON9ai7JiD7jDcW', '2025-05-05 11:18:43', NULL, NULL),
(6, 'yousef', 'yousef@gmail.com', '$2y$10$p35iVkDqFhO6YbhA7gD42uc9TIWGNCMEgB5LfllYqYHMGLAWjFNs6', '2025-05-05 11:59:19', NULL, NULL),
(7, 'abdullah', 'abdu123@gmail.com', '$2y$10$Ie34wYYzxQgGBfHDLiVsL.TXuX14IRRWUdo3RyMfWGBv/Lx2KpgYO', '2025-05-06 06:03:13', NULL, NULL),
(8, 'amr', 'amr@gmail.com', '$2y$10$653ELecpcAwyA29fy9SrAee3rXFwWCHMW4WsPOhROIXTfuUtErKBi', '2025-05-10 09:41:21', NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `about_us`
--
ALTER TABLE `about_us`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `blogs`
--
ALTER TABLE `blogs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`cart_id`,`plant_id`,`product_id`,`item_type`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `plant_id` (`plant_id`),
  ADD KEY `fk_cart_items_product` (`product_id`);

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
  ADD KEY `plant_id` (`plant_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `plants`
--
ALTER TABLE `plants`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `blogs`
--
ALTER TABLE `blogs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=243;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `plants`
--
ALTER TABLE `plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_3` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `fk_cart_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_plant` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`),
  ADD CONSTRAINT `fk_order_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
