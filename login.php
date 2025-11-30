<?php
require_once 'config/db_connect.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_start();
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

include 'includes/header.php';
?>

<div style="max-width: 400px; margin: 0 auto;">
    <div class="card">
        <div class="card-body">
            <h2 class="text-center mb-4">Welcome Back</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div style="text-align: right; margin-top: 0.25rem;">
                        <a href="forgot_password.php" style="font-size: 0.875rem;">Forgot Password?</a>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Login</button>
            </form>
            
            <p class="text-center mt-4 text-secondary">
                Don't have an account? <a href="register.php">Sign up</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
