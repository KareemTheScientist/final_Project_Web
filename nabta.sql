-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 01, 2025 at 03:53 PM
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

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(20) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `shipping_address` text NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `total_amount`, `shipping_address`, `payment_method`, `status`, `created_at`) VALUES
(1, 1, '', 36.23, 'cairo egypt, giza gov, 6th of october, cairo, 3220001, Egypt', 'credit_card', 'completed', '2025-05-01 13:39:32'),
(2, 1, '', 24.62, 'cairo egypt, giza gov, 6th of october, cairo, 3220001, Egypt', 'credit_card', 'completed', '2025-05-01 13:52:14');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `plant_id`, `quantity`, `price`) VALUES
(1, 1, 18, 1, 7.50),
(2, 1, 19, 1, 9.75),
(3, 1, 32, 1, 10.75),
(4, 2, 18, 1, 7.50),
(5, 2, 19, 1, 9.75);

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
(2, 'ahmed', 'ahmed@gmail.com', '$2y$10$zPS3DG6TDWyrG6dvIz2GjunBGfY2RYkFV6nfRXFjvqoPgt.V5Lu5y', '2025-04-12 22:28:26', NULL, NULL);

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
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `plant_id` (`plant_id`);

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
  ADD KEY `plant_id` (`plant_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `plants`
--
ALTER TABLE `plants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
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
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`plant_id`) REFERENCES `plants` (`id`);

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
