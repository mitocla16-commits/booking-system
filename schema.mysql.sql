CREATE DATABASE IF NOT EXISTS booking_demo CHARACTER SET utf8mb4;
USE booking_demo;
CREATE TABLE IF NOT EXISTS bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    service VARCHAR(80) NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    message TEXT NULL,
    status ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_slot (booking_date, booking_time)
) ENGINE=InnoDB;
