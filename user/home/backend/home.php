<?php
ob_start();
session_start();

include '/vendormanagementsystem/user/home/index.html';
include '/vendormanagementsystem/connection/connect.php';
include '/vendormanagementsystem/verify/verifyuser.php';

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

ob_end_flush();
?>
