<?php

if (!isset($_SESSION["username"])) {
    header("location:login.php");
    exit;
}

$username = $_SESSION["username"];

$stmt = $connection->prepare("SELECT usertype FROM signup WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $_SESSION["usertype"] = $row["usertype"];
} 
else {
    exit("User not found.");
}

if ($_SESSION["usertype"] !== "admin") {
    exit("Access Denied.");
}

?>
