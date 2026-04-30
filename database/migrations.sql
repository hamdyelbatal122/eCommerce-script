-- ============================================================
-- ECOMMERCE SYSTEM ENHANCEMENTS - NEW TABLES
-- ============================================================
-- This file contains all new tables for advanced features
-- including: Email Verification, Shopping Cart, Orders, API Tokens,
-- Ratings, Reviews, Notifications, Activity Logging, etc.
-- ============================================================

-- ============================================================
-- TABLE: email_verifications
-- Purpose: Store email verification tokens and status
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_verifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL UNIQUE,
  `verified_at` datetime NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_token (token),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: api_tokens
-- Purpose: Store API tokens for programmatic access
-- ============================================================
CREATE TABLE IF NOT EXISTS `api_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL UNIQUE,
  `last_used_at` datetime NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `revoked_at` datetime NULL,
  `expires_at` datetime NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_token (token),
  INDEX idx_revoked (revoked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: cart_items
-- Purpose: Shopping cart management (temporary storage)
-- ============================================================
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`Item_ID`) ON DELETE CASCADE,
  UNIQUE KEY unique_cart (user_id, item_id),
  INDEX idx_user (user_id),
  INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: orders
-- Purpose: Main orders table
-- ============================================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `order_number` varchar(50) NOT NULL UNIQUE,
  `status` enum('pending', 'confirmed', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
  `payment_method` enum('credit_card', 'bank_transfer', 'cash_on_delivery', 'paypal') DEFAULT 'cash_on_delivery',
  `payment_status` enum('unpaid', 'paid', 'refunded') DEFAULT 'unpaid',
  `total_price` decimal(10, 2) NOT NULL,
  `shipping_address` text NOT NULL,
  `shipping_phone` varchar(20) NOT NULL,
  `notes` text,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `shipped_at` datetime NULL,
  `delivered_at` datetime NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  INDEX idx_order_number (order_number),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: order_items
-- Purpose: Items within each order
-- ============================================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10, 2) NOT NULL,
  `total_price` decimal(10, 2) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`Item_ID`) ON DELETE CASCADE,
  FOREIGN KEY (`seller_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  INDEX idx_order (order_id),
  INDEX idx_seller (seller_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: ratings
-- Purpose: Product ratings by users
-- ============================================================
CREATE TABLE IF NOT EXISTS `ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `item_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
  `title` varchar(255),
  `review_text` text,
  `helpful_count` int(11) DEFAULT 0,
  `status` enum('pending', 'approved', 'rejected') DEFAULT 'pending',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`item_id`) REFERENCES `items`(`Item_ID`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  UNIQUE KEY unique_rating (item_id, user_id),
  INDEX idx_item (item_id),
  INDEX idx_user (user_id),
  INDEX idx_status (status),
  INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: notifications
-- Purpose: User notifications system
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `type` enum('order', 'rating', 'comment', 'message', 'system', 'promotion') NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `data` json,
  `read_at` datetime NULL,
  `action_url` varchar(500),
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_read (read_at),
  INDEX idx_created (created_at),
  INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: activity_logs
-- Purpose: Track all user and system activities
-- ============================================================
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11),
  `action` varchar(100) NOT NULL,
  `entity_type` varchar(50) NOT NULL COMMENT 'e.g., user, item, order, comment',
  `entity_id` int(11),
  `description` text,
  `ip_address` varchar(45),
  `user_agent` text,
  `changes` json COMMENT 'Before/after changes',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE SET NULL,
  INDEX idx_user (user_id),
  INDEX idx_action (action),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: cache_entries
-- Purpose: Simple file-based cache alternative
-- ============================================================
CREATE TABLE IF NOT EXISTS `cache_entries` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `cache_key` varchar(255) NOT NULL UNIQUE,
  `cache_value` longtext NOT NULL,
  `expires_at` datetime NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_key (cache_key),
  INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: email_queue
-- Purpose: Queue for email notifications
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `status` enum('pending', 'sent', 'failed') DEFAULT 'pending',
  `attempt_count` int(11) DEFAULT 0,
  `last_error` text,
  `sent_at` datetime NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `retry_at` datetime NULL,
  INDEX idx_status (status),
  INDEX idx_created (created_at),
  INDEX idx_retry (retry_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: search_filters
-- Purpose: Store user search preferences and filters
-- ============================================================
CREATE TABLE IF NOT EXISTS `search_filters` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11),
  `filter_name` varchar(100) NOT NULL,
  `filters` json NOT NULL COMMENT 'Serialized filter data',
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`UserID`) ON DELETE CASCADE,
  INDEX idx_user (user_id),
  INDEX idx_name (filter_name),
  INDEX idx_public (is_public)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- ALTERATIONS TO EXISTING TABLES
-- ============================================================

-- Add new columns to users table if not exist
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `email_verified_at` datetime NULL AFTER `Email`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `two_factor_enabled` tinyint(1) DEFAULT 0 AFTER `email_verified_at`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `last_login_at` datetime NULL AFTER `two_factor_enabled`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `is_seller` tinyint(1) DEFAULT 0 AFTER `last_login_at`;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `google_id` varchar(191) NULL AFTER `email_verified_at`;
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_email_verified` (`email_verified_at`);
ALTER TABLE `users` ADD INDEX IF NOT EXISTS `idx_is_seller` (`is_seller`);
ALTER TABLE `users` ADD UNIQUE INDEX IF NOT EXISTS `idx_google_id_unique` (`google_id`);

-- Add new columns to items table if not exist
ALTER TABLE `items` ADD COLUMN IF NOT EXISTS `average_rating` decimal(2, 1) DEFAULT 0 AFTER `Rating`;
ALTER TABLE `items` ADD COLUMN IF NOT EXISTS `total_reviews` int(11) DEFAULT 0 AFTER `average_rating`;
ALTER TABLE `items` ADD COLUMN IF NOT EXISTS `stock_quantity` int(11) DEFAULT 999 AFTER `total_reviews`;
ALTER TABLE `items` ADD COLUMN IF NOT EXISTS `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `Add_Date`;
ALTER TABLE `items` ADD INDEX IF NOT EXISTS `idx_average_rating` (`average_rating`);
ALTER TABLE `items` ADD INDEX IF NOT EXISTS `idx_stock` (`stock_quantity`);

-- ============================================================
-- INDEXES FOR BETTER PERFORMANCE
-- ============================================================

-- Full-text search indexes
ALTER TABLE `items` ADD FULLTEXT INDEX IF NOT EXISTS `ft_search` (`Name`, `Description`, `tags`);
ALTER TABLE `comments` ADD FULLTEXT INDEX IF NOT EXISTS `ft_comments` (`comment`);

-- ============================================================
-- VIEWS FOR COMMON QUERIES
-- ============================================================

-- View for items with seller info and ratings
CREATE OR REPLACE VIEW `items_with_seller` AS
SELECT 
  i.*,
  u.Username as seller_username,
  u.FullName as seller_name,
  u.Email as seller_email,
  u.avatar as seller_avatar,
  u.TrustStatus as seller_rank,
  COUNT(DISTINCT r.id) as total_reviews,
  AVG(r.rating) as average_rating
FROM `items` i
LEFT JOIN `users` u ON i.Member_ID = u.UserID
LEFT JOIN `ratings` r ON i.Item_ID = r.item_id AND r.status = 'approved'
GROUP BY i.Item_ID;

-- View for active orders
CREATE OR REPLACE VIEW `active_orders` AS
SELECT 
  o.*,
  u.Username as buyer_username,
  u.FullName as buyer_name,
  COUNT(oi.id) as item_count,
  SUM(oi.quantity) as total_items
FROM `orders` o
LEFT JOIN `users` u ON o.user_id = u.UserID
LEFT JOIN `order_items` oi ON o.id = oi.order_id
WHERE o.status != 'cancelled'
GROUP BY o.id;

-- ============================================================
-- TABLE: payments
-- Purpose: Persist payment intents and lifecycle
-- ============================================================
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_id` int(11) NULL,
  `user_id` int(11) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'stripe',
  `provider_payment_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `status` varchar(50) NOT NULL,
  `raw_payload` longtext NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_provider_payment (provider, provider_payment_id),
  INDEX idx_user_provider (user_id, provider),
  INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: payment_events
-- Purpose: Store webhook events for auditing/idempotency
-- ============================================================
CREATE TABLE IF NOT EXISTS `payment_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `provider` varchar(50) NOT NULL,
  `provider_payment_id` varchar(255) NOT NULL,
  `event_type` varchar(120) NOT NULL,
  `payload` longtext NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_provider_intent (provider, provider_payment_id),
  INDEX idx_event_type (event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: wishlists
-- Purpose: User saved products
-- ============================================================
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_wishlist (user_id, item_id),
  INDEX idx_user (user_id),
  INDEX idx_item (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: coupons
-- Purpose: Discount coupons for checkout
-- ============================================================
CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `code` varchar(50) NOT NULL UNIQUE,
  `discount_percent` decimal(5,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `expires_at` datetime NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_code_active (code, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================
-- TABLE: password_resets
-- Purpose: Reset password flow tokens
-- ============================================================
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL UNIQUE,
  `expires_at` datetime NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_email (email),
  INDEX idx_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Track shipping numbers at order level
ALTER TABLE `orders` ADD COLUMN IF NOT EXISTS `tracking_number` varchar(100) NULL AFTER `notes`;

-- ============================================================
-- END OF MIGRATIONS
-- ============================================================
