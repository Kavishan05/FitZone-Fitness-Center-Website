<?php
$host = 'localhost';
$dbname = 'gym_management';
$username = 'root';
$password = '';

try {

    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    $pdo->exec("USE $dbname");


    

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");





    $pdo->exec("CREATE TABLE IF NOT EXISTS memberships (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        type VARCHAR(50) NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        status ENUM('active', 'expired') NOT NULL DEFAULT 'active',
        FOREIGN KEY (user_id) REFERENCES users(id)


    )");
    

    $pdo->exec("CREATE TABLE IF NOT EXISTS schedules (
        id INT PRIMARY KEY AUTO_INCREMENT,
        class_name VARCHAR(100) NOT NULL,
        trainer VARCHAR(100) NOT NULL,
        date DATE NOT NULL,
        time TIME NOT NULL,
        capacity INT NOT NULL,
        current_bookings INT DEFAULT 0


    )");
    

    $pdo->exec("CREATE TABLE IF NOT EXISTS bookings
    (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        schedule_id INT,
        booking_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'cancelled') DEFAULT 'active',
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (schedule_id) REFERENCES schedules(id)
    )");
    

} catch(PDOException $e) {
    
    echo "Connection failed: " . $e->getMessage();
}
?>
