<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Handle adding new inventory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $weight = $_POST['weight'];
    $location = $_POST['location'];
    
    try {
        $stmt = $conn->prepare("INSERT INTO inventory (name, description, quantity, weight, location) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssids", $name, $description, $quantity, $weight, $location);
        $stmt->execute();
        
        $_SESSION['success'] = "Item added to inventory successfully!";
        header("Location: add_inventory.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding item: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Inventory - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
       <?php require __DIR__ . '\styles\AI.css'; ?> 
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar">
        <div class="sidebar-header text-center">
            <h4>Warehouse Manager</h4>
        </div>
        
        <div class="sidebar-content">
            <ul class="components">
                <li>
                    <a href="manager_inventory.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                </li>
                <li class="active">
                    <a href="add_inventory.php"><i class="bi bi-plus-circle me-2"></i> Add Inventory</a>
                </li>
                <li> 
                <a href="employee_evaluation.php"><i class="bi bi-clipboard-check me-2"></i> Employee Evaluation</a>
            </li>
            <li>
                <a href="employee_performance_chart.php"><i class="bi bi-bar-chart me-2"></i> Performance Chart</a>
            </li>
            <li>
                    <a href="feedback.php"><i class="bi bi-people me-2"></i> Customer Feedback</a>
                </li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <a href="manager_homepage.php" class="btn btn-outline-light">
                <i class="bi bi-arrow-left-circle me-2"></i> Go Back
            </a>
        </div>
    </nav>

    <!-- Page Content -->
    <div id="content">
        <div class="container-fluid p-4">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <div class="form-container">
                <h1>Add New Inventory Item</h1>
                <form method="POST">
                    <div class="mb-3">
                        <label for="name" class="form-label">Item Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="weight" class="form-label">Weight (kg)</label>
                            <input type="number" step="0.01" class="form-control" id="weight" name="weight" min="0.01" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="2"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="location" class="form-label">Location</label>
                        <input type="text" class="form-control" id="location" name="location" required>
                    </div>
                    <button type="submit" name="add_item" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>