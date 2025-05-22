<?php
include '/vendormanagementsystem/connection/connect.php';
include '/vendormanagementsystem//signup/signup';
if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userName = $_POST['username'];
    $contactInformation = $_POST['contact_information'];
    $emailAddress = $_POST['email_address'];
    $password = $_POST['password'];

    $stmt = $connection->prepare("SELECT * FROM signup WHERE username = ? OR email_address = ? OR contact_information = ?");
    $stmt->bind_param("sss", $userName, $emailAddress, $contactInformation);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('User already exists!'); window.history.back();</script>";
    } else {
        $stmt = $connection->prepare("INSERT INTO signup (username, contact_information, email_address, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $userName, $contactInformation, $emailAddress, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Signup successful!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Signup failed.'); window.history.back();</script>";
        }
    }

    $stmt->close();
    $connection->close();
}
?>
