<?php
session_start();
$station_id = $_SESSION['stationId'];
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');

$startDate = "$selectedYear-$selectedMonth-01";
// Set $endDate to the first second of the next month for inclusive date range
$endDate = date("Y-m-01", strtotime("$selectedYear-$selectedMonth +1 month"));

include "../../connection.php";
ini_set('display_errors', 1);
error_reporting(E_ALL);

$reportQuery = "
    SELECT 
        DATE(dpl.created_date) AS report_date,
        dpl.db_surveyValue AS quality,
        bs.stationName,
        bd.divisionName, 
        bg.db_Orgname 
    FROM 
        Daily_Performance_Log dpl
    JOIN 
        baris_station bs ON dpl.db_surveyStationId = bs.stationId
    JOIN 
        baris_division bd ON bs.DivisionId = bd.DivisionId
    JOIN 
        baris_organization bg ON bs.OrgId = bg.OrgId
    WHERE 
        dpl.db_surveyStationId = '$station_id'
        AND dpl.created_date >= '$startDate' AND dpl.created_date < '$endDate';
";


// Execute the query and check for errors
$reportResult = $conn->query($reportQuery);
if (!$reportResult) {
    die("Query failed: " . $conn->error);
}

$dailyData = [];
$stationDetails = [];

while ($row = $reportResult->fetch_assoc()) {
    $date = $row['report_date'];
    $value = floatval($row['quality']);

    // Store station info once
    if (empty($stationDetails)) {
        $stationDetails = [
            'stationName' => $row['stationName'],
            'divisionName' => $row['divisionName'],
            'db_Orgname' => $row['db_Orgname']
        ];
    }

    if (!isset($dailyData[$date])) {
        $dailyData[$date] = [
            'total' => 0,
            'count' => 0,
        ];
    }

    $dailyData[$date]['total'] += $value;
    $dailyData[$date]['count']++;
}

$finalReport = [];

foreach ($dailyData as $date => $data) {
    $score = $data['count'] > 0 ? round(($data['total'] / $data['count']) * 100, 2) : 0;
    $finalReport[] = [
        'date' => date("d-M-Y", strtotime($date)),
        'score' => $score
    ];
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PMC - Daily Performance Report</title>
  <style>
    body {  width: 70%;
            margin: auto;
            font-weight: 800;
            font-size: 12px;
            font-family: 'Roboto'; }
    h2 { text-align: center; margin-bottom: 5px; }
    .subtitle { text-align: center; font-weight: bold; margin-bottom: 20px; }
    .meta { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: bold; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #000; padding: 6px; text-align: center; }
    th { background-color: #f2f2f2; }
    .footer { text-align: right; font-weight: bold; }
    span{ margin-right:20px;}
  </style>
</head>
<body>

<form method="get" style="text-align: center; margin-bottom: 20px;">
  <label for="month">Select Month:</label>
  <select name="month" id="month">
    <?php
    for ($m = 1; $m <= 12; $m++) {
        $monthPadded = str_pad($m, 2, '0', STR_PAD_LEFT);
        $selected = ($selectedMonth == $monthPadded) ? "selected" : "";
        echo "<option value=\"$monthPadded\" $selected>" . date('F', mktime(0, 0, 0, $m, 10)) . "</option>";
    }
    ?>
  </select>

  <label for="year">Select Year:</label>
  <select name="year" id="year">
    <?php
    $currentYear = date("Y");
    for ($y = $currentYear; $y >= 2020; $y--) {
        $selected = ($selectedYear == $y) ? "selected" : "";
        echo "<option value=\"$y\" $selected>$y</option>";
    }
    ?>
  </select>
  <input type="submit" value="Generate Report">
</form>

<h2>PMC - Daily Performance Log</h2>
<div class="subtitle">
  Daily uses of type and quantity of consumables of environmental sanitation, mechanized cleaning and housekeeping contract at Tirupati Railway station
</div>
<center>
<div class="meta">
  <div>
    <span>Month: <u><?= date('F', strtotime($startDate)) ?></u></span>
   <span>Division: <u><?= $stationDetails['divisionName'] ?></u></span>
<span>Station: <u><?= $stationDetails['stationName'] ?></u></span>
<span>Name Of Contractor: <u><?= $stationDetails['db_Orgname'] ?></u></span>

    <span>Average: <u>
        <?php
        $avgScore = count($finalReport) ? round(array_sum(array_column($finalReport, 'score')) / count($finalReport), 2) : 0;
        echo $avgScore;
        ?>
    </u></span>
  </div>
</div>
</center>

<table>
  <thead>
    <tr>
      <th>S.No</th>
      <th>Date</th>
      <th>Score(%)</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($finalReport as $index => $row): ?>
      <tr>
        <td><?= $index + 1 ?></td>
        <td><?= $row['date'] ?></td>
        <td><?= $row['score'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table><br>
<span>Signature of Contractor Representative </span>

</body>
</html>
