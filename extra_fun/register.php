 <?php
require __DIR__ . '/../include/config.php';

// Pre-selected role from choose_role.php
$preselected_role = isset($_GET['role']) ? $_GET['role'] : '';
$allowed_roles = ['customer', 'employee', 'manager', 'supplier'];
if (!in_array($preselected_role, $allowed_roles)) {
    $preselected_role = '';
}

// If no valid role was chosen, send user back to pick one
if ($preselected_role === '' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: choose_role.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'] ?? '';
    $security_question = $_POST['security_question'];
    $security_answer = trim($_POST['security_answer']);

    $errors = [];

    if (empty($username) || empty($email) || empty($password) || empty($role) || empty($security_question) || empty($security_answer)) {
        $errors[] = "Please fill all required fields.";
    }

    if (!in_array($role, $allowed_roles)) {
        $errors[] = "Invalid account type selected.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // Check if username or email already exists
    $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
    $check_stmt->bind_param("ss", $username, $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $errors[] = "Username or email already exists.";
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_answer = password_hash(strtolower($security_answer), PASSWORD_DEFAULT);

        $insert_stmt = $conn->prepare("INSERT INTO users (username, email, password, role, security_question, security_answer) VALUES (?, ?, ?, ?, ?, ?)");
        $insert_stmt->bind_param("ssssss", $username, $email, $hashed_password, $role, $security_question, $hashed_answer);

        if ($insert_stmt->execute()) {
            $_SESSION['success'] = "Registration successful! Please log in.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WMS</title>
    <style>
    <?php require __DIR__ . '\styles\RG.css'; ?>
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
                <h2>Create Your Account</h2>

                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" action="register.php?role=<?php echo urlencode($preselected_role); ?>" method="POST">
                    <!-- Role comes from choose_role.php -->
                    <input type="hidden" name="role" value="<?php echo htmlspecialchars($preselected_role); ?>">

                    <!-- Optional: show which role was chosen (read-only) -->
                    <div class="form-group">
                        <label>Account Type</label>
                        <input type="text"
                               value="<?php echo htmlspecialchars(ucfirst($preselected_role)); ?>"
                               disabled
                               style="background:#f0f0f0; cursor:not-allowed; text-transform:capitalize;">
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" id="email" name="email" required autocomplete="off">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="security-question-group">
                        <label for="security_question">Security Question</label>
                        <select id="security_question" name="security_question" required autocomplete="off">
                            <option value="">Select a security question</option>
                            <option value="What is your favorite hobby?">What is your favorite hobby?</option>
                            <option value="What city were you born in?">What city were you born in?</option>
                            <option value="What was your first pet's name?">What was your first pet's name?</option>
                            <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
                            <option value="What was your first school name?">What was your first school name?</option>
                        </select>

                        <label for="security_answer">Security Answer</label>
                        <input type="text" id="security_answer" name="security_answer" required autocomplete="off">
                    </div>

                    <button type="submit" class="auth-btn">Register</button>
                </form>

                <div class="toggle-auth">
                    Already have an account? <a href="login.php">Sign In</a>
                    <br><br>
                    <a href="choose_role.php" style="font-size:0.85rem; color:#888;">← Change account type</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>