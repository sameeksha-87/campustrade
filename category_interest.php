<?php
require_once 'config/db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || !isset($_POST['category_id'])) {
    header("Location: browse.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$category_id = $_POST['category_id'];

try {
    // Save interest in category
    $stmt = $pdo->prepare("INSERT INTO interested (user_id, category_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $category_id]);
    
    // Get category name
    $stmt = $pdo->prepare("SELECT category_name FROM category WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $category = $stmt->fetch();
    
    $_SESSION['success_message'] = "You'll be notified when new items are posted in " . $category['category_name'] . "!";
} catch (PDOException $e) {
    // Already interested in this category
    if ($e->getCode() == 23000) {
        $_SESSION['info_message'] = "You're already subscribed to notifications for this category.";
    } else {
        $_SESSION['error_message'] = "Error subscribing to category: " . $e->getMessage();
    }
}

// Redirect back to index with category filter
header("Location: browse.php?category=" . $category_id);
exit;
?>
