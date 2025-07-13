<?php
ob_start();
session_start();

include '../../connection/connect.php';
include '../../login/index.html';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userName = mysqli_real_escape_string($connection, $_POST["username"]);
    $password = mysqli_real_escape_string($connection, $_POST["password"]);

    $sql = "SELECT * FROM signup WHERE username='$userName' AND password='$password'";
    $result = mysqli_query($connection, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $_SESSION["username"] = $userName;

        if ($row["usertype"] == "user") {
            header("Location: ../../user/home/backend/home.php");
        } elseif ($row["usertype"] == "admin") {
            header("Location: ../../admin/home/backend/home.php");
        }
        exit;
    } else {
        echo "<script>alert('Incorrect username or password.'); window.history.back();</script>";
        exit;
    }
}
ob_end_flush();
?>
