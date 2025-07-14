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
$message = "";

$stmt = $connection->prepare("SELECT VendorID FROM signup WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$BuyerID = null;
if ($row = $result->fetch_assoc()) {
    $BuyerID = $row['VendorID'];
}
$stmt->close();
function removeProduct($connection, $ProductID, $BuyerID, &$message)
{
    $stmt = $connection->prepare("SELECT ProductID, product_name, product_price FROM purchase WHERE ProductID = ? AND BuyerID = ?");
    $stmt->bind_param("ii", $ProductID, $BuyerID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $stmt->close();

        $stmt = $connection->prepare("DELETE FROM purchase WHERE ProductID = ? AND BuyerID = ?");
        $stmt->bind_param("ii", $ProductID, $BuyerID);
        $stmt->execute();
        $stmt->close();

        $stmt = $connection->prepare("SELECT ProductID FROM products WHERE ProductID = ?");
        $stmt->bind_param("i", $ProductID);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows === 0) {
            $stmt->close();
            $stmt = $connection->prepare("INSERT INTO products (ProductID, product_name, product_price) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $row['ProductID'], $row['product_name'], $row['product_price']);
            $stmt->execute();
        }
        $stmt->close();

        $message = "Product removed successfully.";
    } else {
        $message = "Product not found or does not belong to you.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['removeok'])) {
        removeProduct($connection, intval($_POST['removeok']), $BuyerID, $message);
    }

    if (isset($_POST['order_date']) && !empty($_POST['order_date'])) {
        $order_date = $_POST['order_date'];
        $connection->begin_transaction();
        try {
            $stmt = $connection->prepare("SELECT SUM(product_price) AS total_amount FROM purchase WHERE BuyerID = ?");
            $stmt->bind_param("i", $BuyerID);
            $stmt->execute();
            $result = $stmt->get_result();
            $total_amount = ($row = $result->fetch_assoc()) ? $row['total_amount'] : 0;
            $stmt->close();

            $stmt = $connection->prepare("INSERT INTO orders (BuyerID, order_date, total_amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $BuyerID, $order_date, $total_amount);
            $stmt->execute();
            $orderID = $connection->insert_id;
            $stmt->close();

            $stmt = $connection->prepare("INSERT INTO history (OrderID, PurchaseOrderID, BuyerID, SellerID, ProductID, product_price, order_date)
                SELECT ?, PurchaseOrderID, ?, SellerID, ProductID, product_price, ? FROM purchase WHERE BuyerID = ?");
            $stmt->bind_param("iisi", $orderID, $BuyerID, $order_date, $BuyerID);
            $stmt->execute();
            $stmt->close();

            $stmt = $connection->prepare("DELETE FROM purchase WHERE BuyerID = ?");
            $stmt->bind_param("i", $BuyerID);
            $stmt->execute();

            $connection->commit();
            $message = "Order confirmed. Total: Rs. " . number_format($total_amount, 2);
        } catch (Exception $e) {
            $connection->rollback();
            $message = "Error: " . $e->getMessage();
        }
    }
}

$tableRows = '';
$stmt = $connection->prepare("SELECT * FROM purchase WHERE BuyerID = ?");
$stmt->bind_param("i", $BuyerID);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $tableRows .= "<table>
        <tr>
            <th>PRODUCT ID</th>
            <th>BUYER ID</th>
            <th>SELLER ID</th>
            <th>PRODUCT NAME</th>
            <th>PRICE</th>
            <th>ACTION</th>
        </tr>";
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
    $tableRows = "<div class='no-records' style='color:white; text-align:center;'>No records found.</div>";
}
$stmt->close();

$template = file_get_contents("../index.html");
$template = str_replace("%product_table%", $tableRows, $template);

if (str_starts_with($message, "Order confirmed")) {
    $template = str_replace("%order_message%", "<strong>$message</strong>", $template);
} else {
    $template = str_replace("%order_message%", "", $template);
}

if (
    !empty($message) &&
    !str_starts_with($message, "Product removed successfully.") &&
    !str_starts_with($message, "Order confirmed")
) {
    $js_message = json_encode($message);
    $template = str_replace("</body>", "<script>alert({$js_message});</script></body>", $template);
}

echo $template;

mysqli_close($connection);
ob_end_flush();
?>