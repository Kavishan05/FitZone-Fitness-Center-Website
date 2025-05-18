CREATE DATABASE IF NOT EXISTS gym_management;
USE gym_management;


CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS memberships (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    type VARCHAR(50) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('active', 'expired') NOT NULL DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id)
);


CREATE TABLE IF NOT EXISTS schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_name VARCHAR(100) NOT NULL,
    trainer VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    time TIME NOT NULL,
    capacity INT NOT NULL,
    current_bookings INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS bookings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    schedule_id INT,
    booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'cancelled') DEFAULT 'active',
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (schedule_id) REFERENCES schedules(id)
);


CREATE TABLE IF NOT EXISTS membership_types (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    duration INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT NOT NULL
);


INSERT INTO membership_types (name, duration, price, description) VALUES
('Basic Monthly', 30, 3000, 'Access to gym during regular hours\nBasic fitness equipment\nLocker room access\nFree fitness consultation'),
('Premium Monthly', 30, 4000, 'Access to gym 24/7\nAll fitness equipment\nGroup fitness classes\nPersonal trainer session\nLocker room access\nSauna access'),
('Annual Basic', 365, 6000, 'Access to gym during regular hours\nBasic fitness equipment\nLocker room access\nFree fitness consultation\n2 Personal trainer sessions\nSpecial member events'),
('Annual Premium', 365, 12000, 'Access to gym 24/7\nAll fitness equipment\nUnlimited group fitness classes\nMonthly personal trainer session\nLocker room access\nSauna access\nNutrition consultation\nSpecial member events');


INSERT INTO users (username, password, email, role) VALUES 
('john_doe', 'hashed_password1', 'john@example.com', 'user'),
('admin_user', 'hashed_password2', 'admin@example.com', 'admin');


INSERT IGNORE INTO schedules (class_name, trainer, date, time, capacity) VALUES
('Morning Stretch', 'Kavishan', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '09:00:00', 20),
('Power Workout', 'Harshini', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '11:00:00', 15),
('Dance Fitness', 'Arun', DATE_ADD(CURDATE(), INTERVAL 1 DAY), '15:00:00', 25),
('Core Strength', 'Kenuja', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '10:00:00', 15),
('Fat Burn Express', 'Abishek', DATE_ADD(CURDATE(), INTERVAL 2 DAY), '14:00:00', 20),
('Cycle & Sweat', 'Nashan', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '08:00:00', 18),
('Full Body Boost', 'Afrin', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '13:00:00', 15);

CREATE INDEX idx_schedule_date ON schedules(date);
CREATE INDEX idx_bookings_user ON bookings(user_id);
CREATE INDEX idx_bookings_schedule ON bookings(schedule_id);
CREATE INDEX idx_memberships_user ON memberships(user_id);
