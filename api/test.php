<?php
/**
 * Test API - Debug
 */
session_start();
header('Content-Type: application/json; charset=utf-8');

echo json_encode([
    'session' => [
        'user_id' => $_SESSION['user_id'] ?? null,
        'role_id' => $_SESSION['role_id'] ?? null,
        'username' => $_SESSION['username'] ?? null
    ],
    'method' => $_SERVER['REQUEST_METHOD'],
    'input' => json_decode(file_get_contents('php://input'), true),
    'get' => $_GET,
    'post' => $_POST
]);
