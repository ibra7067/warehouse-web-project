<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: /warehouse_project/login.php");
    exit();
}

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error'] = "Invalid request";
    header("Location: order_list.php");
    exit();
}

$order_id = (int)$_GET['id'];
$action = $_GET['action'];

// Validate the order exists
$order = $conn->query("SELECT * FROM orders WHERE order_id = $order_id")->fetch_assoc();
if (!$order) {
    $_SESSION['error'] = "Order not found";
    header("Location: order_list.php");
    exit();
}

// Process the action
try {
    $conn->begin_transaction();
    
    $new_status = '';
    switch ($action) {
        case 'approve':
            $new_status = 'approved';
            break;
        case 'reject':
            $new_status = 'rejected';
            break;
        case 'process':
            $new_status = 'processing';
            break;
        case 'ship':
            $new_status = 'shipped';
            break;
        case 'deliver':
            $new_status = 'delivered';
            break;
        default:
            throw new Exception("Invalid action");
    }
    
    // Update order status
    $stmt = $conn->prepare("UPDATE orders SET status = ?, manager_id = ?, decision_date = NOW() WHERE order_id = ?");
    $stmt->bind_param("sii", $new_status, $_SESSION['user_id'], $order_id);
    $stmt->execute();
    
    // If delivering, update inventory (reduce quantities)
    if ($action === 'deliver') {
        $items = $conn->query("SELECT item_id, quantity FROM order_items WHERE order_id = $order_id");
        while ($item = $items->fetch_assoc()) {
            $update = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ?");
            $update->bind_param("ii", $item['quantity'], $item['item_id']);
            $update->execute();
        }
    }
    
    $conn->commit();
    $_SESSION['success'] = "Order status updated to " . ucfirst($new_status);
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = "Error processing order: " . $e->getMessage();
}

header("Location: order_list.php");
exit();
?>