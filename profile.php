<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch my listings
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, r.rent_price, r.duration
    FROM products p 
    LEFT JOIN category c ON p.category_id = c.category_id 
    LEFT JOIN rent_lend_details r ON p.product_id = r.product_id
    WHERE p.seller_id = ? AND p.is_deleted = FALSE 
    ORDER BY p.posted_date DESC
");
$stmt->execute([$user_id]);
$my_listings = $stmt->fetchAll();

// Fetch items I'm interested in
$stmt = $pdo->prepare("
    SELECT p.*, c.category_name, u.username as seller_name
    FROM interested i
    JOIN products p ON i.product_id = p.product_id
    JOIN users u ON p.seller_id = u.user_id
    LEFT JOIN category c ON p.category_id = c.category_id
    WHERE i.user_id = ?
");
$stmt->execute([$user_id]);
$interested_items = $stmt->fetchAll();
?>

<div class="grid grid-cols-1" style="gap: 2rem;">
    <!-- Profile Header -->
    <div class="card">
        <div class="card-body flex justify-between items-center">
            <div>
                <h1 style="margin-bottom: 0.5rem;"><?php echo htmlspecialchars($user['username']); ?></h1>
                <p class="text-secondary"><?php echo htmlspecialchars($user['email']); ?></p>
            </div>
            <div>
                <a href="notifications.php" class="btn btn-outline">Notifications</a>
                <a href="post_product.php" class="btn btn-primary">Post New Item</a>
            </div>
        </div>
    </div>

    <!-- My Listings -->
    <div>
        <h2 class="mb-4">My Listings</h2>
        <?php if (empty($my_listings)): ?>
            <div class="card"><div class="card-body text-secondary">You haven't listed any items yet.</div></div>
        <?php else: ?>
            <div class="grid grid-cols-3">
                <?php foreach ($my_listings as $item): ?>
                    <div class="card">
                        <div class="card-body">
                            <div class="flex justify-between items-start mb-2">
                                <span class="badge badge-secondary"><?php echo htmlspecialchars($item['category_name']); ?></span>
                                <span class="badge <?php echo $item['status'] == 'available' ? 'badge-primary' : 'badge-secondary'; ?>">
                                    <?php echo ucfirst($item['status']); ?>
                                </span>
                            </div>
                            <h3 class="card-title">
                                <a href="product_details.php?id=<?php echo $item['product_id']; ?>">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </a>
                            </h3>
                            <p class="card-text">
                                ₹<?php echo number_format($item['price'] ?? $item['rent_price'] ?? 0, 2); ?>
                            </p>
                            <div class="flex gap-2">
                                <a href="product_details.php?id=<?php echo $item['product_id']; ?>" class="btn btn-outline w-full">View</a>
                                <!-- Add Edit/Delete buttons here if needed -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Interested Items -->
    <div>
        <h2 class="mb-4">Items I'm Interested In</h2>
        <?php if (empty($interested_items)): ?>
            <div class="card"><div class="card-body text-secondary">You haven't expressed interest in any items yet.</div></div>
        <?php else: ?>
            <div class="grid grid-cols-3">
                <?php foreach ($interested_items as $item): ?>
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">
                                <a href="product_details.php?id=<?php echo $item['product_id']; ?>">
                                    <?php echo htmlspecialchars($item['product_name']); ?>
                                </a>
                            </h3>
                            <p class="text-secondary mb-2">Seller: <?php echo htmlspecialchars($item['seller_name']); ?></p>
                            <a href="product_details.php?id=<?php echo $item['product_id']; ?>" class="btn btn-outline w-full">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
