<?php
ob_start();
session_start();

include '/vendormanagementsystem/connection/connection.php';
include '/vendormanagementsystem/user/storeproduct/index.html';
include '/vendormanagementsystem/verify/verifyuser.php';

if (!isset($_SESSION["username"])) {
    die("Unauthorized access.");
}

if (!$connection) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (isset($_POST['save'])) {
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $vendorid = $_SESSION['id'];

    $stmt = $conn->prepare("INSERT INTO product (ProductName, ProductPrice, VendorID) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $product_name, $product_price, $vendorid);
    $stmt->execute();
    $stmt->close();

    echo "<div class='message'>Product added successfully.</div>";
}

if (isset($_POST['edit'])) {
    $ProductID = $_POST['ProductID'];
    $product_price = $_POST['product_price'];
    $vendorid = $_SESSION['id'];

    $stmt = $conn->prepare("UPDATE product SET ProductPrice = ? WHERE ProductID = ? AND VendorID = ?");
    $stmt->bind_param("dii", $product_price, $ProductID, $vendorid);
    $stmt->execute();
    $stmt->close();

    echo "<div class='message'>Product updated successfully.</div>";
}

if (isset($_POST['remove'])) {
    $ProductID = $_POST['remove'];
    $vendorid = $_SESSION['id'];

    $stmt = $conn->prepare("DELETE FROM product WHERE ProductID = ? AND VendorID = ?");
    $stmt->bind_param("ii", $ProductID, $vendorid);
    $stmt->execute();
    $stmt->close();

    echo "<div class='message'>Product removed successfully.</div>";
}

$vendorid = $_SESSION['id'];
$stmt = $conn->prepare("SELECT ProductID, ProductName, ProductPrice FROM product WHERE VendorID = ?");
$stmt->bind_param("i", $vendorid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "<div class='container'><h2>PRODUCT LIST</h2>";
    echo "<table><tr><th>ID</th><th>NAME</th><th>PRICE (RS.)</th><th>REMOVE</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['ProductID']}</td>
                <td>{$row['ProductName']}</td>
                <td>{$row['ProductPrice']}</td>
                <td>
                    <form method='POST' action='store.php'>
                        <button class='btn btn-secondary' type='submit' name='remove' value='{$row['ProductID']}'>Remove</button>
                    </form>
                </td>
              </tr>";
    }
    echo "</table></div>";
} else {
    echo "<div class='no-records'><center><h2>NO PRODUCTS ADDED</h2></center></div>";
}
$stmt->close();
$conn->close();
ob_end_flush();
?>
