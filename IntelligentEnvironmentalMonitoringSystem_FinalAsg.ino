#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>
#include <DHT.h>
#include <WiFiClient.h>
#include <ArduinoJson.h>

#define DHTPIN D2       // Define the DHT sensor pin
#define DHTTYPE DHT22   // Define the type of DHT sensor
#define MQ135PIN A0     // Define the MQ-135 sensor pin
#define RELAYPIN D5     // Define the relay pin

DHT dht(DHTPIN, DHTTYPE);   // Initialize DHT sensor

const char* ssid = "liying";   // WiFi SSID
const char* password = "liying28";  // WiFi Password
const char* serverName = "http://192.168.43.143/IntelligentEnvironmentalMonitoringSystem_FinalAsg/post.php";  // Server URL

String apiKeyValue = "YongChun021030";  // API Key value

WiFiClient client;   // Initialize WiFi client

void setup() {
  Serial.begin(115200);  // Initialize serial communication at 115200 baud rate
  dht.begin();           // Start the DHT sensor
  pinMode(RELAYPIN, OUTPUT);   // Set relay pin as output

  WiFi.begin(ssid, password);  // Begin WiFi connection
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {  // Wait until the WiFi is connected
    delay(1000);                           // Delay for 1 second
    Serial.print(".");                     // Print a dot for connection progress
  }
  Serial.println("\nConnected to WiFi");  // Print confirmation when connected
}

void loop() {
  float temperature = dht.readTemperature();  // Read temperature from DHT22
  float humidity = dht.readHumidity();        // Read humidity from DHT22
  int co2_level = analogRead(MQ135PIN);       // Read CO2 level from MQ-135

  if (isnan(temperature) || isnan(humidity)) {          // Check if the readings are valid
    Serial.println("Failed to read from DHT sensor!");  // Print error message if readings are invalid
    return;                                             // Exit the loop if readings are invalid
  }

  // Print readings to serial monitor
  Serial.print("Humidity: ");
  Serial.print(humidity);
  Serial.print(" %\t");
  Serial.print("Temperature: ");
  Serial.print(temperature);
  Serial.print(" °C");
  Serial.println("");
  Serial.print("CO2: ");
  Serial.print(co2_level);
  Serial.println(" PPM");

  String condition;  // Variable to store air quality condition

  // Determine air quality status based on CO2 level
  if ((co2_level >= 300) && (co2_level <= 1000)) {          // Check if CO2 level is in the 'Good' range
    Serial.println("Condition: Good");                      // Print condition to serial monitor
    condition = "Good";                                     // Set condition variable to 'Good'
  } else if ((co2_level >= 1000) && (co2_level <= 2000)) {  // Check if CO2 level is in the 'Bad' range
    Serial.println("Condition: Bad");                       // Print condition to serial monitor
    condition = "Bad";                                      // Set condition variable to 'Bad'
  } else {                                                  // Check if CO2 level is in the 'Danger' range
    Serial.println("Condition: Danger");                    // Print condition to serial monitor
    condition = "Danger";                                   // Set condition variable to 'Danger'
  }

  if (WiFi.status() == WL_CONNECTED) {                                    // Check if WiFi is connected
    HTTPClient http;                                                      // Create an HTTP client instance
    Serial.println("WiFi is connected");                                  // Print confirmation of WiFi connection
    Serial.println("Connecting to server...");                            // Print message before connecting to server
    http.begin(client, serverName);                                       // Initialize HTTP connection to server
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");  // Add HTTP header for form data

    // Prepare data to be sent in the HTTP POST request
    String httpRequestData = "api_key=" + apiKeyValue + "&temperature=" + String(temperature) + "&humidity=" + String(humidity) + "&co2_level=" + String(co2_level) + "&condition=" + condition;

    Serial.print("Sending data: ");
    Serial.println(httpRequestData);  // Print data being sent

    int httpResponseCode = http.POST(httpRequestData);  // Send HTTP POST request and get response code

    if (httpResponseCode > 0) {  // Check if response code indicates success
      String response = http.getString();  // Get response from server
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);  // Print HTTP response code
      Serial.println(response);          // Print server response

      // Parse JSON response
      DynamicJsonDocument doc(1024);
      deserializeJson(doc, response);
      int relay_status = doc["relay_status"];  // Get relay state from JSON response

      // Control relay based on relay state from server
      if (relay_status == 1) {
        digitalWrite(RELAYPIN, HIGH);  // Turn relay on
        delay(100);                    // Blink relay on for 100 ms
        digitalWrite(RELAYPIN, LOW);   // Turn relay off
        delay(100);                    // Blink relay off for 100 ms
      } else {
        digitalWrite(RELAYPIN, LOW);  // Turn relay off
      }

    } else {
      Serial.print("Error on sending POST: ");
      Serial.println(httpResponseCode);  // Print error message if POST request fails
    }

    http.end();  // End HTTP connection
  } else {
    Serial.println("WiFi Disconnected");  // Print error message if WiFi is disconnected
  }

  delay(10000);  // Delay for 10 seconds before next loop
}
