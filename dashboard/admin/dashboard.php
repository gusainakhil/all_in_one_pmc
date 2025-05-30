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
  <aside class="w-64 bg-white shadow-lg hidden md:block">
    <div class="p-4 text-lg font-semibold border-b">Dashboard</div>
    <nav class="p-4 space-y-2">
      <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-home w-5"></i> Dashboard</a>
      <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-user w-5"></i> User</a>
      <a href="#" class="flex items-center gap-2 text-gray-700 hover:text-blue-600"><i class="fas fa-layer-group w-5"></i> Type</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 overflow-y-auto p-6">

    <!-- Card -->
    <div class="bg-white rounded-lg shadow p-4">

      <!-- Tabs -->
      <div class="flex border-b">
        <button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">Owner</button>
        <button class="px-4 py-2 text-gray-500 hover:text-blue-500">Auditor</button>
      </div>

      <!-- Search -->
      <div class="flex justify-between items-center my-4">
        <h2 class="text-lg font-semibold">User List</h2>
        <input type="text" placeholder="Search..." class="border px-3 py-2 rounded shadow-sm w-64">
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm text-left border">
          <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
            <tr>
              <th class="px-4 py-2">SR.NO.</th>
              <th class="px-4 py-2">Name</th>
              <th class="px-4 py-2">Username</th>
              <th class="px-4 py-2">Mobile</th>
              <th class="px-4 py-2">Email</th>
              <th class="px-4 py-2">Organization</th>
              <th class="px-4 py-2">Division</th>
              <th class="px-4 py-2">Station</th>
              <th class="px-4 py-2">User ID</th>
              <th class="px-4 py-2">Action</th>
            </tr>
          </thead>
          <tbody class="text-gray-700">
            <?php
            $sr = 1;
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr class='border-t hover:bg-gray-50'>";
                    echo "<td class='px-4 py-2'>{$sr}</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($row['db_userLoginName']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($row['db_username']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($row['db_phone']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($row['db_email']) . "</td>";
                    echo "<td class='px-4 py-2'>" . htmlspecialchars($row['reportType']) . "</td>";
                    echo "<td class='px-4 py-2'>Division_" . htmlspecialchars($row['DivisionId']) . "</td>";
                    echo "<td class='px-4 py-2'>Station_" . htmlspecialchars($row['StationId']) . "</td>";
                    echo "<td class='px-4 py-2'>STA_" . htmlspecialchars($row['userId']) . "</td>";
                    echo "<td class='px-4 py-2'>
                            <a href='../user-dashboard/index.php?token=".$row['login_token'] . "' class='bg-blue-500 text-white text-sm px-4 py-1 rounded hover:bg-blue-600' target='blank'>Login</a>

                          </td>";
                    echo "</tr>";
                    $sr++;
                }
            } else {
                echo "<tr><td colspan='10' class='px-4 py-2 text-center text-gray-500'>No users found</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>

    </div>

    <!-- Footer -->
    <div class="text-center text-sm text-gray-400 mt-6">
      Copyright © 2020 BeatleBuddy. All rights reserved.
    </div>
  </main>
</div>

</body>
</html>

<?php $conn->close(); ?>
