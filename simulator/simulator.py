import time
import random
import requests
from datetime import datetime

# Flask API 地址（在 Docker 里用服务名，本地测试用 localhost）
API_URL = "http://flask-api:5000/reading"

# 三个传感器，对应数据库里的 sensor_id 1、2、3
SENSORS = [
    {"sensor_id": 1, "name": "Sensor-A", "min": 2.0,  "max": 8.0},
    {"sensor_id": 2, "name": "Sensor-B", "min": 2.0,  "max": 8.0},
    {"sensor_id": 3, "name": "Sensor-C", "min": -5.0, "max": 0.0},
]

def generate_temperature(sensor):
    # 90% 概率正常，10% 概率超标（触发告警）
    if random.random() < 0.10:
        if random.random() < 0.5:
            # 偏高
            return round(random.uniform(sensor["max"] + 0.5, sensor["max"] + 5.0), 2)
        else:
            # 偏低
            return round(random.uniform(sensor["min"] - 5.0, sensor["min"] - 0.5), 2)
    else:
        return round(random.uniform(sensor["min"], sensor["max"]), 2)

def send_reading(sensor, temperature):
    payload = {
        "sensor_id": sensor["sensor_id"],
        "temperature": temperature,
        "recorded_at": datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    }
    try:
        response = requests.post(API_URL, json=payload, timeout=5)
        status = "✓" if response.status_code == 201 else "✗"
        print(f"[{payload['recorded_at']}] {status} {sensor['name']}: {temperature}°C")
    except Exception as e:
        print(f"Connection error: {e}")

if __name__ == "__main__":
    print("ColdWatch Simulator started...")
    while True:
        for sensor in SENSORS:
            temp = generate_temperature(sensor)
            send_reading(sensor, temp)
        time.sleep(5)  # 每 5 秒发一次