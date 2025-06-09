<?php
// add division in database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include database connection
    include "../../connection.php";
    // Check if the connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>create division</title>
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
                <h1 class="text-2xl font-semibold text-gray-800"> station List</h1>
                <a href="../user-dashboard/user-list.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Division List
                   
                </a>
            </div>
            <!-- Card -->
            <div class="bg-white shadow-md rounded-lg p-6">
                <!-- table -->
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="py-2 px-4 text-left">station Name</th>
                            <th class="py-2 px-4 text-left">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Include database connection
                        include "../../connection.php";

                        // Fetch divisions from the database
                        $result = $conn->query("SELECT * FROM baris_station");

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td class='py-2 px-4 border-b'>" . htmlspecialchars($row['stationName']) . "</td>";
                                echo "<td class='py-2 px-4 border-b'>";
                                echo "<a href='edit-station.php?stationId=" . $row['stationId'] . "' class='text-blue-500 hover:underline'>Edit</a> | ";
                                echo "<a href='add-weightage.php?stationId=" . $row['stationId'] . "' class='text-red-500 hover:underline'>Add weightage</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='2' class='py-2 px-4 text-center'>No divisions found.</td></tr>";
                        }

                        // Close the connection
                        $conn->close();
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <br class="my-6">
            <div class=" text-sm text-gray-400 mt-6">
                Copyright © 2020 BeatleBuddy. All rights reserved.
            </div>
        </main>
    </div>

</body>
</html>