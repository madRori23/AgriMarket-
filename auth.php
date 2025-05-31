<?php
require_once '/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: index.html");
    exit;
}

$errorMsg = '';
$registerMode = isset($_GET['register']);

// Process login form
if ($_SERVER["REQUEST_METHOD"] == "POST" && !$registerMode) {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    
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
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";
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
                
                // Redirect to login page
                redirectWithMessage("auth.php", "Registration successful! Please log in.", "success");
            } catch (Exception $e) {
                $pdo->rollBack();
                $errorMsg = "Registration failed: " . $e->getMessage();
            }
        }
    }
}
?>

<?php require_once '/header.php'; ?>

<main class="main">
    <div class="container" style="max-width: 480px;">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title text-center mb-4">
                    <?php echo $registerMode ? 'Create an Account' : 'Welcome Back'; ?>
                </h1>
                
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-error"><?php echo $errorMsg; ?></div>
                <?php endif; ?>
                
                <?php if (!$registerMode): ?>
                    <!-- Login Form -->
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
                    </form>
                    
                    <p class="text-center mt-4">
                        Don't have an account? 
                        <a href="?register=1" class="text-forest">Sign up</a>
                    </p>
                <?php else: ?>
                    <!-- Registration Form -->
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>?register=1">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="role">I am a:</label>
                            <select id="role" name="role" class="form-control">
                                <option value="buyer">Buyer</option>
                                <option value="seller">Seller</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Sign Up</button>
                    </form>
                    
                    <p class="text-center mt-4">
                        Already have an account? 
                        <a href="auth.php" class="text-forest">Login</a>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once '/footer.php'; ?>
