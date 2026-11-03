<?php
require __DIR__ . '\..\include\config.php'; 
session_start();

// Handle report submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_report'])) {
    $recipient_id = $_POST['recipient_id'];
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $priority = $_POST['priority'];
    
    $stmt = $conn->prepare("INSERT INTO reports (sender_id, recipient_id, subject, message, priority) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $_SESSION['user_id'], $recipient_id, $subject, $message, $priority);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Report sent successfully!";
    } else {
        $_SESSION['error'] = "Error sending report.";
    }
    
    header("Location: report.php");
    exit();
}

// Handle report status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $report_id = $_POST['report_id'];
    $status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE reports SET status = ?, status_changed_date = NOW() WHERE report_id = ?");
    $stmt->bind_param("si", $status, $report_id);
    $stmt->execute();
    
    $_SESSION['success'] = "Report status updated!";
    header("Location: report.php");
    exit();
}

// Get users for dropdown (based on role)
$users = [];
if ($_SESSION['role'] === 'manager') {
    $users = $conn->query("SELECT user_id, username, role FROM users WHERE role IN ('employee', 'supplier')");
} else {
    // Employees and suppliers can only send to manager
    $users = $conn->query("SELECT user_id, username, role FROM users WHERE role = 'manager'");
}

// Get reports for current user
$received_reports = $conn->query("
    SELECT r.*, u.username as sender_name
    FROM reports r
    JOIN users u ON r.sender_id = u.user_id
    WHERE r.recipient_id = {$_SESSION['user_id']}
    ORDER BY r.sent_date DESC
");

$sent_reports = $conn->query("
    SELECT r.*, u.username as recipient_name
    FROM reports r
    JOIN users u ON r.recipient_id = u.user_id
    WHERE r.sender_id = {$_SESSION['user_id']}
    ORDER BY r.sent_date DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warehouse Reporting System</title>
    <style>
        <?php require __DIR__ . '\styles\RE.css'; ?>
    </style>
</head>
<body>
    <div class="report-container">
        <!-- Left Panel - Navigation -->
        <div class="left-panel">
            <div class="logo">WMS Reports</div>
            
            <div class="nav-menu">
                <a href="#" class="nav-item active" onclick="showPanel('send')">Send Report</a>
                <a href="#" class="nav-item" onclick="showPanel('received')">Received Reports</a>
                <a href="#" class="nav-item" onclick="showPanel('sent')">Sent Reports</a>
            </div>
            
            <a href="supplier_homepage.php" class="back-btn">← Back to Dashboard</a>
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
            
            <!-- Send Report Panel -->
            <div id="send-panel" class="content-panel">
                <h2 class="panel-title">Send New Report</h2>
                
                <div class="report-form">
                    <form method="POST">
                        <div class="form-group">
                            <label class="form-label">Recipient</label>
                            <select name="recipient_id" class="form-control" required>
                                <option value="">Select Recipient</option>
                                <?php 
                                $users->data_seek(0); // Reset pointer
                                while ($user = $users->fetch_assoc()): ?>
                                    <option value="<?= $user['user_id'] ?>">
                                        <?= htmlspecialchars($user['username']) ?> (<?= ucfirst($user['role']) ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input type="text" name="subject" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Priority</label>
                            <select name="priority" class="form-control" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="8" required></textarea>
                        </div>
                        
                        <button type="submit" name="send_report" class="btn btn-primary">Send Report</button>
                    </form>
                </div>
            </div>
            
            <!-- Received Reports Panel -->
            <div id="received-panel" class="content-panel" style="display:none;">
                <h2 class="panel-title">Received Reports</h2>
                
                <?php if ($received_reports->num_rows > 0): ?>
                    <div class="report-list">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>From</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $received_reports->data_seek(0); // Reset pointer
                                while ($report = $received_reports->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($report['sender_name']) ?></td>
                                        <td><?= htmlspecialchars($report['subject']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $report['priority'] ?>">
                                                <?= ucfirst($report['priority']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i a', strtotime($report['sent_date'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= str_replace('_', '-', $report['status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $report['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="action-btn" onclick="viewReport(
                                                '<?= htmlspecialchars($report['subject']) ?>',
                                                '<?= htmlspecialchars($report['message']) ?>',
                                                <?= $report['report_id'] ?>,
                                                '<?= $report['status'] ?>'
                                            )">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No reports have been received yet.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sent Reports Panel -->
            <div id="sent-panel" class="content-panel" style="display:none;">
                <h2 class="panel-title">Sent Reports</h2>
                
                <?php if ($sent_reports->num_rows > 0): ?>
                    <div class="report-list">
                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>To</th>
                                    <th>Subject</th>
                                    <th>Priority</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $sent_reports->data_seek(0); // Reset pointer
                                while ($report = $sent_reports->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($report['recipient_name']) ?></td>
                                        <td><?= htmlspecialchars($report['subject']) ?></td>
                                        <td>
                                            <span class="badge badge-<?= $report['priority'] ?>">
                                                <?= ucfirst($report['priority']) ?>
                                            </span>
                                        </td>
                                        <td><?= date('M j, Y g:i a', strtotime($report['sent_date'])) ?></td>
                                        <td>
                                            <span class="badge badge-<?= str_replace('_', '-', $report['status']) ?>">
                                                <?= ucfirst(str_replace('_', ' ', $report['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="#" class="action-btn" onclick="viewReport(
                                                '<?= htmlspecialchars($report['subject']) ?>',
                                                '<?= htmlspecialchars($report['message']) ?>'
                                            )">View</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No reports have been sent yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Report View Modal -->
    <div id="reportModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000; justify-content:center; align-items:center;">
        <div style="background:white; border-radius:8px; width:90%; max-width:700px; max-height:90vh; overflow:auto; padding:25px;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h3 id="modalSubject" style="margin:0;"></h3>
                <button onclick="closeModal()" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
            </div>
            
            <div id="modalMessage" style="margin-bottom:30px; white-space:pre-line;"></div>
            
            <form id="statusForm" method="POST" style="margin-top:20px;">
                <input type="hidden" name="report_id" id="modalReportId">
                <div style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:10px; font-weight:500;">Update Status</label>
                    <div class="status-form">
                        <button type="button" class="status-btn" onclick="setStatus('not_complete')">Not Complete</button>
                        <button type="button" class="status-btn" onclick="setStatus('ongoing')">Ongoing</button>
                        <button type="button" class="status-btn" onclick="setStatus('complete')">Complete</button>
                    </div>
                    <input type="hidden" name="status" id="selectedStatus">
                </div>
                
                <div style="display:flex; justify-content:flex-end; gap:10px;">
                    <button type="button" onclick="closeModal()" style="padding:10px 20px; background:#eee; border:none; border-radius:6px; cursor:pointer;">Close</button>
                    <button type="submit" name="update_status" style="padding:10px 20px; background:var(--primary-color); color:white; border:none; border-radius:6px; cursor:pointer;">Update Status</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Show different panels
        function showPanel(panelName) {
            document.querySelectorAll('.content-panel').forEach(panel => {
                panel.style.display = 'none';
            });
            document.getElementById(panelName + '-panel').style.display = 'block';
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            event.target.classList.add('active');
        }
        
        // View report in modal
        function viewReport(subject, message, reportId = null, currentStatus = null) {
            document.getElementById('modalSubject').textContent = subject;
            document.getElementById('modalMessage').textContent = message;
            
            if (reportId) {
                document.getElementById('modalReportId').value = reportId;
                document.getElementById('statusForm').style.display = 'block';
                
                // Highlight current status
                if (currentStatus) {
                    setStatus(currentStatus);
                }
            } else {
                document.getElementById('statusForm').style.display = 'none';
            }
            
            document.getElementById('reportModal').style.display = 'flex';
        }
        
        // Close modal
        function closeModal() {
            document.getElementById('reportModal').style.display = 'none';
        }
        
        // Set status in form
        function setStatus(status) {
            document.getElementById('selectedStatus').value = status;
            
            // Update button styles
            document.querySelectorAll('.status-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            event.target.classList.add('active');
        }
        
        // Initialize with send panel visible
        document.addEventListener('DOMContentLoaded', function() {
            showPanel('send');
        });
    </script>
</body>
</html>