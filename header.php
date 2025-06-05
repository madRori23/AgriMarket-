<?php require_once 'config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Farm to Table</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <nav class="navbar">
        <div class="container">
            <a href="/" class="brand">AgriMarket</a>
            
            <div class="nav-links">
                <a href="/" class="nav-link">Home</a>
                <a href="/products.php" class="nav-link">Products</a>
                <a href="/about.php" class="nav-link">About Us</a>
                <a href="/contact.php" class="nav-link">Contact</a>
            </div>

            <div class="nav-actions">
                <?php if(isLoggedIn()): ?>
                    <a href="/dashboard.php" class="user-menu">
                        <?php echo $_SESSION['user_name']; ?>
                        <span class="role-badge"><?php echo ucfirst($_SESSION['user_role']); ?></span>
                    </a>
                    <a href="/logout.php" class="btn btn-ghost">Logout</a>
                <?php else: ?>
                    <a href="/auth.php" class="btn btn-ghost">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <?php displayFlashMessage(); ?>
