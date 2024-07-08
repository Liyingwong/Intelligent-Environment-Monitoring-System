# Intelligent-Environment-Monitoring-System
This project implements an intelligent environmental monitoring system using ESP8266, sensors (DHT22 and MQ135), and a web-based dashboard for real-time data visualization.

Features
Sensor Integration: Measures humidity, temperature, and CO2 levels using DHT22 and MQ135 sensors.
Real-time Data: Sends sensor data to a MySQL database for storage and analysis.
Dashboard: Visualizes sensor data in real-time using Chart.js.
Control: Automatically controls a relay based on environmental conditions.

Setup Instructions
Hardware Requirements
ESP8266 microcontroller (NodeMCU or similar)
DHT22 temperature and humidity sensor
MQ135 gas sensor
1 Channel Relay module

Software Requirements
Arduino IDE for ESP8266 programming
PHP and MySQL server for data storage and backend processing
Web server (Apache, Nginx) for hosting the dashboard

Installation Steps
Clone the repository:
git clone https://github.com/Liyingwong/Intelligent-Environment-Monitoring-System.git
cd Intelligent-Environment-Monitoring-System

ESP8266 Setup:

Open esp8266_code/esp8266_code.ino in Arduino IDE.
Update WiFi credentials (ssid and password) and server IP (serverName) in the code.
Upload the code to your ESP8266 board.

Server Setup:

Import database.sql into your MySQL database to create the necessary table.
Place insert-data.php on your web server (e.g., Apache’s htdocs directory).

Dashboard Setup:

Place index.html and script.js in your web server’s directory.
Ensure Chart.js is included and accessible in your HTML file.

Run the System:

Power on the ESP8266 and sensors.
Access the dashboard via a web browser to view real-time sensor data.
