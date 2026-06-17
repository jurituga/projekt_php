-- Freelancer type + service availability system
-- Run in phpMyAdmin (select database "webjob")
--
-- IMPORTANT: phpMyAdmin stops on the first error.
-- If a column already exists, comment out that ALTER TABLE line and re-run.
-- Or run each statement one at a time.

USE webjob;

-- 1. Add freelancer_type column (skip if already added)
-- ALTER TABLE freelancer_profiles
--     ADD COLUMN freelancer_type VARCHAR(50) NULL DEFAULT 'general' AFTER user_id;

-- 2. Drop old table if it was created from previous migration
DROP TABLE IF EXISTS freelancer_availability;

-- 3. Service availability: each row = one date + time slot for a specific service
CREATE TABLE IF NOT EXISTS service_availability (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    service_id INT UNSIGNED NOT NULL,
    available_date DATE NOT NULL,
    slot_time TIME NOT NULL COMMENT 'e.g. 08:00, 11:00, 14:00, 17:00',
    is_booked TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_slot (service_id, available_date, slot_time),
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_service (service_id),
    INDEX idx_date (available_date),
    INDEX idx_booked (is_booked)
) ENGINE=InnoDB;

-- 4. Add booking columns to service_requests
--    Run these ONE AT A TIME in phpMyAdmin if you get "Duplicate column" errors.
--    Comment out any line that already exists.

ALTER TABLE service_requests
    ADD COLUMN booking_time TIME NULL AFTER booking_date;

ALTER TABLE service_requests
    ADD COLUMN booking_slot_id INT UNSIGNED NULL AFTER booking_time;

-- 5. Add rejection_reason column
ALTER TABLE service_requests
    ADD COLUMN rejection_reason TEXT NULL AFTER booking_slot_id;

-- 6. Freelancer ratings table
CREATE TABLE IF NOT EXISTS freelancer_ratings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    freelancer_id INT UNSIGNED NOT NULL,
    reviewer_id INT UNSIGNED NOT NULL,
    service_request_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL COMMENT '1-5 stars',
    review TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (service_request_id),
    FOREIGN KEY (freelancer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_request_id) REFERENCES service_requests(id) ON DELETE CASCADE,
    INDEX idx_freelancer (freelancer_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB;

-- 7. Conversations table
CREATE TABLE IF NOT EXISTS conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_one INT UNSIGNED NOT NULL,
    user_two INT UNSIGNED NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_pair (user_one, user_two),
    FOREIGN KEY (user_one) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user_two) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_one (user_one),
    INDEX idx_user_two (user_two)
) ENGINE=InnoDB;

-- 8. Messages table
CREATE TABLE IF NOT EXISTS messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation (conversation_id),
    INDEX idx_sender (sender_id),
    INDEX idx_read (is_read)
) ENGINE=InnoDB;

-- 9. Payment columns on service_requests (run each one separately if needed)
ALTER TABLE service_requests
    ADD COLUMN payment_status ENUM('unpaid','paid','refunded') NOT NULL DEFAULT 'unpaid' AFTER rejection_reason;

ALTER TABLE service_requests
    ADD COLUMN payment_amount DECIMAL(10,2) NULL AFTER payment_status;

ALTER TABLE service_requests
    ADD COLUMN stripe_session_id VARCHAR(255) NULL AFTER payment_amount;

ALTER TABLE service_requests
    ADD COLUMN stripe_payment_intent VARCHAR(255) NULL AFTER stripe_session_id;

ALTER TABLE service_requests
    ADD COLUMN paid_at TIMESTAMP NULL AFTER stripe_payment_intent;
