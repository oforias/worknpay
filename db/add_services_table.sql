-- Add services table for Service Selection feature
-- This allows workers to create specific service offerings with pricing

CREATE TABLE IF NOT EXISTS services (
    service_id INT PRIMARY KEY AUTO_INCREMENT,
    worker_id INT NOT NULL,
    service_name VARCHAR(200) NOT NULL,
    service_category VARCHAR(100) NOT NULL,
    service_description TEXT,
    base_price DECIMAL(10,2) NOT NULL,
    estimated_duration INT COMMENT 'Duration in minutes',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    INDEX idx_worker (worker_id),
    INDEX idx_category (service_category),
    INDEX idx_active (is_active),
    INDEX idx_price (base_price)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add service_id column to bookings table
ALTER TABLE bookings 
ADD COLUMN service_id INT NULL AFTER worker_id,
ADD FOREIGN KEY (service_id) REFERENCES services(service_id) ON DELETE SET NULL;

-- Add index for faster lookups
ALTER TABLE bookings ADD INDEX idx_service (service_id);
