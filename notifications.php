<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch notifications
$stmt = $pdo->prepare("SELECT * FROM notification WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$notifications = $stmt->fetchAll();

// Mark all as read
$pdo->prepare("UPDATE notification SET is_read = TRUE WHERE user_id = ?")->execute([$user_id]);
?>

<div style="max-width: 800px; margin: 0 auto;">
    <h1 class="mb-4">Notifications</h1>

    <?php if (empty($notifications)): ?>
        <div class="card">
            <div class="card-body text-center text-secondary">
                No notifications yet.
            </div>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1">
            <?php foreach ($notifications as $notif): ?>
                <div class="card" style="<?php echo !$notif['is_read'] ? 'border-left: 4px solid var(--primary-color);' : ''; ?>">
                    <div class="card-body">
                        <p class="mb-2"><?php echo htmlspecialchars($notif['message']); ?></p>
                        <small class="text-secondary"><?php echo date('M j, Y g:i A', strtotime($notif['created_at'])); ?></small>
                        <?php if ($notif['type'] == 'product'): ?>
                            <a href="product_details.php?id=<?php echo $notif['reference_id']; ?>" class="btn btn-outline" style="margin-left: 1rem; padding: 0.25rem 0.5rem; font-size: 0.875rem;">View Item</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
