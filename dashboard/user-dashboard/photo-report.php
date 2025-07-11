<?php 
session_start(); 
include "../../connection.php"; 
?>
<!doctype html>
<html lang="en">
<?php
include "head.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get filter values
$startDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$endDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');
$station_id = $_SESSION['stationId'];

$period = new DatePeriod(
    new DateTime($startDate),
    new DateInterval('P1D'),
    (new DateTime($endDate))->modify('+1 day')
);
?>
<head>
    <title>Before/After Photo Report</title>
    <style>
        .railway-frame {
            height: 90vh;
            overflow-y: auto;
            font-size: 12px;
            font-weight: 400;
            box-sizing: border-box;
        }
        .railway-container {
            width: 95%;
            margin: auto;
            page-break-after: always;
        }
        .railway-section-title {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            margin: 20px 0 10px;
            text-transform: uppercase;
        }
        .photo-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .photo-table th,
        .photo-table td {
            border: 1px solid #000;
            vertical-align: top;
            padding: 10px;
            text-align: center;
        }
        .photo-table th {
            background-color: #f2f2f2;
            font-size: 14px;
        }
        .photo-img {
            max-width: 100%;
            width: 280px;
            height: auto;
            margin-bottom: 5px;
            border: 1px solid #ccc;
        }
        .photo-info {
            font-size: 12px;
            text-align: left;
            margin-top: 5px;
        }
        .railway-filter-form {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin: 15px auto;
            width: fit-content;
        }
        .railway-filter-form label {
            font-weight: 500;
        }
        .railway-filter-form input[type="date"],
        .railway-filter-form button {
            padding: 8px 12px;
            font-size: 14px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .railway-filter-form button {
            background-color: green;
            color: white;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">
    <?php include "header.php"; ?>
    <main class="app-main">
        <form class="railway-filter-form" method="GET">
            <label for="from_date">From:</label>
            <input type="date" name="from_date" id="from_date" value="<?= $startDate; ?>">
            <label for="to_date">To:</label>
            <input type="date" name="to_date" id="to_date" value="<?= $endDate; ?>">
            <input type="hidden" name="station_id" value="<?= htmlspecialchars($station_id); ?>">
            <button type="submit">Go</button>
        </form>

        <div style="text-align:center;"><strong>Station:</strong> <?= $_SESSION['stationName']; ?></div>
        <br>

        <div class="railway-frame">
            <?php
            foreach ($period as $date) {
                $currentDate = $date->format("Y-m-d");

                // Fetch before photos
                $beforeQuery = "SELECT * FROM baris_pictures 
                                WHERE DATE(created_date) = '$currentDate' 
                                AND db_surveyStationId = '$station_id' 
                                AND photo_type = 'before' 
                                ORDER BY created_date";
                $beforeResult = $conn->query($beforeQuery);

                // Fetch after photos
                $afterQuery = "SELECT * FROM baris_pictures 
                               WHERE DATE(created_date) = '$currentDate' 
                               AND db_surveyStationId = '$station_id' 
                               AND photo_type = 'after' 
                               ORDER BY created_date";
                $afterResult = $conn->query($afterQuery);

                if (($beforeResult && $beforeResult->num_rows > 0) || ($afterResult && $afterResult->num_rows > 0)) {
                    echo '<div class="railway-container">';
                    echo "<div class='railway-section-title'>Before / After Photo Report - " . date('d-m-Y', strtotime($currentDate)) . "</div>";
                    echo '<table class="photo-table">
                            <tr>
                                <th>Before Photo</th>
                                <th>After Photo</th>
                            </tr>';

                    $maxRows = max($beforeResult->num_rows, $afterResult->num_rows);

                    for ($i = 0; $i < $maxRows; $i++) {
                        $beforeRow = $beforeResult->fetch_assoc();
                        $afterRow = $afterResult->fetch_assoc();

                        echo "<tr>";

                        // Before Photo
                        echo "<td>";
                        if ($beforeRow) {
                            $beforeImg = !empty($beforeRow['imagename']) ? "../../uploads/photos/" . $beforeRow['imagename'] : "../../uploads/photos/no-image.jpg";
                            echo "<img src='$beforeImg' class='photo-img'>";
                            echo "<div class='photo-info'>";
                            echo "<strong>User ID:</strong> {$beforeRow['db_surveyUserid']}<br>";
                            echo "<strong>Process:</strong> {$beforeRow['db_process_type']}<br>";
                            echo "<strong>Remark:</strong> {$beforeRow['remarks']}<br>";
                            echo "<strong>Time:</strong> " . date('H:i', strtotime($beforeRow['created_date']));
                            echo "</div>";
                        } else {
                            echo "No data";
                        }
                        echo "</td>";

                        // After Photo
                        echo "<td>";
                        if ($afterRow) {
                            $afterImg = !empty($afterRow['imagename']) ? "../../uploads/photos/" . $afterRow['imagename'] : "../../uploads/photos/no-image.jpg";
                            echo "<img src='$afterImg' class='photo-img'>";
                            echo "<div class='photo-info'>";
                            echo "<strong>User ID:</strong> {$afterRow['db_surveyUserid']}<br>";
                            echo "<strong>Process:</strong> {$afterRow['db_process_type']}<br>";
                            echo "<strong>Remark:</strong> {$afterRow['remarks']}<br>";
                            echo "<strong>Time:</strong> " . date('H:i', strtotime($afterRow['created_date']));
                            echo "</div>";
                        } else {
                            echo "No data";
                        }
                        echo "</td>";

                        echo "</tr>";
                    }

                    echo "</table>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </main>
    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>&copy; 2025</strong> All rights reserved.
    </footer>
</div>
</body>
</html>
