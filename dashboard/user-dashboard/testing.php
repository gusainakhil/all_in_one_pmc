<?php
session_start();
include_once "../../connection.php";

$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');
$station_id = $_SESSION['stationId'];

// 1️⃣ Achieved Weightage (only for this station)
$sqlAchieved = "
  SELECT 
    SUM(
      CASE WHEN dpl.db_surveyValue = 1 THEN 
        CAST(SUBSTRING_INDEX(bp.db_pageChoice2, '@', -1) AS UNSIGNED)
      ELSE 0 END
    ) AS achieved_weightage
  FROM 
    Daily_Performance_Log dpl
    INNER JOIN baris_page bp ON dpl.db_surveyPageId = bp.pageId
  WHERE 
    DATE(dpl.created_date) BETWEEN '$fromDate' AND '$toDate'
    AND dpl.db_surveyStationId = '$station_id'
";

$resultAchieved = $conn->query($sqlAchieved);
$rowAchieved = $resultAchieved->fetch_assoc();
$achieved = $rowAchieved['achieved_weightage'] ?: 0;

// 2️⃣ Total Possible Weightage (from all pages — no station filter)
$sqlTarget = "
  SELECT 
    SUM(CAST(SUBSTRING_INDEX(db_pageChoice2, '@', -1) AS UNSIGNED)) AS total_weightage
  FROM 
    baris_page
";

$resultTarget = $conn->query($sqlTarget);
$rowTarget = $resultTarget->fetch_assoc();
$target = $rowTarget['total_weightage'] ?: 0;

// 3️⃣ Final Score
$finalScore = ($target > 0) ? round(($achieved / $target) * 100, 2) : 0;

echo "Monthly Final Score: " . $finalScore . "%";
?>
