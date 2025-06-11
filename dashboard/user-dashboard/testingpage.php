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

$conn->close();
?>
