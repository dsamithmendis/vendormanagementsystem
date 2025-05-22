<?php
ob_start();
session_start();

include '/vendormanagementsystem/user/home/index';
include '/vendormanagementsystem/connection/connect.php';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

include '/vendormanagementsystem/verify/verifyuser.php';
if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

ob_end_flush();
?>
