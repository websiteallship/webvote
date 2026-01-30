<?php
require_once 'csrf_protection.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');

// Tạo hoặc lấy CSRF token
$token = generateCsrfToken();

echo json_encode([
    'csrf_token' => $token
]);
?>