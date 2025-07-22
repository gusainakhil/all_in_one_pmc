<?php
session_start();
include '../../connection.php';

$stationId = $_SESSION['stationId'];
$OrgID = $_SESSION['OrgID'];
if (isset($_GET['id'])) $_SESSION['squeld'] = $_GET['id'];
$squeld = $_SESSION['squeld'];

$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');
$start = "$year-" . str_pad($month,2,'0',STR_PAD_LEFT)."-01";
$end = date("Y-m-t", strtotime($start));

// Fetch achievements
$achievement_sql = "
    SELECT dpl.db_surveyPageId,
           SUBSTRING(bpage.db_pageChoice2, INSTR(bpage.db_pageChoice2,'@')+1) AS Percentage_Weightage,
           SUM(CASE WHEN bp.paramName='Shift 1' THEN dpl.db_surveyValue ELSE 0 END) AS s1,
           SUM(CASE WHEN bp.paramName='Shift 2' THEN dpl.db_surveyValue ELSE 0 END) AS s2,
           SUM(CASE WHEN bp.paramName='Shift 3' THEN dpl.db_surveyValue ELSE 0 END) AS s3
    FROM Daily_Performance_Log dpl
    JOIN baris_param bp ON dpl.db_surveyParamId=bp.paramId
    JOIN baris_page bpage ON dpl.db_surveyPageId=bpage.pageId
    WHERE dpl.db_surveyStationId='$stationId' AND dpl.created_date BETWEEN '$start' AND '$end'
    GROUP BY dpl.db_surveyPageId";
$res = $conn->query($achievement_sql);

// Fetch targets
$t_res = $conn->query("
    SELECT pageId,
           SUBSTRING_INDEX(value, ',',1) AS t1,
           SUBSTRING_INDEX(SUBSTRING_INDEX(value,',',2),',',-1) AS t2,
           SUBSTRING_INDEX(SUBSTRING_INDEX(value,',',3),',',-1) AS t3
    FROM baris_target
    WHERE OrgID='$OrgID' AND month='$month' AND subqueId='$squeld'
    ORDER BY id DESC LIMIT 24");
$targets=[];
while($t=$t_res->fetch_assoc()) $targets[$t['pageId']]=$t;

// Calculate total
$total=0;
while($r=$res->fetch_assoc()){
    $t=$targets[$r['db_surveyPageId']] ?? ['t1'=>0,'t2'=>0,'t3'=>0];
    foreach(['1','2','3'] as $i) if($t["t$i"]==0) $r["s$i"]=0;
    $t_sum=$t['t1']+$t['t2']+$t['t3'];
    $a_sum=$r['s1']+$r['s2']+$r['s3'];
    $fs=($t_sum>0)?($a_sum/$t_sum)*100:0;
    $total+= $fs*floatval($r['Percentage_Weightage'])/100;
}
echo "Monthly Final Score: ".number_format($total,2)."%";
?>
