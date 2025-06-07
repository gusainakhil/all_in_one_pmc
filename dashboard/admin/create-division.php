<?php
// add division in database
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Include database connection
    include "../../connection.php";

    // Get the division name from the form
    $division_name = $_POST['division_name'];

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO baris_division (DivisionName) VALUES (?)");
    $stmt->bind_param("s", $division_name);

    if ($stmt->execute()) {
        // Redirect before any output
        header("Location: create-division.php?success=true");
        exit();
    } else {
        $errorMsg = "Error: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
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
                <h1 class="text-2xl font-semibold text-gray-800">Create Division</h1>
                <a href="../user-dashboard/user-list.php" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Division List
                   
                </a>
            </div>
            <!-- Card -->
            <div class="bg-white rounded-lg shadow p-4">
                <!-- Tabs -->
                <div class="flex border-b">
                    <button class="px-4 py-2 border-b-2 border-blue-500 text-blue-600 font-semibold">create division</button>
                </div>
                <!-- form -->
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-gray-700">Division Name</label>
                            <input type="text" name="division_name" required class="mt-1 block w-full border rounded px-3 py-2" placeholder="Enter Division Name" />
                            <?php
                if (isset($_GET['success']) && $_GET['success'] == 'true') {
                    echo '<div class="mt-4 text-green-600">Division created successfully!</div>';
                }
                if (isset($errorMsg)) {
                    echo '<div class="mt-4 text-red-600">' . htmlspecialchars($errorMsg) . '</div>';
                }
                ?>
                        </div>
                         <!--submit button  -->
                         <div class="col-span-2">
                            <button type="submit" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Create Division</button>
                         </div>
                </form>
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