<!DOCTYPE html>
<html>

<head>
    <title>Analysis Summary</title>
    <meta http-equiv="refresh" content="10"> <!-- Refreshes the page every 10 seconds -->
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
        }

        .header {
            background-color: #D8BFD8;
            color: white;
            padding: 7px;
            font-size: 20px;
            font-weight: bold;
        }

        .sidebar {
            height: 100%;
            width: 200px;
            position: fixed;
            left: 0;
            top: 0;
            background-color: #f1f1f1;
            padding-top: 20px;
        }

        .sidebar a {
            display: block;
            padding: 16px;
            text-decoration: none;
            color: black;
            font-size: 18px;
        }

        .sidebar a:hover {
            background-color: #ddd;
        }

        .content {
            margin-left: 200px;
            padding: 20px;
        }

        .title {
            margin-left: 50px;
            font-size: 18px;
            margin: 5px 0;
        }

        .host {
            background-color: #D8BFD8;
            color: white;
            padding: 10px;
            font-size: 18px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background-color: #f0f0f0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }

        .current-analysis {
            margin: 20px 0;
            font-size: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        .current-analysis div {
            margin: 10px 0;
            display: flex;
            align-items: center;
        }

        .current-analysis img {
            margin-right: 10px;
        }
    </style>
</head>

<body>
    <!-- Sidebar for navigation -->
    <div class="sidebar">
        <div class="header">IEMS</div>
        <a href="index.php">Dashboard</a>
        <a href="analysis_summary.php">Analysis Summary</a>
        <a href="statistic_summary.php">Statistic Summary</a>
        <a href="view_data.php">View Data</a>
    </div>

    <!-- Header for the Analysis Summary page -->
    <div class="header">
        <div class="title">Intelligent Environmental Monitoring System - Analysis Summary</div>
    </div>

    <!-- Content section for displaying the analysis summary -->
    <div class="content">
        <?php
        // Database connection details
        $servername = "localhost";
        $dbname = "intelligent_environmental_monitoring";
        $username = "root";
        $password = "";

        // Create connection to the database
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check the connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // SQL query to fetch the latest analysis results
        $avgSql = "SELECT avg_temperature, avg_humidity, avg_co2_level, relay_status FROM analysis_results ORDER BY id DESC LIMIT 1";
        $avgResult = $conn->query($avgSql);
        $avgData = $avgResult->fetch_assoc();

        // Display the analysis results if available
        if ($avgData) {
            echo '<div class="current-analysis">';
            echo '<div class="title"> </div>';
            echo '<div><img src="humidity.png" alt="Humidity Icon" width="30"><span>Average Humidity: ' . round($avgData["avg_humidity"], 2) . '%</span></div>';
            echo '<div><img src="temperature.jpg" alt="Temperature Icon" width="30"><span>Average Temperature: ' . round($avgData["avg_temperature"], 2) . '°C</span></div>';
            echo '<div><img src="co2level.jpg" alt="CO2 Icon" width="30"><span>Average CO2 Level: ' . round($avgData["avg_co2_level"], 2) . ' ppm</span></div>';
            echo '<div> </div>';
            echo '<div>Current Relay Status: <span style="color: ' . ($avgData["relay_status"] ? "red" : "green") . '; font-weight: bold;">' . ($avgData["relay_status"] ? "ON" : "OFF") . '</span></div>';
            echo '</div>';
        }

        // Close the database connection
        $conn->close();
        ?>
    </div>

    <!-- Footer section -->
    <div class="footer">
        &copy; 2024 Intelligent Environmental Monitoring System. Wong Li Ying 289912 All Rights Reserved.
    </div>
</body>

</html>
