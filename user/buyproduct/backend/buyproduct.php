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

$product_table = "";
$result = mysqli_query($connection, "SELECT * FROM products");
if ($result && mysqli_num_rows($result) > 0) {
    $product_table .= "<form method='POST' action='/vendormanagementsystem/user/buyproduct/backend/buyproduct.php'><table>
        <tr><th>PRODUCT ID</th><th>NAME</th><th>PRICE (RS.)</th><th>ORDER</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $product_table .= "<tr>
            <td>" . htmlspecialchars($row['ProductID']) . "</td>
            <td>" . htmlspecialchars($row['product_name']) . "</td>
            <td>" . htmlspecialchars($row['product_price']) . "</td>
            <td>
                <form method='POST' action='/vendormanagementsystem/user/buyproduct/backend/buyproduct.php'>
                    <input type='hidden' name='addok' value='" . htmlspecialchars($row['ProductID']) . "'>
                    <button type='submit' class='btn btn-primary'>Add to cart</button>
                </form>
            </td>
        </tr>";
    }
    $product_table .= "</table>";
} else {
    $product_table = "<div class='no-records'>No records found.</div>";
}

$template = file_get_contents("../index.html");
$template = str_replace("%message%", $message ? "<div class='message'>$message</div>" : "", $template);
$template = str_replace("%product_table%", $product_table, $template);

echo $template;

mysqli_close($connection);
ob_end_flush();
?>