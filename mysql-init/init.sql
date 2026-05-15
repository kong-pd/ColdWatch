-- ColdWatch database initialization script
CREATE DATABASE IF NOT EXISTS coldwatch;
USE coldwatch;

-- Sensor registry
CREATE TABLE sensors (
    sensor_id   INT PRIMARY KEY AUTO_INCREMENT,
    sensor_name VARCHAR(100) NOT NULL,
    location    VARCHAR(100) NOT NULL,
    min_temp    DECIMAL(5,2) NOT NULL DEFAULT 2.00,
    max_temp    DECIMAL(5,2) NOT NULL DEFAULT 8.00,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Temperature readings
CREATE TABLE temperature_readings (
    reading_id  INT PRIMARY KEY AUTO_INCREMENT,
    sensor_id   INT NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    recorded_at DATETIME NOT NULL,
    received_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id),
    INDEX idx_sensor_time (sensor_id, recorded_at DESC)
);

-- Alert logs
CREATE TABLE alert_logs (
    alert_id         INT PRIMARY KEY AUTO_INCREMENT,
    sensor_id        INT NOT NULL,
    temperature      DECIMAL(5,2) NOT NULL,
    breach_type      ENUM('HIGH','LOW') NOT NULL,
    threshold_value  DECIMAL(5,2) NOT NULL,
    acknowledged     BOOLEAN DEFAULT FALSE,
    created_at       DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id)
);

-- Users
CREATE TABLE users (
    user_id       INT PRIMARY KEY AUTO_INCREMENT,
    username      VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','operator') DEFAULT 'operator',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed: test sensors
INSERT INTO sensors (sensor_name, location, min_temp, max_temp) VALUES
('Sensor-A', 'Warehouse Zone A', 2.00, 8.00),
('Sensor-B', 'Warehouse Zone B', 2.00, 8.00),
('Sensor-C', 'Cold Storage Room', -5.00, 0.00);

-- Seed: test users (bcrypt cost=12)
-- admin   / admin123
-- operator / operator123
INSERT INTO users (username, password_hash, role) VALUES
('admin',    '$2b$12$sUwLEr9d1Qqi0sYQygPnpuw0o3UAod.4sx2EThOm/UUM1I3eFxfBu', 'admin'),
('operator', '$2b$12$VYx7QQ4HaPoWsasZ4V2ZJug8kEHV6HpZAA2wfMGQZ93q1B5P5WZ3S', 'operator');
