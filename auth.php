<?php
session_start();

// Database configuration
$host = 'localhost';
$dbname = 'agrimarket';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize variables
$errorMsg = '';
$successMsg = '';
$registerMode = isset($_GET['register']);

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$registerMode) {
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    
    if (empty($email) || empty($password)) {
        $errorMsg = "Please enter both email and password";
    } else {
        // Check user credentials
        $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
        } else {
            $errorMsg = "Invalid email or password";
        }
    }
}

// Process registration form
if ($_SERVER["REQUEST_METHOD"] == "POST" && $registerMode) {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");
    $role = $_POST["role"] ?? "buyer";
    
    // Validate inputs
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $errorMsg = "All fields are required";
    } elseif ($password !== $confirm_password) {
        $errorMsg = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $errorMsg = "Password must be at least 6 characters long";
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errorMsg = "Email already exists";
        } else {
            // Create new user
            try {
                $pdo->beginTransaction();
                
                // Insert into users table
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $userId = bin2hex(random_bytes(16)); // Generate UUID
                
                $stmt = $pdo->prepare("INSERT INTO users (id, name, email, password, role) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$userId, $name, $email, $hashedPassword, $role]);
                
                // Insert into role-specific table
                if ($role === 'seller') {
                    $stmt = $pdo->prepare("INSERT INTO sellers (user_id) VALUES (?)");
                    $stmt->execute([$userId]);
                } elseif ($role === 'buyer') {
                    $stmt = $pdo->prepare("INSERT INTO buyers (user_id) VALUES (?)");
                    $stmt->execute([$userId]);
                }
                
                $pdo->commit();
                
                // Set success message and redirect
                $_SESSION['success_message'] = "Registration successful! Please log in.";
                header("Location: auth.php");
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errorMsg = "Registration failed: " . $e->getMessage();
            }
        }
    }
}

// Check for success message from redirect
if (isset($_SESSION['success_message'])) {
    $successMsg = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Authentication</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
    <link href="/styles.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex-shrink-0 flex items-center">
                    <a href="index.php" class="text-2xl font-bold text-green-700">AgriMarket</a>
                </div>
                
                <div class="hidden sm:flex sm:space-x-8 items-center">
                    <a href="/index.php" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium">Home</a>
                    <a href="/products.php" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium">Products</a>
                    <a href="/about.php" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium">About Us</a>
                    <a href="/contact.php" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium">Contact</a>
                </div>

                <div class="flex items-center space-x-4">
                    <a href="/auth.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow py-12 px-4 sm:px-6 lg:px-8 bg-cream-light">
        <div class="container" style="max-width: 480px;">
            <div class="card">
                <div class="card-body">
                    <?php if (!$registerMode): ?>
                        <!-- Login Form -->
                        <div id="loginForm">
                            <h1 class="card-title text-center mb-4">Welcome Back</h1>
                            
                            <?php if (!empty($errorMsg)): ?>
                                <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($errorMsg); ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($successMsg)): ?>
                                <div class="alert alert-success mb-4"><?php echo htmlspecialchars($successMsg); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                        required
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    >
                                </div>
                                
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                        required
                                    >
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="w-full btn btn-primary"
                                >
                                    Login
                                </button>
                            </form>
                            
                            <p class="text-center mt-4">
                                Don't have an account? 
                                <a href="?register=1" class="text-forest font-bold">Sign up</a>
                            </p>
                        </div>
                    <?php else: ?>
                        <!-- Registration Form -->
                        <div id="registerForm">
                            <h1 class="card-title text-center mb-4">Create an Account</h1>
                            
                            <?php if (!empty($errorMsg)): ?>
                                <div class="alert alert-danger mb-4"><?php echo htmlspecialchars($errorMsg); ?></div>
                            <?php endif; ?>
                            
                            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?register=1" class="space-y-4">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                                    <input 
                                        type="text" 
                                        id="name" 
                                        name="name"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                        required
                                        value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                    >
                                </div>
                                
                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <input 
                                        type="email" 
                                        id="email" 
                                        name="email"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                        required
                                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    >
                                </div>
                                
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                    <input 
                                        type="password" 
                                        id="password" 
                                        name="password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                        required
                                    >
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        name="confirm_password"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                        required
                                    >
                                </div>
                                
                                <div>
                                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">I am a:</label>
                                    <select 
                                        id="role" 
                                        name="role"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-forest"
                                    >
                                        <option value="buyer" <?php echo (isset($_POST['role']) && $_POST['role'] === 'buyer') ? 'selected' : ''; ?>>Buyer</option>
                                        <option value="seller" <?php echo (isset($_POST['role']) && $_POST['role'] === 'seller') ? 'selected' : ''; ?>>Seller</option>
                                    </select>
                                </div>
                                
                                <button 
                                    type="submit" 
                                    class="w-full btn btn-primary"
                                >
                                    Sign Up
                                </button>
                            </form>
                            
                            <p class="text-center mt-4">
                                Already have an account? 
                                <a href="auth.php" class="text-forest font-bold">Login</a>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-green-800 text-white mt-auto">
        <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
            <div class="grid grid-rows-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">AgriMarket</h3>
                    <p class="text-sm text-gray-300">
                        Connecting farmers and consumers for a sustainable future.
                    </p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Quick Links</h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="/about.php" class="text-sm text-gray-300 hover:text-white">
                                About Us
                            </a>
                        </li>
                        <li>
                            <a href="/products.php" class="text-sm text-gray-300 hover:text-white">
                                Products
                            </a>
                        </li>
                        <li>
                            <a href="/contact.php" class="text-sm text-gray-300 hover:text-white">
                                Contact
                            </a>
                        </li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contact Us</h3>
                    <p class="text-sm text-gray-300">
                        Email: info@agrimarket.com<br />
                        Phone: (123) 456-7890
                    </p>
                </div>
            </div>
            <div class="mt-8 pt-8 border-t border-gray-700">
                <p class="text-center text-sm text-gray-300">
                    &copy; 2024 AgriMarket. All rights reserved.
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Simple client-side toggle for better UX
        function toggleForms() {
            window.location.href = window.location.href.includes('register') 
                ? 'auth.php' 
                : 'auth.php?register=1';
        }
    </script>
</body>
</html>
