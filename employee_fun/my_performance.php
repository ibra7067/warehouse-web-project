<?php
require __DIR__ . '/../include/config.php';
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['employee', 'manager', 'supplier'])) {
    header("Location: /warehouse_project/login.php");
    exit();
}

// Get employee performance data (only for the current employee)
$employee_id = $_SESSION['user_id'];
$performance_data = $conn->query("
    SELECT rating, comments, evaluation_date, u.username as evaluator_name 
    FROM employee_evaluations e
    JOIN users u ON e.evaluator_id = u.user_id
    WHERE e.employee_id = $employee_id
    ORDER BY e.evaluation_date DESC
");

// Prepare data for chart and feedback
$evaluations = [];
$rating_counts = [
    'Excellent (80-100)' => 0,
    'Good (60-79)' => 0,
    'Average (40-59)' => 0,
    'Needs Improvement (0-39)' => 0
];

while ($row = $performance_data->fetch_assoc()) {
    $evaluations[] = $row;
    
    // Categorize ratings for pie chart
    $rating = $row['rating'];
    if ($rating >= 80) {
        $rating_counts['Excellent (80-100)']++;
    } elseif ($rating >= 60) {
        $rating_counts['Good (60-79)']++;
    } elseif ($rating >= 40) {
        $rating_counts['Average (40-59)']++;
    } else {
        $rating_counts['Needs Improvement (0-39)']++;
    }
}

// Prepare chart data
$pie_labels = array_keys($rating_counts);
$pie_data = array_values($rating_counts);
$pie_colors = [
    'rgba(40, 167, 69, 0.8)',   // Green for Excellent
    'rgba(0, 123, 255, 0.8)',    // Blue for Good
    'rgba(255, 193, 7, 0.8)',    // Yellow for Average
    'rgba(220, 53, 69, 0.8)'     // Red for Needs Improvement
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Performance - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        <?php require __DIR__ . '\styles\EI.css'; ?>
        .chart-container {
            position: relative;
            height: 400px;
            margin-bottom: 30px;
        }
        .evaluation-card {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .performance-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .performance-badge {
            font-size: 1rem;
            padding: 0.5rem 1rem;
        }
        .rating-distribution {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .rating-category {
            display: flex;
            align-items: center;
        }
        .rating-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="inventory-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Employee</div>
            
            <div class="nav-menu">
                <a href="employee_inventory.php" class="nav-item">View Inventory</a>
                <a href="my_performance.php" class="nav-item active">My Performance</a>
            </div>
            
            <a href="employee_homepage.php" class="back-btn">← Back to Dashboard</a>
        </div>
        
        <!-- Right Panel - Content -->
        <div class="right-panel">
            <!-- Performance Section -->
            <div class="performance-section">
                <h2 class="panel-title performance-title">My Performance</h2>
                
                <?php if (count($evaluations) > 0): 
                    $current_rating = $evaluations[0]['rating'];
                    $average_rating = array_sum(array_column($evaluations, 'rating')) / count($evaluations);
                ?>
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Current Rating</h5>
                                    <h3 class="text-primary"><?= $current_rating ?></h3>
                                    <span class="performance-badge bg-primary">/100</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Average Rating</h5>
                                    <h3 class="text-primary"><?= round($average_rating, 1) ?></h3>
                                    <span class="performance-badge bg-info">/100</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <h5 class="card-title">Evaluations</h5>
                                    <h3 class="text-primary"><?= count($evaluations) ?></h3>
                                    <span class="performance-badge bg-secondary">Total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                        <div class="rating-distribution">
                            <div class="rating-category">
                                <div class="rating-color" style="background-color: rgba(40, 167, 69, 0.8);"></div>
                                <span>Excellent (80-100)</span>
                            </div>
                            <div class="rating-category">
                                <div class="rating-color" style="background-color: rgba(0, 123, 255, 0.8);"></div>
                                <span>Good (60-79)</span>
                            </div>
                            <div class="rating-category">
                                <div class="rating-color" style="background-color: rgba(255, 193, 7, 0.8);"></div>
                                <span>Average (40-59)</span>
                            </div>
                            <div class="rating-category">
                                <div class="rating-color" style="background-color: rgba(220, 53, 69, 0.8);"></div>
                                <span>Needs Improvement (0-39)</span>
                            </div>
                        </div>
                    </div>
                    
                    <h3 class="mt-4">Recent Feedback</h3>
                    <?php foreach ($evaluations as $eval): ?>
                        <div class="evaluation-card">
                            <div class="d-flex justify-content-between">
                                <h5><?= date('M d, Y', strtotime($eval['evaluation_date'])) ?></h5>
                                <span class="badge bg-primary"><?= $eval['rating'] ?>/100</span>
                            </div>
                            <p class="mt-2"><?= htmlspecialchars($eval['comments']) ?></p>
                            <div class="text-muted">
                                <small>Evaluated by: <?= $eval['evaluator_name'] ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="alert alert-info">
                        No performance evaluations available yet.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <?php
// [Previous PHP code remains the same until the JavaScript section]
?>

<?php if (count($evaluations) > 0): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            
            // Get the average rating (0-100 scale)
            const averageRating = <?= round($average_rating, 1) ?>;
            
            // Create a performance gauge chart
            const gaugeChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [averageRating, 100 - averageRating],
                        backgroundColor: [
                            getRatingColor(averageRating),
                            'rgba(200, 200, 200, 0.2)'
                        ],
                        borderWidth: 0,
                        circumference: 270, // 270 degrees (3/4 circle)
                        rotation: 225,     // Start at -135 degrees (top left)
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            });

            // Function to determine color based on rating
            function getRatingColor(rating) {
                if (rating >= 80) return 'rgba(40, 167, 69, 0.8)';   // Green
                if (rating >= 60) return 'rgba(0, 123, 255, 0.8)';   // Blue
                if (rating >= 40) return 'rgba(255, 193, 7, 0.8)';    // Yellow
                return 'rgba(220, 53, 69, 0.8)';                     // Red
            }

            // Add custom center text
            const centerText = {
                id: 'centerText',
                afterDatasetsDraw(chart, args, options) {
                    const {ctx, chartArea: {left, right, top, bottom, width, height}} = chart;
                    
                    ctx.save();
                    const text = '<?= round($average_rating, 1) ?>';
                    const subtext = getRatingLabel(averageRating);
                    
                    ctx.font = 'bold 30px Arial';
                    ctx.fillStyle = '#333';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(text, width / 2, height / 2 - 10);
                    
                    ctx.font = '14px Arial';
                    ctx.fillStyle = getRatingColor(averageRating);
                    ctx.fillText(subtext, width / 2, height / 2 + 20);
                    
                    ctx.restore();
                }
            };
            
            // Function to get rating label
            function getRatingLabel(rating) {
                if (rating >= 80) return 'Excellent';
                if (rating >= 60) return 'Good';
                if (rating >= 40) return 'Average';
                return 'Needs Improvement';
            }
            
            // Register the plugin
            Chart.register(centerText);
            
            // Update the chart to reflect changes
            gaugeChart.update();
        });
    </script>
    <?php endif; ?>
</body>
</html>