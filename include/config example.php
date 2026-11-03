<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "databaseexample";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Helper functions
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function getCurrentUserRole() {
    return $_SESSION['role'] ?? null;
}

 
 
?>