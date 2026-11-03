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

// Get all feedback data
$feedback_data = $conn->query("
    SELECT f.*, u.username as customer_name 
    FROM customer_feedback f
    JOIN users u ON f.customer_id = u.user_id
    ORDER BY f.submission_date DESC
");

// Prepare data for chart
$chart_labels = [];
$chart_ratings = [];
$chart_messages = [];
while ($row = $feedback_data->fetch_assoc()) {
    $chart_labels[] = $row['customer_name'];
    $chart_ratings[] = $row['rating'];
    $chart_messages[] = $row['message'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback Dashboard - WMS</title>
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
        .feedback-card {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        .star-rating {
            color: #ffc107;
        }
        .low-rating {
            border-left-color: #dc3545 !important;
            background-color: #f8d7da;
        }
        .high-rating {
            border-left-color: #28a745 !important;
            background-color: #d4edda;
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
                <li>
                    <a href="manager_inventory.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="add_inventory.php"><i class="bi bi-plus-circle me-2"></i> Add Inventory</a>
                </li>
                <li>
                    <a href="employee_evaluation.php"><i class="bi bi-clipboard-check me-2"></i> Employee Evaluation</a>
                </li>
                <li >
                <a href="employee_performance_chart.php"><i class="bi bi-bar-chart me-2"></i> Performance Chart</a>
            </li>
                <li class="active">
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
            <h1 class="h3 mb-4 text-gray-800">Customer Feedback Dashboard</h1>
            
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Average Rating</h5>
                            <?php if (count($chart_ratings) > 0): ?>
                                <h3 class="text-primary">
                                    <?= round(array_sum($chart_ratings) / count($chart_ratings), 1) ?>
                                    <small>/5</small>
                                </h3>
                                <div class="star-rating">
                                    <?php
                                    $avg = round(array_sum($chart_ratings) / count($chart_ratings));
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $avg ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                    }
                                    ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted">No feedback yet</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Total Feedback</h5>
                            <h3 class="text-primary"><?= count($chart_ratings) ?></h3>
                            <p class="text-muted">Submissions</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h5 class="card-title">Low Ratings (1-2 stars)</h5>
                            <?php
                            $low_ratings = array_filter($chart_ratings, function($rating) {
                                return $rating <= 2;
                            });
                            ?>
                            <h3 class="text-primary"><?= count($low_ratings) ?></h3>
                            <p class="text-muted">Need attention</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Customer Satisfaction Trend</h6>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="feedbackChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Feedback</h6>
                </div>
                <div class="card-body">
                    <?php 
                    // Reset pointer to reuse the data
                    $feedback_data->data_seek(0);
                    
                    if ($feedback_data->num_rows > 0): ?>
                        <?php while ($feedback = $feedback_data->fetch_assoc()): ?>
                            <div class="card feedback-card <?= $feedback['rating'] <= 2 ? 'low-rating' : ($feedback['rating'] >= 4 ? 'high-rating' : '') ?>">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title"><?= $feedback['subject'] ?></h5>
                                        <div class="star-rating">
                                            <?php
                                            for ($i = 1; $i <= 5; $i++) {
                                                echo $i <= $feedback['rating'] ? '<i class="bi bi-star-fill"></i>' : '<i class="bi bi-star"></i>';
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <p class="card-text"><?= htmlspecialchars($feedback['message']) ?></p>
                                    <div class="d-flex justify-content-between text-muted">
                                        <small>Customer: <?= $feedback['customer_name'] ?></small>
                                        <small><?= date('M d, Y H:i', strtotime($feedback['submission_date'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No feedback submissions yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('feedbackChart').getContext('2d');
            const feedbackChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($chart_labels) ?>,
                    datasets: [{
                        label: 'Customer Ratings (1-5 stars)',
                        data: <?= json_encode($chart_ratings) ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2,
                        pointBackgroundColor: 'rgba(54, 162, 235, 1)',
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 1,
                            max: 5,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                afterLabel: function(context) {
                                    const index = context.dataIndex;
                                    return 'Message: ' + <?= json_encode($chart_messages) ?>[index];
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