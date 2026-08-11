-- Migration: Add image tables for venues and courts
-- Date: 2026-06-23

-- Venue Images Table
CREATE TABLE IF NOT EXISTS venue_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    venue_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_type ENUM('featured', 'gallery') DEFAULT 'gallery',
    caption VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (venue_id) REFERENCES venues(id) ON DELETE CASCADE,
    INDEX idx_venue_id (venue_id),
    INDEX idx_image_type (image_type),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Court Images Table
CREATE TABLE IF NOT EXISTS court_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    court_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    image_type ENUM('featured', 'gallery') DEFAULT 'gallery',
    caption VARCHAR(255) DEFAULT NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (court_id) REFERENCES courts(id) ON DELETE CASCADE,
    INDEX idx_court_id (court_id),
    INDEX idx_image_type (image_type),
    INDEX idx_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
