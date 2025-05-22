<?php
ob_start();
session_start();

include '/vendormanagementsystem/connection/connect.php';
include '/vendormanagementsystem/verify/verifyuser.php';
include '/vendormanagementsystem/user/buyproducts/index.html';

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addok'])) {
    $addok = intval($_POST['addok']);
    
    $stmt = mysqli_prepare($connection, "SELECT ProductID, product_name, product_price, SellerID FROM products WHERE ProductID = ?");
    mysqli_stmt_bind_param($stmt, "i", $addok);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        $stmtInsert = mysqli_prepare($connection, "INSERT INTO purchase (ProductID, product_name, product_price, SellerID) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtInsert, "isdi", $row['ProductID'], $row['product_name'], $row['product_price'], $row['SellerID']);
        
        if (mysqli_stmt_execute($stmtInsert)) {
            $message = "Product added to cart successfully.";
        } else {
            $message = "Error adding product: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmtInsert);
    } else {
        $message = "Product not found.";
    }
    mysqli_stmt_close($stmt);
}
ob_end_flush();
?>
