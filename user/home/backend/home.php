<?php
ob_start();
session_start();

include '../../home/index.html';
include '../../../connection/connect.php';
include '../../../verify/verifyuser.php';

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

ob_end_flush();
?>