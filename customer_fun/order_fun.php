<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: /testor/login.php");
    exit();
}

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $conn->begin_transaction();
    
    try {
        // Create the order
        $order_stmt = $conn->prepare("INSERT INTO orders (customer_id, status) VALUES (?, 'pending')");
        $order_stmt->bind_param("i", $_SESSION['user_id']);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        // Add order items
        $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, item_id, quantity) VALUES (?, ?, ?)");
        
        foreach ($_POST['items'] as $item_id => $quantity) {
            if ($quantity > 0) {
                $item_stmt->bind_param("iii", $order_id, $item_id, $quantity);
                $item_stmt->execute();
            }
        }
        
        $conn->commit();
        $_SESSION['success'] = "Order #$order_id placed successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error placing order: " . $e->getMessage();
    }
    
    header("Location: order_fun.php");
    exit();
}

// Get available inventory items
$inventory = $conn->query("SELECT * FROM inventory WHERE quantity > 0 AND deleted = 0 ORDER BY name");

// Get customer's orders
$orders = $conn->prepare("
    SELECT o.order_id, o.order_date, o.status, o.manager_id, 
           COUNT(oi.order_item_id) as item_count,
           SUM(oi.quantity * i.weight) as total_weight
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN inventory i ON oi.item_id = i.item_id
    WHERE o.customer_id = ?
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
$orders->bind_param("i", $_SESSION['user_id']);
$orders->execute();
$orders_result = $orders->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Orders - WMS</title>
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
    <a href="order_fun.php#new-order" class="nav-item active" onclick="showPanel('new-order', this); return false;">Place New Order</a>
    <a href="order_fun.php#track-orders" class="nav-item" onclick="showPanel('track-orders', this); return false;">Track Orders</a>
    <a href="c_feedback.php" class="nav-item">Feedback</a>
</div>
            
            <a href="customer_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Right Panel - Content -->
        <div class="right-panel">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- New Order Panel -->
            <div id="new-order-panel" class="content-panel active">
                <h1 class="panel-title">Place New Order</h1>
                
                <form method="POST">
                    <?php if ($inventory->num_rows > 0): ?>
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Available</th>
                                    <th>Weight (kg)</th>
                                    <th>Location</th>
                                    <th>Quantity to Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $inventory->data_seek(0); // Reset pointer
                                while ($item = $inventory->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item['name']) ?></td>
                                        <td><?= htmlspecialchars($item['description']) ?: 'N/A' ?></td>
                                        <td><?= $item['quantity'] ?></td>
                                        <td><?= $item['weight'] ?></td>
                                        <td><?= $item['location'] ?: 'N/A' ?></td>
                                        <td>
                                            <input type="number" class="quantity-input" 
                                                   name="items[<?= $item['item_id'] ?>]" 
                                                   min="0" max="<?= $item['quantity'] ?>" 
                                                   value="0">
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        
                        <div class="button-group">
                            <button type="submit" name="place_order" class="btn btn-primary">Place Order</button>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No inventory items available for ordering.</p>
                        </div>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Track Orders Panel -->
            <div id="track-orders-panel" class="content-panel">
                <h1 class="panel-title">Your Order History</h1>
                
                <?php if ($orders_result->num_rows > 0): ?>
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total Weight</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders_result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?= $order['order_id'] ?></td>
                                    <td><?= date('M j, Y g:i a', strtotime($order['order_date'])) ?></td>
                                    <td><?= $order['item_count'] ?></td>
                                    <td><?= number_format($order['total_weight'], 2) ?> kg</td>
                                    <td>
                                        <span class="status-badge status-<?= $order['status'] ?>">
                                            <?= ucfirst($order['status']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <p>You haven't placed any orders yet.</p>
                        <div class="button-group" style="justify-content: center;">
                            <button onclick="showPanel('new-order')" class="btn btn-primary">Place Your First Order</button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
       <script>
    function showPanel(panelName, clickedLink) {
        // Hide all panels
        document.querySelectorAll('.content-panel').forEach(panel => {
            panel.classList.remove('active');
        });

        // Show selected panel
        const target = document.getElementById(panelName + '-panel');
        if (target) target.classList.add('active');

        // Update active nav item
        document.querySelectorAll('.nav-item').forEach(item => {
            item.classList.remove('active');
        });

        if (clickedLink) {
            clickedLink.classList.add('active');
        } else {
            const linkMap = {
                'new-order': 'Place New Order',
                'track-orders': 'Track Orders'
            };
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.textContent.trim() === linkMap[panelName]) {
                    item.classList.add('active');
                }
            });
        }
    }

    // On page load, check URL hash and open the correct panel
    window.addEventListener('DOMContentLoaded', function () {
        const hash = window.location.hash;
        if (hash === '#track-orders') {
            showPanel('track-orders');
        } else if (hash === '#new-order') {
            showPanel('new-order');
        }
    });
</script>
     
</body>
</html>