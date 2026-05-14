import os
import boto3
from datetime import datetime
from flask import Flask, request, jsonify
from flask_cors import CORS
import mysql.connector
from dotenv import load_dotenv

load_dotenv()

app = Flask(__name__)
CORS(app)

# 数据库连接
def get_db():
    return mysql.connector.connect(
        host=os.getenv("DB_HOST", "mysql"),
        user=os.getenv("DB_USER", "coldwatch"),
        password=os.getenv("DB_PASSWORD", "coldwatch123"),
        database=os.getenv("DB_NAME", "coldwatch")
    )

# 发送 SNS 告警邮件
def send_alert(sensor_name, temperature, breach_type, threshold):
    try:
        sns = boto3.client(
            "sns",
            region_name=os.getenv("AWS_REGION", "ap-southeast-1"),
            aws_access_key_id=os.getenv("AWS_ACCESS_KEY_ID"),
            aws_secret_access_key=os.getenv("AWS_SECRET_ACCESS_KEY")
        )
        message = (
            f"[ColdWatch Alert] {breach_type} temperature breach!\n"
            f"Sensor  : {sensor_name}\n"
            f"Temperature : {temperature}°C\n"
            f"Threshold   : {threshold}°C\n"
            f"Time        : {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
        )
        sns.publish(
            TopicArn=os.getenv("SNS_TOPIC_ARN"),
            Message=message,
            Subject="ColdWatch Temperature Alert"
        )
    except Exception as e:
        print(f"SNS error: {e}")

# 接收温度数据
@app.route("/reading", methods=["POST"])
def receive_reading():
    data = request.get_json()
    sensor_id   = data["sensor_id"]
    temperature = data["temperature"]
    recorded_at = data.get("recorded_at", datetime.now().strftime("%Y-%m-%d %H:%M:%S"))

    db = get_db()
    cursor = db.cursor(dictionary=True)

    # 存温度记录
    cursor.execute(
        "INSERT INTO temperature_readings (sensor_id, temperature, recorded_at) VALUES (%s, %s, %s)",
        (sensor_id, temperature, recorded_at)
    )

    # 检查是否超标
    cursor.execute("SELECT * FROM sensors WHERE sensor_id = %s", (sensor_id,))
    sensor = cursor.fetchone()

    if sensor:
        breach_type = None
        threshold   = None
        if temperature > float(sensor["max_temp"]):
            breach_type = "HIGH"
            threshold   = sensor["max_temp"]
        elif temperature < float(sensor["min_temp"]):
            breach_type = "LOW"
            threshold   = sensor["min_temp"]

        if breach_type:
            cursor.execute(
                "INSERT INTO alert_logs (sensor_id, temperature, breach_type, threshold_value) VALUES (%s, %s, %s, %s)",
                (sensor_id, temperature, breach_type, threshold)
            )
            send_alert(sensor["sensor_name"], temperature, breach_type, threshold)

    db.commit()
    cursor.close()
    db.close()

    return jsonify({"status": "ok"}), 201

# 查询最新温度（给 PHP 用）
@app.route("/readings", methods=["GET"])
def get_readings():
    db = get_db()
    cursor = db.cursor(dictionary=True)
    cursor.execute("""
        SELECT s.sensor_name, s.location, t.temperature, t.recorded_at
        FROM temperature_readings t
        JOIN sensors s ON t.sensor_id = s.sensor_id
        ORDER BY t.recorded_at DESC
        LIMIT 50
    """)
    rows = cursor.fetchall()
    for row in rows:
        row["recorded_at"] = str(row["recorded_at"])
    cursor.close()
    db.close()
    return jsonify(rows)

# 查询告警记录
@app.route("/alerts", methods=["GET"])
def get_alerts():
    db = get_db()
    cursor = db.cursor(dictionary=True)
    cursor.execute("""
        SELECT a.*, s.sensor_name
        FROM alert_logs a
        JOIN sensors s ON a.sensor_id = s.sensor_id
        ORDER BY a.created_at DESC
        LIMIT 20
    """)
    rows = cursor.fetchall()
    for row in rows:
        row["created_at"] = str(row["created_at"])
    cursor.close()
    db.close()
    return jsonify(rows)

import bcrypt

@app.route("/login", methods=["POST"])
def login():
    data = request.get_json()
    username = data.get("username")
    password = data.get("password")

    db = get_db()
    cursor = db.cursor(dictionary=True)
    cursor.execute("SELECT * FROM users WHERE username = %s", (username,))
    user = cursor.fetchone()
    cursor.close()
    db.close()

    if not user:
        return jsonify({"error": "Invalid credentials"}), 401

    if bcrypt.checkpw(password.encode(), user["password_hash"].encode()):
        return jsonify({
            "user_id": user["user_id"],
            "username": user["username"],
            "role": user["role"]
        }), 200
    else:
        return jsonify({"error": "Invalid credentials"}), 401
    
if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5000, debug=True)