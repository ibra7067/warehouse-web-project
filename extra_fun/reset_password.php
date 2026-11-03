<?php
session_start();
require __DIR__ . '\..\include\config.php'; 

if (!isset($_SESSION['verified_for_reset'])) {
    header("Location: ../forgot_password.php");
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }
    
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $_SESSION['reset_user_id']);
        
        if ($stmt->execute()) {
            session_unset();
            session_destroy();
            $_SESSION['message'] = "Your password has been updated successfully!";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - WMS</title>
    <style>
        
        <?php require __DIR__ . '\styles\RS.css'; ?> 
        
      
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        <a href="index.php" class="logo-link">
            <img src="/warehouse_project/img/storage.png" alt="WMS Logo" class="header-logo">
            <span class="logo-text">WMS</span>
        </a>
        <nav>
            <a href="index.php">Home</a>
            <a href="#">About Us</a>
            <a href="#">Contact Us</a>
            <a href="#">Services</a>
        </nav>
        <div class="header-buttons">
            <a href="login.php" class="btn">Log In</a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="auth-container">
            <div class="auth-form-container">
                <h2>Reset Your Password</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form class="auth-form" action="reset_password.php" method="POST">
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="auth-btn">Reset Password</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Footer and Chatbot code remains the same -->
</body>
</html>