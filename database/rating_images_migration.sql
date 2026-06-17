-- Rating Images Migration
-- Run this in phpMyAdmin to add support for review photos.

CREATE TABLE IF NOT EXISTS rating_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    rating_id INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rating_id) REFERENCES freelancer_ratings(id) ON DELETE CASCADE,
    INDEX idx_rating (rating_id)
) ENGINE=InnoDB;
