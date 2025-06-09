<?php
// Always include database connection
include "../../connection.php";
// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// add division in database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Your POST handling code here
    $stationName = $_POST['stationName'];
    $station_login_id = $_POST['db_stLoginId'];
    $zoneName = $_POST['zoneName'];
    $divisionId = $_POST['DivisionId'];
    $reportType = isset($_POST['reportType']) ? $_POST['reportType'] : [];

    // Insert into baris_question for each selected reportType
    // Prepare a comma-separated list of selected subqueIds for subqueId column
    $subqueIdsCsv = implode(',', array_map('intval', $reportType));

    $questionIds = [];
    if (!empty($subqueIdsCsv)) {
        $stmtQ = $conn->prepare("INSERT INTO baris_question (queName, subqueId) VALUES (?, ?)");
        if ($stmtQ) {
            $queName = "PMC";
            $stmtQ->bind_param("ss", $queName, $subqueIdsCsv);
            if ($stmtQ->execute()) {
                $questionIds[] = $conn->insert_id;
            }
            $stmtQ->close();
        }
    }
    // If at least one question inserted, use the first queId for db_questionsId
    $db_questionsId = !empty($questionIds) ? $questionIds[0] : null;
    

    try {
        $stmt = $conn->prepare("INSERT INTO baris_station (stationName, db_stLoginId, zoneName, divisionId , db_questionsId) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }

        if (!$stmt->bind_param("ssssi", $stationName, $station_login_id, $zoneName, $divisionId, $db_questionsId)) {
            throw new Exception("Bind param failed: " . $stmt->error);
        }

        if ($stmt->execute()) {
            $successMsg = "New station created successfully";
        } else {
            throw new Exception("Execute failed: " . $stmt->error);
        }

        $stmt->close();
    } catch (Exception $e) {
        echo '<div class="mt-4 text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>create station </title>
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
                <h1 class="text-2xl font-semibold text-gray-800">Create station</h1>
                <a href="../user-dashboard/user-list.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Division List

                </a>
            </div>
            <!-- Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <!-- Tabs -->
                <div class="flex border-b">
                    <button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">create station</button>
                </div>
                <!-- form -->
                <form method="POST" class="space-y-4">
                    <div>
                        <label for="stationName" class="block text-gray-700">Station Name</label>
                        <input type="text" name="stationName" id="stationName" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>
                    <div>
                        <label for="db_stLoginId" class="block text-gray-700">Station Login ID</label>
                        <input type="text" name="db_stLoginId" id="db_stLoginId" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" />
                    </div>

                    <div>
                        <label for="zoneName" class="block text-gray-700">Zone Name</label>
                        <select name="zoneName" id="zoneName" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <option value="">Select Zone</option>
                            <option value="western Zone">western Zone</option>
                            <option value="eastern Zone">eastern Zone</option>
                            <option value="northern Zone">northern Zone</option>
                            <option value="southern Zone">southern Zone</option>
                        </select>
                    </div>
            
            <div>
                <label for="DivisionId" class="block text-gray-700">Division</label>
                <select name="DivisionId" id="DivisionId" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                    <?php
                    // Fetch divisions for select
                    $divResult = $conn->query("SELECT DivisionId, divisionName FROM baris_division");
                    if ($divResult && $divResult->num_rows > 0) {
                        while ($div = $divResult->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($div['DivisionId']) . '">' . htmlspecialchars($div['divisionName']) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>

            <!-- generate check box for station type -->
            <div>
                <label class="block text-gray-700">Select Report</label>
                <div class="mt-1">
                    <?php
                    //fetch station types from database
                    $reportType = $conn->query("SELECT subqueName, MIN(subqueId) AS subqueId FROM baris_subquestion GROUP BY subqueName;");
                    if ($reportType && $reportType->num_rows > 0) {
                        while ($type = $reportType->fetch_assoc()) {
                            echo '<label class="inline-flex items-center">
                                    <input type="checkbox" name="reportType[]" value="' . htmlspecialchars($type['subqueId']) . '" class="form-checkbox" />
                                   <span class="ml-2">' . htmlspecialchars($type['subqueName']) . '</span>
                                  </label>&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- print success message -->
            <?php if (isset($successMsg)): ?>
                <div class="mt-4 text-green-500"><?php echo $successMsg; ?></div>
            <?php endif; ?>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Station</button>
            </form>

         </div>   

    </div>
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