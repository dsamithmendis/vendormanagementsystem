<?php
ob_start();
session_start();

include 'connect.php';
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

include 'verifyuser.php';
if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

include 'home.html';

ob_end_flush();
?>
