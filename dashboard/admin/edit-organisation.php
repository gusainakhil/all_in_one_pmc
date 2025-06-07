<?php
// Include database connection
include "../../connection.php";

// Initialize variables
$orgName = "";
$orgId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";

// Fetch organization data if ID is set
if ($orgId > 0) {
    $stmt = $conn->prepare("SELECT * FROM baris_organization WHERE OrgID = ?");
    $stmt->bind_param("i", $orgId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $orgName = $row['db_Orgname'];
    } else {
        $message = "Organization not found.";
    }
    $stmt->close();
} else {
    $message = "Invalid organization ID.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $orgId > 0) {
    $orgName = trim($_POST['org_name']);
    if ($orgName != "") {
        $stmt = $conn->prepare("UPDATE baris_organization SET db_Orgname = ? WHERE OrgID = ?");
        $stmt->bind_param("si", $orgName, $orgId);
        if ($stmt->execute()) {
            $message = "Organization updated successfully.";
        } else {
            $message = "Error updating organization.";
        }
        $stmt->close();
    } else {
        $message = "Organization name cannot be empty.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Organization</title>
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
                <h1 class="text-2xl font-semibold text-gray-800">Edit Organization</h1>
                <a href="edit-organisation.php" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">
                    Back to List
                </a>
            </div>
            <div class="bg-white shadow-md rounded-lg p-6 max-w-md mx-auto">
                <?php if ($message): ?>
                    <div class="mb-4 text-center text-red-500"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                <?php if ($orgId > 0 && $orgName !== ""): ?>
                <form method="POST">
                    <div class="mb-4">
                        <label class="block text-gray-700 mb-2">Organization Name</label>
                        <input type="text" name="org_name" value="<?php echo htmlspecialchars($orgName); ?>" class="w-full px-3 py-2 border rounded" required>
                    </div>
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Update</button>
                </form>
                <?php endif; ?>
            </div>
            <div class="text-sm text-gray-400 mt-6">
                Copyright © 2020 BeatleBuddy. All rights reserved.
            </div>
        </main>
    </div>

</body>
</html>
