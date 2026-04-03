CREATE DATABASE IF NOT EXISTS hotel_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_management;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  phone VARCHAR(25) NOT NULL,
  role ENUM('owner','admin','manager','reception','housekeeping','kitchen','security','customer') NOT NULL DEFAULT 'customer',
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE rooms (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_number VARCHAR(20) NOT NULL UNIQUE,
  floor_no INT NOT NULL,
  room_type VARCHAR(40) NOT NULL,
  status ENUM('available','occupied','dirty','maintenance','blocked') NOT NULL DEFAULT 'available',
  base_rate DECIMAL(12,2) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_rooms_status_floor (status, floor_no)
) ENGINE=InnoDB;

CREATE TABLE bookings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_code VARCHAR(24) NOT NULL UNIQUE,
  guest_user_id INT UNSIGNED NOT NULL,
  room_id INT UNSIGNED NOT NULL,
  check_in DATE NOT NULL,
  check_out DATE NOT NULL,
  adults TINYINT UNSIGNED NOT NULL DEFAULT 1,
  children TINYINT UNSIGNED NOT NULL DEFAULT 0,
  source VARCHAR(40) NOT NULL DEFAULT 'Direct',
  status ENUM('pending','confirmed','checked_in','checked_out','cancelled') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_bookings_guest FOREIGN KEY (guest_user_id) REFERENCES users(id),
  CONSTRAINT fk_bookings_room FOREIGN KEY (room_id) REFERENCES rooms(id),
  INDEX idx_bookings_dates_status (check_in, check_out, status),
  INDEX idx_bookings_guest (guest_user_id)
) ENGINE=InnoDB;

CREATE TABLE service_requests (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  booking_id INT UNSIGNED NULL,
  room_id INT UNSIGNED NULL,
  guest_user_id INT UNSIGNED NULL,
  request_type VARCHAR(40) NOT NULL,
  description TEXT NOT NULL,
  priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  status ENUM('open','in_progress','done') NOT NULL DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_service_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL,
  CONSTRAINT fk_service_room FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
  CONSTRAINT fk_service_guest FOREIGN KEY (guest_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_service_status_priority (status, priority)
) ENGINE=InnoDB;

CREATE TABLE housekeeping_tasks (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  room_id INT UNSIGNED NOT NULL,
  assigned_to_user_id INT UNSIGNED NULL,
  task_type VARCHAR(50) NOT NULL,
  priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
  status ENUM('pending','in_progress','done') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_tasks_room FOREIGN KEY (room_id) REFERENCES rooms(id),
  CONSTRAINT fk_tasks_user FOREIGN KEY (assigned_to_user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX idx_tasks_status_priority (status, priority)
) ENGINE=InnoDB;

CREATE TABLE invoices (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_no VARCHAR(30) NOT NULL UNIQUE,
  booking_id INT UNSIGNED NOT NULL,
  gstin VARCHAR(30) NULL,
  sub_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  tax_total DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
  payment_status ENUM('unpaid','partial','paid','refunded') NOT NULL DEFAULT 'unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_invoice_booking FOREIGN KEY (booking_id) REFERENCES bookings(id),
  INDEX idx_invoice_status_date (payment_status, created_at),
  INDEX idx_invoice_booking (booking_id)
) ENGINE=InnoDB;

CREATE TABLE payments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  invoice_id INT UNSIGNED NOT NULL,
  method ENUM('cash','card','upi','netbanking','wallet','corporate') NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  transaction_ref VARCHAR(80) NULL,
  payment_status ENUM('pending','success','failed','refunded') NOT NULL DEFAULT 'pending',
  paid_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
  INDEX idx_payments_status (payment_status),
  INDEX idx_payments_invoice (invoice_id)
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX idx_password_resets_user (user_id),
  INDEX idx_password_resets_expires (expires_at)
) ENGINE=InnoDB;
