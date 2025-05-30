<?php session_start();
include "../../connection.php"; ?>
<?php
$_SESSION['machine_report_id'] = $_GET['id'];

// Start the session
session_start();

// Check if userId is not set
if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
    // Destroy the session
    session_unset();
    session_destroy();
    header("Location: https://pmc.beatleme.co.in/");
    exit();
}

?>

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$fromDate = isset($_GET['fromDate']) ? $_GET['fromDate'] : '';
$toDate = isset($_GET['toDate']) ? $_GET['toDate'] : '';

// If no custom date provided, fallback to current month
if (empty($fromDate) || empty($toDate)) {
    $selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
    $selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

    $fromDate = "$selectedYear-$selectedMonth-01";
    $toDate = date("Y-m-t", strtotime($fromDate));
}



$station_id = $_SESSION['stationId'];

// Default date range (last 7 days)
if (empty($fromDate) || empty($toDate)) {
    $fromDate = date('Y-m-d', strtotime('-7 days'));
    $toDate = date('Y-m-d');
}

// Initialize data
$data = [];

// Variables to hold auditor names for shifts
$auditorShift1 = '';
$auditorShift2 = '';
$auditorShift3 = '';

// Query to fetch data
$query = "SELECT 
                bap.paramName AS task, 
                bp.db_pagename AS parameters, 
                bas.db_surveyValue AS Quality_of_done_work,
                bas.auditorname AS auditor_name,
                bs.stationName AS station_name,
                bo.db_Orgname AS organisation_name,
                bd.DivisionName AS division_name,
                bas.created_date AS report_date
            FROM 
                baris_param bap
                INNER JOIN baris_machine_report bas ON bap.paramId = bas.db_surveyParamId
                INNER JOIN baris_page bp ON bas.db_surveyPageId = bp.pageId
                INNER JOIN baris_station bs ON bas.db_surveyStationId = bs.stationId
                INNER JOIN baris_organization bo ON bas.OrgID = bo.OrgID
                INNER JOIN baris_division bd ON bas.DivisionId = bd.DivisionId
            WHERE 
                bas.db_surveyStationId = '$station_id' 
                AND DATE(bas.created_date) BETWEEN '$fromDate' AND '$toDate'
            ORDER BY bas.created_date ASC;";

$result = $conn->query($query);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $param = $row['parameters'];
        $task = $row['task'];
        $quantity = (int) $row['Quality_of_done_work'];
        $auditor = $row['auditor_name'];
        $station = $row['station_name'];
        $contractor = $row['organisation_name'];
        $division = $row['division_name'];
        $date = date('Y-m-d', strtotime($row['report_date']));

        // Initialize date if not set
        if (!isset($data[$date])) {
            $data[$date] = [];
        }

        // Initialize parameter if not set
        if (!isset($data[$date][$param])) {
            $data[$date][$param] = [
                'shift1' => 0,
                'shift2' => 0,
                'shift3' => 0,
                'auditor1' => '',
                'auditor2' => '',
                'auditor3' => ''
            ];
        }

        // Identify shift and assign quantity and auditor name
        if (strpos(strtolower($task), 'shift 1') !== false) {
            $data[$date][$param]['shift1'] += $quantity;
            $data[$date][$param]['auditor1'] = $auditor;
            $auditorShift1 = $auditor;
        } elseif (strpos(strtolower($task), 'shift 2') !== false) {
            $data[$date][$param]['shift2'] += $quantity;
            $data[$date][$param]['auditor2'] = $auditor;
            $auditorShift2 = $auditor;
        } elseif (strpos(strtolower($task), 'shift 3') !== false) {
            $data[$date][$param]['shift3'] += $quantity;
            $data[$date][$param]['auditor3'] = $auditor;
            $auditorShift3 = $auditor;
        }
    }
}

// Close the database connection
$conn->close();
?>

<!doctype html>
<html lang="en">
<?php include "head.php" ?>

<head>
    <style>
        .body {
            font-size: 12px;
            font-family: 'Roboto';
        }

        .container {
            width: 95%;
            margin: 10px auto;
        }

        .header {
            text-align: center;
        }

        .header h5,
        .header h6 {
            margin: 5px 0;
        }

        .sub-header {
            text-align: center;

            margin-left: 10px;


            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 2px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .auditor-row {
            background-color: #f9f9f9;
            font-weight: bold;
        }

        .custom-month-report-form {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            /*background-color: #f0f8ff;*/
            padding: 10px 15px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);

            /*font-family: 'Segoe UI', sans-serif;*/
            /*max-width: fit-content;*/
        }

        .custom-month-report-form label {
            font-weight: 600;
            color: #333;
        }

        .custom-month-report-form select {
            padding: 6px 10px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fff;
            color: #333;
        }

        .custom-month-report-form input[type="submit"] {
            padding: 8px 16px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .custom-month-report-form input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
        <?php include "header.php" ?>
        <main class="app-main">
            <div class="app-content-header">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <h3 class="mb-0">Daily machine Report </h3>
                        </div>
                        <div class="col-sm-6"> <a href="machinedailytarge.php" class="btn btn-success" target="blank">
                                Set Monthly Target</a>
                            <a href="machine_summary_report.php?id=<?php echo $id ?>" class="btn btn-success"
                                target="blank"> Summary</a>
                            <a href="machine_report.php" target="blank" class="btn btn-success"> Machine Report</a>
                        </div>
                    </div>
                    <center>
                        <form method="get" class="custom-month-report-form">
                            <label for="fromDate">From Date:</label>
                            <input type="date" name="fromDate" id="fromDate"
                                value="<?php echo isset($_GET['fromDate']) ? $_GET['fromDate'] : ''; ?>">

                            <label for="toDate">To Date:</label>
                            <input type="date" name="toDate" id="toDate"
                                value="<?php echo isset($_GET['toDate']) ? $_GET['toDate'] : ''; ?>">

                            <input type="submit" value="Generate Report">
                        </form>
                    </center>
                </div>
            </div>
            <div class="app-content">
                <div class="container-fluid">
                    <div class="row g-4">
                        <div class="col-md-12">
                            <div class=" mb-4">

                                <div class="body"
                                    style="max-height: 80vh; overflow-y: auto; padding: 10px; border: 1px solid #ccc;">
                                    <?php

                                    foreach ($data as $date => $params) {
                                        // Skip if no data for this date
                                        if (empty($params)) {
                                            continue;
                                        }
                                        $totalTasks = 0;
                                        $completedTasks = 0;

                                        // Calculate task completion for all shifts
                                        foreach ($params as $param => $shifts) {
                                            if ($shifts['shift1'] > 0) {
                                                $completedTasks++;
                                            }
                                            $totalTasks++;

                                            if ($shifts['shift2'] > 0) {
                                                $completedTasks++;
                                            }
                                            $totalTasks++;

                                            if ($shifts['shift3'] > 0) {
                                                $completedTasks++;
                                            }
                                            $totalTasks++;
                                        }

                                        // Calculate percentage
                                        $totalScore = $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0;
                                        ?>

                                        <div class="container">
                                            <div class="header">
                                                <h5>SOUTH CENTRAL RAILWAY</h5>
                                                <h6>Daily Machine Report - Date: <?php echo $date; ?></h6>
                                                <p>Daily uses of type and quantity of consumables of environmental
                                                    sanitation, mechanized cleaning and housekeeping contract at Tirupati
                                                    Railway station</p>
                                            </div>
                                            <div class="sub-header">
                                                <span>Date: <?php echo $date; ?></span>
                                                <span>Division: <?php echo $division; ?></span>
                                                <span>Station: <?php echo $station; ?></span>
                                                <span>Name Of Contractor: <?php echo $contractor; ?></span>
                                                <span>Total Score Obtained: <?php echo $totalScore; ?>%</span>
                                            </div>

                                            <table>
                                                <tr>
                                                    <th rowspan="2">S.No</th>
                                                    <th rowspan="2">Machine No</th>
                                                    <th rowspan="2">Name of Machines</th>
                                                    <th colspan="3">Nominated Work Area for Each Machine</th>
                                                    <th colspan="3">Used During The Shift</th>
                                                    <th rowspan="2">Remarks</th>
                                                </tr>
                                                <tr>
                                                    <th style="width:4%">Shift 1</th>
                                                    <th style="width:4%">Shift 2</th>
                                                    <th style="width:4%">Shift 3</th>
                                                    <th style="width:10%">Shift 1 Work Done (Y/N)</th>
                                                    <th style="width:10%">Shift 2 Work Done (Y/N)</th>
                                                    <th style="width:10%">Shift 3 Work Done (Y/N)</th>
                                                </tr>

                                                <?php
                                                $sno = 1;
                                                foreach ($params as $param => $shifts) {
                                                    echo "<tr>";
                                                    echo "<td>{$sno}</td>";
                                                    echo "<td>" . strtoupper(substr($param, 0, 5)) . " " . $sno . "</td>";
                                                    echo "<td>{$param}</td>";
                                                    echo "<td>" . ($shifts['shift1'] > 0 ? 'Y' : 'N') . "</td>";
                                                    echo "<td>" . ($shifts['shift2'] > 0 ? 'Y' : 'N') . "</td>";
                                                    echo "<td>" . ($shifts['shift3'] > 0 ? 'Y' : 'N') . "</td>";
                                                    echo "<td>" . ($shifts['shift1'] > 0 ? 'Y' : 'N') . "</td>";
                                                    echo "<td>" . ($shifts['shift2'] > 0 ? 'Y' : 'N') . "</td>";
                                                    echo "<td>" . ($shifts['shift3'] > 0 ? 'Y' : 'N') . "</td>";
                                                    echo "<td></td>";
                                                    echo "</tr>";
                                                    $sno++;
                                                }
                                                ?>
                                                <tr class="auditor-row">
                                                    <td colspan="6" style="text-align: center;"><strong>Name OF
                                                            Auditor(s)</strong></td>
                                                    <td colspan="1" style="text-align: center;">
                                                        <?php echo !empty($auditorShift1) ? $auditorShift1 : '-'; ?></td>
                                                    <td colspan="1" style="text-align: center;">
                                                        <?php echo !empty($auditorShift2) ? $auditorShift2 : '-'; ?></td>
                                                    <td colspan="1" style="text-align: center;">
                                                        <?php echo !empty($auditorShift3) ? $auditorShift3 : '-'; ?></td>
                                                    <td></td>
                                                </tr>
                                                <tr class="auditor-row">
                                                    <td colspan="6" style="text-align: center;"><strong>Signature of On Duty
                                                            Supervisor</strong></td>
                                                    <td colspan="1" style="text-align: center;"></td>
                                                    <td colspan="1" style="text-align: center;"></td>
                                                    <td colspan="1" style="text-align: center;"></td>
                                                    <td></td>
                                                </tr>
                                            </table>
                                            <br>

                                            <p><strong>Signature of Contractor Representative:</strong>
                                                _______________________
                                                <strong>CHI IN Charge:</strong> _______________________
                                            </p>
                                        </div>

                                        <?php
                                    }
                                    ?>

                                </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </main>
        <?php include "footer.php" ?>
    </div>
</body>

</html>