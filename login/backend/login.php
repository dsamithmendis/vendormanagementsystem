<?php
session_start();
include 'connection/connect.php';

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
            header("Location: userhome.php");
        } elseif ($row["usertype"] == "admin") {
            header("Location: adminhome.php");
        }
        exit;
    } else {
        $_SESSION["login_error"] = "Incorrect username or password.";
        header("Location: loginform.php");
        exit;
    }
}
?>
