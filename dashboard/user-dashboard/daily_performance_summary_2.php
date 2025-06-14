<?php
session_start();
include '../../connection.php';
 $station_id = $_SESSION['stationId'];
$OrgID =$_SESSION['OrgID'];
if (isset($_GET['id'])) {
    $_SESSION['squeld'] = $_GET['id']; // Store it in session
}
$squeld = $_SESSION['squeld'];
// $station_id = '44';
$month = isset($_GET['month']) ? $_GET['month'] : date('n');
$year = isset($_GET['year']) ? $_GET['year'] : date('Y');

$startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
$endDate = date("Y-m-t", strtotime($startDate));

// Fetch achievement data
$achievement_sql = "SELECT 
    dpl.db_surveyPageId,
    bs.stationName,
    bd.DivisionName,
    bpage.db_pagename AS Description_of_Items,
    bpage.db_pageChoice2 AS Frequency,
    bo.db_Orgname ,
    SUBSTRING(bpage.db_pageChoice2, INSTR(bpage.db_pageChoice2, '@') + 1) AS Percentage_Weightage,
    SUM(CASE WHEN bp.paramName = 'Shift 1' THEN dpl.db_surveyValue ELSE 0 END) AS Shift_1_Achievement,
    SUM(CASE WHEN bp.paramName = 'Shift 2' THEN dpl.db_surveyValue ELSE 0 END) AS Shift_2_Achievement,
    SUM(CASE WHEN bp.paramName = 'Shift 3' THEN dpl.db_surveyValue ELSE 0 END) AS Shift_3_Achievement
FROM 
    Daily_Performance_Log dpl
JOIN baris_station bs ON dpl.db_surveyStationId = bs.stationId
JOIN baris_division bd ON bs.DivisionId = bd.DivisionId
JOIN baris_param bp ON dpl.db_surveyParamId = bp.paramId
JOIN baris_page bpage ON dpl.db_surveyPageId = bpage.pageId
JOIN baris_organization bo on dpl.OrgID=bo.OrgID
WHERE 
    dpl.db_surveyStationId = '$station_id'
    AND dpl.created_date BETWEEN '$startDate' AND '$endDate'
GROUP BY 
    dpl.db_surveyPageId
ORDER BY dpl.db_surveyPageId ASC";

$achievement_result = $conn->query($achievement_sql);

// Fetch monthly target data
$target_sql = "SELECT pageId,
    SUBSTRING_INDEX(value, ',', 1) AS Monthly_target_Shift1,
    SUBSTRING_INDEX(SUBSTRING_INDEX(value, ',', 2), ',', -1) AS Monthly_target_Shift2,
    SUBSTRING_INDEX(SUBSTRING_INDEX(value, ',', 3), ',', -1) AS Monthly_target_Shift3
FROM baris_target
WHERE OrgID = '$OrgID' 
    AND month = '$month' 
    AND subqueId = '$squeld'
ORDER BY id DESC
LIMIT 24";

$target_result = $conn->query($target_sql);
$targets = [];
while ($row = $target_result->fetch_assoc()) {
    $targets[$row['pageId']] = [
        'Monthly_target_Shift1' => $row['Monthly_target_Shift1'],
        'Monthly_target_Shift2' => $row['Monthly_target_Shift2'],
        'Monthly_target_Shift3' => $row['Monthly_target_Shift3']
    ];
}

$avg_final_score = 0;
$avg_final_score_raw = 0;
$total_weightage_achieved = 0;
$total_final_score = 0;
$count_rows = 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PMC - Daily Performance Log</title>
  <link rel="stylesheet" href="assets/css/performace-log-summary2.css">
</head>
<body>
  <form method="get">
    <label>Month:
      <select name="month">
        <?php for ($m = 1; $m <= 12; $m++): ?>
          <option value="<?= $m ?>" <?= ($month == $m) ? 'selected' : '' ?>><?= date('F', mktime(0, 0, 0, $m, 10)) ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label>Year:
      <select name="year">
        <?php for ($y = 2023; $y <= date('Y'); $y++): ?>
          <option value="<?= $y ?>" <?= ($year == $y) ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <button type="submit">View Report</button>
  </form>

  <div class="pmc-container">
    <h2 class="pmc-title">Western Railway</h2>
    <h3 class="pmc-subtitle">PMC - Daily Performance Log</h3>
    <p class="pmc-meta">Daily uses of type and quantity of consumables of environmental sanitation, mechanized cleaning and housekeeping contract at Bhuj Railway station</p>
    <div class="pmc-details">
      <p><strong>Month:</strong> <?= date('F', mktime(0, 0, 0, $month, 10)) ?> - <?= $year ?></p>
      <?php if ($achievement_result->num_rows > 0) {
        $first_row = $achievement_result->fetch_assoc();
        echo "<p><strong>Division:</strong> {$first_row['DivisionName']}</p>";
        echo "<p><strong>Station:</strong> {$first_row['stationName']}</p>";
        echo "<p><strong>Name Of Contractor:</strong> {$first_row['db_Orgname']}</p>";
        $achievement_result->data_seek(0);  
      } ?>
    
    <p><strong>Monthly Final Score:</strong> <span id="monthly-final-score-display"></span></p>
    <script>
      // Get the value from the table's <td id="monthly-final-score">
      document.addEventListener('DOMContentLoaded', function() {
      var scoreTd = document.getElementById('monthly-final-score');
      var displaySpan = document.getElementById('monthly-final-score-display');
      if (scoreTd && displaySpan) {
        displaySpan.textContent = scoreTd.textContent;
      }
      });
    </script>

    </div>

    <div class="pmc-table-wrapper">
      <table class="pmc-table">
        <thead>
          <tr>
            <th rowspan="2">S.No</th>
            <th rowspan="2">Description of Items</th>
            <th rowspan="2">Frequency</th>
            <th rowspan="2">Percentage Weightage</th>
            <th colspan="3">Monthly Target</th>
            <th colspan="3">Monthly Achievement</th>
            <th rowspan="2">Final Score</th>
            <th rowspan="2">Weightage Achieved</th>
          </tr>
          <tr>
            <th>Shift 1</th><th>Shift 2</th><th>Shift 3</th>
            <th>Shift 1</th><th>Shift 2</th><th>Shift 3</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($achievement_result->num_rows > 0): 
            $i = 1;
            while ($row = $achievement_result->fetch_assoc()):
              $pageId = $row['db_surveyPageId'];
                $target = $targets[$pageId] ?? ['Monthly_target_Shift1' => 0, 'Monthly_target_Shift2' => 0, 'Monthly_target_Shift3' => 0];

                // If any monthly shift target is zero, skip the calculation for which shift target is 0
              if ($target['Monthly_target_Shift1'] == 0) {
                  $row['Shift_1_Achievement'] = 0;
              }
              if ($target['Monthly_target_Shift2'] == 0) {
                  $row['Shift_2_Achievement'] = 0;
              }
              if ($target['Monthly_target_Shift3'] == 0) {
                  $row['Shift_3_Achievement'] = 0;
              }

              // Calculate final score and weightage achieved
              $target_total = $target['Monthly_target_Shift1'] + $target['Monthly_target_Shift2'] + $target['Monthly_target_Shift3'];
              $achieved_total = $row['Shift_1_Achievement'] + $row['Shift_2_Achievement'] + $row['Shift_3_Achievement'];
              $final_score = $target_total > 0 ? ($achieved_total / $target_total) * 100 : 0;
              $weightage_achieved = $final_score * floatval($row['Percentage_Weightage']) / 100;

              $total_weightage_achieved += $weightage_achieved;
              $total_final_score += $final_score;
              $count_rows++;
          ?>
          <tr>
            <td><?= $i++ ?></td>
            <td><?= $row['Description_of_Items'] ?></td>
            <td><?= strtok($row['Frequency'], '@') ?></td>
            <td><?= $row['Percentage_Weightage'] ?></td>
            <td><?= $target['Monthly_target_Shift1'] ?></td>
            <td><?= $target['Monthly_target_Shift2'] ?></td>
            <td><?= $target['Monthly_target_Shift3'] ?></td>
            <td><?= $row['Shift_1_Achievement'] ?></td>
            <td><?= $row['Shift_2_Achievement'] ?></td>
            <td><?= $row['Shift_3_Achievement'] ?></td>
            <td><?= number_format($final_score, 2) ?>%</td>
            <td><?= number_format($weightage_achieved, 2) ?>%</td>
          </tr>
          <?php endwhile; 
            $avg_final_score = $total_weightage_achieved;

            $avg_final_score_raw = $total_final_score / $count_rows;
          endif; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="10" style="text-align:right;"><strong>Monthly Final Score:</strong></td>
            <!-- <td><strong><?= number_format($avg_final_score_raw, 2) ?>%</strong></td> -->
        
            <td colspan="2" id="monthly-final-score" style="text-align: center;"><strong><?= number_format($avg_final_score, 2) ?>%</strong></td>

      
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</body>
</html>
