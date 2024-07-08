<!DOCTYPE html>
<html>

<head>
  <title>Data Dashboard</title>
  <meta http-equiv="refresh" content="10">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/justgage@1.2.9/raphael-2.1.4.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/justgage@1.2.9/justgage.js"></script>
  <style>
    /* Basic styling for the dashboard */
    body {
      font-family: Arial, sans-serif;
      text-align: center;
    }

    .header {
      background-color: #D8BFD8;
      /* Light purple */
      color: white;
      padding: 10px;
      font-size: 24px;
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
      font-size: 36px;
      margin: 20px 0;
    }

    .host {
      background-color: #D8BFD8;
      /* Light purple */
      color: white;
      padding: 10px;
      font-size: 18px;
      margin-bottom: 20px;
      font-weight: bold;
    }

    .current-values {
      margin: 20px 0;
      font-size: 20px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .current-values div {
      margin: 10px 0;
      display: flex;
      align-items: center;
    }

    .current-values img {
      vertical-align: middle;
      margin-right: 10px;
    }

    .condition-button {
      padding: 10px 20px;
      font-size: 20px;
      border: none;
      color: white;
      cursor: pointer;
    }

    .good {
      background-color: green;
    }

    .bad {
      background-color: red;
    }

    .gauge-container {
      width: 200px;
      height: 160px;
      display: inline-block;
      margin-right: 30px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin: 20px 0;
    }

    th,
    td {
      border: 1px solid #ddd;
      padding: 8px;
    }

    th {
      padding-top: 12px;
      padding-bottom: 12px;
      text-align: center;
      background-color: #D8BFD8;
      color: white;
    }

    .chart-container {
      width: 45%;
      margin: 20px auto;
    }

    .nav-buttons {
      margin: 20px 0;
    }
  </style>
</head>

<body>
  <div class="sidebar">
    <div class="header" style="background-color: #D8BFD8; color: white; padding: 10px; font-size: 24px; font-weight: bold;">IEMS</div>
    <a href="index.php">Dashboard</a>
    <a href="analysis_summary.php">Analysis Summary</a>
    <a href="statistic_summary.php">Statistic Summary</a>
    <a href="view_data.php">View Data</a>
  </div>

  <div class="content">
    <div class="header">Intelligent Environmental Monitoring System</div>
    <div class="host">Host: Wong Li Ying 289912</div>

    <?php
    // Database connection settings
    $servername = "localhost";
    $dbname = "intelligent_environmental_monitoring";
    $username = "root";
    $password = "";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Fetch the latest average data
    $avgSql = "SELECT avg_temperature, avg_humidity, avg_co2_level, relay_status FROM analysis_results ORDER BY id DESC LIMIT 1";
    $avgResult = $conn->query($avgSql);
    $avgData = $avgResult->fetch_assoc();

    // Pagination and data fetching
    $limit = 10;
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $viewAll = isset($_GET['viewall']);

    // Toggle between viewing all data and limited data
    $sql = $viewAll ? "SELECT id, humidity, temperature, co2_level, `condition`, timestamp FROM sensor_input_data ORDER BY id DESC" : "SELECT id, humidity, temperature, co2_level, `condition`, timestamp FROM sensor_input_data ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
    $data = [];

    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        $data[] = $row;
      }
    } else {
      echo '<p>No data available</p>';
    }

    // Display the latest sensor data
    if (!empty($data)) {
      $latestData = $data[0];
      echo '<div class="current-values">';
      echo '<div><img src="humidity.png" alt="Humidity Icon" width="30"><span>Current Humidity: ' . $latestData["humidity"] . '%</span></div>';
      echo '<div><img src="temperature.jpg" alt="Temperature Icon" width="30"><span>Current Temperature: ' . $latestData["temperature"] . '°C</span></div>';
      echo '<div><img src="co2level.jpg" alt="CO2 Icon" width="30"><span>Current CO2 Level: ' . $latestData["co2_level"] . ' ppm</span></div>';
      echo '<div>Condition:  <span style="color: green; font-weight: bold;">' . $latestData["condition"] . '</span></div>';
      echo '</div>';
    }

    // Total rows count for pagination
    $totalRowsResult = $conn->query("SELECT COUNT(*) AS total FROM sensor_input_data");
    $totalRows = $totalRowsResult->fetch_assoc()['total'];

    // Fetch data for the charts
    $temperatureData = [];
    $humidityData = [];
    $co2Data = [];
    $timestamps = [];
    foreach ($data as $row) {
      $temperatureData[] = $row['temperature'];
      $humidityData[] = $row['humidity'];
      $co2Data[] = $row['co2_level'];
      $timestamps[] = $row['timestamp'];
    }

    // Fetching the average data for the chart
    $avgTemperature = $avgData['avg_temperature'];
    $avgHumidity = $avgData['avg_humidity'];
    $avgCO2 = $avgData['avg_co2_level'];
    $averageTimestamps = ['Average']; // Use a single point for the average

    // Close the database connection
    $conn->close();
    ?>

    <div class="chart-container">
      <canvas id="averageGraph"></canvas>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function () {
        // Fetching data from PHP
        let temperatureData = <?php echo json_encode($temperatureData); ?>;
        let humidityData = <?php echo json_encode($humidityData); ?>;
        let co2Data = <?php echo json_encode($co2Data); ?>;
        let timestamps = <?php echo json_encode($timestamps); ?>;

        // Average data
        let avgTemperature = <?php echo json_encode($avgTemperature); ?>;
        let avgHumidity = <?php echo json_encode($avgHumidity); ?>;
        let avgCO2 = <?php echo json_encode($avgCO2); ?>;
        let averageTimestamps = <?php echo json_encode($averageTimestamps); ?>;

        console.log('Average Data:', avgTemperature, avgHumidity, avgCO2); // Debugging

        // Chart.js bar chart configuration
        let averageGraphConfig = {
          type: 'bar',
          data: {
            labels: averageTimestamps, // Single point for average
            datasets: [{
              label: 'Average Temperature (°C)',
              data: [avgTemperature],
              backgroundColor: 'rgba(255, 99, 132, 0.2)',
              borderColor: 'rgba(255, 99, 132, 1)',
              borderWidth: 1
            }, {
              label: 'Average Humidity (%)',
              data: [avgHumidity],
              backgroundColor: 'rgba(54, 162, 235, 0.2)',
              borderColor: 'rgba(54, 162, 235, 1)',
              borderWidth: 1
            }, {
              label: 'Average CO2 Level (ppm)',
              data: [avgCO2],
              backgroundColor: 'rgba(75, 192, 192, 0.2)',
              borderColor: 'rgba(75, 192, 192, 1)',
              borderWidth: 1
            }]
          },
          options: {
            scales: {
              y: {
                beginAtZero: true,
                title: {
                  display: true,
                  text: 'Values'
                }
              }
            }
          }
        };

        // Render average graph
        let averageGraph = new Chart(document.getElementById('averageGraph'), averageGraphConfig);
      });
    </script>
  </div>
</body>

</html>
