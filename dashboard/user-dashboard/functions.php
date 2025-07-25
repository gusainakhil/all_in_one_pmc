<?php
session_start();
// include '../../connection.php';

function getMonthlyFinalScore($stationId, $OrgID, $squeld, $month, $year, $conn) {
    $start = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
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

    $targets = [];
    while($t = $t_res->fetch_assoc()) {
        $targets[$t['pageId']] = $t;
    }

    // Calculate total
    $total = 0;
    while($r = $res->fetch_assoc()) {
        $t = $targets[$r['db_surveyPageId']] ?? ['t1'=>0,'t2'=>0,'t3'=>0];
        foreach(['1','2','3'] as $i) if($t["t$i"]==0) $r["s$i"]=0;
        $t_sum = $t['t1'] + $t['t2'] + $t['t3'];
        $a_sum = $r['s1'] + $r['s2'] + $r['s3'];
        $fs = ($t_sum > 0) ? ($a_sum / $t_sum) * 100 : 0;
        $total += $fs * floatval($r['Percentage_Weightage']) / 100;
    }

    return number_format($total, 2);
}

// ====== CALL FUNCTION ======
$stationId = $_SESSION['stationId'];
$OrgID = $_SESSION['OrgID'];
if (isset($_GET['id'])) $_SESSION['squeld'] = $_GET['id'];
$squeld = $_SESSION['daily_performance'];
$month = $_GET['month'] ?? date('n');
$year = $_GET['year'] ?? date('Y');

// echo "Monthly Final Score: " . getMonthlyFinalScore($stationId, $OrgID, $squeld, $month, $year, $conn);

function calculate_feedback_psi($conn, $station_id, $firstDay, $lastDay) {
    // Print station id
    // echo "Station ID: $station_id\n";

    try {
        // Fetch station
        $stmt_station = $conn->prepare("SELECT name, feedback_target FROM feedback_stations WHERE id = ?");
        $stmt_station->bind_param("i", $station_id);
        $stmt_station->execute();
        $station_result = $stmt_station->get_result();
        $station = $station_result->fetch_assoc();
        $stmt_station->close();

        if (!$station) {
            throw new Exception("Station not found");
        }

        $station_name = $station['name'];
        $daily_target = (int)($station['feedback_target'] ?? 0);

        // Get max rating score
        $stmt_max = $conn->prepare("SELECT value FROM rating_parameters WHERE station_id = ?");
        $stmt_max->bind_param("i", $station_id);
        $stmt_max->execute();
        $max_result = $stmt_max->get_result();
        $max_rating_score = (int)($max_result->fetch_assoc()['value'] ?? 3);
        $stmt_max->close();

        // Fetch feedback
        $stmt_feedback = $conn->prepare("
            SELECT GROUP_CONCAT(CONCAT(fa.question_id, ':', fa.rating)) AS question_ratings
            FROM feedback_form ff
            LEFT JOIN feedback_answers fa ON ff.id = fa.feedback_form_id
            WHERE ff.station_id = ?
            AND DATE(ff.created_at) BETWEEN ? AND ?
            GROUP BY ff.id
        ");
        $stmt_feedback->bind_param("iss", $station_id, $firstDay, $lastDay);
        $stmt_feedback->execute();
        $feedback_result = $stmt_feedback->get_result();

        $total_feedbacks = 0;
        $total_score_sum = 0;

        while ($row = $feedback_result->fetch_assoc()) {
            $ratings = explode(',', $row['question_ratings']);
            $sum = 0;
            $count = 0;
            foreach ($ratings as $rating_pair) {
                [$q, $r] = explode(':', $rating_pair);
                $sum += (int)$r;
                $count++;
            }
            if ($count > 0) {
                $avg = $sum / $count;
                $total_score_sum += $avg;
                $total_feedbacks++;
            }
        }

        $stmt_feedback->close();

        // PSI Calculation
        $start = new DateTime($firstDay);
        $end = new DateTime($lastDay);
        $end->modify('+1 day');
        $interval = new DateInterval('P1D');
        $total_days = iterator_count(new DatePeriod($start, $interval, $end));

        $expected_feedbacks = $total_days * $daily_target;
        $avg_score = $total_feedbacks > 0 ? $total_score_sum / $total_feedbacks : 0;
        $quality_psi = ($avg_score / $max_rating_score) * 100;
        $quantity_achievement = $expected_feedbacks > 0 ? ($total_feedbacks / $expected_feedbacks) : 0;
        $psi = $quality_psi * $quantity_achievement;

        // Final Output
        // echo $station_name . ' - ' . number_format($psi, 2) . "%\n";
        echo  number_format($psi, 2);

    } catch (Exception $e) {
        // echo "Error: " . $e->getMessage() . "\n";
    }
}

// Example call:
// $station_id = 33;

// $conn already declared outside
// calculate_feedback_psi($conn, 33, $firstDay, $lastDay);

?>