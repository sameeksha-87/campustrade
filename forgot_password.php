<?php
require_once 'config/db_connect.php';
include 'includes/header.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            // Generate Token
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store Token
            $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$email, $token, $expires_at]);

            // Simulate Email Sending (Display Link)
            $reset_link = "http://localhost/CampusTrade-1/reset_password.php?token=" . $token . "&email=" . urlencode($email);
            
            $message = "<div class='alert alert-success'>
                            <strong>Password Reset Link Generated!</strong><br>
                            (In a real app, this would be emailed. For testing, click below:)<br>
                            <a href='$reset_link' style='word-break: break-all;'>$reset_link</a>
                        </div>";
        } else {
            // For security, don't reveal if email exists, but here we can be helpful
            $error = 'We could not find an account with that email address.';
        }
    }
}
?>

<div style="max-width: 400px; margin: 0 auto;">
    <div class="card">
        <div class="card-body">
            <h2 class="text-center mb-4">Forgot Password</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($message): ?>
                <?php echo $message; ?>
            <?php else: ?>
                <p class="text-secondary text-center mb-4">Enter your email address and we'll send you a link to reset your password.</p>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-full">Send Reset Link</button>
                </form>
            <?php endif; ?>
            
            <p class="text-center mt-4 text-secondary">
                <a href="login.php">Back to Login</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
