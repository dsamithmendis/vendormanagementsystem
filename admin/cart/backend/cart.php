<?php
ob_start();
session_start();

include '/vendormanagementsystem/user/cart/index.html';
include '/vendormanagementsystem/connection/connect.php';
include '/vendormanagementsystem/verify/verifyuser.php';

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

$BuyerID = null;
$message = '';

if (isset($_SESSION["username"])) {
    $username = $_SESSION["username"];
    $stmt = $connection->prepare("SELECT `VendorID` FROM `signup` WHERE `username` = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $BuyerID = $row['VendorID'];
    } else {
        $message = "Error retrieving VendorID.";
    }
    $stmt->close();
}

function removeProduct($connection, $ProductID)
{
    global $message;
    $stmt = $connection->prepare("SELECT `ProductID`, `product_name`, `product_price` FROM `purchase` WHERE `ProductID` = ?");
    $stmt->bind_param("i", $ProductID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt = $connection->prepare("DELETE FROM `purchase` WHERE `ProductID` = ?");
        $stmt->bind_param("i", $ProductID);
        $stmt->execute();

        $stmt = $connection->prepare("SELECT `ProductID` FROM `products` WHERE `ProductID` = ?");
        $stmt->bind_param("i", $ProductID);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 0) {
            $stmt = $connection->prepare("INSERT INTO `products` (`ProductID`, `product_name`, `product_price`) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $row['ProductID'], $row['product_name'], $row['product_price']);
            $stmt->execute();
        }
        $message = "Product removed successfully.";
    } else {
        $message = "Product not found.";
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['removeok'])) {
        removeProduct($connection, intval($_POST['removeok']));
    }

    if (isset($_POST['order_date']) && !empty($_POST['order_date'])) {
        $order_date = $_POST['order_date'];
        $connection->begin_transaction();

        try {
            $stmt = $connection->prepare("INSERT INTO `history` (`PurchaseOrderID`, `BuyerID`, `SellerID`, `ProductID`, `product_price`, `order_date`)
                SELECT `PurchaseOrderID`, ?, `SellerID`, `ProductID`, `product_price`, ? FROM `purchase`");
            $stmt->bind_param("is", $BuyerID, $order_date);
            $stmt->execute();

            $result = $connection->query("SELECT SUM(product_price) AS total_amount FROM purchase");
            $total_amount = ($row = $result->fetch_assoc()) ? $row['total_amount'] : 0;

            $stmt = $connection->prepare("INSERT INTO `orders` (`BuyerID`, `order_date`, `total_amount`) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $BuyerID, $order_date, $total_amount);
            $stmt->execute();

            $connection->query("DELETE FROM `purchase`");

            $connection->commit();
            $message = "Order confirmed. Total amount spent: Rs. " . number_format($total_amount, 2);
        } catch (Exception $e) {
            $connection->rollback();
            $message = "Error occurred: " . $e->getMessage();
        }
    }
}

$result = $connection->query("SELECT * FROM `purchase`");
$purchase_data = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $purchase_data[] = $row;
    }
}

$connection->close();
ob_end_flush();
?>
