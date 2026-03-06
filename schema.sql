-- Database Name: thaniyamhub

CREATE DATABASE IF NOT EXISTS `thaniyamhub`;
USE `thaniyamhub`;

-- --------------------------------------------------------
-- Table Structure for Users
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `fullname` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('customer', 'admin') DEFAULT 'customer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table Structure for Products
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2) NOT NULL,
  `image_url` VARCHAR(255),
  `stock_quantity` INT(11) DEFAULT 0,
  `category` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table Structure for Orders
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `total_amount` DECIMAL(10, 2) NOT NULL,
  `status` ENUM('pending', 'completed', 'cancelled') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table Structure for Order Items
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `product_id` INT(11) NOT NULL,
  `quantity` INT(11) NOT NULL,
  `price_at_purchase` DECIMAL(10, 2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Sample Data (Products)
-- --------------------------------------------------------
INSERT INTO `products` (`name`, `description`, `price`, `image_url`, `stock_quantity`, `category`) VALUES
('Premium Pearl Millet (Bajra)', 'Organically grown, highly nutritious Pearl Millet. Rich in iron, protein, and fiber. Perfect for making traditional rotis, porridges, and health drinks.', 120.00, 'assets/pearl-millet.jpg', 50, 'Whole Grains'),
('Finger Millet (Ragi) Flour', 'Finely ground Ragi flour sourced from premium farms. Excellent for calcium and bone health. Ideal for dosas, idlis, and baked goods.', 95.00, 'assets/finger-millet.jpg', 100, 'Flours'),
('Foxtail Millet (Thinai)', 'A diabetic-friendly millet that helps in regulating blood sugar. Great alternative to rice and can be used in upma, pongal, and salads.', 150.00, 'assets/foxtail-millet.jpg', 75, 'Whole Grains'),
('Little Millet (Samai)', 'Known as a cooling grain, Little Millet is perfect for summer diets. It is easy to digest and rich in antioxidants.', 140.00, 'assets/little-millet.jpg', 60, 'Whole Grains'),
('Barnyard Millet (Kuthiraivali)', 'Low in calories and high in fiber, this millet is great for weight management. Tastes similar to broken rice when cooked.', 160.00, 'assets/barnyard-millet.jpg', 45, 'Whole Grains'),
('Kodo Millet (Varagu)', 'High significantly in dietary fiber and vitamins. Helps in strengthening the nervous system.', 155.00, 'assets/kodo-millet.jpg', 80, 'Whole Grains');
