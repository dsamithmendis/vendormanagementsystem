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
$BuyerID = null;
$message = '';

$stmt = $connection->prepare("SELECT VendorID FROM signup WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($row = $result->fetch_assoc()) {
    $BuyerID = $row['VendorID'];
}
$stmt->close();

function removeProduct($connection, $ProductID, &$message)
{
    $stmt = $connection->prepare("SELECT ProductID, product_name, product_price FROM purchase WHERE ProductID = ?");
    $stmt->bind_param("i", $ProductID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt = $connection->prepare("DELETE FROM purchase WHERE ProductID = ?");
        $stmt->bind_param("i", $ProductID);
        $stmt->execute();

        $stmt = $connection->prepare("SELECT ProductID FROM products WHERE ProductID = ?");
        $stmt->bind_param("i", $ProductID);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 0) {
            $stmt = $connection->prepare("INSERT INTO products (ProductID, product_name, product_price) VALUES (?, ?, ?)");
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
        removeProduct($connection, intval($_POST['removeok']), $message);
    }

    if (isset($_POST['order_date']) && !empty($_POST['order_date'])) {
        $order_date = $_POST['order_date'];
        $connection->begin_transaction();
        try {
            $stmt = $connection->prepare("INSERT INTO history (PurchaseOrderID, BuyerID, SellerID, ProductID, product_price, order_date)
                SELECT PurchaseOrderID, ?, SellerID, ProductID, product_price, ? FROM purchase");
            $stmt->bind_param("is", $BuyerID, $order_date);
            $stmt->execute();

            $result = $connection->query("SELECT SUM(product_price) AS total_amount FROM purchase");
            $total_amount = ($row = $result->fetch_assoc()) ? $row['total_amount'] : 0;

            $stmt = $connection->prepare("INSERT INTO orders (BuyerID, order_date, total_amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $BuyerID, $order_date, $total_amount);
            $stmt->execute();

            $connection->query("DELETE FROM purchase");
            $connection->commit();
            $message = "Order confirmed. Total: Rs. " . number_format($total_amount, 2);
        } catch (Exception $e) {
            $connection->rollback();
            $message = "Error: " . $e->getMessage();
        }
    }
}

$orderForm = <<<HTML
<form method='POST' class='order-form'>
    <label for='order_date'>Select Order Date:</label>
    <input type='date' name='order_date' id='order_date' required>
    <button type='submit' name='confirm' class='btn btn-primary'>Confirm</button>
</form>
HTML;

$tableRows = '';
$result = $connection->query("SELECT * FROM purchase");
if ($result->num_rows > 0) {
    $tableRows .= "<table><tr><th>PRODUCT ID</th><th>BUYER ID</th><th>SELLER ID</th><th>PRODUCT NAME</th><th>PRICE</th><th>ACTION</th></tr>";
    while ($row = $result->fetch_assoc()) {
        $pid = htmlspecialchars($row['ProductID']);
        $sid = htmlspecialchars($row['SellerID']);
        $name = htmlspecialchars($row['product_name']);
        $price = htmlspecialchars($row['product_price']);

        $tableRows .= "<tr>
            <td>$pid</td>
            <td>$BuyerID</td>
            <td>$sid</td>
            <td>$name</td>
            <td>$price</td>
            <td>
                <form method='POST'>
                    <input type='hidden' name='removeok' value='$pid'>
                    <button type='submit' class='btn btn-danger'>Remove</button>
                </form>
            </td>
        </tr>";
    }
    $tableRows .= "</table>";
} else {
    $tableRows = "<div class='no-records'>No records found.</div>";
}

$page = file_get_contents('../index.html');
$page = str_replace('<!--MESSAGE-->', $message ? "<div class='message'>{$message}</div>" : '', $page);
$page = str_replace('<!--ORDER_FORM-->', $orderForm, $page);
$page = str_replace('<!--PRODUCT_TABLE-->', $tableRows, $page);

echo $page;
?>