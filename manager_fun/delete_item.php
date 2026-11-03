<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: /warehouse_project/login.php");
    exit();
}

if (isset($_GET['id'])) {
    $item_id = $_GET['id'];
    
    try {
        // Mark item as deleted instead of actually deleting it
        $stmt = $conn->prepare("UPDATE inventory SET deleted = 1 WHERE item_id = ?");
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Item has been marked as deleted!";
    } catch (Exception $e) {
        $_SESSION['error'] = "Error deleting item: " . $e->getMessage();
    }
}

header("Location: manager_inventory.php");
exit();
?>