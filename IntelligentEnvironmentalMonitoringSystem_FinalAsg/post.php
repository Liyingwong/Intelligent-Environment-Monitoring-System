<?php

// Database and API Key Configuration
$servername = "localhost";
$dbname = "intelligent_environmental_monitoring";
$username = "root";
$password = "";
$api_key_value = "YongChun021030";

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Handling POST Requests
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate the API key
    $api_key = test_input($_POST["api_key"]);
    
    if ($api_key == $api_key_value) {
        // Retrieve sensor data from the POST request
        $humidity = test_input($_POST["humidity"]);
        $temperature = test_input($_POST["temperature"]);
        $co2_level = test_input($_POST["co2_level"]);
        $condition = test_input($_POST["condition"]);

        // Create database connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check database connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Prepare and bind the insert statement for sensor data
        $stmt = $conn->prepare("INSERT INTO sensor_input_data (humidity, temperature, co2_level, `condition`) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            die("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("ddds", $humidity, $temperature, $co2_level, $condition);

        // Execute the prepared statement
        if ($stmt->execute()) {
            echo "New record created successfully\n";

            // Fetch the last 10 readings in descending order
            $avgSql = "SELECT humidity, temperature, co2_level FROM sensor_input_data ORDER BY id DESC LIMIT 10";
            $avgResult = $conn->query($avgSql);

            if ($avgResult && $avgResult->num_rows > 0) {
                $total_humidity = 0;
                $total_temperature = 0;
                $total_co2_level = 0;
                $count = 0;

                // Calculate the sum of the last 10 readings
                while ($row = $avgResult->fetch_assoc()) {
                    $total_humidity += $row['humidity'];
                    $total_temperature += $row['temperature'];
                    $total_co2_level += $row['co2_level'];
                    $count++;
                }

                // Calculate the average values
                $avg_humidity = $total_humidity / $count;
                $avg_temperature = $total_temperature / $count;
                $avg_co2_level = $total_co2_level / $count;

                // Debugging outputs to check averages
                echo "Average Humidity: " . $avg_humidity . "\n";
                echo "Average Temperature: " . $avg_temperature . "\n";
                echo "Average CO2 Level: " . $avg_co2_level . "\n";

                // Determine relay status based on the new condition
                if (($avg_co2_level < 400 || $avg_co2_level > 1000) || $avg_humidity < 70 || $avg_temperature > 35) {
                    $relay_status = 1;
                    echo "Relay Status: ON\n";
                } else {
                    $relay_status = 0;
                    echo "Relay Status: OFF\n";
                }

                // Prepare and bind the insert statement for analysis results
                $analysisStmt = $conn->prepare("INSERT INTO analysis_results (avg_temperature, avg_humidity, avg_co2_level, relay_status) VALUES (?, ?, ?, ?)");
                if ($analysisStmt === false) {
                    die("Prepare failed: " . $conn->error);
                }
                $analysisStmt->bind_param("dddi", $avg_temperature, $avg_humidity, $avg_co2_level, $relay_status);
                $analysisStmt->execute();
                $analysisStmt->close();

                echo "Average values calculated and inserted into analysis_results table";

                // Send the relay status back to the ESP8266 in a JSON response
                echo json_encode(array("relay_status" => $relay_status));
            } else {
                echo "Error fetching last 10 readings: " . $conn->error;
            }
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the statement and connection
        $stmt->close();
        $conn->close();
    } else {
        echo "Wrong API Key provided.";
    }
} else {
    echo "No data posted with HTTP POST.";
}

// Input Sanitization
function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
