<?php session_start(); 
 include"../../connection.php";?>
<!doctype html>
<html lang="en">
  <?php
  include"head.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
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
    <title>Daily Surprise Visit Report</title>
<style>
    .railway-frame {
        height: 90vh;
        overflow-y: auto;
         font-size:14px;
        box-sizing: border-box;
            /* font-family: 'Roboto' !IMPORTANT; */
    font-weight: 800;
        
    }
    
    body {

}

    .railway-container {
        width: 80%;
        margin: auto;
        page-break-after: always;
    }

    .railway-report-title {
        text-align: center;
        font-weight: bold;
    }

    .railway-report-subtitle {
        text-align: center;
        font-weight: bold;
        
    }

    .railway-section-title {
        text-align: center;
        font-weight: bold;
        font-size:14px;
        
    }

    .railway-table {
        border-collapse: collapse;
        width: 100%;
        margin-bottom: 20px;
    }

    .railway-table, .railway-table th, .railway-table td {
        border: 1px solid black;
        text-align: center;
    }

    .railway-table th {
        background-color: #f2f2f2;
    }
    .railway-table th:nth-child(1) { width: 2%; }   
    .railway-table th:nth-child(2) { width: 20%; } 
    .railway-table th:nth-child(3) { width: 8%; }  
    .railway-table th:nth-child(4) { width: 5%; } 
    .railway-table th:nth-child(5) { width: 5%; }  
    .railway-table th:nth-child(6) { width: 5%; }
    .railway-filter-form {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 15px;
        margin: 4px auto;
        width: fit-content;
        flex-wrap: wrap;
    }
    .railway-filter-form label {
        font-weight: 500;
        margin-right: 5px;
    }
    
    
    .railway-filter-form input[type="date"],
    .railway-filter-form button {
        padding: 4px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }
    .railway-filter-form select {
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }
    
    .railway-filter-form button {
        background-color: green;
        color: white;
        border: none;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    /*.railway-filter-form button:hover {*/
    /*    background-color: green;*/
    /*}*/
    /* Print styles */
    
</style>
</head>
  <body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
    <div class="app-wrapper">
     <?php include"header.php"?>
      <main class="app-main">
        
        <form class="railway-filter-form" method="GET">
           
            <select>
                <option>select Auditor</option>
                
            </select>
            
    <label for="from_date">From:</label>
    <input type="date" name="from_date" id="from_date"
        value="<?php echo isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01'); ?>">
    <label for="to_date">To:</label>
    <input type="date" name="to_date" id="to_date"
        value="<?php echo isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t'); ?>">
    <input type="hidden" name="station_id" value="<?php echo htmlspecialchars($station_id); ?>">
    <button type="submit">Go</button>
     <a href="daily_surprise_summary.php" target="_blank" style="text-decoration:none; color:white; background-color:green; padding:8px 12px; border-radius:5px; border:none; font-size:14px; display:inline-block;">summary</a>
         <!--<a href="daily_surprise_summary.php" target="_blank" style="text-decoration:none; color:white; background-color:green; padding:8px 12px; border-radius:5px; border:none; font-size:14px; display:inline-block;">print</a>-->
</form>
<br>
<div class="railway-frame">
<?php
foreach ($period as $dateObj) {
    $reportDate = $dateObj->format('Y-m-d');

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
            bas.created_date AS report_date
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
            AND DATE(bas.created_date) = '$reportDate';
    ";

    $result = $conn->query($sql);

    $date = '';
    $auditor = '';
    $division = '';
    $station = '';
    $contractor = '';
    $overallAverage = 0;
    $totalScore = 0;
    $totalRecords = 0;

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $date = $row['report_date'];
            $auditor = $row['name'];
            $division = $row['division_name'];
            $station = $row['station_name'];
            $contractor = $row['organisation_name'];
            $totalScore += $row['Quality_of_done_work'];
            $totalRecords++;
        }
        $overallAverage = $totalRecords > 0 ? round(($totalScore / ($totalRecords * 10)) * 100, 2) : 0;

        // Reset result pointer
        $result->data_seek(0);

       echo "<div class='railway-container'>";
    echo "<h4 class='railway-report-title'>SOUTH CENTRAL RAILWAY</h4>";
echo "<h6 class='railway-report-subtitle'>Daily Surprise Visit</h4>";

        echo "<p class='railway-report-subtitle'>
            <span>Date:</span> $date &nbsp;|&nbsp;
            <span>Auditor:</span> $auditor &nbsp;|&nbsp;
            <span>Division:</span> $division &nbsp;|&nbsp;
            <span>Station:</span> $station &nbsp;|&nbsp;
            <span>Name Of Contractor:</span> $contractor &nbsp;|&nbsp;
            <span>Overall Average:</span> $overallAverage% &nbsp;|&nbsp;
            <span>Total Score Obtained:</span> $totalScore
        </p>";

        $currentTask = '';
        $serialNo = 1;
        while ($row = $result->fetch_assoc()) {
            if ($currentTask != $row['task']) {
                if ($currentTask != '') echo "</table>";
                $currentTask = $row['task'];
               echo "<h4 class='railway-section-title'>$currentTask</h4>";

                echo "
                <table class='railway-table'>
                    <tr>
                        <th>S.No</th>
                        <th>Parameters</th>
                        <th>Range</th>
                        <th>Grade</th>
                        <th>Quality of Work Done</th>
                        <th>Payable Grade</th>
                    </tr>";
                $serialNo = 1;
            }

            echo "<tr>
                <td>" . $serialNo++ . "</td>
                <td>" . $row['parameters'] . "</td>
                <td>" . implode('<br>', array_map('trim', explode(',', $row['rank1']))) . "</td>
                <td>" . implode('<br>', array_map('trim', explode(',', $row['grade']))) . "</td>
                <td>" . $row['Quality_of_done_work'] . "</td>
                <td>" . $row['payable_grade'] . "</td>
            </tr>";
            
           
        }
        echo "</table>";
        echo "
       
            <div style='text-align: center; font-weight: bold; margin: 15px 0;'>
                <div style='display: flex; justify-content: space-around; margin-bottom: 10px;'>
                    <div>Signature of Contractor Representative</div>
                    <div>SCHI IN Charge</div>
                </div>
                <div style='display: flex; justify-content: space-around; margin-top: 10px;'>
                    <div style='border-top: 1px solid #ccc; width: 30%; max-width: 200px;'></div>
                    <div style='border-top: 1px solid #ccc; width: 30%; max-width: 200px;'></div>
                </div>
            </div>";
        echo "</div>  <br>";
      
    }
}
$conn->close();
?>
</div>
      </main>
      <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">Anything you want</div>
        <strong>
          Copyright &copy; 2025&nbsp;
          <a href="#" class="text-decoration-none"></a>.
        </strong>
        All rights reserved.
      </footer>
    </div>
  </body>
</html>
