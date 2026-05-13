-- ColdWatch 数据库初始化脚本
CREATE DATABASE IF NOT EXISTS coldwatch;
USE coldwatch;

-- 传感器注册表
CREATE TABLE sensors (
    sensor_id   INT PRIMARY KEY AUTO_INCREMENT,
    sensor_name VARCHAR(100) NOT NULL,
    location    VARCHAR(100) NOT NULL,
    min_temp    DECIMAL(5,2) NOT NULL DEFAULT 2.00,
    max_temp    DECIMAL(5,2) NOT NULL DEFAULT 8.00,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 温度记录
CREATE TABLE temperature_readings (
    reading_id  INT PRIMARY KEY AUTO_INCREMENT,
    sensor_id   INT NOT NULL,
    temperature DECIMAL(5,2) NOT NULL,
    recorded_at DATETIME NOT NULL,
    received_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sensor_id) REFERENCES sensors(sensor_id),
    INDEX idx_sensor_time (sensor_id, recorded_at DESC)
);

-- 告警日志
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

-- 用户表
CREATE TABLE users (
    user_id       INT PRIMARY KEY AUTO_INCREMENT,
    username      VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','operator') DEFAULT 'operator',
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- 插入测试传感器
INSERT INTO sensors (sensor_name, location, min_temp, max_temp) VALUES
('Sensor-A', 'Warehouse Zone A', 2.00, 8.00),
('Sensor-B', 'Warehouse Zone B', 2.00, 8.00),
('Sensor-C', 'Cold Storage Room', -5.00, 0.00);