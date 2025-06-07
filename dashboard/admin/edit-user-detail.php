<?php
session_start();
include "../../connection.php";

// Get login_token from GET or POST
$login_token = $_GET['token'] ?? $_POST['login_token'] ?? '';

if (!$login_token) {
    die("No user token provided.");
}

// Fetch user data for pre-filling the form
$user = [];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $stmt = $conn->prepare("SELECT * FROM baris_userlogin WHERE login_token = ?");
    $stmt->bind_param("s", $login_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    if (!$user) {
        die("User not found.");
    }
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db_userLoginName = $_POST['db_userLoginName'];
    $db_username = $_POST['db_username'];
    $db_phone = $_POST['db_phone'];
    $db_email = $_POST['db_email'];
    $db_usertype = $_POST['db_usertype'];
    $db_designation = $_POST['db_designation'];
    $reportType = $_POST['reportType'];
  
    $DivisionId = $_POST['DivisionId'];
    $StationId = $_POST['StationId'];
    $db_valid_from = $_POST['db_valid_from'];
    $db_valid = $_POST['db_valid'];
    $paid_amount = $_POST['paid_amount'];
    $gst_amount = $_POST['gst_amount'];
    $total_paid_amount = $_POST['total_paid_amount'];
    $renewal_amount = $_POST['renewal_amount'];
    $renewal_gst_amount = $_POST['renewal_gst_amount'];
    $renewal_total_amount = $_POST['renewal_total_amount'];

    // Only update password if provided
    $update_password = !empty($_POST['db_password']);
    if ($update_password) {
        $db_password = password_hash($_POST['db_password'], PASSWORD_DEFAULT);
        $sql = "UPDATE baris_userlogin SET db_userLoginName=?, db_username=?, db_password=?, db_phone=?, db_email=?, db_usertype=?, db_designation=?, reportType=?,  DivisionId=?, StationId=?, db_valid_from=?, db_valid=?, paid_amount=?, gst_amount=?, total_paid_amount=?, renewal_amount=?, renewal_gst_amount=?, renewal_total_amount=? WHERE login_token=?";
    } else {
        $sql = "UPDATE baris_userlogin SET db_userLoginName=?, db_username=?, db_phone=?, db_email=?, db_usertype=?, db_designation=?, reportType=?,  DivisionId=?, StationId=?, db_valid_from=?, db_valid=?, paid_amount=?, gst_amount=?, total_paid_amount=?, renewal_amount=?, renewal_gst_amount=?, renewal_total_amount=? WHERE login_token=?";
    }

    try {
        if ($update_password) {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "sssssssssssssssssss",
                $db_userLoginName, $db_username, $db_password, $db_phone, $db_email, $db_usertype, $db_designation, $reportType, $DivisionId, $StationId, $db_valid_from, $db_valid, $paid_amount, $gst_amount, $total_paid_amount, $renewal_amount, $renewal_gst_amount, $renewal_total_amount, $login_token
            );
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                "ssssssssssssssssss",
                $db_userLoginName, $db_username, $db_phone, $db_email, $db_usertype, $db_designation, $reportType, $DivisionId, $StationId, $db_valid_from, $db_valid, $paid_amount, $gst_amount, $total_paid_amount, $renewal_amount, $renewal_gst_amount, $renewal_total_amount, $login_token
            );
        }
        $stmt->execute();
        echo "<script>alert('User updated successfully');</script>";
        // Optionally redirect
        // header("Location: user-list.php");
        // exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
    // Refresh user data
    $stmt = $conn->prepare("SELECT * FROM baris_userlogin WHERE login_token = ?");
    $stmt->bind_param("s", $login_token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gray-100">
    <div class="flex h-screen overflow-hidden">
        <?php include 'sidebar.php'; ?>
        <main class="flex-1 overflow-y-auto p-6">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-semibold text-gray-800">Edit User</h1>
                <a href="../user-dashboard/user-list.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Back to User List</a>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <form action="" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700">User Login Name</label>
                            <input type="text" name="db_userLoginName" id="db_userLoginName" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['db_userLoginName'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Username</label>
                            <input type="text" name="db_username" id="db_username" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['db_username'] ?? ''); ?>" readonly />
                        </div>
                        <div>
                            <label class="block text-gray-700">Password (leave blank to keep unchanged)</label>
                            <input type="password" name="db_password" class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Phone</label>
                            <input type="text" name="db_phone" maxlength="12" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['db_phone'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Email</label>
                            <input type="email" name="db_email" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['db_email'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">User Type</label>
                            <input type="text" name="db_usertype" value="<?php echo htmlspecialchars($user['db_usertype'] ?? 'owner'); ?>" class="mt-1 block w-full border rounded px-3 py-2" readonly />
                        </div>
                        <div>
                            <label class="block text-gray-700">Designation</label>
                            <input type="text" name="db_designation" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['db_designation'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Report Type</label>
                            <input type="text" name="reportType" value="<?php echo htmlspecialchars($user['reportType'] ?? 'PMC'); ?>" required class="mt-1 block w-full border rounded px-3 py-2" readonly />
                        </div>
                     
                        <div>
                            <label class="block text-gray-700">Select Division</label>
                            <select name="DivisionId" required class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">Select Division</option>
                                <?php
                                include "../../connection.php";
                                $result = $conn->query("SELECT DivisionId, DivisionName FROM baris_division");
                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($user['DivisionId'] ?? '') == $row['DivisionId'] ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($row['DivisionId']) . "' $selected>" . htmlspecialchars($row['DivisionName']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700">Select Station</label>
                            <select name="StationId" id="StationName" required class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">Select Station</option>
                                <?php
                                include "../../connection.php";
                                $result = $conn->query("SELECT stationId, stationName FROM baris_station");
                                while ($row = $result->fetch_assoc()) {
                                    $selected = ($user['StationId'] ?? '') == $row['stationId'] ? 'selected' : '';
                                    echo "<option value='" . htmlspecialchars($row['stationId']) . "' $selected>" . htmlspecialchars($row['stationName']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700">Valid From</label>
                            <input type="datetime-local" name="db_valid_from" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo isset($user['db_valid_from']) ? date('Y-m-d\TH:i', strtotime($user['db_valid_from'])) : ''; ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Valid To</label>
                            <input type="datetime-local" name="db_valid" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo isset($user['db_valid']) ? date('Y-m-d\TH:i', strtotime($user['db_valid'])) : ''; ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['paid_amount'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">GST Amount</label>
                            <input type="number" step="0.01" name="gst_amount" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['gst_amount'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Total Paid Amount</label>
                            <input type="number" step="0.01" name="total_paid_amount" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['total_paid_amount'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Renewal Amount</label>
                            <input type="number" step="0.01" name="renewal_amount" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['renewal_amount'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Renewal GST Amount</label>
                            <input type="number" step="0.01" name="renewal_gst_amount" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['renewal_gst_amount'] ?? ''); ?>" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Renewal Total Amount</label>
                            <input type="number" step="0.01" name="renewal_total_amount" required class="mt-1 block w-full border rounded px-3 py-2" value="<?php echo htmlspecialchars($user['renewal_total_amount'] ?? ''); ?>" />
                        </div>
                        <input type="hidden" name="login_token" value="<?php echo htmlspecialchars($login_token); ?>" />
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update User</button>
                    </div>
                </form>
                <div class="text-center text-sm text-gray-400 mt-6">
                    Copyright © 2020 BeatleBuddy. All rights reserved.
                </div>
        </main>
    </div>  
    <script>
        document.getElementById('db_userLoginName').addEventListener('input', function() {
            let val = this.value.replace(/\s+/g, '');
            let rand = Math.floor(100 + Math.random() * 900);
            document.getElementById('db_username').value = val ? (val + rand) : '';
        });
    </script>
</body>
</html>
