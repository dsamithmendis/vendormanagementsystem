<?php
ob_start();
session_start();

include '../../../connection/connect.php';
include '../../../verify/verifyuser.php';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

$username = $_SESSION["username"];

$query = "SELECT username, usertype, contact_information, email_address, VendorID FROM signup WHERE username = ?";
$stmt = $connection->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("User not found.");
}

$user = $result->fetch_assoc();
$stmt->close();

$template = file_get_contents("../index.html");

$template = str_replace("{{username}}", htmlspecialchars($user['username']), $template);
$template = str_replace("{{email}}", htmlspecialchars($user['email_address']), $template);
$template = str_replace("{{usertype}}", htmlspecialchars($user['usertype']), $template);
$template = str_replace("{{contact}}", htmlspecialchars($user['contact_information']), $template);
$template = str_replace("{{vendorid}}", htmlspecialchars($user['VendorID']), $template);

echo $template;

$connection->close();
ob_end_flush();
?>
