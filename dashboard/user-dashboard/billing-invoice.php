<?php 
include "../../connection.php";
session_start();

if (!isset($_SESSION['OrgID'], $_SESSION['stationId'])) {
    die("Session data missing.");
}
$org_id = $_SESSION['OrgID'];
$station_id = $_SESSION['stationId'];

// Fetch billing details
$stmt = $conn->prepare("SELECT sactioned_amount, nos_of_worker, security_deposit, mb_no, performance_guarant, agreement_letter_no_dt, cost_of_work_per_day, period_of_contract_from, period_of_contract_to FROM baris_bill_rate WHERE OrgID = ?");
$stmt->bind_param("i", $org_id);
$stmt->execute();
$bill = $stmt->get_result()->fetch_assoc();

// Get date range
$currentMonth = date('m');
$currentYear = date('Y');
$selectedMonth = $_GET['month'] ?? $currentMonth;
$selectedYear = $_GET['year'] ?? $currentYear;
$firstDay = date('Y-m-01', strtotime("$selectedYear-$selectedMonth-01"));
$lastDay = date('Y-m-t', strtotime("$selectedYear-$selectedMonth-01"));

// Fetch surprise visit score
$sql = "
    SELECT SUM(bas.db_surveyValue) AS total_score, COUNT(bas.db_surveyValue) AS total_records, brw.weightage
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

// Static earnings and deductions (replace with dynamic if needed)
$earnings = [
    ['ATTENDANCE RECORDS OF THE STAFF', '25%', '92.39%', 369040.82],
    ['CLEANLINESS RECORD', '15%', '100%', 221424.49],
    ['USE OF TYPE AND QUANTITY OF CONSUMABLES', '10%', '97.85%', 147616.33],
    ['MACHINERY USAGE', '5%', '100%', 73808.16],
    ['SURPRISE VISITS CONDUCTED BY OFFICIALS OF INDIAN RAILWAYS', "$totalWeight%", "$overallAverage%", 147616.33],
    ['MACHINE CONSUMABLES', '5%', '90.32%', 73808.16],
    ['PASSENGER FEEDBACK AND COMPLAINTS', '30%', '86%', 442848.98],
];
$earnings_total = 1476163.27;
$consolidated_score = '91.47%';

$deductions = [
    ['PASSENGER COMPLAINT', 0],
    ['NON REMOVAL OF GARBAGE FROM DUSTBINS', 0],
    ['OPEN BURNING OF WASTE IN RAILWAYS PREMISES', 0],
    ['ROOF OF PLATFORM SHELTERS', 0],
    ['MANPOWER AND UNIFORM PENALTY', 154912],
    ['PENALTY IMPOSED BY NGT', 0],
    ['SPOT PENALTY', 0],
    ['PENALTY IMPOSED DUE TO MACHINE SHORTAGE/ OUT OF ORDER', 0],
    ['PENALTY IMPOSED DUE TO SHORTAGE OF MACHINE CONSUMABLES', 9600],
    ['PENALTY DUE TO NON AVAILABILITY OF CHEMICALS', 137850],
    ['MONITORING EQUIPMENTS PENALTY', 0],
    ['MISCELLANEOUS', 0],
];
$deductions_total = 302362;
$total_payable = $earnings_total - $deductions_total;
$total_payable_rounded = round($total_payable);

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

    <div style="text-align:center;">
        <strong>Selected Date Range:</strong>
        <?= date('d.m.Y', strtotime($firstDay)) ?> TO <?= date('d.m.Y', strtotime($lastDay)) ?>
    </div>

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
            <tr><th>S.NO</th><th>DEDUCTIONS</th><th>AMOUNT</th></tr>
            <?php foreach ($deductions as $i => $row): ?>
                <tr><td><?= $i+1 ?></td><td><?= htmlspecialchars($row[0]) ?></td><td><?= number_format($row[1], 2) ?></td></tr>
            <?php endforeach; ?>
            <tr><th colspan="2">TOTAL</th><th><?= number_format($deductions_total, 2) ?></th></tr>
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
