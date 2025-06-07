<?php
session_start();

include "../../connection.php";
// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// add user in database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Your POST handling code here
    $db_userLoginName = $_POST['db_userLoginName'];
    $db_username = $_POST['db_username'];
    // Hash the password before storing
    $db_password = password_hash($_POST['db_password'], PASSWORD_DEFAULT);
    $db_phone = $_POST['db_phone'];
    $db_email = $_POST['db_email'];
    $db_usertype = $_POST['db_usertype'];
    $db_designation = $_POST['db_designation'];
    $reportType = $_POST['reportType'];
    $OrgID = $_POST['OrgID'];
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

    $login_token = $_POST['login_token'];

    try {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO baris_userlogin (db_userLoginName, db_username, db_password, db_phone, db_email, db_usertype, db_designation, reportType, OrgID, DivisionId, StationId, db_valid_from, db_valid, paid_amount, gst_amount, total_paid_amount, renewal_amount, renewal_gst_amount, renewal_total_amount, login_token) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param("sssssssissssssssssss", $db_userLoginName, $db_username, $db_password, $db_phone, $db_email, $db_usertype, $db_designation, $reportType, $OrgID, $DivisionId, $StationId, $db_valid_from, $db_valid, $paid_amount, $gst_amount, $total_paid_amount, $renewal_amount, $renewal_gst_amount, $renewal_total_amount, $login_token);

        $stmt->execute();
        echo "<script>alert('New record created successfully');</script>";
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

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
                <h1 class="text-2xl font-semibold text-gray-800">Create user </h1>
                <a href="../user-dashboard/user-list.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Add User</a>
            </div>
            <!-- Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <!-- Tabs -->
                <div class="flex border-b">
                    <button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">create user</button>
                </div>
                <!-- form -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700">User Login Name</label>
                            <input type="text" name="db_userLoginName" id="db_userLoginName" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Username</label>
                            <input type="text" name="db_username" id="db_username" required class="mt-1 block w-full border rounded px-3 py-2" readonly />
                        </div>

                        <div>
                            <label class="block text-gray-700">Password</label>
                            <input type="password" name="db_password" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Phone</label>
                            <input type="text" name="db_phone" maxlength="12" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Email</label>
                            <input type="email" name="db_email" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">User Type</label>
                            <input type="text" name="db_usertype" value="owner" class="mt-1 block w-full border rounded px-3 py-2" readonly />
                        </div>
                        <div>
                            <label class="block text-gray-700">Designation</label>
                            <input type="text" name="db_designation" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Report Type</label>
                            <input type="text" name="reportType" value="PMC" required class="mt-1 block w-full border rounded px-3 py-2" readonly />
                        </div>
                        
                        <div>
                            <label class="block text-gray-700">select Division </label>
                            <select name="DivisionId" required class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">Select Division</option>
                                <?php
                                // Include database connection
                                include "../../connection.php";

                                // Fetch divisions from the database
                                $result = $conn->query("SELECT DivisionId , DivisionName FROM baris_division");

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['DivisionId']) . "'>" . htmlspecialchars($row['DivisionName']) . "</option>";
                                    }
                                }

                                // Close the connection
                                $conn->close();
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700">Select Station</label>
                            <select name="StationId" id="StationName" required class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">Select Station</option>
                                <?php
                                // Include database connection
                                include "../../connection.php";

                                // Fetch stations from the database
                                $result = $conn->query("SELECT stationId  , stationName FROM baris_station");

                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['stationId']) . "'>" . htmlspecialchars($row['stationName']) . "</option>";
                                    }
                                }

                                // Close the connection
                                $conn->close();
                                ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-gray-700">Organization name</label>
                            <select name="OrgID" required class="mt-1 block w-full border rounded px-3 py-2">
                                <option value="">Select Organization</option>
                                <?php
                                // Include database connection
                                include "../../connection.php";

                                // Fetch organizations from the database
                                $result = $conn->query("SELECT db_Orgname , OrgID FROM baris_organization");

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . htmlspecialchars($row['OrgID']) . "'>" . htmlspecialchars($row['db_Orgname']) . "</option>";
                                    }
                                }

                                // Close the connection
                                $conn->close();
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-gray-700">Valid From</label>
                            <input type="datetime-local" name="db_valid_from" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Valid To</label>
                            <input type="datetime-local" name="db_valid" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Paid Amount</label>
                            <input type="number" step="0.01" name="paid_amount" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">GST Amount</label>
                            <input type="number" step="0.01" name="gst_amount" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Total Paid Amount</label>
                            <input type="number" step="0.01" name="total_paid_amount" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Renewal Amount</label>
                            <input type="number" step="0.01" name="renewal_amount" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Renewal GST Amount</label>
                            <input type="number" step="0.01" name="renewal_gst_amount" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <div>
                            <label class="block text-gray-700">Renewal Total Amount</label>
                            <input type="number" step="0.01" name="renewal_total_amount" required class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        
                        <div>
                            <input type="hidden" name="login_token" id="login_token" maxlength="64" class="mt-1 block w-full border rounded px-3 py-2" />
                        </div>
                        <script>
                            // Generate a random 32-character token
                            function generateToken(length = 32) {
                                const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
                                let token = '';
                                for (let i = 0; i < length; i++) {
                                    token += chars.charAt(Math.floor(Math.random() * chars.length));
                                }
                                return token;
                            }
                            document.addEventListener('DOMContentLoaded', function() {
                                document.getElementById('login_token').value = generateToken(32);
                            });
                        </script>
                    </div>
                    <div class="mt-4">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create User</button>
                    </div>

                </form>


                <!-- Footer -->
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