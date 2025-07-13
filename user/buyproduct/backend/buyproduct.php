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

$buyerID = null;
$stmtUser = mysqli_prepare($connection, "SELECT VendorID FROM signup WHERE username = ?");
mysqli_stmt_bind_param($stmtUser, "s", $username);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);

if ($rowUser = mysqli_fetch_assoc($resultUser)) {
    $buyerID = $rowUser['VendorID'];
} else {
    die("Buyer ID not found for the current user.");
}
mysqli_stmt_close($stmtUser);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addok'])) {
    $addok = intval($_POST['addok']);

    $stmt = mysqli_prepare($connection, "SELECT ProductID, product_name, product_price, SellerID FROM products WHERE ProductID = ? AND SellerID != ?");
    mysqli_stmt_bind_param($stmt, "ii", $addok, $buyerID);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $stmtInsert = mysqli_prepare($connection, "INSERT INTO purchase (ProductID, product_name, product_price, BuyerID, SellerID) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmtInsert, "isdii", $row['ProductID'], $row['product_name'], $row['product_price'], $buyerID, $row['SellerID']);

        if (mysqli_stmt_execute($stmtInsert)) {
            $message = "Product added to cart successfully.";
        } else {
            $message = "Error adding product: " . mysqli_error($connection);
        }
        mysqli_stmt_close($stmtInsert);
    } else {
        $message = "Product not found or you cannot add your own product.";
    }
    mysqli_stmt_close($stmt);
}

$query = "
    SELECT * FROM products 
    WHERE SellerID != ? 
      AND ProductID NOT IN (
          SELECT ProductID FROM purchase WHERE BuyerID = ?
      )
";
$stmtProd = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmtProd, "ii", $buyerID, $buyerID);
mysqli_stmt_execute($stmtProd);
$result = mysqli_stmt_get_result($stmtProd);

$product_table = "";

if ($result && mysqli_num_rows($result) > 0) {
    $product_table .= "<table>
        <tr><th>PRODUCT ID</th><th>NAME</th><th>PRICE (RS.)</th><th>ORDER</th></tr>";
    while ($row = mysqli_fetch_assoc($result)) {
        $product_table .= "<tr>
            <td>" . htmlspecialchars($row['ProductID']) . "</td>
            <td>" . htmlspecialchars($row['product_name']) . "</td>
            <td>" . htmlspecialchars($row['product_price']) . "</td>
            <td>
                <form method='POST' action='/vendormanagementsystem/user/buyproduct/backend/buyproduct.php' style='display:inline;'>
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
$template = str_replace("%product_table%", $product_table, $template);

if ($message) {
    $js_message = json_encode($message);
    $template = str_replace("</body>", "<script>alert({$js_message});</script></body>", $template);
}

echo $template;

mysqli_close($connection);
ob_end_flush();
?>
