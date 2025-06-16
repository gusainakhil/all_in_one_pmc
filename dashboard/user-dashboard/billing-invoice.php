<?php 
include "../../connection.php";
session_start();

if (!isset($_SESSION['OrgID'], $_SESSION['stationId'])) {
    die("Session data missing.");
}
$org_id = $_SESSION['OrgID'];   
$station_id = $_SESSION['stationId'];

// Reusable calculation function
function calculatealreportAmount($sactioned_amount, $totalWeight, $monthDate) {
    if ($sactioned_amount <= 0 || $totalWeight <= 0) {
        return 0;
    }
    $weightedAmount = ($sactioned_amount * $totalWeight) / 100;
    $totalDaysInFourYears = 1461;
    $perDayAmount = $weightedAmount / $totalDaysInFourYears;
    $daysInMonth = date('t', strtotime($monthDate));
    return round($perDayAmount * $daysInMonth, 2);
}

// Billing details
$stmt = $conn->prepare("SELECT sactioned_amount, nos_of_worker, security_deposit, mb_no, performance_guarant, agreement_letter_no_dt, cost_of_work_per_day, period_of_contract_from, period_of_contract_to FROM baris_bill_rate WHERE OrgID = ?");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();
if (!$bill) die("Billing record not found.");

// Date range
$currentMonth = date('m');
$currentYear = date('Y');
$selectedMonth = $_GET['month'] ?? $currentMonth;
$selectedYear = $_GET['year'] ?? $currentYear;
$firstDay = date('Y-m-01', strtotime("$selectedYear-$selectedMonth-01"));
$lastDay = date('Y-m-t', strtotime("$selectedYear-$selectedMonth-01"));

// Surprise visit score
$sql = "
    SELECT 
        SUM(bas.db_surveyValue) AS total_score,
        COUNT(bas.db_surveyValue) AS total_records,
        brw.weightage
    FROM baris_param bap
    INNER JOIN baris_survey bas ON bap.paramId = bas.db_surveyParamId
    INNER JOIN baris_page bp ON bas.db_surveyPageId = bp.pageId
    INNER JOIN baris_report_weight brw ON bas.db_surveySubQuestionId = brw.subqueId
    WHERE bas.db_surveyStationId = '$station_id' AND DATE(bas.created_date) BETWEEN '$firstDay' AND '$lastDay'
";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$overallAverage = $data['total_records'] > 0 ? round(($data['total_score'] / ($data['total_records'] * 10)) * 100, 2) : 0;
$totalWeight = $data['weightage'] ?? 0;
$surpriseVisitAmount = calculatealreportAmount($bill['sactioned_amount'], $totalWeight, $firstDay);

// calculate chemiacl reord important for chemical report
// $sql ="SELECT 
//     (
//         SELECT SUM(Bt.value)
//         FROM baris_target AS Bt
//         WHERE Bt.OrgID = 17
//           AND Bt.created_date BETWEEN '$firstDay' AND '$lastDay'
//           AND Bt.subqueId IN (
//               SELECT DISTINCT bcr.db_surveySubQuestionId
//               FROM baris_chemical_report AS bcr
//               WHERE bcr.OrgID = $org_id
//                 AND bcr.created_date BETWEEN '$firstDay' AND '$lastDay'
//           )
//     ) AS total_target,

//     (
//         SELECT 
//             SUM(bcr.db_surveyValue)
//         FROM baris_chemical_report AS bcr
//         INNER JOIN baris_report_weight brw 
//             ON bcr.db_surveySubQuestionId = brw.subqueId
//         WHERE bcr.OrgID = $org_id
//           AND bcr.created_date BETWEEN '$firstDay' AND '$lastDay'
//     ) AS total_survey_value,

//     (
//         SELECT brw.weightage
//         FROM baris_chemical_report AS bcr
//         INNER JOIN baris_report_weight brw 
//             ON bcr.db_surveySubQuestionId = brw.subqueId
//         WHERE bcr.OrgID = $org_id
//           AND bcr.created_date BETWEEN '$firstDay' AND '$lastDay'
//         LIMIT 1
//     ) AS weightage;";
// $result = $conn->query($sql);
// $data = $result->fetch_assoc();
// $total_target = $data['total_target'] ?? 0;
// $total_survey_value = $data['total_survey_value'] ?? 0;

// $weightage = $data['weightage'] ?? 0;
// $cleanlinessRecordPercentage = $total_target > 0 ? round(($total_survey_value / $total_target) * 100, 2) : 0;
// $cleanlinessrecordamount = calculatealreportAmount($bill['sactioned_amount'], $weightage, $firstDay);
//echo "Cleanliness Record Percentage: $cleanlinessRecordPercentage%";

// CLEANLINESS RECORD /performane log


// Sanitize & validate input
function getParam($key, $default = '') {
    return isset($_GET[$key]) ? htmlspecialchars(trim($_GET[$key])) : $default;
}  
$month     = (int) getParam('month', date('n'));
$year      = (int) getParam('year', date('Y'));
// $firstDay = sprintf("%04d-%02d-01", $year, $month);
// $lastDay = date("Y-m-t", strtotime($startDate));
// Auto-fetch subqueId from Daily_Performance_Log
$subqueId = null;
$subque_query = "
    SELECT DISTINCT db_surveySubQuestionId
    FROM Daily_Performance_Log 
    WHERE db_surveyStationId = ?
";
$stmt = $conn->prepare($subque_query);
$stmt->bind_param("i", $station_id);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $subqueId = (int)$row['db_surveySubQuestionId'];
}
$stmt->close();

if (!$subqueId) {
    die("Subquestion ID not found for the given station and date range.");
}



// Fetch monthly targets
$targets = [];
$t_sql = "
    SELECT pageId,
           SUBSTRING_INDEX(value, ',', 1) AS t1,
           SUBSTRING_INDEX(SUBSTRING_INDEX(value, ',', 2), ',', -1) AS t2,
           SUBSTRING_INDEX(SUBSTRING_INDEX(value, ',', 3), ',', -1) AS t3
    FROM baris_target
    WHERE OrgID = ? AND month = ? AND subqueId = ?
    ORDER BY id DESC
    LIMIT 24
";
$stmt = $conn->prepare($t_sql);
$stmt->bind_param("iii", $org_id, $month, $subqueId);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $targets[$row['pageId']] = [(float)$row['t1'], (float)$row['t2'], (float)$row['t3']];
}
$stmt->close();

// Fetch achievements
$score_sql = "
    SELECT dpl.db_surveyPageId, brw.weightage as weiht,
           SUBSTRING(bp2.db_pageChoice2, INSTR(bp2.db_pageChoice2, '@') + 1) AS weightage,
           SUM(CASE WHEN bp1.paramName='Shift 1' THEN dpl.db_surveyValue ELSE 0 END) AS a1,
           SUM(CASE WHEN bp1.paramName='Shift 2' THEN dpl.db_surveyValue ELSE 0 END) AS a2,
           SUM(CASE WHEN bp1.paramName='Shift 3' THEN dpl.db_surveyValue ELSE 0 END) AS a3
    FROM Daily_Performance_Log dpl
    JOIN baris_param bp1 ON dpl.db_surveyParamId = bp1.paramId
    JOIN baris_page bp2 ON dpl.db_surveyPageId = bp2.pageId
    JOIN baris_report_weight brw ON dpl.db_surveySubQuestionId = brw.subqueId

    WHERE dpl.db_surveyStationId = ? AND dpl.created_date BETWEEN ? AND ?
    GROUP BY dpl.db_surveyPageId
";

$stmt = $conn->prepare($score_sql);
$stmt->bind_param("iss", $station_id, $firstDay, $lastDay);
$stmt->execute();
$result = $stmt->get_result();

$total_weightage = 0;
while ($row = $result->fetch_assoc()) {
    $weightagec = $row['weiht'] ?? 0;
   
    $pageId = $row['db_surveyPageId'];
    $target = $targets[$pageId] ?? [0, 0, 0];

    $target_sum = $target[0] + $target[1] + $target[2];
    $achieved_sum = 
        ($target[0] > 0 ? $row['a1'] : 0) +
        ($target[1] > 0 ? $row['a2'] : 0) +
        ($target[2] > 0 ? $row['a3'] : 0);

    $final_score = $target_sum > 0 ? ($achieved_sum / $target_sum) * 100 : 0;
    $weightage = (float)$row['weightage'];
    $weightage_achieved = ($final_score * $weightage) / 100;

    $total_weightage += $weightage_achieved;
}
// $stmt->close();



 echo $total_weightage;


function calculatePerformanceConsumablesAmount($sactioned_amount, $weightagec, $firstDay) {
    if ($sactioned_amount <= 0 || $weightagec <= 0) {
        return 0;
    }
    $weightedAmount = ($sactioned_amount * $weightagec) / 100;
    $totalDaysInFourYears = 1461; // Includes 1 leap year (366 + 365*3)
    $perDayAmount = $weightedAmount / $totalDaysInFourYears;
    $daysInMonth = date('t', strtotime($firstDay)); // Days in the month
    return round($perDayAmount * $daysInMonth, 2);
}
$performanceConsumablesAmount = calculatePerformanceConsumablesAmount($bill['sactioned_amount'], $weightagec, $firstDay);
echo "Performance Amount: $performanceConsumablesAmount";




    

//calculate MACHINE USAGE

$sql = "SELECT 
    (
        SELECT SUM(CAST(REPLACE(Bt.value, ',', '') AS UNSIGNED))
        FROM baris_target AS Bt
        WHERE Bt.OrgID = $org_id
          AND Bt.created_date BETWEEN '$firstDay' AND '$lastDay'
          AND Bt.subqueId IN (
              SELECT DISTINCT bcr.db_surveySubQuestionId
              FROM baris_machine_report AS bcr
              WHERE bcr.OrgID = $org_id
                AND bcr.created_date BETWEEN '$firstDay' AND '$lastDay'
          )
    ) AS total_target,

    (
        SELECT 
            SUM(bcr.db_surveyValue)
        FROM baris_machine_report AS bcr
        INNER JOIN baris_report_weight brw 
            ON bcr.db_surveySubQuestionId = brw.subqueId
        WHERE bcr.OrgID = $org_id
          AND bcr.created_date BETWEEN '$firstDay' AND '$lastDay'
    ) AS total_survey_value,

    (
        SELECT brw.weightage
        FROM baris_machine_report AS bcr
        INNER JOIN baris_report_weight brw 
            ON bcr.db_surveySubQuestionId = brw.subqueId
        WHERE bcr.OrgID =$org_id
          AND bcr.created_date BETWEEN '$firstDay' AND '$lastDay'
        LIMIT 1
    ) AS weightage;";

$result = $conn->query($sql);
$data = $result->fetch_assoc();
$total_target = $data['total_target'] ?? 0;
$total_survey_value = $data['total_survey_value'] ?? 0;
$weightage = $data['weightage'] ?? 0;
$machineConsumablesPercentage = $total_target > 0 ? round(($total_survey_value / $total_target) * 100, 2) : 0;
$machineConsumablesAmount = calculatealreportAmount($bill['sactioned_amount'], $weightage, $firstDay);
//echo "Machine Consumables Percentage: $machineConsumablesPercentage%";



// calculate ATTENDANCE RECORDS OF THE STAFF 

$sql="SELECT 
    (
        SELECT SUM(CAST(REPLACE(Bt.value, ',', '') AS UNSIGNED))
        FROM baris_target AS Bt
        WHERE Bt.OrgID = 17
          AND Bt.created_date BETWEEN '2025-01-01' AND '2025-01-31'
          AND Bt.subqueId IN (
              SELECT DISTINCT bcr.db_surveySubQuestionId
              FROM  Manpower_Log_Details AS bcr
              WHERE bcr.OrgID = 17
                AND bcr.created_date BETWEEN '2025-01-01' AND '2025-01-31'
          )
    ) AS total_target,

    (
        SELECT 
            SUM(bcr.db_surveyValue)
        FROM  Manpower_Log_Details AS bcr
        WHERE bcr.OrgID = 17
          AND bcr.created_date BETWEEN '2025-01-01' AND '2025-01-31'
    ) AS total_survey_value,

    (
        SELECT brw.weightage
        FROM  Manpower_Log_Details AS bcr
        INNER JOIN baris_report_weight brw 
            ON bcr.db_surveySubQuestionId = brw.subqueId
        WHERE bcr.OrgID = 17
          AND bcr.created_date BETWEEN '2025-01-01' AND '2025-01-31'
        LIMIT 1
    ) AS weightage";
$result = $conn->query($sql);
$data = $result->fetch_assoc();
$total_target = $data['total_target'] ?? 0;
$total_survey_value = $data['total_survey_value'] ?? 0;
$weightage = $data['weightage'] ?? 0;
$attendancePercentage = $total_target > 0 ? round(($total_survey_value / $total_target) * 100, 2) : 0;
$attendanceAmount = calculatealreportAmount($bill['sactioned_amount'], $weightage, $firstDay);
//echo "Attendance Percentage: $attendancePercentage%";

// Earnings & Deductions
$earnings = [
    ['ATTENDANCE RECORDS OF THE STAFF', "$weightage%", "$attendancePercentage%", "$attendanceAmount"],
    ['CLEANLINESS RECORD', "0", "0", "0"],
    ['USE OF TYPE AND QUANTITY OF CONSUMABLES', "0", "0", "0"],
    ['MACHINERY USAGE',"$weightage%", "$machineConsumablesPercentage%", "$machineConsumablesAmount"],
    ['SURPRISE VISITS CONDUCTED BY OFFICIALS OF INDIAN RAILWAYS', "$totalWeight%", "$overallAverage%", "$surpriseVisitAmount"],
    ['MACHINE CONSUMABLES', "$weightage%", "$machineConsumablesPercentage%", "$machineConsumablesAmount"],
    ['PASSENGER FEEDBACK AND COMPLAINTS', "0", '0',"0"],
];
$earnings_total = array_sum(array_column($earnings, 3));
$earnings_total = round($earnings_total, 2);
// Consolidated score
$overallAverage = $overallAverage ?: 0;
$consolidated_score = round($overallAverage, 2) . '%';

// Fetch all penalties for the selected period and org
$sql = "SELECT penalty_amount, penalty_review FROM `baris_penalty` WHERE created_date BETWEEN '$firstDay' AND '$lastDay' AND OrgID = $org_id";
$result = $conn->query($sql);

$deductions = [];
$deductions_total = 0;

if ($result && $result->num_rows > 0) {
    $sn = 1;
    while ($row = $result->fetch_assoc()) {
        $amount = $row['penalty_amount'] ?? 0;
        $review = $row['penalty_review'] ?? '';
        $deductions[] = [
            'sn' => $sn++,
            'amount' => $amount,
            'deduction' => $review
        ];
        $deductions_total += $amount;
    }
} else {
    // No penalties found, add a default row
    $deductions[] = [
        'sn' => 1,
  
        'amount' => 0,
        'deduction' => ''
    ];
    $deductions_total = 0;
}

$total_payable = $earnings_total - $deductions_total;
$total_payable_rounded = round($total_payable);

// Deductions
// The following block is removed to prevent overwriting the $deductions array with an incompatible structure.
// $deductions = [
//     [$penalty_review, $penalty_amount, ],
//     // Add other deductions as needed, e.g.:
//     // ['PASSENGER COMPLAINT', 0, ''],
//     // ...
// ];
// $deductions_total = array_sum(array_column($deductions, 1));
// $total_payable = $earnings_total - $deductions_total;
// $total_payable_rounded = round($total_payable);
// Deductions
// $deductions = [
//     ['PASSENGER COMPLAINT', 0],
//     ['NON REMOVAL OF GARBAGE FROM DUSTBINS', 0],
//     ['OPEN BURNING OF WASTE IN RAILWAYS PREMISES', 0],
//     ['ROOF OF PLATFORM SHELTERS', 0],
//     ['MANPOWER AND UNIFORM PENALTY', 154912],
//     ['PENALTY IMPOSED BY NGT', 0],
//     ['SPOT PENALTY', 0],
//     ['PENALTY IMPOSED DUE TO MACHINE SHORTAGE/ OUT OF ORDER', 0],
//     ['PENALTY IMPOSED DUE TO SHORTAGE OF MACHINE CONSUMABLES', 9600],
//     ['PENALTY DUE TO NON AVAILABILITY OF CHEMICALS', 137850],
//     ['MONITORING EQUIPMENTS PENALTY', 0],
//     ['MISCELLANEOUS', 0],
// ];
// $deductions_total = array_sum(array_column($deductions, 1));
// $total_payable = $earnings_total - $deductions_total;
// $total_payable_rounded = round($total_payable);

function numberToWords($number) {
    return class_exists('NumberFormatter')
        ? strtoupper((new NumberFormatter('en_IN', NumberFormatter::SPELLOUT))->format($number)) . ' RUPEES'
        : "$number RUPEES";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PMC Bill Invoice</title>
    <link rel="stylesheet" href="assets/css/billing.css">
</head>
<body>
<form method="get" style="text-align:center; margin-bottom:20px;">
    <label for="month">Month:</label>
    <select name="month"><?php for ($m=1; $m<=12; $m++) {
        $val = str_pad($m, 2, '0', STR_PAD_LEFT);
        $sel = ($val == $selectedMonth) ? 'selected' : '';
        echo "<option value=\"$val\" $sel>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
    } ?></select>
    <label for="year">Year:</label>
    <select name="year"><?php for ($y=2020; $y<=date('Y')+2; $y++) {
        $sel = ($y == $selectedYear) ? 'selected' : '';
        echo "<option value=\"$y\" $sel>$y</option>";
    } ?></select>
    <input type="submit" value="Filter">
</form>

<!-- <div style="text-align:center;">
    <strong>Selected Date Range:</strong>
    <?= date('d.m.Y', strtotime($firstDay)) ?> TO <?= date('d.m.Y', strtotime($lastDay)) ?>
</div> -->

<div class="container">
    <h3 style="text-align:center;">PMC BILL INVOICE</h3>
    <table>
        <tr><td colspan="2"><strong>PAYABLE AMOUNT DECRIPTION OF SANITATION WORK AT TIRUPATI STATION</strong></td></tr>
        <tr>
            <td>PERIOD OF CONTRACT: <?= date('d.m.Y', strtotime($bill['period_of_contract_from'])) ?> TO <?= date('d.m.Y', strtotime($bill['period_of_contract_to'])) ?></td>
            <td>DATE RANGE: <?= date('d.m.Y', strtotime($firstDay)) ?> TO <?= date('d.m.Y', strtotime($lastDay)) ?></td>
        </tr>
        <tr><td>SANCTIONED AMOUNT: <?= $bill['sactioned_amount'] ?></td><td>SECURITY DEPOSIT: <?= $bill['security_deposit'] ?></td></tr>
        <tr><td>NOS OF WORKERS: <?= $bill['nos_of_worker'] ?></td><td>PERFORMANCE GUARANTY: <?= $bill['performance_guarant'] ?></td></tr>
        <tr><td>M.B. NO: <?= $bill['mb_no'] ?></td><td>COST OF WORK PER MONTH: <?= $bill['cost_of_work_per_day'] ?></td></tr>
        <tr><td>AGREEMENT LETTER NO &DT: <?= $bill['agreement_letter_no_dt'] ?></td><td>NUMBER OF DAYS FOR BILLING: <?= date('t', strtotime($firstDay)) ?></td></tr>
        <tr><td colspan="2">ACCEPTANCE LETTER NO &DT: <?= $bill['agreement_letter_no_dt'] ?></td></tr>
    </table>

    <div class="section"><strong>EARNINGS</strong></div>
    <table>
        <tr><th>S.NO</th><th>EARNINGS</th><th>WEIGHTAGE</th><th>SCORED</th><th>AMOUNT</th></tr>
        <?php foreach ($earnings as $i => $row): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td><?= htmlspecialchars($row[0]) ?></td>
                <td><?= $row[1] ?></td>
                <td><?= $row[2] ?></td>
                <td><?= number_format($row[3], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr><td>8</td><td>CONSOLIDATED PERFORMANCE SCORE</td><td colspan="2"><?= $consolidated_score ?></td><td></td></tr>
        <tr><th colspan="4">TOTAL</th><th><?= number_format($earnings_total, 2) ?></th></tr>
    </table>

    <div class="section"><strong>DEDUCTIONS</strong></div>
    <table>
        <tr>
            <th>S.NO</th>
            <th>DEDUCTION</th>
            <th colspan="2">Amount</th>
        </tr>

        <?php foreach ($deductions as $row): ?>
            <tr>
                <td><?= $row['sn'] ?></td>
                <td><?= htmlspecialchars($row['deduction']) ?></td>
                <td colspan="2"><?= number_format($row['amount'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
        
        
        <tr>
            <th colspan="2">TOTAL</th>
            <!-- <th><?= number_format($deductions_total, 2) ?></th> -->
            <th colspan="2"><?= numberToWords(round($deductions_total)) ?></th>
          
        </tr>
    </table>

    <div class="summary">
        TOTAL PAYABLE AMOUNT: <?= number_format($total_payable, 2) ?><br>
        ROUND OFF PAYABLE AMOUNT: <strong><?= $total_payable_rounded ?></strong><br>
        IN WORDS: <?= numberToWords($total_payable_rounded) ?>
    </div>
    <div class="footer">
        THIS IS A COMPUTER GENERATED INVOICE AND NO SIGNATURE IS REQUIRED.
    </div>
</div>
</body>
</html>
