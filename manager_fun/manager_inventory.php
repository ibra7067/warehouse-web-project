<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Get all active inventory items
$inventory = $conn->query("SELECT * FROM inventory WHERE deleted = 0 ORDER BY item_id ASC");

// Calculate inventory health using weighted scoring
$items = $conn->query("SELECT quantity FROM inventory WHERE deleted = 0");
$total_score = 0;
$total_items = 0;
$well_stocked = 0;
$low_stock = 0;
$critical_stock = 0;

while ($item = $items->fetch_assoc()) {
    $quantity = $item['quantity'];
    $score = 0;

    if ($quantity > 20) {
        $score = 100;
        $well_stocked++;
    } elseif ($quantity > 10) {
        $score = 75;
        $low_stock++;
    } elseif ($quantity > 5) {
        $score = 50;
        $low_stock++;
    } elseif ($quantity > 0) {
        $score = 25;
        $critical_stock++;
    } else {
        $score = 0;
        $critical_stock++;
    }

    $total_score += $score;
    $total_items++;
}

$inventory_health = ($total_items > 0) ? ($total_score / $total_items) : 100;

// Get other analytics data
$analytics = $conn->query("
     SELECT 
        COALESCE(SUM(quantity), 0) as total_items,
        COALESCE(SUM(quantity * weight), 0) as total_weight,
        (SELECT COUNT(*) FROM orders WHERE status = 'delivered') as completed_orders,
        (SELECT COUNT(*) FROM orders WHERE status IN ('pending', 'approved', 'processing', 'shipped', 'delivered')) as total_orders
    FROM inventory
    WHERE deleted = 0
")->fetch_assoc();

// Add our calculated health metrics
$analytics['well_stocked_items'] = $well_stocked;
$analytics['low_stock_items'] = $low_stock;
$analytics['critical_stock_items'] = $critical_stock;
$analytics['total_item_types'] = $total_items;

// Calculate other percentages
$storage_percentage = ($analytics['total_weight'] > 0) ? min(100, ($analytics['total_weight'] / 10000) * 100) : 0;
$order_completion = ($analytics['total_orders'] > 0) ? ($analytics['completed_orders'] / $analytics['total_orders']) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        <?php require __DIR__ . '/styles/MI.css'; ?>
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
                <li class="active">
                    <a href="manager_inventory.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                </li>
                <li>
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
            <!-- Page title — visible on dark bg -->
            <h1 class="page-heading">Inventory Dashboard</h1>

            <!-- Analytics Cards -->
            <div class="row mb-4">
                <!-- Storage Capacity -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Storage Capacity</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="progress-circle storage">
                                <svg viewBox="0 0 100 100">
                                    <circle class="bg" cx="50" cy="50" r="45"></circle>
                                    <circle class="progress" cx="50" cy="50" r="45"
                                            data-percentage="<?= round($storage_percentage, 2) ?>"></circle>
                                </svg>
                                <div class="percentage"><?= round($storage_percentage, 2) ?>%</div>
                            </div>
                            <h5 class="text-gray-800"><?= $analytics['total_weight'] ?> kg</h5>
                            <p class="text-muted">of 10,000 kg capacity</p>
                        </div>
                    </div>
                </div>

                <!-- Order Completion -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Order Completion</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="progress-circle orders">
                                <svg viewBox="0 0 100 100">
                                    <circle class="bg" cx="50" cy="50" r="45"></circle>
                                    <circle class="progress" cx="50" cy="50" r="45"
                                            data-percentage="<?= round($order_completion, 2) ?>"></circle>
                                </svg>
                                <div class="percentage"><?= round($order_completion, 2) ?>%</div>
                            </div>
                            <h5 class="text-gray-800"><?= $analytics['completed_orders'] ?> orders</h5>
                            <p class="text-muted">of <?= $analytics['total_orders'] ?> total</p>
                        </div>
                    </div>
                </div>

                <!-- Inventory Health -->
                <div class="col-xl-4 col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-header">
                            <h6 class="m-0 font-weight-bold text-primary">Inventory Health</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="progress-circle health">
                                <svg viewBox="0 0 100 100">
                                    <circle class="bg" cx="50" cy="50" r="45"></circle>
                                    <circle class="progress" cx="50" cy="50" r="45"
                                            data-percentage="<?= round($inventory_health, 2) ?>"></circle>
                                </svg>
                                <div class="percentage"><?= round($inventory_health, 2) ?>%</div>
                            </div>
                            <div class="stock-status">
                                <span class="badge bg-success"><?= $analytics['well_stocked_items'] ?> Well Stocked</span>
                                <span class="badge bg-warning text-dark"><?= $analytics['low_stock_items'] ?> Low Stock</span>
                                <span class="badge bg-danger"><?= $analytics['critical_stock_items'] ?> Critical</span>
                            </div>
                            <p class="text-muted mt-2">Based on weighted stock levels</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Inventory Table -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Current Inventory</h6>
                    <a href="add_inventory.php" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> Add Item
                    </a>
                </div>
                <div class="card-body">
                    <?php if ($inventory->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Qty</th>
                                        <th>Weight</th>
                                        <th>Location</th>
                                        <th>Stock Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($item = $inventory->fetch_assoc()):
                                        $status_class = '';
                                        $status_text = '';
                                        if ($item['quantity'] > 20) {
                                            $status_class = 'bg-success';
                                            $status_text = 'Good';
                                        } elseif ($item['quantity'] > 10) {
                                            $status_class = 'bg-warning text-dark';
                                            $status_text = 'Low';
                                        } elseif ($item['quantity'] > 0) {
                                            $status_class = 'bg-danger';
                                            $status_text = 'Critical';
                                        } else {
                                            $status_class = 'bg-secondary';
                                            $status_text = 'Out of Stock';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $item['item_id'] ?></td>
                                            <td><?= htmlspecialchars($item['name']) ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= $item['weight'] ?> kg</td>
                                            <td><?= htmlspecialchars($item['location']) ?></td>
                                            <td><span class="badge <?= $status_class ?>"><?= $status_text ?></span></td>
                                            <td>
                                                <a href="edit_item.php?id=<?= $item['item_id'] ?>" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="delete_item.php?id=<?= $item['item_id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No inventory items found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Animate circular progress bars
        document.addEventListener('DOMContentLoaded', function() {
            const circles = document.querySelectorAll('.progress-circle .progress');

            circles.forEach(circle => {
                const percentage = circle.getAttribute('data-percentage');
                const circumference = 2 * Math.PI * 45;
                const offset = circumference - (percentage / 100) * circumference;

                circle.style.strokeDashoffset = offset;
                circle.style.transition = 'stroke-dashoffset 1.5s ease-in-out';
            });
        });
    </script>
</body>
</html>