<?php
include "../../connection.php";
session_start();
if (!isset($_SESSION['stationId'])) {
    die("Station ID not set in session.");
}

$startDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('2025-01-01');
$endDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('2025-01-31');
$station_id = $_SESSION['stationId'];

$sql = "
    SELECT 
        SUM(bas.db_surveyValue) AS total_score,
        COUNT(bas.db_surveyValue) AS total_records,
        brw.weightage AS weightage
    FROM 
        baris_param bap
        INNER JOIN baris_survey bas ON bap.paramId = bas.db_surveyParamId
        INNER JOIN baris_page bp ON bas.db_surveyPageId = bp.pageId
        INNER JOIN baris_report_weight brw ON bas.db_surveySubQuestionId  = brw.subqueId
    WHERE  
        bas.db_surveyStationId = '$station_id' 
        AND DATE(bas.created_date) BETWEEN '$startDate' AND '$endDate'
";      

$result = $conn->query($sql);

if ($result && $row = $result->fetch_assoc()) {
    $totalScore = $row['total_score'];
    $totalRecords = $row['total_records'];
     $totalweihgt = $row['weightage'];
    $overallAverage = $totalRecords > 0 ? round(($totalScore / ($totalRecords * 10)) * 100, 2) : 0;

    echo "<span>Overall Average:</span> $overallAverage% &nbsp;|&nbsp;";
    echo "<span>Total Weightage:</span> $totalweihgt<br>";
} else {
    echo "No data found.";
}

$sql ="SELECT 
    (SELECT SUM(Bt.value)
     FROM baris_target AS Bt
     WHERE Bt.OrgID = 17
       AND Bt.created_date BETWEEN '2025-01-01' AND '2025-01-31'
    ) AS total_target,

    (SELECT 
        SUM(bcr.db_surveyValue) AS total_survey_value
     FROM baris_chemical_report AS bcr
     INNER JOIN baris_report_weight brw 
         ON bcr.db_surveySubQuestionId = brw.subqueId
     WHERE bcr.OrgID = 17
       AND bcr.created_date BETWEEN '2025-01-01' AND '2025-01-31'
    ) AS total_survey_value,

    (SELECT brw.weightage
     FROM baris_chemical_report AS bcr
     INNER JOIN baris_report_weight brw 
         ON bcr.db_surveySubQuestionId = brw.subqueId
     WHERE bcr.OrgID = 17
       AND bcr.created_date BETWEEN '2025-01-01' AND '2025-01-31'
     LIMIT 1
    ) AS weightage;";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$total_target = $data['total_target'] ?? 0;
$total_survey_value = $data['total_survey_value'] ?? 0;

 $weightage = $data['weightage'] ?? 0;
 $cleanlinessRecordPercentage = $total_target > 0 ? round(($total_survey_value / $total_target) * 100, 2) : 0;
echo "Cleanliness Record Percentage: $cleanlinessRecordPercentage%";



$conn->close();
?>
