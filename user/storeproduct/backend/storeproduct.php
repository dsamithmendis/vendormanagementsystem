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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['save'])) {
        $product_name = $_POST['product_name'];
        $product_price = floatval($_POST['product_price']);

        if (empty($product_name) || $product_price <= 0) {
            $message = "Please provide a valid product name and price.";
        } else {
            $stmt = $connection->prepare("SELECT VendorID FROM signup WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $SellerID = $result->fetch_assoc()['VendorID'] ?? null;

            if (!$SellerID) {
                $message = "Vendor not found.";
            } else {
                $check = $connection->prepare("SELECT * FROM products WHERE product_name = ? AND product_price = ?");
                $check->bind_param("sd", $product_name, $product_price);
                $check->execute();
                if ($check->get_result()->num_rows == 0) {
                    $insert = $connection->prepare("INSERT INTO products (SellerID, product_name, product_price) VALUES (?, ?, ?)");
                    $insert->bind_param("isd", $SellerID, $product_name, $product_price);
                    $message = $insert->execute() ? "New product added successfully." : "Error inserting product: " . $insert->error;
                } else {
                    $message = "Product already exists.";
                }
            }
        }
    }

    if (isset($_POST['edit'])) {
        $ProductID = intval($_POST['ProductID']);
        $product_price = floatval($_POST['product_price']);

        $stmt = $connection->prepare("SELECT VendorID FROM signup WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $SellerID = $stmt->get_result()->fetch_assoc()['VendorID'] ?? null;

        if ($SellerID) {
            $check = $connection->prepare("SELECT * FROM products WHERE ProductID = ? AND SellerID = ?");
            $check->bind_param("ii", $ProductID, $SellerID);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                $update = $connection->prepare("UPDATE products SET product_price = ? WHERE ProductID = ? AND SellerID = ?");
                $update->bind_param("dii", $product_price, $ProductID, $SellerID);
                $message = $update->execute() ? "Product price updated successfully." : "Error updating product price: " . $update->error;
            } else {
                $message = "Unauthorized or product does not exist.";
            }
        } else {
            $message = "Vendor not found.";
        }
    }

    if (isset($_POST['remove'])) {
        $product_id = intval($_POST['product_id']);
        $delete = $connection->prepare("DELETE FROM products WHERE ProductID = ?");
        $delete->bind_param("i", $product_id);
        $message = $delete->execute() ? "Product has been removed successfully." : "Failed to remove the product.";
    }
}

$SellerID = null;
$stmt = $connection->prepare("SELECT VendorID FROM signup WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$SellerID = $result->fetch_assoc()['VendorID'] ?? null;

$table_html = "";
if ($SellerID) {
    $product_query = $connection->prepare("SELECT * FROM products WHERE SellerID = ?");
    $product_query->bind_param("i", $SellerID);
    $product_query->execute();
    $result = $product_query->get_result();

    if ($result->num_rows > 0) {
        $table_html .= "<table><tr><th>SELLER ID</th><th>PRODUCT ID</th><th>PRODUCT NAME</th><th>PRICE (RS.)</th><th>ACTION</th></tr>";
        while ($row = $result->fetch_assoc()) {
            $table_html .= "<tr>
                <td>{$row['SellerID']}</td>
                <td>{$row['ProductID']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['product_price']}</td>
                <td>
                    <form action='/vendormanagementsystem/user/storeproduct/backend/storeproduct.php' method='POST'>
                        <input type='hidden' name='product_id' value='{$row['ProductID']}'>
                        <button type='submit' class='btn btn-danger' name='remove'>Remove</button>
                    </form>
                </td>
            </tr>";
        }
        $table_html .= "</table>";
    } else {
        $table_html = "<div class='no-records'>No records found.</div>";
    }
}

$template = file_get_contents("../index.html");
$template = str_replace("%product_table%", $table_html, $template);

if ($message && stripos($message, "success") === false) {
    $js_message = json_encode($message);
    $template = str_replace("</body>", "<script>alert({$js_message});</script></body>", $template);
}

echo $template;

mysqli_close($connection);
ob_end_flush();
?>