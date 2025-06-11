<?php 
include "../../connection.php";

session_start();
if (!isset($_SESSION['OrgID'])) {
    die("Organization ID not set in session.");
}
$org_id = $_SESSION['OrgID'];

$sql = "SELECT sactioned_amount, nos_of_worker, security_deposit, mb_no, performance_guarant, agreement_letter_no_dt, cost_of_work_per_day, period_of_contract_from, period_of_contract_to FROM `baris_bill_rate` WHERE OrgID = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $org_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result) {
    $bill = $result->fetch_assoc();
} else {
    $bill = null;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="description" content="PMC Bill Invoice for sanitation work at Tirupati Station, detailing earnings, deductions, and payable amount."/>
    <meta name="keywords" content="PMC, Bill Invoice, Sanitation Work, Tirupati Station, Indian Railways"/>
    <meta name="author" content="Indian Railways"/>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/v4-shims.min.css"/>
    <link rel="stylesheet" href="assets/css/billing.css">
    <title>PMC Bill Invoice</title>

</head>
<body>

        <!-- data filter select autmatically current month -->
        <?php
        // Set default to current month/year
        $currentMonth = date('m');
        $currentYear = date('Y');

        // Get selected month/year from GET or default to current
        $selectedMonth = isset($_GET['month']) ? $_GET['month'] : $currentMonth;
        $selectedYear = isset($_GET['year']) ? $_GET['year'] : $currentYear;

        // Calculate first and last day of selected month
        $firstDay = date('Y-m-01', strtotime("$selectedYear-$selectedMonth-01"));
        $lastDay = date('Y-m-t', strtotime("$selectedYear-$selectedMonth-01"));
        ?>

        <form method="get" style="margin-bottom:20px; text-align:center;">
            <label for="month">Month:</label>
            <select name="month" id="month">
                <?php
                for ($m = 1; $m <= 12; $m++) {
                    $val = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $selected = ($val == $selectedMonth) ? 'selected' : '';
                    echo "<option value=\"$val\" $selected>" . date('F', mktime(0,0,0,$m,10)) . "</option>";
                }
                ?>
            </select>
            <label for="year">Year:</label>
            <select name="year" id="year">
                <?php
                $startYear = 2020;
                $endYear = date('Y') + 2;
                for ($y = $startYear; $y <= $endYear; $y++) {
                    $selected = ($y == $selectedYear) ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                }
                ?>
            </select>
            <input type="submit" value="Filter" />
        </form>
        <div style="text-align:center; margin-bottom:10px;">
            <strong>Selected Date Range:</strong>
            <?php echo date('d.m.Y', strtotime($firstDay)); ?> TO <?php echo date('d.m.Y', strtotime($lastDay)); ?>
        </div>

        

        <!-- when user slect the data range submit the data then execulte the below code  -->

    <?php
    // Example: You would fetch earnings and deductions from your database based on $org_id, $firstDay, $lastDay
    // For now, using static data as in your original code

    // You can replace these arrays with dynamic queries as needed
    $earnings = [
        ['ATTENDANCE RECORDS OF THE STAFF', '25%', '92.39%', 369040.82],
        ['CLEANLINESS RECORD', '15%', '100%', 221424.49],
        ['USE OF TYPE AND QUANTITY OF CONSUMABLES', '10%', '97.85%', 147616.33],
        ['MACHINERY USAGE', '5%', '100%', 73808.16],
        ['SURPRISE VISITS CONDUCTED BY OFFICIALS OF INDIAN RAILWAYS', '10%', '82.28%', 147616.33],
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

    // Helper function to convert number to words (simple version)
    function numberToWords($number) {
        // You can use NumberFormatter if intl extension is enabled
        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter('en_IN', NumberFormatter::SPELLOUT);
            return strtoupper($fmt->format($number)) . ' RUPEES';
        }
        // Fallback
        return $number . ' RUPEES';
    }
    ?>
    <div class="container">
        <h3 style="text-align:center;">PMC BILL INVOICE</h3>
        <div class="center">PMC BILL INVOICE</div>
        <table>
            <tr>
                <td colspan="2"><strong>PAYABLE AMOUNT DECRIPTION OF SANITATION WORK AT TIRUPATI STATION</strong></td>
            </tr>
            <tr>
                <td>PERIOD OF CONTRACT: <?php echo date('d.m.Y', strtotime($bill['period_of_contract_from'])); ?> TO <?php echo date('d.m.Y', strtotime($bill['period_of_contract_to'])); ?> </td>
                <td>DATE RANGE: <?php echo date('d.m.Y', strtotime($firstDay)); ?> TO <?php echo date('d.m.Y', strtotime($lastDay)); ?></td>
            </tr>
            <tr>
                <td>SANCTIONED AMOUNT: <?php echo $bill['sactioned_amount'];?></td>
                <td>SECURITY DEPOSIT: <?php echo $bill['security_deposit'];?></td>
            </tr>
            <tr>
                <td>NOS OF WORKERS : <?php echo $bill['nos_of_worker'];?></td>
                <td>PERFORMANCE GUARANTY : <?php echo $bill['performance_guarant'];?></td>
            </tr>
            <tr>
                <td>M.B. NO : <?php echo $bill['mb_no'];?></td>
                <td>COST OF WORK PER MONTH AS PER AGREEMENT : <strong><?php echo $bill['cost_of_work_per_day'];?></strong></td>
            </tr>
            <tr>
                <td>AGREEMENT LETTER NO &DT : <?php echo $bill['agreement_letter_no_dt'];?></td>
                <td>NUMBER OF DAYS FOR BILLING : <?php echo date('t', strtotime($firstDay)); ?></td>
            </tr>
            <tr>
                <td colspan="2">ACCEPTANCE LETTER NO &DT : <?php echo $bill['agreement_letter_no_dt'];?></td>
            </tr>
        </table>
        <div class="section"><strong>EARNINGS</strong></div>
        <table>
            <tr>
                <th>S.NO</th><th>EARNINGS</th><th>WEIGHTAGE</th><th>SCORED</th><th>AMOUNT</th>
            </tr>
            <?php foreach ($earnings as $i => $row): ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><?php echo htmlspecialchars($row[0]); ?></td>
                    <td><?php echo $row[1]; ?></td>
                    <td><?php echo $row[2]; ?></td>
                    <td><?php echo number_format($row[3], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td>8</td>
                <td>CONSOLIDATED PERFORMANCE SCORE</td>
                <td colspan="2"><?php echo $consolidated_score; ?></td>
                <td></td>
            </tr>
            <tr>
                <th colspan="4">TOTAL</th><th><?php echo number_format($earnings_total, 2); ?></th>
            </tr>
        </table>
        <div class="section"><strong>DEDUCTIONS</strong></div>
        <table>
            <tr>
                <th>S.NO</th><th>DEDUCTIONS</th><th>AMOUNT</th>
            </tr>
            <?php foreach ($deductions as $i => $row): ?>
                <tr>
                    <td><?php echo $i+1; ?></td>
                    <td><?php echo htmlspecialchars($row[0]); ?></td>
                    <td><?php echo number_format($row[1], 2); ?></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <th colspan="2">TOTAL</th><th><?php echo number_format($deductions_total, 2); ?></th>
            </tr>
        </table>
        <div class="summary">
            TOTAL PAYABLE AMOUNT : <?php echo number_format($total_payable, 2); ?><br/>
            TOTAL ROUND OFF PAYABLE AMOUNT : <strong><?php echo $total_payable_rounded; ?></strong><br/>
            IN WORDS: <?php echo numberToWords($total_payable_rounded); ?>
        </div>
        <div class="footer">
            THIS IS A COMPUTER GENERATED INVOICE AND NO SIGNATURE IS REQUIRED.
        </div>
    </div>
</body>
</html>
