<?php
 require __DIR__ . '\..\include\config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Check if input is email or username
    $is_email = filter_var($username, FILTER_VALIDATE_EMAIL);
    $field = $is_email ? 'email' : 'username';

    // Get user from database using MySQLi
    $stmt = $conn->prepare("SELECT user_id, username, password, role FROM users WHERE $field = ?");
    $stmt->bind_param("s", $username); // "s" indicates string type
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Start session
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Redirect based on role
        header("Location: /warehouse_project/{$user['role']}_fun/{$user['role']}_homepage.php");
        exit();
    } else {
        $error = "Invalid username/email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WMS</title>
    <style>
         
         <?php require __DIR__ . '\styles\LG.css'; ?> 
     
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <a href="/warehouse_project/index.php" class="logo-link">
            <img src="/warehouse_project/img/storage.png" alt="WMS Logo" class="header-logo">
            <span class="logo-text">WMS</span>
        </a>
        <nav>
            <a href="/warehouse_project/index.php">Home</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            <a href="#">Services</a>
        </nav>
        <div class="header-buttons">
            <a href="register.php" class="btn secondary">Sign Up</a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="auth-container">
            <div class="auth-form-container">
                <h2>Welcome Back</h2>
                
                <?php if (isset($error)): ?>
                    <div class="error-message">
                        <p><?php echo htmlspecialchars($error); ?></p>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" action="login.php" method="POST">
                    <div class="form-group">
                        <label for="username">Username or Email</label>
                        <input type="text" id="username" name="username" required autocomplete="off">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required>
                        <div class="forgot-password">
                            <a href="forgot_password.php">Forgot Password?</a>
                        </div>
                    </div>
                    
                    <button type="submit" class="auth-btn">Login</button>
                </form>
                
                <div class="toggle-auth">
                    Don't have an account? <a href="register.php">Register</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>Contact us at <a href="mailto:info@warehouse.com">info@warehouse.com</a> | Follow us on <a href="#">Social Media</a></p>
    </footer>

    <script>
        // Simple form validation
        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();
            
            if (!username || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
            }
        });
    </script>
</body>
</html>