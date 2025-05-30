<?php
include "../../connection.php";
if (!isset($_GET['token'])) {
    die("Token missing.");
}

$token = $conn->real_escape_string($_GET['token']);
$sql = "SELECT * FROM baris_userlogin WHERE login_token = '$token'";
$result = $conn->query($sql);

if ($result->num_rows !== 1) {
    die("Invalid or expired token.");
}
else{
     $_SESSION['userId'] = $row['userId'];
          
            $_SESSION['db_usertype'] = $row['db_usertype'];  
              $_SESSION['OrgID'] = $row['OrgID']; 
}

$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
  <title><?php echo htmlspecialchars($user['db_userLoginName']); ?>'s Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-6">
  <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">Welcome, <?php echo htmlspecialchars($user['db_username']); ?></h2>
    <p><strong>Login Name:</strong> <?php echo htmlspecialchars($user['db_userLoginName']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['db_phone']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['db_email']); ?></p>
    <p><strong>Organization:</strong> <?php echo htmlspecialchars($user['reportType']); ?></p>
    <p><strong>Division:</strong> <?php echo htmlspecialchars($user['DivisionId']); ?></p>
    <p><strong>Station:</strong> <?php echo htmlspecialchars($user['StationId']); ?></p>
    

    <div class="mt-6">
      <a href="dashboard.php" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">Back to User List</a>
    </div>
  </div>
</body>
</html>

<?php $conn->close(); ?>
