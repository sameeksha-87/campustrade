<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Fetch categories
$categories = $pdo->query("SELECT * FROM category ORDER BY category_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $description = trim($_POST['description']);
    $price = !empty($_POST['price']) ? $_POST['price'] : null;
    $listing_type = $_POST['listing_type'];
    $category_id = $_POST['category_id'];
    $seller_id = $_SESSION['user_id'];

    // Rent details
    $rent_price = !empty($_POST['rent_price']) ? $_POST['rent_price'] : null;
    $duration = !empty($_POST['duration']) ? $_POST['duration'] : null;
    $terms = trim($_POST['terms']);

    if (empty($product_name) || empty($listing_type) || empty($category_id)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO products (product_name, description, price, listing_type, seller_id, category_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$product_name, $description, $price, $listing_type, $seller_id, $category_id]);
            $product_id = $pdo->lastInsertId();

            // Handle Rent/Lend details
            if ($listing_type == 'rent' || $listing_type == 'lend' || $listing_type == 'sell_rent') {
                $stmt = $pdo->prepare("INSERT INTO rent_lend_details (product_id, rent_price, duration, terms) VALUES (?, ?, ?, ?)");
                $stmt->execute([$product_id, $rent_price, $duration, $terms]);
            }

            // Handle File Uploads
            if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
                $total_files = count($_FILES['images']['name']);
                $upload_dir = 'uploads/';
                
                // Ensure upload directory exists
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                for ($i = 0; $i < $total_files; $i++) {
                    $file_name = $_FILES['images']['name'][$i];
                    $file_tmp = $_FILES['images']['tmp_name'][$i];
                    $file_size = $_FILES['images']['size'][$i];
                    $file_error = $_FILES['images']['error'][$i];

                    if ($file_error === 0) {
                        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                        $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

                        if (in_array($file_ext, $allowed)) {
                            if ($file_size < 5000000) { // 5MB limit
                                $new_file_name = uniqid('', true) . "." . $file_ext;
                                $file_destination = $upload_dir . $new_file_name;

                                if (move_uploaded_file($file_tmp, $file_destination)) {
                                    $stmt = $pdo->prepare("INSERT INTO product_images (product_id, url) VALUES (?, ?)");
                                    // Store relative path for web access
                                    $web_path = '/CampusTrade-1/uploads/' . $new_file_name;
                                    $stmt->execute([$product_id, $web_path]);
                                }
                            }
                        }
                    }
                }
            }

            $pdo->commit();
            $success = 'Product listed successfully!';
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'Error listing product: ' . $e->getMessage();
        }
    }
}
?>

<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <div class="card-body">
            <h2 class="mb-4">Post a New Item</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="post_product.php" enctype="multipart/form-data">
                <div class="grid grid-cols-2">
                    <div class="form-group">
                        <label for="product_name">Product Name *</label>
                        <input type="text" id="product_name" name="product_name" required>
                    </div>

                    <div class="form-group">
                        <label for="category_id">Category *</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>"><?php echo htmlspecialchars($cat['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label for="images">Product Images (Max 5MB each)</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/*" class="form-control" style="padding: 0.5rem;">
                    <small class="text-secondary">Hold Ctrl/Cmd to select multiple files.</small>
                </div>

                <div class="form-group">
                    <label for="listing_type">Listing Type *</label>
                    <select id="listing_type" name="listing_type" required onchange="toggleFields()">
                        <option value="sell">Sell</option>
                        <option value="rent">Rent</option>
                        <option value="lend">Lend (Free Borrow)</option>
                        <option value="sell_rent">Sell or Rent</option>
                    </select>
                </div>

                <!-- Sale Fields -->
                <div id="sale_fields" class="form-group">
                    <label for="price">Sale Price</label>
                    <input type="number" id="price" name="price" step="0.01">
                </div>

                <!-- Rent/Lend Fields -->
                <div id="rent_fields" style="display: none;">
                    <div class="grid grid-cols-2">
                        <div class="form-group">
                            <label for="rent_price">Rent Price (per duration)</label>
                            <input type="number" id="rent_price" name="rent_price" step="0.01">
                        </div>
                        <div class="form-group">
                            <label for="duration">Max Duration (Days)</label>
                            <input type="number" id="duration" name="duration">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="terms">Terms & Conditions</label>
                        <textarea id="terms" name="terms" rows="3" placeholder="e.g. Deposit required, return in same condition..."></textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Post Listing</button>
            </form>
        </div>
    </div>
</div>

<script>
function toggleFields() {
    const type = document.getElementById('listing_type').value;
    const saleFields = document.getElementById('sale_fields');
    const rentFields = document.getElementById('rent_fields');

    if (type === 'sell') {
        saleFields.style.display = 'block';
        rentFields.style.display = 'none';
    } else if (type === 'rent' || type === 'lend') {
        saleFields.style.display = 'none';
        rentFields.style.display = 'block';
    } else {
        saleFields.style.display = 'block';
        rentFields.style.display = 'block';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
