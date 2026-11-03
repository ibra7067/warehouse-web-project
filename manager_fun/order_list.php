<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Get all orders with customer information
$orders = $conn->query("
    SELECT o.*, u.username as customer_name 
    FROM orders o
    JOIN users u ON o.customer_id = u.user_id
    ORDER BY o.order_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - WMS</title>
    <style>
      <?php require __DIR__ . '\styles\OL.css'; ?> 
     </style>  
</head>
<body>
    <div class="management-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Manager</div>
            
            <div class="nav-menu">
                <a href="#" class="nav-item active">Order Management</a>
            </div>
            
            <a href="manager_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Right Panel - Content -->
        <div class="right-panel">
            <h1 class="panel-title">Order Management</h1>
            
            <div style="overflow-x: auto;">
                <table class="order-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= $order['order_id'] ?></td>
                                <td><?= $order['customer_name'] ?></td>
                                <td><?= date('M j, Y g:i a', strtotime($order['order_date'])) ?></td>
                                <td>
                                    <span class="status-badge status-<?= $order['status'] ?>">
                                        <?= ucfirst($order['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        <a href="view_order.php?id=<?= $order['order_id'] ?>" class="btn btn-primary btn-sm">View</a>
                                        <?php if ($order['status'] == 'pending'): ?>
                                            <a href="process_order.php?action=approve&id=<?= $order['order_id'] ?>" class="btn btn-success btn-sm">Approve</a>
                                            <a href="process_order.php?action=reject&id=<?= $order['order_id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                                        <?php elseif ($order['status'] == 'approved'): ?>
                                            <a href="process_order.php?action=process&id=<?= $order['order_id'] ?>" class="btn btn-info btn-sm">Process</a>
                                        <?php elseif ($order['status'] == 'processing'): ?>
                                            <a href="process_order.php?action=ship&id=<?= $order['order_id'] ?>" class="btn btn-warning btn-sm">Ship</a>
                                        <?php elseif ($order['status'] == 'shipped'): ?>
                                            <a href="process_order.php?action=deliver&id=<?= $order['order_id'] ?>" class="btn btn-success btn-sm">Deliver</a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>