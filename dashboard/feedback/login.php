<?php
session_start(); // Start a session
include 'connection.php';
// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the input data from the form
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Prepare and execute query to fetch user by username
    $stmt = $conn->prepare("SELECT id, username, password_hash, station_id FROM users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $userData = $result->fetch_assoc();

        // Verify the hashed password
        if (password_verify($pass, $userData['password_hash'])) {
            // Start session and store user data
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['station_id'] = $userData['station_id'];

            // Redirect to index.php
            header("Location: index.php");
            exit();
        } else {
            // Invalid password
            $error = "Invalid username or password";
        }
    } else {
        // User not found
        $error = "Invalid username or password";
    }

    // Close statement and connection
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Page</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #0066cc, #3399ff);
            font-family: Arial, sans-serif;
        }

        .login-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-form {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h3 {
            color: #333;
        }

        .form-label {
            font-weight: 600;
        }

        .btn-primary {
            background-color: #0066cc;
            border-color: #0066cc;
        }

        .btn-primary:hover {
            background-color: #005bb5;
            border-color: #005bb5;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .forgot-password {
            text-decoration: none;
            color: #0066cc;
        }

        .forgot-password:hover {
            color: #005bb5;
        }

        .text-center a {
            color: #0066cc;
        }

        .text-center a:hover {
            color: #005bb5;
        }
    </style>
</head>

<body>

    <div class="login-container">
        <div class="login-form">
            <div class="login-header">
                <h3>Login</h3>
                <p class="text-muted">Please enter your credentials to continue</p>
            </div>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter username"
                        required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password"
                        placeholder="Enter password" required>
                </div>
                <?php if (isset($error)) {
                    echo "<div class='alert alert-danger'>$error</div>";
                } ?>
                <div class="d-flex justify-content-between mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                    </div>
                    <a href="#" class="text-muted forgot-password">Forgot password?</a>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
            <div class="text-center mt-3">
                <p class="text-muted">Don't have an account? <a href="#">Sign up</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>