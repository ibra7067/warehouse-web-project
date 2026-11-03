<?php
session_start();
session_unset();
session_destroy();
header("Location: /warehouse_project/index.php");
exit();
?>