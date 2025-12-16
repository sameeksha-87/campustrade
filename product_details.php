<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: browse.php");
    exit;
}

$product_id = $_GET['id'];

// Fetch product details
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, u.username as seller_name, u.email as seller_email, u.phone as seller_phone,
    r.rent_price, r.start_date, r.end_date, r.terms
    FROM products p 
    JOIN users u ON p.seller_id = u.user_id
    LEFT JOIN category c ON p.category_id = c.category_id 
    LEFT JOIN rent_lend_details r ON p.product_id = r.product_id
    WHERE p.product_id = ?
");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo "<div class='container'><div class='alert alert-error'>Product not found.</div></div>";
    include 'includes/footer.php';
    exit;
}

// Fetch images
$stmt = $pdo->prepare("SELECT url FROM product_images WHERE product_id = ?");
$stmt->execute([$product_id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle "Interested" action
$interest_msg = '';
if (isset($_POST['interested']) && isset($_SESSION['user_id'])) {
    try {
        // Add debug info
        $buyer_id = $_SESSION['user_id'];
        $buyer_name = $_SESSION['username'];
        $seller_id = $product['seller_id'];
        $prod_name = $product['product_name'];
        
        // Insert interest record
        $stmt = $pdo->prepare("INSERT INTO interested (user_id, product_id) VALUES (?, ?)");
        $stmt->execute([$buyer_id, $product_id]);
        
        // Always notify seller when someone is interested
        $msg = "User " . $buyer_name . " is interested in your item: " . $prod_name;
        
        // DEBUG: Log what we're about to insert
        error_log("=== NOTIFICATION DEBUG ===");
        error_log("Buyer ID: " . $buyer_id);
        error_log("Buyer Name: " . $buyer_name);
        error_log("Seller ID: " . $seller_id);
        error_log("Product ID: " . $product_id);
        error_log("Product Name: " . $prod_name);
        error_log("Message: " . $msg);
        
        $stmt = $pdo->prepare("INSERT INTO notification (user_id, message, reference_id, type) VALUES (?, ?, ?, 'product')");
        $result = $stmt->execute([$seller_id, $msg, $product_id]);
        
        error_log("Notification Insert Result: " . ($result ? 'SUCCESS' : 'FAILED'));
        error_log("Last Insert ID: " . $pdo->lastInsertId());
        error_log("=========================");
        
        if ($result) {
            if ($product['status'] == 'available') {
                $interest_msg = "<div class='alert alert-success'>Seller has been notified of your interest! (Seller ID: $seller_id)</div>";
            } else {
                $interest_msg = "<div class='alert alert-success'>You have been added to the waitlist! The seller has been notified. (Seller ID: $seller_id)</div>";
            }
        } else {
            $interest_msg = "<div class='alert alert-error'>Failed to notify seller. Please try again.</div>";
        }
    } catch (PDOException $e) {
        error_log("NOTIFICATION ERROR: " . $e->getMessage());
        if ($e->getCode() == 23000) { // Duplicate entry
            $interest_msg = "<div class='alert alert-warning'>You have already expressed interest in this item.</div>";
        } else {
            $interest_msg = "<div class='alert alert-error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}
?>

<div class="grid grid-cols-2" style="gap: 2rem;">
    <div>
        <div class="card">
            <div style="height: 400px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; border-radius: var(--radius-lg); margin-bottom: 1rem;">
                <?php if (!empty($images)): ?>
                    <img id="mainImage" src="<?php echo htmlspecialchars($images[0]); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 100%; object-fit: contain;">
                <?php else: ?>
                    <span style="color: #94a3b8; font-size: 5rem;">📷</span>
                <?php endif; ?>
            </div>
            
            <?php if (count($images) > 1): ?>
                <div class="grid grid-cols-4" style="gap: 0.5rem; padding: 0 1rem 1rem;">
                    <?php foreach ($images as $img): ?>
                        <div style="height: 80px; border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; cursor: pointer;" onclick="document.getElementById('mainImage').src='<?php echo htmlspecialchars($img); ?>'">
                            <img src="<?php echo htmlspecialchars($img); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div>
        <div class="mb-4">
            <span class="badge badge-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
            <span class="badge badge-primary"><?php echo ucfirst($product['listing_type']); ?></span>
        </div>

        <h1 class="mb-4"><?php echo htmlspecialchars($product['product_name']); ?></h1>
        
        <div class="card mb-4">
            <div class="card-body">
                <h3 style="color: var(--primary-color); margin-bottom: 1rem;">
                    <?php 
                    if ($product['listing_type'] == 'sell') {

                        echo '₹ ' . number_format($product['price'], 2);
                    } elseif ($product['listing_type'] == 'rent') {
                        $rental_info = '₹ ' . number_format($product['rent_price'], 2);
                        if ($product['start_date'] && $product['end_date']) {
                            $rental_info .= ' (' . date('M j', strtotime($product['start_date'])) . ' - ' . date('M j, Y', strtotime($product['end_date'])) . ')';
                        }
                        echo $rental_info;
                    } else {
                        echo 'Free to Borrow';
                    }
                    ?>
                </h3>
                
                <p class="text-secondary mb-4"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

                <?php if ($product['listing_type'] != 'sell' && $product['terms']): ?>
                    <div class="alert alert-warning">
                        <strong>Terms:</strong> <?php echo nl2br(htmlspecialchars($product['terms'])); ?>
                    </div>
                <?php endif; ?>

                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; margin-top: 1rem;">
                    <p><strong>Seller:</strong> <?php echo htmlspecialchars($product['seller_name']); ?></p>
                    <p><strong>Posted:</strong> <?php echo date('M j, Y', strtotime($product['posted_date'])); ?></p>
                </div>
            </div>
        </div>

        <?php
        // Handle Status Update (Seller Only)
        if (isset($_POST['update_status']) && isset($_SESSION['user_id']) && $_SESSION['user_id'] == $product['seller_id']) {
            $new_status = $_POST['status'];
            $old_status = $product['status'];
            
            $stmt = $pdo->prepare("UPDATE products SET status = ? WHERE product_id = ?");
            $stmt->execute([$new_status, $product_id]);

            // Notify interested users if item becomes available
            if ($new_status == 'available' && $old_status != 'available') {
                $stmt = $pdo->prepare("SELECT user_id FROM interested WHERE product_id = ?");
                $stmt->execute([$product_id]);
                $interested_users = $stmt->fetchAll(PDO::FETCH_COLUMN);

                $msg = "The item '" . $product['product_name'] . "' is now available!";
                $insert_notif = $pdo->prepare("INSERT INTO notification (user_id, message, reference_id, type) VALUES (?, ?, ?, 'product')");

                foreach ($interested_users as $uid) {
                    // Don't notify the seller if they are somehow in the interested list (unlikely but safe)
                    if ($uid != $_SESSION['user_id']) {
                        $insert_notif->execute([$uid, $msg, $product_id]);
                    }
                }
            }

            // Refresh to show new status
            echo "<meta http-equiv='refresh' content='0'>";
            exit;
        }
        ?>

        <?php echo $interest_msg; ?>

        <?php if (isset($_SESSION['user_id'])): ?>
            <?php if ($_SESSION['user_id'] != $product['seller_id']): ?>
                <!-- Buyer View -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>Seller Details</h4>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($product['seller_email']); ?></p>
                        <?php if ($product['seller_phone']): ?>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($product['seller_phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <form method="POST">
                    <?php if ($product['status'] == 'available'): ?>
                        <button type="submit" name="interested" class="btn btn-primary w-full" style="padding: 1rem; font-size: 1.1rem;">
                            I'm Interested
                        </button>
                    <?php else: ?>
                        <button type="submit" name="interested" class="btn btn-outline w-full" style="padding: 1rem; font-size: 1.1rem;">
                            Notify Me When Available
                        </button>
                        <p class="text-center text-secondary mt-2">Item is currently <?php echo $product['status']; ?>. Join the waitlist!</p>
                    <?php endif; ?>
                </form>

            <?php else: ?>
                <!-- Seller View -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h4>Manage Status</h4>
                        <form method="POST" class="flex gap-2 items-center mt-2">
                            <select name="status" class="form-control" style="flex: 1;">
                                <option value="available" <?php echo $product['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                                <option value="rented" <?php echo $product['status'] == 'rented' ? 'selected' : ''; ?>>Rented</option>
                                <option value="borrowed" <?php echo $product['status'] == 'borrowed' ? 'selected' : ''; ?>>Borrowed</option>
                                <option value="sold" <?php echo $product['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                                <option value="unavailable" <?php echo $product['status'] == 'unavailable' ? 'selected' : ''; ?>>Unavailable</option>
                            </select>
                            <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="#" class="btn btn-outline w-full">Edit Listing Details</a>
                    <form method="POST" action="delete_product.php" onsubmit="return confirm('Are you sure you want to delete this product?');" style="width: 100%;">
                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                        <button type="submit" class="btn btn-danger w-full" style="background-color: #ef4444; color: white; border: none;">Delete Product</button>
                    </form>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <a href="login.php" class="btn btn-primary w-full">Login to Contact Seller</a>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
