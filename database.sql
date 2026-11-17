-- SeyBook Appointment Booking System
-- Import this file in phpMyAdmin (Import tab). It creates the database, tables and sample data.
CREATE DATABASE IF NOT EXISTS seybook_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE seybook_db;

DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS services;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin','user') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE services (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  description VARCHAR(255) NOT NULL,
  price DECIMAL(8,2) NOT NULL,
  duration_min INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE appointments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  service_id INT NOT NULL,
  appt_date DATE NOT NULL,
  appt_time TIME NOT NULL,
  notes VARCHAR(255) DEFAULT NULL,
  status ENUM('Pending','Confirmed','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Sample accounts: admin / Admin@123  and  demo / User@123 (passwords are stored hashed)
INSERT INTO users (full_name, email, username, password, role) VALUES
('System Administrator', 'admin@seybook.sc', 'admin', '$2b$12$wubejoWIHLS.cPe4QY2JT.L/oIuHoMuVhJcEa/LMGb8VxKoOyXetO', 'admin'),
('Demo Patient', 'demo@seybook.sc', 'demo', '$2b$12$b0vnNBUVCJ/Y/SMBoYJfOeN9ModZQ/mIZk9drlBQiahL/KVJ814i.', 'user');

INSERT INTO services (name, description, price, duration_min) VALUES
('General Consultation', 'See a doctor about everyday health concerns and get a treatment plan.', 350.00, 30),
('Dental Check-up', 'Full dental examination with cleaning and advice.', 450.00, 45),
('Physiotherapy Session', 'One-to-one session for back, joint and sports injuries.', 500.00, 60),
('Eye Test', 'Complete vision test and glasses prescription.', 300.00, 30),
('Nutrition Consultation', 'Personal meal guidance from a registered dietitian.', 400.00, 45),
('Massage Therapy', 'Deep-tissue massage to relieve muscle tension.', 550.00, 60);

INSERT INTO appointments (user_id, service_id, appt_date, appt_time, notes, status) VALUES
(2, 1, '2026-11-20', '09:00:00', 'Follow-up visit', 'Confirmed'),
(2, 3, '2026-11-24', '14:00:00', 'Lower back pain', 'Pending'),
(2, 4, '2026-11-12', '11:00:00', NULL, 'Completed');
