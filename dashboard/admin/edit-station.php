    <?php
    include "../../connection.php";
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stationId = isset($_GET['stationId']) ? intval($_GET['stationId']) : 0;
    $station = null;
    $selectedReports = [];
    $db_questionsId = null;

    // Fetch existing station data
    if ($stationId > 0) {
        $stmt = $conn->prepare("SELECT * FROM baris_station WHERE stationId = ?");
        $stmt->bind_param("i", $stationId);
        $stmt->execute();
        $result = $stmt->get_result();
        $station = $result->fetch_assoc();
        $stmt->close();

        if ($station) {
            $db_questionsId = $station['db_questionsId'];
            // Fetch existing subqueIds from baris_question
            if ($db_questionsId) {
                $q = $conn->prepare("SELECT subqueId FROM baris_question WHERE queId = ?");
                $q->bind_param("i", $db_questionsId);
                $q->execute();
                $q->bind_result($subqueIdsCsv);
                if ($q->fetch()) {
                    $selectedReports = array_map('intval', explode(',', $subqueIdsCsv));
                }
                $q->close();
            }
        }
    }

    // Handle update
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $stationId > 0) {
        $stationName = $_POST['stationName'];
        $station_login_id = $_POST['db_stLoginId'];
        $zoneName = $_POST['zoneName'];
        $divisionId = $_POST['DivisionId'];
        $reportType = isset($_POST['reportType']) ? $_POST['reportType'] : [];

        // Update baris_question if exists, else insert new
        $subqueIdsCsv = implode(',', array_map('intval', $reportType));
        if ($db_questionsId) {
            $stmtQ = $conn->prepare("UPDATE baris_question SET subqueId = ? WHERE queId = ?");
            $stmtQ->bind_param("si", $subqueIdsCsv, $db_questionsId);
            $stmtQ->execute();
            $stmtQ->close();
        } else {
            $stmtQ = $conn->prepare("INSERT INTO baris_question (queName, subqueId) VALUES (?, ?)");
            $queName = "PMC";
            $stmtQ->bind_param("ss", $queName, $subqueIdsCsv);
            if ($stmtQ->execute()) {
                $db_questionsId = $conn->insert_id;
            }
            $stmtQ->close();
        }

        try {
            $stmt = $conn->prepare("UPDATE baris_station SET stationName=?, db_stLoginId=?, zoneName=?, divisionId=?, db_questionsId=? WHERE stationId=?");
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            if (!$stmt->bind_param("ssssii", $stationName, $station_login_id, $zoneName, $divisionId, $db_questionsId, $stationId)) {
                throw new Exception("Bind param failed: " . $stmt->error);
            }

            if ($stmt->execute()) {
                $successMsg = "Station updated successfully";
                // Refresh data
                header("Location: edit-station.php?stationId=$stationId&updated=1");
                exit;
            } else {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            $stmt->close();
        } catch (Exception $e) {
            echo '<div class="mt-4 text-red-500">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        exit;
    }

    // Show success message after redirect
    if (isset($_GET['updated'])) {
        $successMsg = "Station updated successfully";
    }
    ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Station</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    </head>
    <body class="bg-gray-100">
        <div class="flex h-screen overflow-hidden">
            <?php include 'sidebar.php'; ?>
            <main class="flex-1 overflow-y-auto p-6">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold text-gray-800">Edit Station</h1>
                    <a href="../user-dashboard/user-list.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Division List</a>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex border-b">
                        <button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">Edit Station</button>
                    </div>
                    <?php if ($station): ?>
                    <form method="POST" class="space-y-4">
                        <input type="hidden" name="stationId" value="<?php echo htmlspecialchars($stationId); ?>" />
                        <div>
                            <label for="stationName" class="block text-gray-700">Station Name</label>
                            <input type="text" name="stationName" id="stationName" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value="<?php echo htmlspecialchars($station['stationName']); ?>" />
                        </div>
                        <div>
                            <label for="db_stLoginId" class="block text-gray-700">Station Login ID</label>
                            <input type="text" name="db_stLoginId" id="db_stLoginId" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                                value="<?php echo htmlspecialchars($station['db_stLoginId']); ?>" />
                        </div>
                        <div>
                            <label for="zoneName" class="block text-gray-700">Zone Name</label>
                            <select name="zoneName" id="zoneName" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Zone</option>
                                <?php
                                $zones = ["western Zone", "eastern Zone", "northern Zone", "southern Zone"];
                                foreach ($zones as $zone) {
                                    $selected = ($station['zoneName'] == $zone) ? 'selected' : '';
                                    echo "<option value=\"" . htmlspecialchars($zone) . "\" $selected>" . htmlspecialchars($zone) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label for="DivisionId" class="block text-gray-700">Division</label>
                            <select name="DivisionId" id="DivisionId" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <?php
                                $divResult = $conn->query("SELECT DivisionId, divisionName FROM baris_division");
                                if ($divResult && $divResult->num_rows > 0) {
                                    while ($div = $divResult->fetch_assoc()) {
                                        $selected = ($station['divisionId'] == $div['DivisionId']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($div['DivisionId']) . "\" $selected>" . htmlspecialchars($div['divisionName']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700">Select Report</label>
                            <div class="mt-1">
                                <?php
                                $reportType = $conn->query("SELECT subqueName, MIN(subqueId) AS subqueId FROM baris_subquestion GROUP BY subqueName;");
                                if ($reportType && $reportType->num_rows > 0) {
                                    while ($type = $reportType->fetch_assoc()) {
                                        $checked = in_array($type['subqueId'], $selectedReports) ? 'checked' : '';
                                        echo '<label class="inline-flex items-center">
                                                <input type="checkbox" name="reportType[]" value="' . htmlspecialchars($type['subqueId']) . '" class="form-checkbox" ' . $checked . ' />
                                               <span class="ml-2">' . htmlspecialchars($type['subqueName']) . '</span>
                                              </label>&nbsp;&nbsp;&nbsp;&nbsp;';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <?php if (isset($successMsg)): ?>
                            <div class="mt-4 text-green-500"><?php echo $successMsg; ?></div>
                        <?php endif; ?>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update Station</button>
                    </form>
                    <?php else: ?>
                        <div class="text-red-500">Station not found.</div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
        <br class="my-6">
        <div class=" text-sm text-gray-400 mt-6">
            Copyright © 2020 BeatleBuddy. All rights reserved.
        </div>
    </body>
    </html>