<?php

ob_start();                    
session_start();               
error_reporting(E_ALL);        
ini_set('display_errors', 1);  

include "connection.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    $query = "SELECT * FROM baris_userlogin 
              WHERE db_userLoginName = '$username' 
              AND db_usertype = 'SU_admin' 
              LIMIT 1";

    $result = mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) === 1) {
        $user = mysqli_fetch_assoc($result);

        if (password_verify($password, $user['db_password'])) {
            $_SESSION['user_id'] = $user['userId'];
            $_SESSION['username'] = $user['db_userLoginName'];
            $_SESSION['usertype'] = $user['db_usertype'];
            
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Division Manager Portal | Indian Railways</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <style>
    :root {
      --primary: #0056b3;
      --secondary: #004080;
      --accent: #ff6b01;
      --light: #f8f9fa;
      --dark: #343a40;
      --success: #28a745;
      --danger: #dc3545;
      --warning: #ffc107;
      --info: #17a2b8;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background-color: #f0f2f5;
      height: 100vh;
    }
    
    .container {
      display: flex;
      height: 100%;
    }
    
    .left-panel {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: white;
      padding: 20px;
    }
    
    .right-panel {
      flex: 1.2;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      color: white;
      position: relative;
      overflow: hidden;
    }
    
    .right-panel::before {
      content: '';
      position: absolute;
      width: 200%;
      height: 200%;
      background-image: url('assets/image/railway_pattern.png');
      opacity: 0.1;
      background-size: 400px;
      transform: rotate(-15deg);
    }
    
    .login-form {
      width: 100%;
      max-width: 400px;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
    }
    
    .login-form h2 {
      margin-bottom: 30px;
      font-weight: 600;
      color: var(--dark);
      text-align: center;
    }
    
    .highlight {
      color: var(--primary);
      font-weight: 700;
    }
    
    .input-group {
      margin-bottom: 20px;
    }
    
    .input-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--dark);
    }
    
    .input-group input {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid #e1e1e1;
      border-radius: 4px;
      font-size: 15px;
      transition: all 0.3s;
    }
    
    .input-group input:focus {
      border-color: var(--primary);
      box-shadow: 0 0 0 2px rgba(0, 86, 179, 0.2);
      outline: none;
    }
    
    .toggle-password {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
      font-size: 14px;
    }
    
    .toggle-password input {
      margin-right: 8px;
    }
    
    .btn {
      width: 100%;
      padding: 12px;
      background-color: var(--primary);
      color: white;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 500;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .btn:hover {
      background-color: var(--secondary);
    }
    
    .extras {
      display: flex;
      justify-content: space-between;
      margin: 20px 0;
      font-size: 14px;
      color: #666;
    }
    
    .extras a {
      color: var(--primary);
      text-decoration: none;
    }
    
    .copyright {
      text-align: center;
      margin-top: 30px;
      font-size: 12px;
      color: #999;
    }
    
    .railway-info {
      max-width: 500px;
      z-index: 1;
      text-align: center;
      padding: 0 30px;
    }
    
    .railway-info h1 {
      font-size: 32px;
      margin-bottom: 15px;
      font-weight: 600;
    }
    
    .railway-info p {
      font-size: 16px;
      margin-bottom: 25px;
      line-height: 1.6;
    }
    
    .railway-logo {
      width: 150px;
      margin-bottom: 30px;
    }
    
    .features {
      display: flex;
      flex-wrap: wrap;
      justify-content: center;
      margin-top: 20px;
    }
    
    .feature {
      display: flex;
      align-items: center;
      margin: 10px 20px;
    }
    
    .feature i {
      margin-right: 10px;
      font-size: 18px;
      color: var(--accent);
    }
    
    .error {
      background-color: #ffecec;
      color: var(--danger);
      padding: 10px 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 14px;
      border-left: 4px solid var(--danger);
    }
    
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }
      
      .left-panel, .right-panel {
        flex: none;
      }
      
      .right-panel {
        padding: 30px 0;
      }
      
      .login-form {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="left-panel">
      <form class="login-form" method="POST">
        <h2>Division Manager <span class="highlight">Portal</span></h2>
        <?php if (!empty($error)): ?>
          <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>
        <div class="input-group">
          <label>Username</label>
          <input type="text" name="username" required />
        </div>
        <div class="input-group">
          <label>Password</label>
          <input type="password" name="password" id="password" required />
        </div>
        <div class="toggle-password">
          <input type="checkbox" id="showPassword" onclick="togglePassword()" />
          <label for="showPassword">Show Password</label>
        </div>
        <button type="submit" name="login" class="btn">Log In</button>
        <div class="extras">
          <label><input type="checkbox" /> Keep me logged in</label>
          <a href="#">Forgot Password?</a>
        </div>
        <p class="copyright">© 2025 Indian Railways. All rights reserved.</p>
      </form>
    </div>
    <div class="right-panel">
      <img src="assets/image/tarin_logo.png" alt="Indian Railways Logo" class="railway-logo" />
      <div class="railway-info">
        <h1>Division Manager Administration</h1>
        <p>Access the control center to manage railway operations, staff, schedules and maintenance across your division.</p>
        <div class="features">
          <div class="feature">
            <i class="fas fa-chart-line"></i>
            <span>Performance Analytics</span>
          </div>
          <div class="feature">
            <i class="fas fa-users"></i>
            <span>Staff Management</span>
          </div>
          <div class="feature">
            <i class="fas fa-train"></i>
            <span>Train Operations</span>
          </div>
          <div class="feature">
            <i class="fas fa-tools"></i>
            <span>Maintenance</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    function togglePassword() {
      const passwordInput = document.getElementById('password');
      passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
    }
  </script>
</body>
</html>