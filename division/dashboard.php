<?php

$division_id = 16;
session_start();

include "connection.php";
// Fetch Division Managers
// Fetch users by type for the division
// Query to count auditors and owners in the division
$station_count = "SELECT db_usertype, COUNT(*) as count 
  FROM baris_userlogin 
  WHERE divisionId = ? AND db_usertype IN ('owner', 'auditor')
  GROUP BY db_usertype";

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare($station_count);
if (!$stmt) {
  error_log("Prepare failed: " . $conn->error);
  die("Database error. Please contact the administrator.");
}

$stmt->bind_param("i", $division_id);
$stmt->execute();
$userTypeResult = $stmt->get_result();

if (!$userTypeResult) {
  error_log("Query execution failed: " . $stmt->error);
  die("Failed to retrieve user data. Please try again later.");
}

// Initialize counters
$auditorCount = 0;
$ownerCount = 0;
$stationCount = 0;

// Process all rows in the result
while ($row = $userTypeResult->fetch_assoc()) {
  if ($row['db_usertype'] === 'auditor') {
    $auditorCount = $row['count'];
  } elseif ($row['db_usertype'] === 'owner') {
    $ownerCount = $row['count'];
  }
}

// Calculate total station count (sum of auditors and owners)
echo $auditorCount;
echo $ownerCount;

// Close the statement
$stmt->close();

// Sample railway-specific data (in a real system, these would come from DB)
$totalStations = 3;
$trainsOperational = 5;
$totalStaff = 15;
$punctualityRate = 94.5;
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Division Manager Dashboard | Indian Railways</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

    :root {
      --primary: #0056b3;
      /* Indian Railways blue */
      --secondary: #004080;
      --accent: #ff6b01;
      /* Indian Railways orange */
      --green: #1e4620;
      /* Indian Railways green */
      --light: #f8f9fa;
      --dark: #343a40;
    }

    body {
      font-family: 'Inter', sans-serif;
      background-color: #f3f4f6;
      text-transform: capitalize;

    }

    .card {
      border-radius: 12px;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
      background-color: white;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .stat-card {
      display: flex;
      flex-direction: column;
      padding: 1.5rem;
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1rem;
    }

    .dropdown-menu {
      opacity: 0;
      visibility: hidden;
      transform: translateY(10px);
      transition: opacity 0.2s, transform 0.2s, visibility 0.2s;
    }

    .dropdown:hover .dropdown-menu {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }

    /* Railway specific styles */
    .railway-header {
      background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      color: white;
    }

    .railway-status-pill {
      display: inline-block;
      padding: 0.25rem 0.75rem;
      border-radius: 9999px;
      font-weight: 500;
      font-size: 0.75rem;
      text-transform: uppercase;
    }

    .railway-status-pill.on-time {
      background-color: #ccffcc;
      color: #006600;
    }

    .railway-status-pill.delayed {
      background-color: #ffeecc;
      color: #996600;
    }

    .railway-status-pill.cancelled {
      background-color: #ffcccc;
      color: #990000;
    }

    .train-status-dot {
      display: inline-block;
      width: 8px;
      height: 8px;
      border-radius: 50%;
      margin-right: 0.5rem;
    }

    .train-status-dot.running {
      background-color: #10b981;
    }

    .train-status-dot.delayed {
      background-color: #f59e0b;
    }

    .train-status-dot.cancelled {
      background-color: #ef4444;
    }

    /* Responsive fixes */
    @media (max-width: 768px) {
      .railway-header {
        padding: 1rem;
      }

      .stat-card {
        padding: 1rem;
      }
    }
  </style>
</head>

<body>

  <div class="flex min-h-screen">
    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main Content -->
    <div class="ml-0 md:ml-64 flex-1 min-h-screen">
      <!-- Top Navigation -->
      <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="flex items-center justify-between px-4 md:px-6 py-4">
          <div class="flex items-center">
            <button id="mobile-menu-button" class="mr-4 text-gray-600 md:hidden">
              <i class="fas fa-bars"></i>
            </button>
            <h1 class="text-xl font-bold text-gray-800">Division Manager Dashboard | Ahmedabad</h1>
          </div>

          <div class="flex items-center space-x-2 md:space-x-4">
            <!-- Search -->
            <div class="relative hidden md:block">
              <input type="text" placeholder="Search trains, stations..."
                class="w-48 lg:w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
              <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>

            <!-- Notifications -->
            <div class="relative dropdown">
              <button class="p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-bell text-gray-600"></i>
                <span
                  class="absolute top-0 right-0 h-4 w-4 bg-red-500 rounded-full text-xs text-white flex items-center justify-center">5</span>
              </button>
              <div class="dropdown-menu absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg overflow-hidden z-20">
                <div class="p-3 border-b border-gray-200">
                  <h3 class="font-semibold">Alerts & Notifications</h3>
                  <p class="text-xs text-gray-500">You have 5 unread notifications</p>
                </div>
                <div class="max-h-64 overflow-y-auto">
                  <a href="#" class="block p-4 border-b border-gray-200 hover:bg-gray-50">
                    <div class="flex">
                      <div class="rounded-full bg-red-100 p-2 mr-3">
                        <i class="fas fa-exclamation-circle text-red-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Delayed: Shatabdi Express</p>
                        <p class="text-xs text-gray-500">30 minutes ago</p>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="block p-4 border-b border-gray-200 hover:bg-gray-50">
                    <div class="flex">
                      <div class="rounded-full bg-yellow-100 p-2 mr-3">
                        <i class="fas fa-wrench text-yellow-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Track maintenance required at Kanpur</p>
                        <p class="text-xs text-gray-500">2 hours ago</p>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="block p-4 border-b border-gray-200 hover:bg-gray-50">
                    <div class="flex">
                      <div class="rounded-full bg-blue-100 p-2 mr-3">
                        <i class="fas fa-clipboard-list text-blue-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Staff roster updated for Agra Fort</p>
                        <p class="text-xs text-gray-500">5 hours ago</p>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="block p-4 border-b border-gray-200 hover:bg-gray-50">
                    <div class="flex">
                      <div class="rounded-full bg-green-100 p-2 mr-3">
                        <i class="fas fa-check-circle text-green-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Safety inspection completed</p>
                        <p class="text-xs text-gray-500">Yesterday</p>
                      </div>
                    </div>
                  </a>
                  <a href="#" class="block p-4 hover:bg-gray-50">
                    <div class="flex">
                      <div class="rounded-full bg-purple-100 p-2 mr-3">
                        <i class="fas fa-user-plus text-purple-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">New station master assigned</p>
                        <p class="text-xs text-gray-500">2 days ago</p>
                      </div>
                    </div>
                  </a>
                </div>
                <div class="p-3 border-t border-gray-200 bg-gray-50">
                  <a href="#" class="text-sm text-blue-600 hover:text-blue-800 font-medium">View all
                    alerts</a>
                </div>
              </div>
            </div>

            <!-- User Menu -->
            <div class="relative dropdown">
              <button class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center">
                  <span class="font-bold text-white">DM</span>
                </div>
                <span class="hidden md:inline-block font-medium text-sm">Division Manager</span>
                <i class="fas fa-chevron-down text-xs text-gray-500"></i>
              </button>
              <div class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg overflow-hidden z-20">
                <a href="#" class="block p-3 hover:bg-gray-50">
                  <div class="flex items-center">
                    <i class="fas fa-user-circle w-5 mr-2 text-gray-500"></i>
                    <span>Profile</span>
                  </div>
                </a>
                <a href="#" class="block p-3 hover:bg-gray-50">
                  <div class="flex items-center">
                    <i class="fas fa-cog w-5 mr-2 text-gray-500"></i>
                    <span>Settings</span>
                  </div>
                </a>
                <a href="#" class="block p-3 hover:bg-gray-50 border-t border-gray-200">
                  <div class="flex items-center">
                    <i class="fas fa-sign-out-alt w-5 mr-2 text-gray-500"></i>
                    <span>Logout</span>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </header>

      <!-- Dashboard Content -->
      <div class="p-4 md:p-6">
        <!-- Railway Division Header -->
        <div class="railway-header mb-6">
          <h1 class="text-2xl font-bold">Western Railway</h1>
          <p class="text-sm opacity-80 mb-4">Operational overview for Ahmedabad</p>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-4">
            <div class="bg-white bg-opacity-20 p-3 rounded-lg">
              <div class="text-2xl font-bold"><?php echo $ownerCount; ?></div>
              <div class="text-sm opacity-80">Stations</div>
            </div>
            <!-- <div class="bg-white bg-opacity-20 p-3 rounded-lg">
            <div class="text-2xl font-bold"><?php echo $trainsOperational; ?></div>
            <div class="text-sm opacity-80">Reports</div>
          </div> -->
            <!-- <div class="bg-white bg-opacity-20 p-3 rounded-lg">
            <div class="text-2xl font-bold"><?php echo $punctualityRate; ?>%</div>
            <div class="text-sm opacity-80">Overall</div>
          </div> -->
            <!-- <div class="bg-white bg-opacity-20 p-3 rounded-lg">
            <div class="text-2xl font-bold"><?php echo $auditorCount; ?></div>
            <div class="text-sm opacity-80">Total Staff</div>
          </div> -->
          </div>
        </div>





        <div class="mb-8">
          <div class="flex flex-wrap items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Division Overview</h2>
            <div class="flex flex-wrap gap-2">
              <?php
              // Get the current filter from URL parameter, default to "month"
              $timeFilter = isset($_GET['timeFilter']) ? $_GET['timeFilter'] : 'month';

              // Define time period options
              $timePeriods = [
                'today' => 'Today',
                'yesterday' => 'Yesterday',
                'week' => 'Last 7 days',
                'month' => 'This month',
                'last_month' => 'Last month'
              ];
              ?>

              <form method="GET" action="" class="inline">
                <select name="timeFilter" onchange="this.form.submit()"
                  class="px-4 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                  <?php foreach ($timePeriods as $value => $label): ?>
                    <option value="<?php echo $value; ?>" <?php echo ($timeFilter === $value) ? 'selected' : ''; ?>>
                      <?php echo $label; ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </form>
              <!-- <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
              <i class="fas fa-download mr-2"></i>Export Report
            </button> -->
            </div>
          </div>

          <!-- Overview Section -->
          <?php

          // Date range
// Get the time filter from URL parameter, default to "month"
          $timeFilter = isset($_GET['timeFilter']) ? $_GET['timeFilter'] : 'month';

          // Calculate date range based on time filter
          switch ($timeFilter) {
            case 'today':
              $firstDay = date('Y-m-d');
              $lastDay = date('Y-m-d');
              break;
            case 'yesterday':
              $firstDay = date('Y-m-d', strtotime('yesterday'));
              $lastDay = date('Y-m-d', strtotime('yesterday'));
              break;
            case 'week':
              $firstDay = date('Y-m-d', strtotime('-6 days'));
              $lastDay = date('Y-m-d');
              break;
            case 'last_month':
              $firstDay = date('Y-m-01', strtotime('first day of last month'));
              $lastDay = date('Y-m-t', strtotime('last day of last month'));
              break;
            case 'month':
            default:
              $firstDay = date('Y-m-01'); // First day of current month
              $lastDay = date('Y-m-t');   // Last day of current month
              break;
          }

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
    WHERE bas.DivisionId = '$division_id' AND DATE(bas.created_date) BETWEEN '2025-01-01' AND '2025-06-30'
";
          $result = $conn->query($sql);
          $data = $result->fetch_assoc();
          $daily_surprise_visit = $data['total_records'] > 0 ? round(($data['total_score'] / ($data['total_records'] * 10)) * 100, 2) : 0;
          $totalWeight = $data['weightage'] ?? 0;
          $daily_surprise_visit;
          //  $surpriseVisitAmount = calculatealreportAmount($bill['sactioned_amount'], $totalWeight, $firstDay);
          
          //calculate manpower division wise
          
          $manpower_sql_target = "SELECT DISTINCT bt.id, bt.value 
FROM baris_target bt 
JOIN Manpower_Log_Details mld ON bt.subqueId = mld.db_surveySubQuestionId
WHERE mld.DivisionId = $division_id
AND bt.created_date BETWEEN '2025-01-01' AND '2025-01-31'";

          $result = $conn->query($manpower_sql_target);

          $total_values = []; // To accumulate all values
          
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              "ID: " . $row['id'] . " - Value: " . $row['value'] . "<br>";

              // Explode comma-separated values
              $values = explode(',', $row['value']);

              // Trim and convert to integers, then merge into total array
              foreach ($values as $v) {
                $v = trim($v);
                if (is_numeric($v)) {
                  $total_values[] = (float) $v;
                }
              }
            }

            // Now calculate total sum of all values
          
            $total_sum = array_sum($total_values);
            // echo "<br><strong>Total Manpower Sum:</strong> $total_sum<br>";
            // Calculate the howmany day in selected month
          
            // Extract month and year from the first day of the selected time period
            $selectedMonth = date('m', strtotime($firstDay));
            $selectedYear = date('Y', strtotime($firstDay));
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $selectedMonth, $selectedYear);
            $total_manpower_target = $total_sum * $daysInMonth;
          } else {
            echo "No results found.";
          }

          $manpower_sum_sql = "SELECT sum(db_surveyValue) FROM `Manpower_Log_Details` 
WHERE DivisionId ='$division_id'
AND created_date BETWEEN '2025-01-01' AND '2025-01-31';";
          $result = $conn->query($manpower_sum_sql);
          $manpower_sum = $result->fetch_row()[0] ?? 0;

          // Calculate the total manpower amount
          $total_manpower = $manpower_sum / $total_manpower_target * 100; // Assuming the value is in percentage
          


          //calculate chemical /consumbale  target report division wise 
          $calculateChemicalTarget = "SELECT DISTINCT bt.id, bt.value 
 FROM baris_target bt
  JOIN baris_chemical_report mld ON bt.subqueId = mld.db_surveySubQuestionId
   WHERE mld.DivisionId = $division_id
 AND bt.created_date BETWEEN '2025-02-01' AND '2025-02-28'
  ORDER BY `bt`.`value` DESC;";
          $result = $conn->query($calculateChemicalTarget);
          $chemical_total_values = []; // To accumulate all values
          if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
              $chemical_total_values[] = (float) $row['value'];

            }

            // Now calculate total sum of all values
            $chemical_total_sum = array_sum($chemical_total_values);
          } else {
            echo "No results found.";
          }

          // Calculate the total chemical amount
          $chemical_sum_sql = "SELECT sum(db_surveyValue) FROM `baris_chemical_report` 
WHERE DivisionId ='$division_id' 
AND created_date BETWEEN '2025-02-01' AND '2025-02-28'";

          $result = $conn->query($chemical_sum_sql);
          $chemical_sum = $result->fetch_row()[0] ?? 0;

          // Calculate the total chemical amount
          $total_chemical = $chemical_sum / $chemical_total_sum * 100; // Assuming the value is in percentage
          


          ?>
          <!-- Stat Cards this box for cl Cleanliness  Reports-->

          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 md:gap-6">
               <div class="card stat-card">
              <div class="stat-icon bg-red-100 text-red-600">
                <i class="fas fa-shield-alt text-xl"></i>
              </div>
              <div class="mt-2">
                <h3 class="text-3xl font-bold">88%</h3>
                <p class="text-gray-500 text-sm">cleanliness report</p>
              </div>
              <!-- <div class="mt-4 flex items-center text-sm">
              <span class="text-green-500 flex items-center">
          <i class="fas fa-arrow-up mr-1"></i>4
              </span>
              <span class="text-gray-500 ml-2">vs last month</span>
            </div> -->
            </div>
            

            <div class="card stat-card">
              <div class="stat-icon bg-yellow-100 text-yellow-600">
                <i class="fas fa-user-tie text-xl"></i>
              </div>
              <div class="mt-2">
                <h3 class="text-3xl font-bold"><?php echo $daily_surprise_visit; ?>%</h3>
                <p class="text-gray-500 text-sm">Daily surprise visit</p>
              </div>
              <!-- <div class="mt-4 flex items-center text-sm">
              <span class="text-green-500 flex items-center">
          <i class="fas fa-arrow-up mr-1"></i>5
              </span>
              <span class="text-gray-500 ml-2">vs last month</span>
            </div> -->
            </div>
            <div class="card stat-card">
              <div class="stat-icon bg-purple-100 text-purple-600">
                <i class="fas fa-comments text-xl"></i>
              </div>
              <div class="mt-2">
                <h3 class="text-3xl font-bold">89%</h3>
                <p class="text-gray-500 text-sm">Passenger Feedback</p>
              </div>
              <!-- <div class="mt-4 flex items-center text-sm">
              <span class="text-red-500 flex items-center">
          <i class="fas fa-arrow-down mr-1"></i>2
              </span>
              <span class="text-gray-500 ml-2">vs last month</span>
            </div> -->
            </div>
         

            <div class="card stat-card">
              <div class="stat-icon bg-blue-100 text-blue-600">
                <i class="fas fa-users text-xl"></i>
              </div>
              <div class="mt-2">
                <!-- <h3 class="text-3xl font-bold"><?php echo round($total_manpower, 2); ?>%</h3> -->
                 <h3 class="text-3xl font-bold">NA</h3>
                <p class="text-gray-500 text-sm">man power Logs</p>
              </div>
              <!-- <div class="mt-4 flex items-center text-sm">
              <span class="text-red-500 flex items-center">
          <i class="fas fa-arrow-down mr-1"></i>3
              </span>
              <span class="text-gray-500 ml-2">vs last month</span>
            </div> -->
            </div>


            <div class="card stat-card">
              <div class="stat-icon bg-blue-100 text-blue-600">
                <i class="fas fa-cogs text-xl"></i>
              </div>
              <div class="mt-2">
                <h3 class="text-3xl font-bold">NA</h3>
                <p class="text-gray-500 text-sm">Machine Reports</p>
              </div>
              <!-- <div class="mt-4 flex items-center text-sm">
              <span class="text-red-500 flex items-center">
          <i class="fas fa-arrow-down mr-1"></i>3
              </span>
              <span class="text-gray-500 ml-2">vs last month</span>
            </div> -->
            </div>
            <div class="card stat-card">
              <div class="stat-icon bg-green-100 text-green-600">
                <i class="fas fa-broom text-xl"></i>
              </div>
              <div class="mt-2">
                <!-- <h3 class="text-3xl font-bold"><?php echo round($total_chemical, 0); ?>%</h3> -->
                 <h3 class="text-3xl font-bold">NA</h3>
                <p class="text-gray-500 text-sm">Chemical Reports</p>
              </div>
              <!-- <div class="mt-4 flex items-center text-sm">
              <span class="text-green-500 flex items-center">
          <i class="fas fa-arrow-up mr-1"></i>2
              </span>
              <span class="text-gray-500 ml-2">vs last month</span>
            </div> -->
            </div>

          </div>
        </div>

        <!-- Report Operations Section -->
        <div class="mb-8">
          <h2 class="text-xl font-bold text-gray-800 mb-6">Current Month Report Data </h2>

          <div class="card p-4 md:p-5">
            <!-- <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
            <h3 class="font-semibold text-lg">Current Month Performance</h3>
            <div class="flex items-center space-x-4">
              <div class="flex items-center">
                <span class="train-status-dot running"></span>
                <span class="text-sm">On Time (32)</span>
              </div>
              <div class="flex items-center">
                <span class="train-status-dot delayed"></span>
                <span class="text-sm">Delayed (8)</span>
              </div>  
              <div class="flex items-center">
                <span class="train-status-dot cancelled"></span>
                <span class="text-sm">Cancelled (2)</span>
              </div>
            </div>
          </div> -->

            <div class="overflow-x-auto">
              <table class="w-full">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      station name</th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      cleanliness report%</t h>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Daily surprise visit %</th>
                    <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Passenger Feedback %</th>
                   
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">Ahmedabad
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">
                      <span
                        class="<?php echo (85 < 85) ? 'text-red-600' : ((85 >= 85 && 85 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">88%</span>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">
                      <span
                        class="<?php echo (85 < 85) ? 'text-red-600' : ((91 >= 85 && 91 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">81.81%</span>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">
                      <span
                        class="<?php echo (85 < 85) ? 'text-red-600' : ((92 >= 85 && 92 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">89%</span>
                    </td>
                
                  </tr>
                  <!--<tr>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">Vadodara-->
                  <!--  </td>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">-->
                  <!--    <span-->
                  <!--      class="<?php echo (82 < 85) ? 'text-red-600' : ((82 >= 85 && 82 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">82%</span>-->
                  <!--  </td>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">-->
                  <!--    <span-->
                  <!--      class="<?php echo (87 < 85) ? 'text-red-600' : ((87 >= 85 && 87 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">87%</span>-->
                  <!--  </td>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">-->
                  <!--    <span-->
                  <!--      class="<?php echo (90 < 85) ? 'text-red-600' : ((90 >= 85 && 90 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">90%</span>-->
                  <!--  </td>-->
                  
                  <!--</tr>-->
                  <!--<tr>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">Surat</td>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">-->
                  <!--    <span-->
                  <!--      class="<?php echo (80 < 85) ? 'text-red-600' : ((80 >= 85 && 80 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">80%</span>-->
                  <!--  </td>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">-->
                  <!--    <span-->
                  <!--      class="<?php echo (83 < 85) ? 'text-red-600' : ((83 >= 85 && 83 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">83%</span>-->
                  <!--  </td>-->
                  <!--  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">-->
                  <!--    <span-->
                  <!--      class="<?php echo (88 < 85) ? 'text-red-600' : ((88 >= 85 && 88 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">88%</span>-->
                  <!--  </td>-->
                   
                  <!--</tr>-->
                  <!-- <tr>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">Overall
                      Average</td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <span
                        class="<?php echo (82 < 85) ? 'text-red-600' : ((82 >= 85 && 82 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">82%</span>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <span
                        class="<?php echo (87 < 85) ? 'text-red-600' : ((87 >= 85 && 87 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">87%</span>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <span
                        class="<?php echo (90 < 85) ? 'text-red-600' : ((90 >= 85 && 90 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">90%</span>
                    </td>
                    <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <span
                        class="<?php echo (87 < 85) ? 'text-red-600' : ((87 >= 85 && 87 <= 95) ? 'text-yellow-600' : 'text-green-600'); ?>">87%</span>
                    </td>
                  </tr> -->
                </tbody>
              </table>
            </div>

            <!-- <div class="mt-4 text-center">
            <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">
              View all trains <i class="fas fa-arrow-right ml-1"></i>
            </a>
          </div> -->
          </div>
        </div>

        <!-- Station Performance & Analytics -->
        <div class="mb-8">
          <h2 class="text-xl font-bold text-gray-800 mb-6">Performance Analytics & Trends</h2>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-4 md:mb-6">
            <!-- Station Performance Chart -->
            <div class="card p-4 md:p-5">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg">Station Performance Comparison</h3>
                <div class="flex space-x-2">
                  <!-- <button class="text-sm px-3 py-1 rounded-full bg-blue-100 text-blue-600">Overall</button>
                  <button class="text-sm px-3 py-1 rounded-full text-gray-500 hover:bg-gray-100">Daily
                    Visit</button>
                  <button class="text-sm px-3 py-1 rounded-full text-gray-500 hover:bg-gray-100">Machine</button> -->
                </div>
              </div>
              <div style="height: 300px;">
                <canvas id="stationPerformanceChart"></canvas>
              </div>
            </div>

            <!-- Performance Distribution -->
            <div class="card p-4 md:p-5">
              <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-lg">Performance by Category</h3>
                <button class="text-gray-400 hover:text-gray-600">
                  <i class="fas fa-ellipsis-h"></i>
                </button>
              </div>
              <div style="height: 300px;">
                <canvas id="staffDistributionChart"></canvas>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-12">
            <!-- Monthly Trend -->
            <div class="card p-4 md:p-12 md:col-span-12">
              <h3 class="font-semibold text-lg mb-12">Monthly Performance Trend</h3>
              <div style="height: 240px;">
                <canvas id="punctualityChart"></canvas>
              </div>
            </div>

            <!-- Category Comparison -->
            <!-- <div class="card p-4 md:p-5">
        <h3 class="font-semibold text-lg mb-4">Performance by Station</h3>
        <div style="height: 240px;">
          <canvas id="safetyChart"></canvas>
        </div>
          </div> -->

            <!-- Overall Status -->
            <!-- <div class="card p-4 md:p-5">
        <h3 class="font-semibold text-lg mb-4">Performance Rating Range</h3>
        <div style="height: 240px;">
          <canvas id="infrastructureChart"></canvas>
        </div>
        <div class="mt-4 text-xs text-gray-500">
          <div class="flex items-center mb-1">
            <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
            <span>Excellent (95%+)</span>
          </div>
          <div class="flex items-center mb-1">
            <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
            <span>Good (85-95%)</span>
          </div>
          <div class="flex items-center mb-1">
            <span class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></span>
            <span>Fair (75-85%)</span>
          </div>
          <div class="flex items-center">
            <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
            <span>Needs Attention (<75%)</span>
          </div>
        </div>
          </div> -->
          </div>
        </div>

        <!-- Recent Maintenance Activity -->
        <!-- <div class="mb-8">
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-xl font-bold text-gray-800">Recent Maintenance Activity</h2>
          <a href="#" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View all activities</a>
        </div>
        
        <div class="card overflow-hidden">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                  <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Team</th>
                  <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-4 md:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Completion</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="rounded-md bg-blue-100 p-2 mr-3">
                        <i class="fas fa-tools text-blue-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Track Maintenance</p>
                        <p class="text-xs text-gray-500">Routine inspection</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">Agra - Mathura section</td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">P-Way Team A</td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Completed</span>
                  </td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">Yesterday</td>
                </tr>
                <tr>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="rounded-md bg-yellow-100 p-2 mr-3">
                        <i class="fas fa-bolt text-yellow-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Signal Repair</p>
                        <p class="text-xs text-gray-500">Emergency fix</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">Mathura Junction</td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">Signal Team C</td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">In Progress</span>
                  </td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">Today</td>
                </tr>
                <tr>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="rounded-md bg-green-100 p-2 mr-3">
                        <i class="fas fa-building text-green-600"></i>
                      </div>
                      <div>
                        <p class="font-medium text-sm">Station Upgrade</p>
                        <p class="text-xs text-gray-500">Passenger amenities</p>
                      </div>
                    </div>
                  </td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">Kanpur Central</td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm">Works Division</td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap">
                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Scheduled</span>
                  </td>
                  <td class="px-4 md:px-6 py-4 whitespace-nowrap text-sm text-gray-500">Next Week</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div> -->
      </div>
    </div>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    // Set default Chart.js options
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6b7280';
    Chart.defaults.plugins.legend.position = 'bottom';

    // Make charts responsive
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    // Station Performance Chart (Horizontal Bar)
    const stationPerformanceCtx = document.getElementById('stationPerformanceChart').getContext('2d');
    new Chart(stationPerformanceCtx, {
      type: 'bar',
      data: {
        labels: ['Ahmedabad', 'Vadodara', 'Surat'],
        datasets: [{
          label: 'Overall Performance (%)',
          data: [91, 86, 84], // Average of all metrics for each station
          backgroundColor: '#0056b3',
          borderWidth: 0,
          borderRadius: 4,
          maxBarThickness: 40
        }]
      },
      options: {
        indexAxis: 'y',
        scales: {
          x: {
            beginAtZero: true,
            max: 100,
            grid: {
              display: false
            }
          },
          y: {
            grid: {
              display: false
            }
          }
        }
      }
    });

    // Performance by Category Chart (Doughnut)
    const staffDistributionCtx = document.getElementById('staffDistributionChart').getContext('2d');
    new Chart(staffDistributionCtx, {
      type: 'doughnut',
      data: {
        labels: ['cleanliness report', 'Daily surprise visit Reports',  'Passenger Feedback'],
        datasets: [{
          data: [82, 87, 90, 87], // Overall averages from the table
          backgroundColor: [
            '#0056b3',
            '#3b82f6',
            '#1e4620',
            '#ff6b01'
          ],
          borderWidth: 0,
          hoverOffset: 10
        }]
      },
      options: {
        cutout: '65%',
        plugins: {
          legend: {
            position: 'right',
            labels: {
              boxWidth: 12,
              usePointStyle: true,
              pointStyle: 'circle'
            }
          }
        }
      }
    });

    // Monthly Performance Trend Chart (Line)
    const punctualityCtx = document.getElementById('punctualityChart').getContext('2d');
    new Chart(punctualityCtx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
          label: 'Overall Performance (%)',
          data: [84, 86, 88, 85, 87, 88], // Adjusted to match overall performance of 88%
          borderColor: '#0056b3',
          backgroundColor: 'rgba(0, 86, 179, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        scales: {
          y: {
            beginAtZero: false,
            min: 80,
            max: 100,
            ticks: {
              stepSize: 5
            }
          }
        }
      }
    });

    // Performance by Station Chart (Radar)
    const safetyCtx = document.getElementById('safetyChart').getContext('2d');
    new Chart(safetyCtx, {
      type: 'radar',
      data: {
        labels: ['Daily Visit', 'Machine Reports', 'Cleanliness', 'Passenger Feedback'],
        datasets: [{
          label: 'Ahmedabad',
          data: [85, 91, 100, 89], // Ahmedabad data from table
          backgroundColor: 'rgba(0, 86, 179, 0.2)',
          borderColor: '#0056b3',
          borderWidth: 2,
          pointBackgroundColor: '#0056b3'
        }, {
          label: 'Vadodara',
          data: [82, 87, 90, 86], // Vadodara data from table
          backgroundColor: 'rgba(107, 114, 128, 0.2)',
          borderColor: '#6b7280',
          borderWidth: 1,
          pointBackgroundColor: '#6b7280'
        }, {
          label: 'Surat',
          data: [80, 83, 88, 85], // Surat data from table
          backgroundColor: 'rgba(255, 107, 1, 0.2)',
          borderColor: '#ff6b01',
          borderWidth: 1,
          pointBackgroundColor: '#ff6b01'
        }]
      },
      options: {
        scales: {
          r: {
            beginAtZero: true,
            max: 100,
            ticks: {
              display: false,
              stepSize: 20
            }
          }
        }
      }
    });

    // Performance Rating Range Chart (Pie)
    const infrastructureCtx = document.getElementById('infrastructureChart').getContext('2d');
    new Chart(infrastructureCtx, {
      type: 'pie',
      data: {
        labels: ['Excellent', 'Good', 'Fair', 'Needs Attention'],
        datasets: [{
          data: [15, 55, 25, 5], // Adjusted based on the performance metrics
          backgroundColor: [
            '#10b981',
            '#3b82f6',
            '#f59e0b',
            '#ef4444'
          ],
          borderWidth: 0
        }]
      }
    });

    // Mobile menu toggle
    document.addEventListener('DOMContentLoaded', function () {
      const mobileMenuBtn = document.getElementById('mobile-menu-button');

      if (mobileMenuBtn && typeof window.toggleSidebar === 'function') {
        mobileMenuBtn.addEventListener('click', window.toggleSidebar);
      } else if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', function () {
          const sidebar = document.getElementById('sidebar');
          const sidebarOverlay = document.getElementById('sidebar-overlay');

          if (sidebar && sidebarOverlay) {
            sidebar.classList.toggle('-translate-x-full');
            sidebarOverlay.classList.toggle('hidden');
          }
        });
      }
    });
  </script>

</body>

</html>