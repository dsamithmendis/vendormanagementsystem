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
$order_table = "";

$stmtVendor = $connection->prepare("SELECT VendorID FROM signup WHERE username = ?");
$stmtVendor->bind_param("s", $username);
$stmtVendor->execute();
$resultVendor = $stmtVendor->get_result();
$rowVendor = $resultVendor->fetch_assoc();
$vendorID = $rowVendor['VendorID'];
$stmtVendor->close();

$query = "SELECT PurchaseOrderID, OrderID, BuyerID, SellerID, ProductID, product_price, order_date 
          FROM history 
          WHERE BuyerID = ?";

$stmt = $connection->prepare($query);
$stmt->bind_param("i", $vendorID);

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order_table .= "<table>
            <tr>
                <th>PURCHASE ID</th>
                <th>ORDER ID</th>
                <th>BUYER ID</th>
                <th>SELLER ID</th>
                <th>PRODUCT ID</th>
                <th>PRICE (RS.)</th>
                <th>ORDER DATE</th>
            </tr>";
        while ($row = $result->fetch_assoc()) {
            $order_table .= "<tr>
                <td>" . htmlspecialchars($row['PurchaseOrderID']) . "</td>
                <td>" . htmlspecialchars($row['OrderID']) . "</td>
                <td>" . htmlspecialchars($row['BuyerID']) . "</td>
                <td>" . htmlspecialchars($row['SellerID']) . "</td>
                <td>" . htmlspecialchars($row['ProductID']) . "</td>
                <td>" . htmlspecialchars($row['product_price']) . "</td>
                <td>" . htmlspecialchars($row['order_date']) . "</td>
            </tr>";
        }
        $order_table .= "</table>";
    } else {
        $order_table = "<div class='no-records'>No records found.</div>";
    }

    $stmt->close();
} else {
    $order_table = "<div class='no-records'>Failed to prepare the SQL statement.</div>";
}

$template = file_get_contents("../index.html");
$template = str_replace("%order_table%", $order_table, $template);

echo $template;

mysqli_close($connection);
ob_end_flush();
?>