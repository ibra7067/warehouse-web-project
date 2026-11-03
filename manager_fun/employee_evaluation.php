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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_evaluation'])) {
    $employee_id = $_POST['employee_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];
    $evaluator_id = $_SESSION['user_id'];
    
    // Insert or update evaluation
    $stmt = $conn->prepare("INSERT INTO employee_evaluations (employee_id, evaluator_id, rating, comments, evaluation_date) 
                           VALUES (?, ?, ?, ?, NOW())
                           ON DUPLICATE KEY UPDATE rating = VALUES(rating), comments = VALUES(comments), evaluation_date = NOW()");
    $stmt->bind_param("iiis", $employee_id, $evaluator_id, $rating, $comments);
    $stmt->execute();
    $stmt->close();
    
    $_SESSION['success_message'] = "Evaluation submitted successfully!";
    header("Location: employee_evaluation.php");
    exit();
}

// Get all employees (excluding managers and customers)
$employees = $conn->query("SELECT user_id, username, email FROM users WHERE role = 'employee' ORDER BY username");

// Get existing evaluations
$evaluations = [];
$result = $conn->query("SELECT employee_id, rating, comments FROM employee_evaluations");
while ($row = $result->fetch_assoc()) {
    $evaluations[$row['employee_id']] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Evaluation - WMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        <?php require __DIR__ . '\styles\MI.css'; ?>
        .rating-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .rating-option {
            margin: 5px;
            cursor: pointer;
        }
        .rating-option input[type="radio"] {
            display: none;
        }
        .rating-option label {
            display: inline-block;
            width: 40px;
            height: 40px;
            line-height: 40px;
            text-align: center;
            border-radius: 50%;
            background-color: #f8f9fa;
            color: #495057;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .rating-option input[type="radio"]:checked + label {
            background-color: #0d6efd;
            color: white;
        }
        .evaluation-card {
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        
        /* Fixed modal backdrop styles */
        .modal-backdrop.show {
            z-index: 1040;
            background-color: #000 !important;
        }
        .modal {
            z-index: 1050;
        }
        body.modal-open {
            overflow: auto;
            padding-right: 0 !important;
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
                <li class="active"> 
                    <a href="employee_evaluation.php"><i class="bi bi-clipboard-check me-2"></i> Employee Evaluation</a>
                </li>
                <li>
                    <a href="employee_performance_chart.php"><i class="bi bi-bar-chart me-2"></i> Performance Chart</a>
                </li>
                <li >
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
            <h1 class="h3 mb-4 text-gray-800">Employee Evaluation</h1>
            
            <?php if (isset($_SESSION['success_message'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success_message'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php unset($_SESSION['success_message']); ?>
            <?php endif; ?>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Evaluate Employees</h6>
                </div>
                <div class="card-body">
                    <?php if ($employees->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Current Rating</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($employee = $employees->fetch_assoc()): 
                                        $current_rating = isset($evaluations[$employee['user_id']]) ? $evaluations[$employee['user_id']]['rating'] : 'Not rated';
                                        $current_comments = isset($evaluations[$employee['user_id']]) ? $evaluations[$employee['user_id']]['comments'] : '';
                                    ?>
                                        <tr>
                                            <td><?= $employee['user_id'] ?></td>
                                            <td><?= $employee['username'] ?></td>
                                            <td><?= $employee['email'] ?></td>
                                            <td>
                                                <?php if ($current_rating !== 'Not rated'): ?>
                                                    <span class="badge bg-primary"><?= $current_rating ?>/100</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Not rated</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary evaluate-btn" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#evaluateModal<?= $employee['user_id'] ?>"
                                                        data-employee-id="<?= $employee['user_id'] ?>"
                                                        data-employee-name="<?= htmlspecialchars($employee['username']) ?>"
                                                        data-current-rating="<?= $current_rating === 'Not rated' ? '' : $current_rating ?>"
                                                        data-current-comments="<?= htmlspecialchars($current_comments) ?>">
                                                    <i class="bi bi-clipboard-plus"></i> Evaluate
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No employees found.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Evaluations -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Evaluations</h6>
                </div>
                <div class="card-body">
                    <?php 
                    $recent_evaluations = $conn->query("
                        SELECT e.*, u.username as employee_name, m.username as evaluator_name 
                        FROM employee_evaluations e
                        JOIN users u ON e.employee_id = u.user_id
                        JOIN users m ON e.evaluator_id = m.user_id
                        ORDER BY e.evaluation_date DESC LIMIT 5
                    ");
                    
                    if ($recent_evaluations->num_rows > 0): ?>
                        <?php while ($eval = $recent_evaluations->fetch_assoc()): ?>
                            <div class="card evaluation-card mb-3">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <h5 class="card-title"><?= $eval['employee_name'] ?></h5>
                                        <span class="badge bg-primary"><?= $eval['rating'] ?>/100</span>
                                    </div>
                                    <p class="card-text"><?= htmlspecialchars($eval['comments']) ?></p>
                                    <div class="d-flex justify-content-between text-muted">
                                        <small>Evaluated by: <?= $eval['evaluator_name'] ?></small>
                                        <small><?= date('M d, Y H:i', strtotime($eval['evaluation_date'])) ?></small>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">No evaluations found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Single Modal for all evaluations -->
    <div class="modal fade" id="evaluateModal" tabindex="-1" aria-labelledby="evaluateModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="employee_evaluation.php" class="needs-validation" novalidate>
                    <div class="modal-header">
                        <h5 class="modal-title" id="evaluateModalLabel">Evaluate Employee</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="employee_id" id="modalEmployeeId" value="">
                        
                        <div class="mb-3">
                            <label class="form-label">Rating (10-100)</label>
                            <div class="rating-container" id="modalRatingContainer">
                                <?php for ($i = 10; $i <= 100; $i += 10): ?>
                                    <div class="rating-option">
                                        <input type="radio" id="rating_<?= $i ?>" name="rating" value="<?= $i ?>" required>
                                        <label for="rating_<?= $i ?>"><?= $i ?></label>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="invalid-feedback">Please select a rating.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="modalComments" class="form-label">Comments</label>
                            <textarea class="form-control" id="modalComments" name="comments" rows="3" required></textarea>
                            <div class="invalid-feedback">Please provide comments.</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="submit_evaluation" class="btn btn-primary">Save Evaluation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the modal
            const evaluateModal = new bootstrap.Modal(document.getElementById('evaluateModal'));
            
            // Handle evaluate button clicks
            document.querySelectorAll('.evaluate-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const employeeId = this.getAttribute('data-employee-id');
                    const employeeName = this.getAttribute('data-employee-name');
                    const currentRating = this.getAttribute('data-current-rating');
                    const currentComments = this.getAttribute('data-current-comments');
                    
                    // Update modal content
                    document.getElementById('evaluateModalLabel').textContent = `Evaluate ${employeeName}`;
                    document.getElementById('modalEmployeeId').value = employeeId;
                    document.getElementById('modalComments').value = currentComments || '';
                    
                    // Reset all ratings
                    document.querySelectorAll('input[name="rating"]').forEach(radio => {
                        radio.checked = false;
                    });
                    
                    // Set current rating if exists
                    if (currentRating) {
                        document.querySelector(`input[name="rating"][value="${currentRating}"]`).checked = true;
                    }
                    
                    // Show the modal
                    evaluateModal.show();
                    document.body.classList.add('modal-open');
                });
            });
            
            evaluateModal._element.addEventListener('hidden.bs.modal', function() {
                // Remove the backdrop
                const backdrops = document.getElementsByClassName('modal-backdrop');
                while(backdrops[0]) {
                    backdrops[0].parentNode.removeChild(backdrops[0]);
                }
                
                // Remove modal-open class
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
            });

            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            forms.forEach(form => {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
            
            // Clean up backdrop when modal is hidden
            evaluateModal._element.addEventListener('hidden.bs.modal', function() {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.classList.remove('modal-open');
                document.body.style.paddingRight = '';
                document.body.style.overflow = '';
            });
        });
    </script>
</body>
</html>