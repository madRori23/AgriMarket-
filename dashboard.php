<?php
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit;
}

// Database configuration (should be in a separate config file)
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

// Get user details
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Get role-specific data
if ($user['role'] === 'seller') {
    $stmt = $pdo->prepare("SELECT * FROM sellers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $roleData = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM buyers WHERE user_id = ?");
    $stmt->execute([$userId]);
    $roleData = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AgriMarket - Dashboard</title>
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
        <div class="max-w-7xl mx-auto">
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-2 text-gray-600">Welcome back to your AgriMarket dashboard</p>
            </div>

            <!-- Dashboard Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- User Profile Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-green-100 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold">Profile Information</h2>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium">Name:</span> <?php echo htmlspecialchars($user['name']); ?></p>
                        <p><span class="font-medium">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><span class="font-medium">Role:</span> <?php echo ucfirst(htmlspecialchars($user['role'])); ?></p>
                    </div>
                    <a href="/profile.php" class="mt-4 inline-block text-green-600 hover:text-green-800 font-medium">Edit Profile</a>
                </div>

                <!-- Role-Specific Card -->
                <?php if ($user['role'] === 'seller'): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-blue-100 p-3 rounded-full mr-4">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold">Seller Dashboard</h2>
                        </div>
                        <div class="space-y-2">
                            <p><span class="font-medium">Products Listed:</span> 15</p>
                            <p><span class="font-medium">Total Sales:</span> $2,450</p>
                            <p><span class="font-medium">Rating:</span> 4.8/5</p>
                        </div>
                        <div class="mt-4 space-x-3">
                            <a href="/products/add.php" class="btn btn-primary">Add Product</a>
                            <a href="/products/manage.php" class="btn btn-outline">Manage Products</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center mb-4">
                            <div class="bg-yellow-100 p-3 rounded-full mr-4">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                            <h2 class="text-xl font-semibold">Buyer Dashboard</h2>
                        </div>
                        <div class="space-y-2">
                            <p><span class="font-medium">Orders:</span> 8</p>
                            <p><span class="font-medium">Pending Orders:</span> 2</p>
                            <p><span class="font-medium">Favorite Farmers:</span> 5</p>
                        </div>
                        <div class="mt-4 space-x-3">
                            <a href="/products.php" class="btn btn-primary">Browse Products</a>
                            <a href="/orders.php" class="btn btn-outline">View Orders</a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Quick Actions Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center mb-4">
                        <div class="bg-purple-100 p-3 rounded-full mr-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-semibold">Quick Actions</h2>
                    </div>
                    <div class="space-y-3">
                        <a href="/messages.php" class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                            </svg>
                            Messages (3)
                        </a>
                        <a href="/notifications.php" class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                            Notifications
                        </a>
                        <a href="/settings.php" class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <svg class="w-5 h-5 text-gray-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Account Settings
                        </a>
                    </div>
                </div>
            </div>

            <!-- Recent Activity Section -->
            <div class="mt-12 bg-white rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-semibold">Recent Activity</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-green-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Order #12345 completed</p>
                                <p class="text-sm text-gray-500">Your order has been delivered successfully</p>
                                <p class="text-xs text-gray-400 mt-1">2 hours ago</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-blue-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">New message from Farmer John</p>
                                <p class="text-sm text-gray-500">"Regarding your inquiry about organic tomatoes..."</p>
                                <p class="text-xs text-gray-400 mt-1">5 hours ago</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 bg-yellow-100 p-2 rounded-full">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900">Payment reminder</p>
                                <p class="text-sm text-gray-500">Your payment for Order #12344 is due tomorrow</p>
                                <p class="text-xs text-gray-400 mt-1">1 day ago</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 text-right">
                    <a href="/activity.php" class="text-sm font-medium text-green-600 hover:text-green-800">View all activity</a>
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
</body>
</html>
