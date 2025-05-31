<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Database configuration
require_once 'config.php'; // Assuming you have a config file with DB credentials

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $dbusername, $dbpassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize variables
$errorMsg = '';
$successMsg = '';
$user = [];
$roleData = [];

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Get role-specific data
if ($user['role'] === 'seller') {
    $stmt = $pdo->prepare("SELECT * FROM sellers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $roleData = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM buyers WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $roleData = $stmt->fetch();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $currentPassword = trim($_POST["current_password"] ?? "");
    $newPassword = trim($_POST["new_password"] ?? "");
    $confirmPassword = trim($_POST["confirm_password"] ?? "");

    // Basic validation
    if (empty($name) || empty($email)) {
        $errorMsg = "Name and email are required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Invalid email format";
    } elseif (!empty($newPassword) && $newPassword !== $confirmPassword) {
        $errorMsg = "New passwords do not match";
    } else {
        try {
            $pdo->beginTransaction();

            // Verify current password if changing password
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    throw new Exception("Current password is required to change password");
                }
                if (!password_verify($currentPassword, $user['password'])) {
                    throw new Exception("Current password is incorrect");
                }
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            } else {
                $hashedPassword = $user['password'];
            }

            // Update user in database
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
            $stmt->execute([$name, $email, $hashedPassword, $_SESSION['user_id']]);

            // Update role-specific data if needed
            if ($user['role'] === 'seller') {
                // Example: Update seller-specific fields
                $farmName = trim($_POST["farm_name"] ?? "");
                $location = trim($_POST["location"] ?? "");
                
                $stmt = $pdo->prepare("UPDATE sellers SET farm_name = ?, location = ? WHERE user_id = ?");
                $stmt->execute([$farmName, $location, $_SESSION['user_id']]);
            } else {
                // Example: Update buyer-specific fields
                $address = trim($_POST["address"] ?? "");
                $phone = trim($_POST["phone"] ?? "");
                
                $stmt = $pdo->prepare("UPDATE buyers SET address = ?, phone = ? WHERE user_id = ?");
                $stmt->execute([$address, $phone, $_SESSION['user_id']]);
            }

            $pdo->commit();

            // Update session variables
            $_SESSION['user_name'] = $name;
            $_SESSION['user_email'] = $email;

            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();

            $successMsg = "Profile updated successfully!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $errorMsg = "Error updating profile: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - My Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@latest/dist/tailwind.min.css" rel="stylesheet">
    <link href="/styles.css" rel="stylesheet">
</head>
<body class="min-h-screen flex flex-col bg-gray-50">
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
                    <a href="/dashboard.php" class="text-gray-700 hover:text-green-700 px-3 py-2 text-sm font-medium">Dashboard</a>
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-gray-700">Welcome, <?php echo htmlspecialchars($user['name']); ?></span>
                    <a href="/logout.php" class="btn btn-outline">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">My Profile</h1>
                <p class="mt-2 text-gray-600">Manage your account information and settings</p>
            </div>

            <?php if (!empty($errorMsg)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($errorMsg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($successMsg)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                    <?php echo htmlspecialchars($successMsg); ?>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow overflow-hidden">
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="p-6 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input 
                                    type="text" 
                                    id="name" 
                                    name="name" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    value="<?php echo htmlspecialchars($user['name']); ?>"
                                    required
                                >
                            </div>
                            
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                <input 
                                    type="email" 
                                    id="email" 
                                    name="email" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    value="<?php echo htmlspecialchars($user['email']); ?>"
                                    required
                                >
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Change Password</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                    <input 
                                        type="password" 
                                        id="current_password" 
                                        name="current_password" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="Leave blank to keep current"
                                    >
                                </div>
                                
                                <div>
                                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input 
                                        type="password" 
                                        id="new_password" 
                                        name="new_password" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="Leave blank to keep current"
                                    >
                                </div>
                                
                                <div>
                                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input 
                                        type="password" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                        placeholder="Leave blank to keep current"
                                    >
                                </div>
                            </div>
                        </div>

                        <?php if ($user['role'] === 'seller'): ?>
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Seller Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="farm_name" class="block text-sm font-medium text-gray-700 mb-1">Farm Name</label>
                                        <input 
                                            type="text" 
                                            id="farm_name" 
                                            name="farm_name" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                            value="<?php echo htmlspecialchars($roleData['farm_name'] ?? ''); ?>"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="location" class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                                        <input 
                                            type="text" 
                                            id="location" 
                                            name="location" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                            value="<?php echo htmlspecialchars($roleData['location'] ?? ''); ?>"
                                        >
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-gray-900 mb-4">Buyer Information</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                                        <input 
                                            type="text" 
                                            id="address" 
                                            name="address" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                            value="<?php echo htmlspecialchars($roleData['address'] ?? ''); ?>"
                                        >
                                    </div>
                                    
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                        <input 
                                            type="tel" 
                                            id="phone" 
                                            name="phone" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                            value="<?php echo htmlspecialchars($roleData['phone'] ?? ''); ?>"
                                        >
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 text-right">
                        <button 
                            type="submit" 
                            class="btn btn-primary"
                        >
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Danger Zone -->
            <div class="mt-8 bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                    <h3 class="text-lg font-medium text-red-800">Danger Zone</h3>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div>
                            <h4 class="text-md font-medium text-gray-900">Delete Account</h4>
                            <p class="text-sm text-gray-500 mt-1">
                                Once you delete your account, there is no going back. Please be certain.
                            </p>
                        </div>
                        <button 
                            type="button" 
                            class="btn btn-danger"
                            onclick="confirmDelete()"
                        >
                            Delete Account
                        </button>
                    </div>
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
        function confirmDelete() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone.')) {
                // In a real application, you would redirect to a delete account script
                alert('Account deletion would be processed here');
                // window.location.href = 'delete_account.php';
            }
        }
    </script>
</body>
</html>
