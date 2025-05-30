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
    <title>PMC Bill Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 14px;
            font-size: 14px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .center {
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            margin-top: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 1em;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px 2px;
            text-align: center;
        }
        th {
            background-color: #f0f0f0;
        }
        .section {
            margin-top: 10px;
        }
        .summary {
            margin-top: 10px;
            text-align: right;
            font-weight: bold;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
        }
        img {
            max-width: 100px;
            height: auto;
            display: block;
            margin: 0 auto 10px auto;
        }
        @media (max-width: 700px) {
            .container {
                padding: 10px;
            }
            table, thead, tbody, th, td, tr {
                display: block;
                width: 100%;
            }
            thead tr {
                display: none;
            }
            tr {
                margin-bottom: 15px;
                border-bottom: 2px solid #eee;
            }
            td {
                text-align: right;
                padding-left: 50%;
                position: relative;
                border: none;
                border-bottom: 1px solid #eee;
            }
            td:before {
                position: absolute;
                left: 10px;
                top: 8px;
                width: 45%;
                white-space: nowrap;
                font-weight: bold;
                text-align: left;
            }
            /* Add labels for each cell */
            table:nth-of-type(1) td:nth-of-type(1):before { content: "Description"; }
            table:nth-of-type(1) td:nth-of-type(2):before { content: "Details"; }
            table:nth-of-type(2) td:nth-of-type(1):before { content: "S.NO"; }
            table:nth-of-type(2) td:nth-of-type(2):before { content: "EARNINGS"; }
            table:nth-of-type(2) td:nth-of-type(3):before { content: "WEIGHTAGE"; }
            table:nth-of-type(2) td:nth-of-type(4):before { content: "SCORED"; }
            table:nth-of-type(2) td:nth-of-type(5):before { content: "AMOUNT"; }
            table:nth-of-type(3) td:nth-of-type(1):before { content: "S.NO"; }
            table:nth-of-type(3) td:nth-of-type(2):before { content: "DEDUCTIONS"; }
            table:nth-of-type(3) td:nth-of-type(3):before { content: "AMOUNT"; }
            .summary {
                text-align: left;
                font-size: 1em;
            }
        }
        @media (max-width: 400px) {
            body {
                font-size: 12px;
            }
            .footer {
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
       
        <h3 style="text-align:center;">PMC BILL INVOICE</h3> 
        <div class="center">PMC BILL INVOICE</div>
        <table>
            <tr>
                <td colspan="2"><strong>PAYABLE AMOUNT DECRIPTION OF SANITATION WORK AT TIRUPATI STATION</strong></td>
            </tr>
            <tr>
                <td>PERIOD OF CONTRACT: <?php echo date('d.m.Y', strtotime($bill['period_of_contract_from'])); ?> TO <?php echo date('d.m.Y', strtotime($bill['period_of_contract_to'])); ?> </td>
                <td>DATE RANGE: 01.05.2025 TO 31.05.2025</td>
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
                <td>NUMBER OF DAYS FOR BILLING : 31</td>
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
            <tr><td>1</td><td>ATTENDANCE RECORDS OF THE STAFF</td><td>25%</td><td>92.39%</td><td>369040.82</td></tr>
            <tr><td>2</td><td>CLEANLINESS RECORD</td><td>15%</td><td>100%</td><td>221424.49</td></tr>
            <tr><td>3</td><td>USE OF TYPE AND QUANTITY OF CONSUMABLES</td><td>10%</td><td>97.85%</td><td>147616.33</td></tr>
            <tr><td>4</td><td>MACHINERY USAGE</td><td>5%</td><td>100%</td><td>73808.16</td></tr>
            <tr><td>5</td><td>SURPRISE VISITS CONDUCTED BY OFFICIALS OF INDIAN RAILWAYS</td><td>10%</td><td>82.28%</td><td>147616.33</td></tr>
            <tr><td>6</td><td>MACHINE CONSUMABLES</td><td>5%</td><td>90.32%</td><td>73808.16</td></tr>
            <tr><td>7</td><td>PASSENGER FEEDBACK AND COMPLAINTS</td><td>30%</td><td>86%</td><td>442848.98</td></tr>
            <tr><td>8</td><td>CONSOLIDATED PERFORMANCE SCORE</td><td colspan="2">91.47%</td><td></td></tr>
            <tr>
                <th colspan="4">TOTAL</th><th>1476163.27</th>
            </tr>
        </table>
        <div class="section"><strong>DEDUCTIONS</strong></div>
        <table>
            <tr>
                <th>S.NO</th><th>DEDUCTIONS</th><th>AMOUNT</th>
            </tr>
            <tr><td>1</td><td>PASSENGER COMPLAINT</td><td>0</td></tr>
            <tr><td>2</td><td>NON REMOVAL OF GARBAGE FROM DUSTBINS</td><td>0</td></tr>
            <tr><td>3</td><td>OPEN BURNING OF WASTE IN RAILWAYS PREMISES</td><td>0</td></tr>
            <tr><td>4</td><td>ROOF OF PLATFORM SHELTERS</td><td>0</td></tr>
            <tr><td>5</td><td>MANPOWER AND UNIFORM PENALTY</td><td>154912</td></tr>
            <tr><td>6</td><td>PENALTY IMPOSED BY NGT</td><td>0</td></tr>
            <tr><td>7</td><td>SPOT PENALTY</td><td>0</td></tr>
            <tr><td>8</td><td>PENALTY IMPOSED DUE TO MACHINE SHORTAGE/ OUT OF ORDER</td><td>0</td></tr>
            <tr><td>9</td><td>PENALTY IMPOSED DUE TO SHORTAGE OF MACHINE CONSUMABLES</td><td>9600</td></tr>
            <tr><td>10</td><td>PENALTY DUE TO NON AVAILABILITY OF CHEMICALS</td><td>137850</td></tr>
            <tr><td>11</td><td>MONITORING EQUIPMENTS PENALTY</td><td>0</td></tr>
            <tr><td>12</td><td>MISCELLANEOUS</td><td>0</td></tr>
            <tr>
                <th colspan="2">TOTAL</th><th>302362</th>
            </tr>
        </table>
        <div class="summary">
            TOTAL PAYABLE AMOUNT : 1173801.27<br/>
            TOTAL ROUND OFF PAYABLE AMOUNT : <strong>1173801</strong><br/>
            IN WORDS: ELEVEN LAKHS SEVENTY THREE THOUSANDS EIGHT HUNDRED AND ONE RUPEES
        </div>
        <div class="footer">
            THIS IS A COMPUTER GENERATED INVOICE AND NO SIGNATURE IS REQUIRED.
        </div>
    </div>
</body>
</html>
