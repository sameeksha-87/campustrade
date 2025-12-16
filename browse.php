<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

// Display session messages
$success_msg = '';
$error_msg = '';
$info_msg = '';

if (isset($_SESSION['success_message'])) {
    $success_msg = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_msg = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['info_message'])) {
    $info_msg = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

// Filter Logic
$where_clauses = ["is_deleted = FALSE"];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}

if (isset($_GET['type']) && !empty($_GET['type'])) {
    $where_clauses[] = "listing_type = ?";
    $params[] = $_GET['type'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_clauses[] = "(product_name LIKE ? OR description LIKE ?)";
    $params[] = "%" . $_GET['search'] . "%";
    $params[] = "%" . $_GET['search'] . "%";
}

if (isset($_GET['min_price']) && $_GET['min_price'] !== '') {
    $where_clauses[] = "(p.price >= ? OR r.rent_price >= ?)";
    $params[] = $_GET['min_price'];
    $params[] = $_GET['min_price'];
}

if (isset($_GET['max_price']) && $_GET['max_price'] !== '') {
    $where_clauses[] = "(p.price <= ? OR r.rent_price <= ? OR p.price IS NULL OR r.rent_price IS NULL)";
    $params[] = $_GET['max_price'];
    $params[] = $_GET['max_price'];
}

$sql = "SELECT p.*, c.category_name, r.rent_price,
        (SELECT url FROM product_images WHERE product_id = p.product_id LIMIT 1) as image_url 
        FROM products p 
        LEFT JOIN category c ON p.category_id = c.category_id 
        LEFT JOIN rent_lend_details r ON p.product_id = r.product_id
        WHERE " . implode(" AND ", $where_clauses) . " 
        ORDER BY posted_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for filter
$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
?>

<?php if ($success_msg): ?>
    <div class="alert alert-success"><?php echo $success_msg; ?></div>
<?php endif; ?>

<?php if ($error_msg): ?>
    <div class="alert alert-error"><?php echo $error_msg; ?></div>
<?php endif; ?>

<?php if ($info_msg): ?>
    <div class="alert alert-success"><?php echo $info_msg; ?></div>
<?php endif; ?>

<div class="mb-4">
    <form method="GET" action="browse.php" class="card p-4">
        <div class="grid grid-cols-3" style="gap: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Search</label>
                <input type="text" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" placeholder="Search items...">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Type</label>
                <select name="type">
                    <option value="">All Types</option>
                    <option value="sell" <?php echo (isset($_GET['type']) && $_GET['type'] == 'sell') ? 'selected' : ''; ?>>Buy</option>
                    <option value="rent" <?php echo (isset($_GET['type']) && $_GET['type'] == 'rent') ? 'selected' : ''; ?>>Rent</option>
                    <option value="lend" <?php echo (isset($_GET['type']) && $_GET['type'] == 'lend') ? 'selected' : ''; ?>>Borrow</option>
                </select>
            </div>
        </div>
        <div class="grid grid-cols-3" style="gap: 1rem; margin-top: 1rem; align-items: end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label>Min Price (₹)</label>
                <input type="number" name="min_price" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>" placeholder="0" step="0.01" min="0">
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <label>Max Price (₹)</label>
                <input type="number" name="max_price" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" placeholder="10000" step="0.01" min="0">
            </div>
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-3">
    <?php if (empty($products)): ?>
        <div style="grid-column: 1 / -1;" class="card">
            <div class="card-body text-center">
                <h3 class="mb-4">No products found</h3>
                <?php if (isset($_GET['category']) && !empty($_GET['category'])): ?>
                    <p class="text-secondary mb-4">There are currently no items in this category.</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="category_interest.php" style="display: inline-block;">
                            <input type="hidden" name="category_id" value="<?php echo $_GET['category']; ?>">
                            <button type="submit" class="btn btn-primary">
                                🔔 Notify Me When Items Are Posted
                            </button>
                        </form>
                    <?php else: ?>
                        <p class="text-secondary">
                            <a href="login.php">Login</a> to get notified when items are posted in this category.
                        </p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-secondary">Try adjusting your filters or search term.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="card" style="<?php echo $product['status'] != 'available' ? 'opacity: 0.8;' : ''; ?>">
                <div style="height: 200px; background-color: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; position: relative;">
                    <?php if ($product['image_url']): ?>
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 3rem;">📷</span>
                    <?php endif; ?>
                    
                    <?php if ($product['status'] != 'available'): ?>
                        <div style="position: absolute; top: 10px; right: 10px; background-color: rgba(0,0,0,0.7); color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">
                            <?php echo strtoupper($product['status']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="card-body">
                    <div class="flex justify-between items-start mb-2">
                        <span class="badge badge-secondary"><?php echo htmlspecialchars($product['category_name']); ?></span>
                        <span class="badge badge-primary"><?php echo ucfirst($product['listing_type']); ?></span>
                    </div>
                    <h3 class="card-title">
                        <a href="product_details.php?id=<?php echo $product['product_id']; ?>">
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </a>
                    </h3>
                    <p class="card-text">
                        <?php 
                        if ($product['listing_type'] == 'sell') {
                            echo '₹' . number_format($product['price'], 2);
                        } elseif ($product['listing_type'] == 'rent') {
                            echo 'For Rent'; 
                        } else {
                            echo 'Free to Borrow';
                        }
                        ?>
                    </p>
                    <a href="product_details.php?id=<?php echo $product['product_id']; ?>" class="btn btn-outline w-full">View Details</a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
