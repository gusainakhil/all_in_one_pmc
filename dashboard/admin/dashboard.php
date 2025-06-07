<?php
session_start();

include "../../connection.php";

$sql = "SELECT userId, db_userLoginName, db_username, db_phone, db_email, reportType, OrgID, DivisionId, StationId ,login_token FROM baris_userlogin where db_usertype= 'owner'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User List</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100">

<div class="flex h-screen overflow-hidden">
  <!-- Sidebar -->
   <?php include 'sidebar.php'; ?>




  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto p-6">
    <div class="flex items-center justify-between mb-6">
      <h1 class="text-2xl font-semibold text-gray-800">station cleaning </h1>
      <a href="../user-dashboard/index.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add User</a>
    </div>

    <!-- Card -->
    <div class="bg-white rounded-lg shadow p-4">

      <!-- Tabs -->
      <div class="flex border-b">
        <button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">Owner</button>
        <!-- <button class="px-4 py-2 text-gray-500 hover:text-blue-500">Auditor</button> -->
      </div>

   

      <!--   create empty boxes for reports  -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-blue-100 rounded-lg p-4">
          <h3 class="text-lg font-semibold mb-2">User Types</h3>
          <canvas id="chart1"></canvas>
        </div>
        <div class="bg-green-100 rounded-lg p-4">
          <h3 class="text-lg font-semibold mb-2">Login Activity</h3>
          <canvas id="chart2"></canvas>
        </div>
        <div class="bg-yellow-100 rounded-lg p-4">
          <h3 class="text-lg font-semibold mb-2">Reports Overview</h3>
          <canvas id="chart3"></canvas>
        </div>
      </div>

      <!-- Chart.js CDN -->
      <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
      <script>
        // Chart 1: Pie
        new Chart(document.getElementById('chart1'), {
          type: 'pie',
          data: {
            labels: ['Owner', 'Auditor', 'Admin'],
            datasets: [{
              data: [10, 5, 2],
              backgroundColor: ['#3b82f6', '#6366f1', '#a5b4fc']
            }]
          },
          options: { responsive: true }
        });

        // Chart 2: Bar
        new Chart(document.getElementById('chart2'), {
          type: 'bar',
          data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            datasets: [{
              label: 'Logins',
              data: [12, 19, 7, 11, 14],
              backgroundColor: '#22c55e'
            }]
          },
          options: { responsive: true }
        });

        // Chart 3: Line
        new Chart(document.getElementById('chart3'), {
          type: 'line',
          data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
            datasets: [{
              label: 'Reports',
              data: [3, 7, 4, 8, 6],
              borderColor: '#facc15',
              backgroundColor: 'rgba(250, 204, 21, 0.2)',
              fill: true,
              tension: 0.4
            }]
          },
          options: { responsive: true }
        });
      </script>
      <!-- create  3 more javascript  of boxes -->
       




      

    <!-- Footer -->
    <div class="text-center text-sm text-gray-400 mt-6">
      Copyright © 2020 BeatleBuddy. All rights reserved.
    </div>
  </main>
</div>

</body>
</html>

<?php $conn->close(); ?>
