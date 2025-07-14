<?php
session_start();
include '../../../connection/connect.php';

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];
    $contact = $_POST["contact"];
    $username = $_SESSION["username"];

    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../../uploads/profile_images/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileTmpPath = $_FILES['profile_image']['tmp_name'];
        $fileName = basename($_FILES['profile_image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $fileTmpPath);
        finfo_close($finfo);

        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];

        if (in_array($mimeType, $allowedMimeTypes)) {
            $newFileName = $username . '_profile.jpg';
            $destination = $uploadDir . $newFileName;

            switch ($mimeType) {
                case 'image/jpeg':
                    $image = imagecreatefromjpeg($fileTmpPath);
                    break;
                case 'image/png':
                    $image = imagecreatefrompng($fileTmpPath);
                    break;
                case 'image/gif':
                    $image = imagecreatefromgif($fileTmpPath);
                    break;
                default:
                    echo "<script>alert('Unsupported image format.'); window.history.back();</script>";
                    exit;
            }

            if ($image) {
                imagejpeg($image, $destination, 90); // Quality: 90
                imagedestroy($image);

                $relativePath = 'uploads/profile_images/' . $newFileName;

                $stmt = $connection->prepare("UPDATE signup SET email_address = ?, contact_information = ?, profile_image = ? WHERE username = ?");
                $stmt->bind_param("ssss", $email, $contact, $relativePath, $username);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "<script>alert('Failed to process image.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Only JPG, PNG, or GIF images are allowed.'); window.history.back();</script>";
            exit;
        }
    } else {
        $stmt = $connection->prepare("UPDATE signup SET email_address = ?, contact_information = ? WHERE username = ?");
        $stmt->bind_param("sss", $email, $contact, $username);
        $stmt->execute();
        $stmt->close();
    }

    if (!empty($_POST["current_password"]) && !empty($_POST["new_password"]) && !empty($_POST["confirm_password"])) {
        $current_password = $_POST["current_password"];
        $new_password = $_POST["new_password"];
        $confirm_password = $_POST["confirm_password"];

        $stmt = $connection->prepare("SELECT password FROM signup WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        $stmt->close();

        if ($current_password === $stored_password) {
            if ($new_password === $confirm_password) {
                $stmt = $connection->prepare("UPDATE signup SET password = ? WHERE username = ?");
                $stmt->bind_param("ss", $new_password, $username);
                $stmt->execute();
                $stmt->close();
            } else {
                echo "<script>alert('New passwords do not match.'); window.history.back();</script>";
                exit;
            }
        } else {
            echo "<script>alert('Incorrect current password.'); window.history.back();</script>";
            exit;
        }
    }

    header("Location: home.php");
    exit;
}
?>