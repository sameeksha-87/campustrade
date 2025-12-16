<?php
require_once 'config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $user_id = $_SESSION['user_id'];

    // Verify ownership
    $stmt = $pdo->prepare("SELECT seller_id FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if ($product && $product['seller_id'] == $user_id) {
        // Soft delete the product
        $stmt = $pdo->prepare("UPDATE products SET is_deleted = TRUE WHERE product_id = ?");
        if ($stmt->execute([$product_id])) {
            // Redirect to home with success message (could be improved with a session flash message)
            header("Location: browse.php?msg=Product deleted successfully");
            exit;
        } else {
            // Handle error
            echo "Error deleting product.";
        }
    } else {
        echo "Unauthorized action.";
    }
} else {
    header("Location: browse.php");
    exit;
}
?>
