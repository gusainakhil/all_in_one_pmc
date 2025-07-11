<?php

// Start the session
session_start();

// Check if userId is not set
if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
  // Destroy the session
  session_unset();
  session_destroy();
  header("Location: https://pmc.beatleme.co.in/");
  exit();
}

include_once "../../connection.php"; 
$id = isset($_GET['id']) ? $_GET['id'] : '';

// Get selected month/year from form or default to current
$fromDate = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-01');
$toDate = isset($_GET['to_date']) ? $_GET['to_date'] : date('Y-m-t');

$station_id = $_SESSION['stationId'];

// Create date range array
$start = new DateTime($fromDate);
$end = new DateTime($toDate);
$end = $end->modify('+1 day');

$interval = new DateInterval('P1D');
$dateRange = new DatePeriod($start, $interval, $end);
?>
<!doctype html>
<html lang="en">
<!--begin::Head-->
<?php include "head.php" ?>
<style>
  /* Performance dashboard styles */
   .report-container {
            width: 99%;
            margin: auto;
            page-break-after: always;
            font-weight: 800;
            font-size:11px;
            font-family: 'Roboto' !IMPORTANT;
        }
  
  .filter-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    justify-content: center;
  }
  
  
  
  .action-buttons {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 15px;
  }
  
  .action-buttons .btn {
    white-space: nowrap;
    font-size: 14px;
    display: flex;
    align-items: center;  
    gap: 5px;
  }
  
  .report-header {
    background-color: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #e9ecef;
  }
  
  .report-header h2 {
    margin: 0;
    font-size: 20px;
    color: #212529;
  }
  
  .report-header h3 {
    margin: 5px 0 0;
    font-size: 16px;
    color: #6c757d;
    font-weight: normal;
  }
  
  .meta-info {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    background-color: #e9ecef;
    padding: 10px;
    margin-bottom: 15px;
  }
  
  .meta-info span {
    display: inline-flex;
    align-items: center;
  }
  
  .data-table {
    width: 100%;
    border-collapse: collapse;
        font-size: 11px;
    font-family: 'Roboto';
  }
  
  .data-table th, .data-table td {
    border: 1px solid #dee2e6;
  
    text-align: center;
  }
  
  .data-table th {
    background-color: #f8f9fa;
    position: sticky;
    top: 0;
    z-index: 10;
  }
  
  .data-table .desc {
    text-align: left;
  }
  
  .loading {
    text-align: center;
    padding: 20px;
    font-size: 16px;
    color: #6c757d;
  }
  
  /* Print styles */
  @media print {
    .no-print {
      display: none;
    }
    
    body {
      background-color: white;
    }
    
    .performance-container {
      box-shadow: none;
      margin-bottom: 30px;
      page-break-after: always;
    }
  }
  
  /* Responsive adjustments */
  @media (max-width: 768px) {
    .filter-container {
      flex-direction: column;
    }
    
    .filter-container input,
    .filter-container button {
      width: 100%;
    }
    
    .meta-info {
      flex-direction: column;
      gap: 5px;
      align-items: flex-start;
    }
  }
</style>
<!--end::Head-->
<!--begin::Body-->

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
  <!--begin::App Wrapper-->
  <div class="app-wrapper">
    <?php include "header.php" ?>
    <main class="app-main">
      <!--begin::App Content Header-->
      <div class="app-content">
        <!--begin::Container-->
        <div class="container-fluid">
          <div class="row">
               <div class="col-lg-7 mb-3">
              <div class="performance-container p-3">
                <form method="get" class="no-print">
                  <div class="d-flex flex-wrap gap-2 align-items-end">
                    <div class="flex-grow-1">
                      <!-- <label for="from_date" class="form-label mb-1">From Date</label> -->
                      <input type="date" name="from_date" id="from_date" class="form-control" value="<?= htmlspecialchars($fromDate) ?>">
                    </div>
                    <div class="flex-grow-1">
                      <!-- <label for="to_date" class="form-label mb-1">To Date</label> -->
                      <input type="date" name="to_date" id="to_date" class="form-control" value="<?= htmlspecialchars($toDate) ?>">
                    </div>
                    <div>
                      <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-sync-alt me-1"></i> Go
                      </button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
            <div class="col-lg-5 mb-3">
              <div class="performance-container p-3">
                <div class="action-buttons no-print">
                  <!-- <a href="daily_performance_summary.php" class="btn btn-success" target="_blank">
                    <i class="fas fa-chart-pie"></i> Summary
                  </a> -->
                  <a href="daily_performance_summary_2.php?id=<?php echo $id; ?>" class="btn btn-success" target="_blank">
                    <i class="fas fa-chart-bar"></i> Summary2 
                  </a>
                  <!-- <a href="daily_performance_report.php" target="_blank" class="btn btn-success">
                    <i class="fas fa-file-alt"></i> Daily Performance Log
                  </a> -->
                  <a href="daily-performance-target.php?id=<?php echo $id; ?>" target="_blank" class="btn btn-success">
                    <i class="fas fa-bullseye"></i> Daily Performance Target
                  </a>
                </div>
              </div>
            </div>  
            
           
          </div>
          
          <!-- Loading indicator -->
          <div id="loading-indicator" class="loading no-print">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Loading performance data...</p>
          </div>
          
          <!-- Report Container - will be shown after loading -->
          <div class ="report-container" id="report-container" style="display: none;">
            <?php
            foreach ($dateRange as $dateObj) {
              $currentDate = $dateObj->format("Y-m-d");

              $query = "SELECT 
                bap.paramName AS task, 
                bp.db_pagename AS Description_of_Items, 
                bas.db_surveyValue AS Quality_of_done_work,
                bas.auditorname AS auditor_name,
                bs.stationName AS station_name,
                bo.db_Orgname AS organisation_name,
                bd.DivisionName AS division_name,
                bas.created_date AS report_date,
                bp.db_pageChoice2 AS Frequency,
                CONCAT(SUBSTRING(bp.db_pageChoice2, INSTR(bp.db_pageChoice2, '@')+1),'%') AS percentage_weightage
              FROM 
                baris_param bap
                INNER JOIN Daily_Performance_Log bas ON bap.paramId = bas.db_surveyParamId
                INNER JOIN baris_page bp ON bas.db_surveyPageId = bp.pageId
                INNER JOIN baris_station bs ON bas.db_surveyStationId = bs.stationId
                INNER JOIN baris_organization bo ON bas.OrgID = bo.OrgID
                INNER JOIN baris_division bd ON bas.DivisionId = bd.DivisionId
              WHERE 
                DATE(bas.created_date) = '$currentDate' 
                AND bas.db_surveyStationId = '$station_id'
              ORDER BY bas.db_surveyPageId ASC";

              $result = $conn->query($query);

              $tasks = [];
              $auditors = [
                'shift_1' => [],
                'shift_2' => [],
                'shift_3' => []
              ];

              if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  $task = trim($row['Description_of_Items']);
                  $percentage = $row['percentage_weightage'];
                  $frequency = $row['Frequency'];
                  $status = $row['Quality_of_done_work'] == 1 ? 'Y' : 'N';
                  $shift = strtolower(str_replace(' ', '_', $row['task']));
                  $auditorName = $row['auditor_name'];

                  // Use percentage as part of task key to ensure uniqueness only if same percentage
                  $uniqueTaskKey = $task . '||' . $percentage;

                  if (!isset($tasks[$uniqueTaskKey])) {
                    $tasks[$uniqueTaskKey] = [
                      'task' => $task,
                      'quantity' => 'As Available',
                      'frequency' => $frequency,
                      'percentage' => $percentage,
                      'shift_1' => '',
                      'shift_2' => '',
                      'shift_3' => '',
                      'remarks' => ''
                    ];
                  }

                  $tasks[$uniqueTaskKey][$shift] = $status;

                  // Track auditors per shift (avoid duplicates)
                  if (!in_array($auditorName, $auditors[$shift])) {
                    $auditors[$shift][] = $auditorName;
                  }

                  // Store division/station/org from first row
                  $division = $row['division_name'];
                  $station = $row['station_name'];
                  $contractor = $row['organisation_name'];
                }
            ?>
                <div class="performance-container mb-4">
                  <div class="report-header">
                    <h3 class ="text-center">Daily performance log book for cleaning schedule for environmental sanitation, mechanized cleaning and housekeeping contract at Tirupati <?= htmlspecialchars($station) ?> Railway Station</h3>
                  </div>

                  <div class="meta-info">
                    <span><i class="far fa-calendar-alt me-1"></i> <strong>Date:</strong> <?= date('d M Y', strtotime($currentDate)) ?></span>
                    <span><i class="fas fa-building me-1"></i> <strong>Division:</strong> <?= htmlspecialchars($division) ?></span>
                    <span><i class="fas fa-map-marker-alt me-1"></i> <strong>Station:</strong> <?= htmlspecialchars($station) ?></span>
                    <span><i class="fas fa-user-tie me-1"></i> <strong>Contractor:</strong> <?= htmlspecialchars($contractor) ?></span>
                  </div>

                  <div class="table-responsive">
                    <table class="data-table">
                      <thead>
                        <tr>
                          <th rowspan="2" style="width: 1%;">S.No</th>
                          <th rowspan="2" style="width: 35%;" >Description of Items</th>
                          <th rowspan="2" style="width: 6%;">App. Quantity</th>
                          <th rowspan="2" style="width: 20%;">Frequency</th>
                          <th rowspan="2" style="width:5%;">Percentage Weightage</th>
                          <th colspan="3">Shifts</th>
                          <th rowspan="2" style="width:1%">Remarks</th>
                        </tr>
                        <tr>
                          <th style="width:3%">Shift 1<br>(Y/N)</th>
                          <th style="width:3%">Shift 2<br>(Y/N)</th>
                          <th style="width:3%">Shift 3<br>(Y/N)</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php
                        $sn = 1;
                        foreach ($tasks as $taskData): ?>
                          <tr>
                            <td><?= $sn++ ?></td>
                            <td class="desc"><?= htmlspecialchars($taskData['task']) ?></td>
                            <td><?= $taskData['quantity'] ?></td>
                            <td><?= $taskData['frequency'] ?></td>
                            <td><?= $taskData['percentage'] ?></td>
                            <td><?= $taskData['shift_1'] ?: '-' ?></td>
                            <td><?= $taskData['shift_2'] ?: '-' ?></td>
                            <td><?= $taskData['shift_3'] ?: '-' ?></td>
                            <td><?= $taskData['remarks'] ?></td>
                          </tr>
                        <?php endforeach; ?>

                        <!-- Final rows for auditor names and signatures -->
                        <tr>
                          <td style="width:9%;" colspan="5"><strong>Name of Auditor</strong></td>
                          <td style="width:3%;"><strong><?= !empty($auditors['shift_1']) ? $auditors['shift_1'][0] : '-' ?></strong></td>
                          <td style="width:3%;"><strong><?= !empty($auditors['shift_2']) ? $auditors['shift_2'][0] : '-' ?></strong></td>
                          <td style="width:3%;"><strong><?= !empty($auditors['shift_3']) ? $auditors['shift_3'][0] : '-' ?></strong></td>
                          <td></td>
                        </tr>
                        <tr>
                          <td colspan="5"><strong>Signature of On DUTY SUPERVISOR</strong></td>
                          <td></td>
                          <td></td>
                          <td></td>
                          <td></td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

            <?php
              } // end if data exists
            } // end foreach loop
            ?>
          </div>
          
          <!-- Print button -->

        </div>
      </div>
    </main>
    <?php include "footer.php" ?>
  </div>
  
  <!-- Simple script to hide loading indicator and show content -->
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      // Hide loading indicator and show report after page has loaded
      setTimeout(function() {
        document.getElementById('loading-indicator').style.display = 'none';
        document.getElementById('report-container').style.display = 'block';
      }, 500);
    });
  </script>
</body>

</html>