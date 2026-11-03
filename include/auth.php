<?php
// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: /testsor/login.php");
    exit();
}