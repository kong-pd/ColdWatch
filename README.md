ColdWatch:
A cloud-based cold chain temperature monitoring system built with Docker, Flask, MySQL, and AWS SNS.

Architecture:
Python Simulator → Flask REST API → MySQL Database → AWS SNS Email Alerts
PHP Dashboard ← Flask REST API

Tech Stack:
Backend: Python (Flask REST API)
Database: MySQL 8.0
Frontend: PHP
Alerts: AWS SNS (email notifications)
DevOps: Docker, Docker Compose, GitHub

Features:
Real-time temperature monitoring across multiple sensors
Automatic HIGH/LOW breach detection with configurable thresholds
Instant email alerts via AWS SNS
Live dashboard with auto-refresh every 10 seconds
Fully containerized with Docker Compose

Quick Start:
1. Clone the repo
2. Create a `.env` file (see `.env.example`)
3. Run `docker-compose up --build`
4. Open `http://localhost:8080`

Project Structure:
coldwatch/
├── flask-api/       # REST API (Python/Flask)
├── simulator/       # Sensor data simulator (Python)
├── php-app/         # Web dashboard (PHP)
├── mysql-init/      # Database schema
└── docker-compose.yml
