<?php
ob_start();
session_start();

include '../../home/index.html';
include '../../../connection/connect.php';
include '../../../verify/verifyuser.php';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

$username = $_SESSION["username"];

ob_end_flush();
?>