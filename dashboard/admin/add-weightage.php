<?php
//get stationId
$stationId = isset($_GET['stationId']) ? intval($_GET['stationId']) : 0;
include "../../connection.php";
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['weightage'])) {
    foreach ($_POST['weightage'] as $key => $weight) {
        $queId = intval($_POST['queId'][$key]);
        $subqueId = intval($_POST['subqueId'][$key]);
        $weightage = intval($weight);

        $stmt = $conn->prepare("INSERT INTO baris_report_weight (stationId, subqueId, weightage) VALUES (?, ?, ?)");
        $stmt->bind_param("iii", $stationId, $subqueId, $weightage);
        $stmt->execute();
        $stmt->close();
    }
    $successMessage = "<div class='bg-green-100 text-green-800 p-2 mb-4 rounded'>Weightage saved successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weightage Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php include 'sidebar.php'; ?>
        <main class="flex-1 overflow-y-auto p-6">
            <div class="bg-white rounded-lg shadow p-4 mt-8">
                <h2 class="text-xl font-semibold mb-4">Add weightage</h2>
                <form method="post">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">queId</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">queName</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Report Name</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Weightage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $qResult = $conn->query("
                                    SELECT 
                                        bq.queId, 
                                        bq.queName, 
                                        bq.subqueId, 
                                        bs.stationName,
                                        bsub.subqueId as subqueId,
                                        bsub.subqueName
                                    FROM 
                                        baris_question bq
                                    INNER JOIN 
                                        baris_station bs ON bq.queId  = bs.db_questionsId
                                    INNER JOIN 
                                        baris_subquestion bsub ON FIND_IN_SET(bsub.subqueId, bq.subqueId)
                                    WHERE 
                                        bs.stationId = $stationId
                                    ORDER BY 
                                        bq.queId DESC
                                ");
                                if ($qResult && $qResult->num_rows > 0) {
                                    $i = 0;
                                    while ($row = $qResult->fetch_assoc()) {
                                        echo '<tr>
                                                <td class="px-4 py-2 border">' . htmlspecialchars($row['queId']) . 
                                                    '<input type="hidden" name="queId['.$i.']" value="'.htmlspecialchars($row['queId']).'">
                                                    <input type="hidden" name="subqueId['.$i.']" value="'.htmlspecialchars($row['subqueId']).'">
                                                </td>
                                                <td class="px-4 py-2 border">' . htmlspecialchars($row['queName']) . '</td>
                                                <td class="px-4 py-2 border">' . htmlspecialchars($row['subqueName']) . '</td>
                                                <td class="px-4 py-2 border">
                                                    <input type="text" maxlength="3" name="weightage['.$i.']" value="" placeholder="Enter weightage" class="border rounded px-2 py-1 w-20">
                                                </td>
                                              </tr>';
                                        $i++;
                                    }
                                } else {
                                    echo '<tr><td colspan="4" class="px-4 py-2 border text-center">No data found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <button type="submit" class="mt-4 bg-blue-500 text-white px-4 py-2 rounded">Save Weightage</button>
                    <?php if (isset($successMessage)) echo $successMessage; ?>
                </form>
            </div>
            <br class="my-6">
            <div class=" text-sm text-gray-400 mt-6">
                Copyright © 2020 BeatleBuddy. All rights reserved.
            </div>
        </main>
    </div>
</body>
</html>
