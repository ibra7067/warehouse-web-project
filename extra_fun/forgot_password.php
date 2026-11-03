<?php
session_start();
require __DIR__ . '\..\include\config.php';

$errors = [];
$email = '';
$question = '';
$show_question = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['security_answer'])) {
        // First step - submit email
        $email = trim($_POST['email']);
        
        $stmt = $conn->prepare("SELECT user_id, security_question FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['reset_user_id'] = $user['user_id'];
            $question = $user['security_question'];
            $show_question = true;
        } else {
            // Don't reveal if email doesn't exist
            $errors[] = "If this email exists, you'll be able to reset your password.";
        }
    } elseif (isset($_POST['security_answer'])) {
        // Second step - verify answer
        $answer = strtolower(trim($_POST['security_answer']));
        
        $stmt = $conn->prepare("SELECT security_answer FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $_SESSION['reset_user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        
        if (password_verify($answer, $user['security_answer'])) {
            $_SESSION['verified_for_reset'] = true;
            header("Location: ./reset_password.php");
            exit();
        } else {
            $errors[] = "Incorrect answer. Please try again.";
            $show_question = true;
            $email = $_POST['email'];
            $question = $_POST['security_question'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - WMS</title>
    <style>
      <?php require __DIR__ . '\styles\FP.css'; ?> 
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
            <a href="login.php" class="btn">Log In</a>
        </div>
    </header>

    <!-- Main Content -->
    <div class="main-content">
        <div class="auth-container">
            <div class="auth-form-container">
                <h2>Password Recovery</h2>
                
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!$show_question): ?>
                    <form class="auth-form" action="forgot_password.php" method="POST">
                        <div class="form-group">
                            <label for="email">Enter your email address</label>
                            <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($email); ?>">
                        </div>
                        <button type="submit" class="auth-btn">Continue</button>
                    </form>
                <?php else: ?>
                    <a href="forgot_password.php" class="back-link">← Back</a>
                    <form class="auth-form" action="forgot_password.php" method="POST">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <input type="hidden" name="security_question" value="<?php echo htmlspecialchars($question); ?>">
                        
                        <div class="security-question">
                            Security Question: <?php echo htmlspecialchars($question); ?>
                        </div>
                        
                        <div class="form-group">
                            <label for="security_answer">Your Answer</label>
                            <input type="password" id="security_answer" name="security_answer" required>
                        </div>
                        
                        <button type="submit" class="auth-btn">Verify Answer</button>
                    </form>
                <?php endif; ?>
                
                <div class="toggle-auth">
                    Remember your password? <a href="login.php">Sign In</a>
                </div>
            </div>
        </div>
    </div>
 
</body>
</html>