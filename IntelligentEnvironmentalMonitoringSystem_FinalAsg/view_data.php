<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Data</title>
    <meta http-equiv="refresh" content="10">
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

        
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="header">IEMS</div>
        <a href="index.php">Dashboard</a>
        <a href="analysis_summary.php">Analysis Summary</a>
        <a href="statistic_summary.php">Statistic Summary</a>
        <a href="view_data.php">View Data</a>
    </div>

    <div class="header">
        <div class="title">Intelligent Environmental Monitoring System - View Data</div>
    </div>

    <div class="content">
        <?php
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

        $limit = 10;
        $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
        $viewAll = isset($_GET['viewall']);

        // Toggle between viewing all data and limited data
        $sql = $viewAll ? "SELECT id, humidity, temperature, co2_level, `condition`, timestamp FROM sensor_input_data ORDER BY id DESC" : "SELECT id, humidity, temperature, co2_level, `condition`, timestamp FROM sensor_input_data ORDER BY id DESC LIMIT $limit OFFSET $offset";
        $result = $conn->query($sql);
        $data = [];

        if ($result->num_rows > 0) {
            // Fetch data and store it in $data array
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            echo '<p>No data available</p>';
        }

        echo '<table>
          <tr>
              <th>ID</th>
              <th>Humidity (%)</th>
              <th>Temperature (°C)</th>
              <th>CO2 Level (ppm)</th>
              <th>Condition</th>
              <th>Timestamp</th>
          </tr>';
        foreach ($data as $row) {
            // Setting the condition cell color based on the condition value
            $conditionClass = $row["condition"] === 'good' ? 'style="color:green"' : '';

            echo '<tr>
            <td>' . $row["id"] . '</td>
            <td>' . $row["humidity"] . '</td>
            <td>' . $row["temperature"] . '</td>
            <td>' . $row["co2_level"] . '</td>
            <td ' . $conditionClass . '>' . ucfirst($row["condition"]) . '</td>
            <td>' . $row["timestamp"] . '</td>
            </tr>';
        }
        echo '</table>';

        // Total rows count for pagination
        $totalRowsResult = $conn->query("SELECT COUNT(*) AS total FROM sensor_input_data");
        $totalRows = $totalRowsResult->fetch_assoc()['total'];

        // Navigation buttons for pagination and view toggling
        echo '<div class="nav-buttons">';
        if ($viewAll) {
            echo '<button onclick="window.location.href=\'view_data.php\'">Show Latest 10</button>';
        } else {
            echo '<button onclick="window.location.href=\'view_data.php?viewall=1\'">View All</button>';
        }
        echo '</div>';

        $conn->close();
        ?>

    </div>

    <div class="footer">
        
    </div>
</body>

</html>