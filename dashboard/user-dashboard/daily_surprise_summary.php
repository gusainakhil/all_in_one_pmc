<?php session_start(); 
  include"head.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include "../../connection.php";

// Default to current month and year
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : date('m');
$selectedYear = isset($_GET['year']) ? $_GET['year'] : date('Y');
$station_id = $_SESSION['stationId'];
// Start and end date for the selected month
$startDate = "$selectedYear-$selectedMonth-01";
$endDate = date("Y-m-t", strtotime($startDate));

$sql = "
    SELECT 
        bap.paramName AS task, 
        bp.db_pagename AS parameters, 
        bas.db_surveyValue AS Quality_of_done_work,
        bu.db_username AS name,
        bs.stationName AS station_name,
        bo.db_Orgname AS organisation_name,
        bd.DivisionName AS division_name,
        CASE 
            WHEN bas.db_surveyValue IN (9, 10) THEN 'Excellent'
            WHEN bas.db_surveyValue IN (7, 8) THEN 'Very Good'
            WHEN bas.db_surveyValue IN (5, 6) THEN 'Good'
            WHEN bas.db_surveyValue IN (3, 4) THEN 'Average'
            WHEN bas.db_surveyValue IN (1, 2) THEN 'Poor'
            ELSE 'Not Applicable'
        END AS payable_grade,
        bp.db_pageChoice AS grade,
        bp.db_pageChoice2 AS rank1,
        DATE(bas.created_date) AS report_date
    FROM 
        baris_param bap
        INNER JOIN baris_survey bas ON bap.paramId = bas.db_surveyParamId
        INNER JOIN baris_page bp ON bas.db_surveyPageId = bp.pageId
        INNER JOIN baris_userlogin bu ON bas.db_surveyUserid = bu.userId
        INNER JOIN baris_station bs ON bas.db_surveyStationId = bs.stationId
        INNER JOIN baris_organization bo ON bas.OrgID = bo.OrgID
        INNER JOIN baris_division bd ON bas.DivisionId = bd.DivisionId
    WHERE 
        bas.db_surveyStationId = '$station_id' 
        AND bas.created_date >= '$startDate' 
        AND bas.created_date <= '$endDate'
    ORDER BY report_date;
";

$result = $conn->query($sql);

$totalScore = 0;
$totalRecords = 0;
$maxScorePerDay = 300;
$inspections = [];
// Initialize an array to track unique dates
$uniqueDates = [];
// Get all dates in the selected month
$allDates = [];
$currentDate = strtotime($startDate);
$endDateTime = strtotime($endDate);

// Get all dates for the month
while ($currentDate <= $endDateTime) {
    $allDates[] = date('Y-m-d', $currentDate);
    $currentDate = strtotime('+1 day', $currentDate);
}

// Initialize variables for summary
$auditor = '';
$division = '';
$station = '';
$contractor = '';

// Loop through the result set
while ($row = $result->fetch_assoc()) {
    $reportDate = $row['report_date'];
    
    // Set summary values once (assuming all rows have the same values)
    if (empty($auditor)) {
        $auditor = $row['name'];
    }
    if (empty($division)) {
        $division = $row['division_name'];
    }
    if (empty($station)) {
        $station = $row['station_name'];
    }
    if (empty($contractor)) {
        $contractor = $row['organisation_name'];
    }
    
    // Track unique dates
    if (!in_array($reportDate, $uniqueDates)) {
        $uniqueDates[] = $reportDate;
    }

    // Sum up scores
    if (!isset($inspections[$reportDate])) {
        $inspections[$reportDate] = [
            'date' => $reportDate,
            'total' => $maxScorePerDay, 
            'score' => 0,
            'percentage' => 0
        ];
    }

    $inspections[$reportDate]['score'] += $row['Quality_of_done_work'];
    $inspections[$reportDate]['percentage'] = round(($inspections[$reportDate]['score'] / $maxScorePerDay) * 100, 2);
}

// Add missing dates with zero values
foreach ($allDates as $date) {
    if (!isset($inspections[$date])) {
        $inspections[$date] = [
            'date' => $date,
            'total' => $maxScorePerDay,
            'score' => 0,
            'percentage' => 0
        ];
    }
}

// Sort by date to ensure correct order
ksort($inspections);

// Calculate summary
$totalInspections = count($allDates);
$totalScore = array_sum(array_column($inspections, 'score'));
$maximumPossibleScore = $totalInspections * $maxScorePerDay;
$overallAverage = ($maximumPossibleScore > 0) ? round(($totalScore / $maximumPossibleScore) * 100, 2) : 0;
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Summarry report <?php echo $station; ?> </title>
    <!--<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">-->
    <title>Daily Surprise Visit Report</title>
</head>
    <style>
        .container {
            width: 95%;
            margin: auto;
            page-break-after: always;
        }
        .report-title {
            text-align: center;
            font-weight: bold;

        }
        .report-subtitle {
            text-align: center;
            margin-bottom: 20px;
    
        }
        .section-title {
            text-align: center;
            font-weight: bold;
            padding: 5px;
           
        }
        table {
            border-collapse: collapse;
            width: 100%;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid black;
            text-align: center;
            padding:0px;
        }

        table th:nth-child(1) { width: 5%; }   
        table th:nth-child(2) { width: 30%; } 
        table th:nth-child(3) { width: 20%; }  
        table th:nth-child(4) { width: 10%; } 
        table th:nth-child(5) { width: 10%; }  
        table th:nth-child(6) { width: 10%; }

    </style>

</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
 <div class="app-wrapper">
     <?php include"header.php"?>
      <main class="app-main">
    <div class="">
        <h2 class="text-center" style="font-size:20px; padding:0px">SOUTH CENTRAL RAILWAY</h2>
    <h2 class="text-center" style="font-size:20px; padding:0px">Daily Surprise Report Summary</h2>
     <p  class=" text-center" style="font-size:15px; padding:0px">Daily uses of type and quantity of consumables of environmental sanitation, mechanized cleaning and housekeeping contract at <?php echo $station; ?> Railway station</p>
    <p class="text-center" style="font-size:15px; padding:0px ; font-weight: 900;">
        <span class="font-semibold">Month:</span> <span><?php echo date('F Y', strtotime("$selectedYear-$selectedMonth-01")); ?></span> &nbsp;|&nbsp;
        <span class="font-semibold">Division:</span> <span><?php echo $division; ?></span> &nbsp;|&nbsp;
        <span class="font-semibold">Station:</span> <span><?php echo $station; ?></span>
        <span class="font-semibold">Name Of Contractor:</span> <span><?php echo $contractor; ?></span> &nbsp;|&nbsp;
        <span class="font-semibold">Overall Average:</span> <span><?php echo $overallAverage; ?>%</span> &nbsp;|&nbsp;
        <span class="font-semibold">Total Score Obtained:</span> <span><?php echo $totalScore; ?></span>
    </p>
   

        <center><form method="get" class="">
            <select name="month" class="">
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $month = str_pad($i, 2, '0', STR_PAD_LEFT);
                    $selected = ($month == $selectedMonth) ? 'selected' : '';
                    echo "<option value='$month' $selected>" . date('F', strtotime("2023-$month-01")) . "</option>";
                }
                ?>
            </select>
            <input type="number" name="year" value="<?php echo $selectedYear; ?>" />
            <button type="submit" class="text-white rounded" style="background-color:green; color: white; border:none;">GO</button>
        </form></center>
        <table class="container" style="font-size:12px;  font-weight: bold;">
            <thead>
                <tr>
                    <th class="border" style="padding:0px;">S.No</th>
                    <th class="border" style="padding:0px;">Inspection Date</th>
                    <th class="border" style="padding:0px;">Total</th>
                    <th class="border" style="padding:0px;">Score</th>
                    <th class="border" style="padding:0px;">Score(%)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $serialNo = 1;
                foreach ($inspections as $inspection) {
                    echo "<tr>
                        <td class='border'style='padding:0px'>{$serialNo}</td>
                        <td class='border'style='padding:0px'>{$inspection['date']}</td>
                        <td class='border'style='padding:0px'>{$inspection['total']}</td>
                        <td class='border'style='padding:0px'>{$inspection['score']}</td>
                        <td class='border'style='padding:0px'>{$inspection['percentage']}</td>
                    </tr>";
                    $serialNo++;
                }
                ?>
            </tbody>
        </table>
         <span class="text-center" style="margin-left:20%">Signature of Contractor Representative</span>  <span class="text-center" style="margin-left:20%">CHI IN Charge</span><br>
         <span class="text-center" style="margin-left:20%">__________________________________</span>  <span class="text-center" style="margin-left:20%">_______________________</span>
    </div>
</main>
</div>
</body>
</html>
