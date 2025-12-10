<?php
header('Content-Type: application/json');

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['cart_count' => 0]);
    exit;
}

// Include config
require_once '../../config.php';

try {
    // Kết nối database
    $pdo = new PDO(
        "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'db_quanlydienthoai') . ";charset=utf8mb4",
        env('DB_USER', 'root'),
        env('DB_PASS', ''),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $user_id = $_SESSION['user_id'];

    // Get cart count
    $cart_count_stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM shopping_cart WHERE user_id = ?");
    $cart_count_stmt->execute([$user_id]);
    $cart_count = $cart_count_stmt->fetch()['total'] ?? 0;

    echo json_encode(['cart_count' => (int)$cart_count]);

} catch (Exception $e) {
    echo json_encode(['cart_count' => 0]);
}
?>