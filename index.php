<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

// Filter Logic
$where_clauses = ["is_deleted = FALSE"];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_clauses[] = "category_id = ?";
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

$sql = "SELECT p.*, c.category_name, 
        (SELECT url FROM product_images WHERE product_id = p.product_id LIMIT 1) as image_url 
        FROM products p 
        LEFT JOIN category c ON p.category_id = c.category_id 
        WHERE " . implode(" AND ", $where_clauses) . " 
        ORDER BY posted_date DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Fetch categories for filter
$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
?>

<div class="mb-4">
    <form method="GET" action="index.php" class="card p-4">
        <div class="grid grid-cols-4" style="gap: 1rem; align-items: end;">
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
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>
</div>

<div class="grid grid-cols-3">
    <?php if (empty($products)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
            <p class="text-secondary">No products found.</p>
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
                        if ($product['listing_type'] == 'sell' || $product['listing_type'] == 'sell_rent') {
                            echo '$' . number_format($product['price'], 2);
                        } elseif ($product['listing_type'] == 'rent') {
                            // Fetch rent price if needed, or just show "For Rent"
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
