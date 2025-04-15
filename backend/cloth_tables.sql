-- Create cloth_details table if it doesn't exist
CREATE TABLE IF NOT EXISTS `cloth_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `seller_id` int(11) NOT NULL,
  `cloth_title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `size` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL,
  `rental_price` decimal(10,2) NOT NULL,
  `contact_number` varchar(20) NOT NULL,
  `whatsapp_number` varchar(20) NOT NULL,
  `terms_and_conditions` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `seller_id` (`seller_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create cloth_images table if it doesn't exist
CREATE TABLE IF NOT EXISTS `cloth_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `cloth_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `cloth_id` (`cloth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Add foreign key constraint if not already present
ALTER TABLE `cloth_images`
  ADD CONSTRAINT `fk_cloth_images_cloth_details` FOREIGN KEY (`cloth_id`) REFERENCES `cloth_details` (`id`) ON DELETE CASCADE ON UPDATE CASCADE; 