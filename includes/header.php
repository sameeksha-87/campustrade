<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Bazaar</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/CampusTrade-1/assets/style.css">
</head>
<body>
    <header>
        <div class="container">
            <nav class="navbar">
                <a href="/CampusTrade-1/index.php" class="logo">CampusTrade</a>
                <div class="nav-links">
                    <a href="/CampusTrade-1/index.php" class="nav-link">Browse</a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="/CampusTrade-1/post_product.php" class="btn btn-primary">Post Item</a>
                        <a href="/CampusTrade-1/profile.php" class="nav-link">My Profile</a>
                        <a href="/CampusTrade-1/logout.php" class="nav-link">Logout</a>
                    <?php else: ?>
                        <a href="/CampusTrade-1/login.php" class="nav-link">Login</a>
                        <a href="/CampusTrade-1/register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>
    <main class="container">
