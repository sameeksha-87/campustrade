<?php
require_once 'config/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Please fill in all required fields.';
    } elseif (!str_ends_with(strtolower($email), '@iiitdmj.ac.in')) {
        $error = 'Only IIIT DMJ email addresses (@iiitdmj.ac.in) are allowed to register.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = 'Email already registered.';
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            try {
                $stmt = $pdo->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
                $stmt->execute([$username, $email, $phone, $hashed_password]);
                $success = 'Registration successful! You can now <a href="login.php">login</a>.';
            } catch(PDOException $e) {
                $error = 'Registration failed: ' . $e->getMessage();
            }
        }
    }
}

include 'includes/header.php';
?>

<div style="max-width: 500px; margin: 0 auto;">
    <div class="card">
        <div class="card-body">
            <h2 class="text-center mb-4">Create Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="username">Full Name</label>
                    <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address (IIIT DMJ only)</label>
                    <input type="email" id="email" name="email" required placeholder="yourname@iiitdmj.ac.in" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-full">Register</button>
            </form>
            
            <p class="text-center mt-4 text-secondary">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
