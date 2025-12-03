<?php
require_once __DIR__ . '/../config.php'; // Load environment variables

$servername = env('DB_HOST', 'localhost');
$username = env('DB_USER', 'root');
$password = env('DB_PASS', '');
$dbname = env('DB_NAME', 'db_quanlydienthoai');

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
