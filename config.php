<?php
// Database configuration
$host = "localhost";
$dbname = "agrimar3_agrimarket";
$username = "agrimar3_agrimarket";
$password = "wBTLErvFvT5Lg4D2Y8GV"; // 

// Create a database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Start session
session_start();

// Function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Function to check user role
function getUserRole() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

// Function to redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

// Function to display flash messages
function displayFlashMessage() {
    if(isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'];
        
        echo "<div class='alert alert-$type'>$message</div>";
        
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
?>
