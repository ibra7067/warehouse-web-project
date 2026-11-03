<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $customer_id = $_SESSION['user_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $rating = $_POST['rating'];
    
    $stmt = $conn->prepare("INSERT INTO customer_feedback (customer_id, subject, message, rating, submission_date) 
                           VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("issi", $customer_id, $subject, $message, $rating);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_message'] = "Thank you for your feedback!";
    header("Location: c_feedback.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        <?php require __DIR__ . '\styles\OR.css'; ?>
    </style>
</head>
<body>

    <div class="customer-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Customer</div>

            <div class="nav-menu">
                <a href="order_fun.php" class="nav-item">Place New Order</a>
                <a href="order_fun.php#track-orders" class="nav-item">Track Orders</a>
                <a href="c_feedback.php" class="nav-item active">Feedback</a>
            </div>

            <a href="customer_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>

        <!-- Right Panel - Content -->
        <div class="right-panel">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4>Order Feedback</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['success_message'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($_SESSION['success_message']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['success_message']); ?>
                    <?php endif; ?>

                    <form method="POST" action="c_feedback.php">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Your Feedback</label>
                            <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Order Satisfaction (1-5 stars)</label>
                            <div class="rating-container">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <div class="rating-option">
                                        <input type="radio" id="rating<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                        <label for="rating<?= $i ?>">
                                            <i class="bi bi-star-fill star-rating"></i>
                                        </label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                        </div>

                        <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </div><!-- /customer-container -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>