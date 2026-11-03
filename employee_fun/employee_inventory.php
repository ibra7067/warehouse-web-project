<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['employee', 'manager', 'supplier'])) {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Get all inventory items
$inventory = $conn->query("SELECT * FROM inventory WHERE quantity > 0 AND deleted = 0 ORDER BY name");

 ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        <?php require __DIR__ . '\styles\EI.css'; ?>
        
    </style>
</head>
<body>
    <div class="inventory-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Employee</div>
            
            <div class="nav-menu">
                <a href="employee_inventory.php" class="nav-item active">View Inventory</a>
                <a href="my_performance.php" class="nav-item">My Performance</a>
            </div>
            
            <a href="employee_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Right Panel - Content -->
        <div class="right-panel">
            <h1 class="panel-title">Current Inventory</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if ($inventory->num_rows > 0): ?>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Weight (kg)</th>
                            <th>Location</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $inventory->fetch_assoc()): ?>
                            <tr>
                                <td><?= $item['item_id'] ?></td>
                                <td><?= $item['name'] ?></td>
                                <td><?= $item['description'] ?: 'N/A' ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= $item['weight'] ?></td>
                                <td><?= $item['location'] ?: 'N/A' ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No inventory items found.</p>
                </div>
            <?php endif; ?>
            
          
 
</body>
</html>