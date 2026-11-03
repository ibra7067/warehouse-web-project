<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Check if the user is a manager
if ($_SESSION['role'] !== 'manager') {
    header("Location: /warehouse_project/access_denied.php");
    exit();
}

// Get employee evaluation data
$evaluations = $conn->query("
    SELECT u.user_id, u.username, AVG(e.rating) as avg_rating
    FROM users u
    LEFT JOIN employee_evaluations e ON u.user_id = e.employee_id
    WHERE u.role = 'employee'
    GROUP BY u.user_id, u.username
    ORDER BY avg_rating DESC
");

// Prepare data for chart
$chart_labels = [];
$chart_data = [];
while ($row = $evaluations->fetch_assoc()) {
    $chart_labels[] = $row['username'];
    $chart_data[] = $row['avg_rating'] ? round($row['avg_rating'], 1) : 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Performance Chart - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        <?php require __DIR__ . '\styles\MI.css'; ?>
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .performance-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
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
                <li >
                    <a href="manager_inventory.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="add_inventory.php"><i class="bi bi-plus-circle me-2"></i> Add Inventory</a>
                </li>
                <li> 
                <a href="employee_evaluation.php"><i class="bi bi-clipboard-check me-2"></i> Employee Evaluation</a>
            </li>
            <li class="active">
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
            <h1 class="h3 mb-4 text-gray-800">Employee Performance Chart</h1>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Top Performer</h5>
                            <?php if (count($chart_labels) > 0): ?>
                                <h3 class="text-primary"><?= $chart_labels[0] ?></h3>
                                <span class="performance-badge bg-success"><?= $chart_data[0] ?>/100</span>
                            <?php else: ?>
                                <p class="text-muted">No data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Average Rating</h5>
                            <?php if (count($chart_data) > 0): ?>
                                <h3 class="text-primary"><?= round(array_sum($chart_data) / count($chart_data), 1) ?></h3>
                                <span class="performance-badge bg-primary">/100</span>
                            <?php else: ?>
                                <p class="text-muted">No data available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Employees Evaluated</h5>
                            <h3 class="text-primary"><?= count($chart_labels) ?></h3>
                            <span class="performance-badge bg-info">Total</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Detailed Ratings</h6>
                </div>
                <div class="card-body">
                    <?php 
                    // Reset pointer to reuse the data
                    $evaluations->data_seek(0);
                    
                    if ($evaluations->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Average Rating</th>
                                        <th>Performance Level</th>
                                        <th>Last Evaluation</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($employee = $evaluations->fetch_assoc()): 
                                        $last_eval = $conn->query("
                                            SELECT rating, evaluation_date 
                                            FROM employee_evaluations 
                                            WHERE employee_id = {$employee['user_id']} 
                                            ORDER BY evaluation_date DESC LIMIT 1
                                        ")->fetch_assoc();
                                        
                                        $performance_level = '';
                                        $badge_class = '';
                                        $avg_rating = $employee['avg_rating'] ? round($employee['avg_rating'], 1) : 0;
                                        
                                        if ($avg_rating >= 80) {
                                            $performance_level = 'Excellent';
                                            $badge_class = 'bg-success';
                                        } elseif ($avg_rating >= 60) {
                                            $performance_level = 'Good';
                                            $badge_class = 'bg-primary';
                                        } elseif ($avg_rating >= 40) {
                                            $performance_level = 'Average';
                                            $badge_class = 'bg-warning text-dark';
                                        } else {
                                            $performance_level = 'Needs Improvement';
                                            $badge_class = 'bg-danger';
                                        }
                                    ?>
                                        <tr>
                                            <td><?= $employee['username'] ?></td>
                                            <td><?= $avg_rating ?></td>
                                            <td><span class="badge <?= $badge_class ?>"><?= $performance_level ?></span></td>
                                            <td>
                                                <?php if ($last_eval): ?>
                                                    <?= date('M d, Y', strtotime($last_eval['evaluation_date'])) ?>
                                                    (<?= $last_eval['rating'] ?>)
                                                <?php else: ?>
                                                    Not evaluated
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No evaluation data available.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const performanceChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'Performance Rating (10-100)',
                        data: <?= json_encode($chart_data) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Rating (10-100)'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Employees'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Rating: ' + context.raw + '/100';
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>