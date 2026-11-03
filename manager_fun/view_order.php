<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['manager', 'employee'])) {
    header("Location: /testor/login.php");
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "Invalid order ID";
    header("Location: " . ($_SESSION['role'] === 'manager' ? 'order_list.php' : 'employee_orders.php'));
    exit();
}

$order_id = (int)$_GET['id'];

// Get order details
$order = $conn->query("
    SELECT o.*, u.username as customer_name, m.username as manager_name
    FROM orders o
    JOIN users u ON o.customer_id = u.user_id
    LEFT JOIN users m ON o.manager_id = m.user_id
    WHERE o.order_id = $order_id
")->fetch_assoc();

if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: " . ($_SESSION['role'] === 'manager' ? 'order_list.php' : 'employee_orders.php'));
    exit();
}

// Get order items
$items = $conn->query("
    SELECT oi.*, i.name, i.weight, i.description
    FROM order_items oi
    JOIN inventory i ON oi.item_id = i.item_id
    WHERE oi.order_id = $order_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - WMS</title>
    <style>
         <?php require __DIR__ . '\styles\VO.css'; ?> 
    </style>
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1>Order #<?= $order['order_id'] ?> Details</h1>
            <a href="<?= $_SESSION['role'] === 'manager' ? 'order_list.php' : 'employee_orders.php' ?>" class="btn btn-outline-secondary">
                ← Back to Orders
            </a>
        </div>
        
        <div class="card">
            <div class="card-header">
                Order Information
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Customer:</strong> <?= $order['customer_name'] ?></p>
                        <p><strong>Order Date:</strong> <?= date('M j, Y g:i a', strtotime($order['order_date'])) ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Status:</strong> 
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </p>
                        <?php if ($order['manager_name']): ?>
                            <p><strong>Processed by:</strong> <?= $order['manager_name'] ?></p>
                            <p><strong>Decision Date:</strong> <?= $order['decision_date'] ? date('M j, Y g:i a', strtotime($order['decision_date'])) : 'N/A' ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                Order Items
            </div>
            <div class="card-body">
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Quantity</th>
                                <th>Unit Weight (kg)</th>
                                <th>Total Weight (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($item = $items->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= $item['description'] ?: 'N/A' ?></td>
                                    <td><?= $item['quantity'] ?></td>
                                    <td><?= $item['weight'] ?></td>
                                    <td><?= number_format($item['quantity'] * $item['weight'], 2) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>