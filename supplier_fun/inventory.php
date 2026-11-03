<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'supplier') {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Handle restocking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['restock'])) {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    
    $conn->begin_transaction();
    
    try {
        // Update inventory
        $update = $conn->prepare("UPDATE inventory SET quantity = quantity + ? WHERE item_id = ?");
        $update->bind_param("ii", $quantity, $item_id);
        $update->execute();
        
        // Record restock
        $record = $conn->prepare("INSERT INTO supplier_restocks (supplier_id, item_id, quantity_added) VALUES (?, ?, ?)");
        $record->bind_param("iii", $_SESSION['user_id'], $item_id, $quantity);
        $record->execute();
        
        $conn->commit();
        $_SESSION['success'] = "Inventory restocked successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Error restocking: " . $e->getMessage();
    }
    
    header("Location: inventory.php");
    exit();
}

// Get low inventory items
$low_inventory = $conn->query("
    SELECT i.* 
    FROM inventory i
    WHERE i.quantity < 20
    AND i.deleted = 0   
    ORDER BY i.quantity ASC  
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Restocking - WMS</title>
    <style>
      <?php require __DIR__ . '\styles\SI.css'; ?>
    </style>
</head>
<body>
    <div class="supplier-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Supplier</div>
            
            <div class="nav-menu">
                 
                <a href="#" class="nav-item active">Inventory Restocking</a>
                 
                
            </div>
            
            <a href="supplier_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Right Panel - Content -->
        <div class="right-panel">
            <h1 class="panel-title">Inventory Restocking</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if ($low_inventory->num_rows > 0): ?>
                <div class="alert alert-warning">
                    <h3>Low Inventory Items</h3>
                    <p>The following items need restocking (quantity below 20):</p>
                </div>
                
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Item ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Current Qty</th>
                            <th>Weight (kg)</th>
                            <th>Location</th>
                            <th>Restock Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($item = $low_inventory->fetch_assoc()): ?>
                            <tr class="low-inventory">
                                <td><?= $item['item_id'] ?></td>
                                <td><?= $item['name'] ?></td>
                                <td><?= $item['description'] ?: 'N/A' ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= $item['weight'] ?></td>
                                <td><?= $item['location'] ?: 'N/A' ?></td>
                                <td>
                                    <form method="POST" class="restock-form">
                                        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                        <input type="number" name="quantity" min="1" value="20">
                                        <button type="submit" name="restock" class="btn btn-primary">Restock</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <p>No items currently need restocking (all quantities above 20).</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>