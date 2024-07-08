<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8"> <!-- Sets the character encoding for the document -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Ensures proper rendering and touch zooming on mobile devices -->
    <title>Statistic Summary</title> <!-- Title of the webpage -->
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

        .chart-container {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 20px;
            margin: 20px 0;
            width: 80%;
            margin-left: auto;
            margin-right: auto;
        }

        .chart-title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
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

    <!-- Header for the Statistic Summary page -->
    <div class="header">
        <div class="title">Intelligent Environmental Monitoring System - Statistic Summary</div>
    </div>

    <!-- Content section for displaying the statistics -->
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

        $limit = 10; // Number of records to display per page
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0; // Offset for pagination
        $viewAll = isset($_GET['viewall']); // Flag to check if all data should be viewed

        // SQL query to fetch data based on viewAll flag
        $sql = $viewAll ? "SELECT id, humidity, temperature, co2_level, `condition`, timestamp FROM sensor_input_data ORDER BY id DESC" : "SELECT id, humidity, temperature, co2_level, `condition`, timestamp FROM sensor_input_data ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        $data = [];

        // Fetch data and store it in $data array if available
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            echo '<p>No data available</p>';
        }

        // Close the database connection
        $conn->close();
        ?>

        <!-- Chart containers to display data visualizations -->
        <div class="chart-container">
            <div class="chart-title">Humidity Over Time</div>
            <canvas id="humidityChart"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">Temperature Over Time</div>
            <canvas id="temperatureChart"></canvas>
        </div>
        <div class="chart-container">
            <div class="chart-title">CO2 Level Over Time</div>
            <canvas id="co2Chart"></canvas>
        </div>

        <!-- Scripts for rendering charts using Chart.js -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // JavaScript for charts
            const data = <?php echo json_encode($data); ?>; // Convert PHP array to JavaScript array
            if (Array.isArray(data) && data.length > 0) {
                const labels = data.map(item => new Date(item.timestamp).toLocaleString());
                const humidityValues = data.map(item => item.humidity);
                const temperatureValues = data.map(item => item.temperature);
                const co2Values = data.map(item => item.co2_level);

                // Function to generate chart options
                function chartOptions(title) {
                    return {
                        responsive: true,
                        title: {
                            display: true,
                            text: title
                        },
                        scales: {
                            xAxes: [{
                                type: 'time',
                                time: {
                                    unit: 'minute'
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Time'
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true
                                },
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Value'
                                }
                            }]
                        }
                    };
                }

                // Create humidity chart
                new Chart(document.getElementById('humidityChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Humidity (%)',
                            data: humidityValues,
                            borderColor: 'blue',
                            fill: false
                        }]
                    },
                    options: chartOptions('Humidity Over Time')
                });

                // Create temperature chart
                new Chart(document.getElementById('temperatureChart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Temperature (°C)',
                            data: temperatureValues,
                            borderColor: 'red',
                            fill: false
                        }]
                    },
                    options: chartOptions('Temperature Over Time')
                });

                // Create CO2 level chart
                new Chart(document.getElementById('co2Chart'), {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'CO2 Level (ppm)',
                            data: co2Values,
                            borderColor: 'green',
                            fill: false
                        }]
                    },
                    options: chartOptions('CO2 Level Over Time')
                });
            } else {
                console.log('No data available.');
                // Optionally handle the case where no data is available
            }
        </script>
    </div>
</body>

</html>
