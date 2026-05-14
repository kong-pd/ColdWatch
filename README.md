ColdWatch,
A cloud-based cold chain temperature monitoring system built with Docker, Flask, MySQL, and AWS SNS.

ColdWatch monitors temperature across multiple cold storage zones in real time. When a sensor reading exceeds safe thresholds, the system automatically triggers an email alert via AWS SNS, that help prevent spoilage before it happens.

Architecture：

ColdWatch follows a microservices architecture with four containerized services managed by Docker Compose.

The Python Simulator generates sensor readings every 5 seconds and sends them to the Flask REST API via HTTP POST. The API validates the data, writes it to MySQL, and checks whether the temperature exceeds the configured threshold for that sensor. If a breach is detected, it publishes an alert to AWS SNS, which delivers an email notification immediately.

The PHP Dashboard runs independently, fetching the latest readings and alerts from the Flask API every 5 seconds via JavaScript and rendering them without a full page reload.

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
CI pipeline via GitHub Actions: import checks, Docker build verification, AWS connectivity test
Role-based access control with session management (admin / operator)

Quick Start:
Prerequisites: Docker Desktop, Git
1. Clone the repo
git clone https://github.com/kong-gif123/coldwatch.git, cd coldwatch
3. Create .env file
cp .env.example .env (Fill in your AWS credentials in .env)
4. Run `docker-compose up --build`
5. Open dashboard `http://localhost:8080`

Project Structure:
coldwatch/
├── flask-api/          # REST API (Python/Flask)
│   ├── app.py
│   ├── requirements.txt
│   └── Dockerfile
├── simulator/          # Sensor data simulator (Python)
│   ├── simulator.py
│   └── Dockerfile
├── php-app/            # Web dashboard
│   ├── index.php       # HTML structure
│   ├── style.css       # Styles
│   ├── app.js          # API calls & DOM updates
│   └── Dockerfile
├── mysql-init/
│   └── init.sql        # Database schema
├── .github/workflows/
│   └── ci.yml          # GitHub Actions CI
├── docker-compose.yml
└── .env                # (gitignored)
