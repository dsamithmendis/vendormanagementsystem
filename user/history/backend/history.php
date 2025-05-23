<?php
ob_start();
session_start();

include '/vendormanagementsystem/user/history/index.html';
include '/vendormanagementsystem/connection/connect.php';
include '/vendormanagementsystem/verify/verifyuser.php';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

$orders = [];

try {
    $query = "SELECT PurchaseOrderID, BuyerID, SellerID, ProductID, product_price, order_date FROM history";
    $stmt = $connection->prepare($query);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($order = $result->fetch_assoc()) {
            $orders[] = $order;
        }
        $stmt->close();
    } else {
        throw new Exception("Failed to prepare the SQL statement.");
    }
} catch (Exception $e) {
    die("Error occurred: " . $e->getMessage());
}
ob_end_flush();
?>
