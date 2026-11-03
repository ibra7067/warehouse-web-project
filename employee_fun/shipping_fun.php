<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employee') {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Handle picking action for entire order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pick_order'])) {
    $order_id = $_POST['order_id'];
    
    $conn->begin_transaction();
    
    try {
        // Get all items for this order
        $order_items = $conn->prepare("
            SELECT oi.item_id, oi.quantity, i.quantity as available
            FROM order_items oi
            JOIN inventory i ON oi.item_id = i.item_id
            WHERE oi.order_id = ?
        ");
        $order_items->bind_param("i", $order_id);
        $order_items->execute();
        $items_result = $order_items->get_result();
        
        // Check inventory for all items first
        while ($item = $items_result->fetch_assoc()) {
            if ($item['available'] < $item['quantity']) {
                throw new Exception("Not enough inventory available for item ID: " . $item['item_id']);
            }
        }
        
        // Create pack
        $pack = $conn->prepare("
            INSERT INTO packs (order_id, employee_id, status) 
            VALUES (?, ?, 'packing')
        ");
        $pack->bind_param("ii", $order_id, $_SESSION['user_id']);
        $pack->execute();
        $pack_id = $conn->insert_id;
        
        // Add all items to pack and update inventory
        $items_result->data_seek(0); // Reset pointer
        while ($item = $items_result->fetch_assoc()) {
            // Add to pack items
            $pack_item = $conn->prepare("
                INSERT INTO pack_items (pack_id, item_id, quantity) 
                VALUES (?, ?, ?)
            ");
            $pack_item->bind_param("iii", $pack_id, $item['item_id'], $item['quantity']);
            $pack_item->execute();
            
            // Update inventory
            $update_inv = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ?");
            $update_inv->bind_param("ii", $item['quantity'], $item['item_id']);
            $update_inv->execute();
        }
        
        $conn->commit();
        $_SESSION['success'] = "Order picked successfully! All items moved to packing.";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error picking order: " . $e->getMessage();
    }
    
    header("Location: shipping_fun.php");
    exit();
}

// Handle packing completion with weight validation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ship_pack'])) {
    $pack_id = $_POST['pack_id'];
    $max_weight = $_POST['max_weight'];
    
    $conn->begin_transaction();
    
    try {
        // Calculate total weight
        $total_weight = $conn->query("
            SELECT SUM(pi.quantity * i.weight) as total_weight
            FROM pack_items pi
            JOIN inventory i ON pi.item_id = i.item_id
            WHERE pi.pack_id = $pack_id
        ")->fetch_assoc()['total_weight'];
        
        if ($total_weight > $max_weight) {
            throw new Exception("Total weight exceeds maximum allowed weight");
        }
        
        // Update pack status to shipped
        $update_pack = $conn->prepare("UPDATE packs SET status = 'shipped' WHERE pack_id = ?");
        $update_pack->bind_param("i", $pack_id);
        $update_pack->execute();
        
        // Update order status
        $order_id = $conn->query("SELECT order_id FROM packs WHERE pack_id = $pack_id")->fetch_assoc()['order_id'];
        $update_order = $conn->prepare("UPDATE orders SET status = 'shipped' WHERE order_id = ?");
        $update_order->bind_param("i", $order_id);
        $update_order->execute();
        
        $conn->commit();
        
        if ($total_weight > $max_weight * 0.9) {
            $_SESSION['warning'] = "Pack shipped successfully but total weight (" . $total_weight . "kg) is close to maximum (" . $max_weight . "kg). Please be careful!";
        } else {
            $_SESSION['success'] = "Pack shipped successfully! Total weight: " . $total_weight . "kg";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error shipping pack: " . $e->getMessage();
    }
    
    header("Location: shipping_fun.php");
    exit();
}

// Get orders that need picking
$orders = $conn->query("
    SELECT o.order_id, o.order_date, u.username as customer_name,
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    JOIN users u ON o.customer_id = u.user_id
    JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.status = 'approved'
    GROUP BY o.order_id
    ORDER BY o.order_date ASC
");

// Get packs being worked on by this employee
$packs = $conn->prepare("
    SELECT p.*, o.order_date, u.username as customer_name
    FROM packs p
    JOIN orders o ON p.order_id = o.order_id
    JOIN users u ON o.customer_id = u.user_id
    WHERE p.employee_id = ? AND p.status = 'packing'
");
$packs->bind_param("i", $_SESSION['user_id']);
$packs->execute();
$packs_result = $packs->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipping Function - WMS</title>
    <style>
      <?php require __DIR__ . '\styles\SF.css'; ?>
    </style>
</head>
<body>
    <div class="shipping-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Shipping</div>
            
            <div class="nav-menu">
                <a href="#" class="nav-item active" onclick="showPanel('pick')">Pick Orders</a>
                <a href="#" class="nav-item" onclick="showPanel('pack')">Pack Orders</a>
            </div>
            
            <a href="employee_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Right Panel - Content -->
        <div class="right-panel">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning"><?= $_SESSION['warning'] ?></div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <!-- Pick Orders Panel -->
            <div id="pick-panel" class="content-panel">
                <h2 class="panel-title">Orders to Pick</h2>
                
                <?php if ($orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                        <div class="order-card">
                            <div class="order-header">
                                <h3>Order #<?= $order['order_id'] ?> - <?= $order['customer_name'] ?></h3>
                                <small><?= date('M j, Y', strtotime($order['order_date'])) ?></small>
                            </div>
                            
                            <?php
                            // Get items for this order
                            $items = $conn->prepare("
                                SELECT oi.*, i.name, i.weight, i.quantity as available
                                FROM order_items oi
                                JOIN inventory i ON oi.item_id = i.item_id
                                WHERE oi.order_id = ?
                            ");
                            $items->bind_param("i", $order['order_id']);
                            $items->execute();
                            $items_result = $items->get_result();
                            ?>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Ordered</th>
                                        <th>Available</th>
                                        <th>Weight (kg)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items_result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $item['name'] ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= $item['available'] ?></td>
                                            <td><?= $item['weight'] ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            
                            <form method="POST" style="margin-top: 15px; display: flex; justify-content: flex-end;">
                                <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                                <button type="submit" name="pick_order" class="btn btn-primary">Pick Entire Order</button>
                            </form>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="order-card">
                        <p>No orders currently require picking.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pack Orders Panel -->
            <div id="pack-panel" class="content-panel" style="display:none;">
                <h2 class="panel-title">Current Packs</h2>
                
                <?php if ($packs_result->num_rows > 0): ?>
                    <?php while ($pack = $packs_result->fetch_assoc()): ?>
                        <div class="pack-card">
                            <div class="pack-header">
                                <h3>Pack #<?= $pack['pack_id'] ?> for Order #<?= $pack['order_id'] ?></h3>
                                <small>Customer: <?= $pack['customer_name'] ?></small>
                            </div>
                            
                            <?php
                            // Get items in this pack
                            $items = $conn->prepare("
                                SELECT pi.*, i.name, i.weight
                                FROM pack_items pi
                                JOIN inventory i ON pi.item_id = i.item_id
                                WHERE pi.pack_id = ?
                            ");
                            $items->bind_param("i", $pack['pack_id']);
                            $items->execute();
                            $items_result = $items->get_result();
                            
                            $total_weight = 0;
                            ?>
                            
                            <div class="weight-controls">
                                <div class="weight-input">
                                    <label for="max_weight_<?= $pack['pack_id'] ?>">Max Weight (kg):</label>
                                    <input type="number" id="max_weight_<?= $pack['pack_id'] ?>" name="max_weight" min="0.1" step="0.1" value="10.0">
                                    <div class="compact-weight-indicator" id="indicator-<?= $pack['pack_id'] ?>">
                                        <div class="compact-weight-bar" id="bar-<?= $pack['pack_id'] ?>"></div>
                                    </div>
                                </div>
                                <div id="weight-display-<?= $pack['pack_id'] ?>" class="weight-display">
                                    Total: <strong>0.00 kg</strong>
                                </div>
                            </div>
                            
                            <table>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th>Unit Weight</th>
                                        <th>Total Weight</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $items_result->fetch_assoc()): 
                                        $item_total = $item['quantity'] * $item['weight'];
                                        $total_weight += $item_total;
                                    ?>
                                        <tr>
                                            <td><?= $item['name'] ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= $item['weight'] ?> kg</td>
                                            <td><?= number_format($item_total, 2) ?> kg</td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                            
                            <form method="POST" style="margin-top: 20px; display: flex; justify-content: flex-end;">
                                <input type="hidden" name="pack_id" value="<?= $pack['pack_id'] ?>">
                                <input type="hidden" id="actual_max_weight_<?= $pack['pack_id'] ?>" name="max_weight" value="">
                                <button type="submit" name="ship_pack" class="btn btn-primary" id="ship-btn-<?= $pack['pack_id'] ?>">
                                    Ship Package
                                </button>
                            </form>
                        </div>
                        
                        <script>
                            // Update weight display and indicator
                            document.addEventListener('DOMContentLoaded', function() {
                                const maxWeightInput = document.getElementById('max_weight_<?= $pack['pack_id'] ?>');
                                const weightDisplay = document.getElementById('weight-display-<?= $pack['pack_id'] ?>');
                                const weightBar = document.getElementById('bar-<?= $pack['pack_id'] ?>');
                                const shipBtn = document.getElementById('ship-btn-<?= $pack['pack_id'] ?>');
                                const actualMaxWeight = document.getElementById('actual_max_weight_<?= $pack['pack_id'] ?>');
                                
                                const totalWeight = <?= $total_weight ?>;
                                
                                function updateWeightDisplay() {
                                    const maxWeight = parseFloat(maxWeightInput.value) || 0.1;
                                    actualMaxWeight.value = maxWeight;
                                    
                                    weightDisplay.innerHTML = `Total: <strong>${totalWeight.toFixed(2)} kg</strong>`;
                                    
                                    // Update progress bar
                                    const percentage = Math.min((totalWeight / maxWeight) * 100, 100);
                                    weightBar.style.width = `${percentage}%`;
                                    
                                    // Update color based on weight
                                    if (totalWeight > maxWeight) {
                                        weightBar.style.backgroundColor = 'var(--danger-color)';
                                        shipBtn.disabled = true;
                                        shipBtn.title = "Total weight exceeds maximum allowed weight";
                                        shipBtn.classList.remove('btn-primary');
                                        shipBtn.classList.add('btn-warning');
                                    } else if (totalWeight > maxWeight * 0.9) {
                                        weightBar.style.backgroundColor = 'var(--warning-color)';
                                        shipBtn.disabled = false;
                                        shipBtn.title = "Warning: Weight is close to maximum";
                                        shipBtn.classList.remove('btn-warning');
                                        shipBtn.classList.add('btn-primary');
                                    } else {
                                        weightBar.style.backgroundColor = 'var(--success-color)';
                                        shipBtn.disabled = false;
                                        shipBtn.title = "";
                                        shipBtn.classList.remove('btn-warning');
                                        shipBtn.classList.add('btn-primary');
                                    }
                                }
                                
                                // Initial update
                                updateWeightDisplay();
                                
                                // Update when max weight changes
                                maxWeightInput.addEventListener('input', updateWeightDisplay);
                            });
                        </script>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="pack-card">
                        <p>No packs currently being prepared.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Show different panels
        function showPanel(panelName) {
            document.getElementById('pick-panel').style.display = 'none';
            document.getElementById('pack-panel').style.display = 'none';
            document.getElementById(panelName + '-panel').style.display = 'block';
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        // Initialize with pick panel visible
        document.addEventListener('DOMContentLoaded', function() {
            showPanel('pick');
        });
    </script>
</body>
</html>