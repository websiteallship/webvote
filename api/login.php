<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type');

// Simple credentials (in production, use database with hashed passwords)
define('ADMIN_USERNAME', 'admin');
define('ADMIN_PASSWORD', 'yep2025');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['username']) || !isset($input['password'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Thiếu thông tin đăng nhập']);
        exit;
    }

    $username = $input['username'];
    $password = $input['password'];

    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['login_time'] = time();
        
        echo json_encode([
            'success' => true,
            'message' => 'Đăng nhập thành công'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Sai tên đăng nhập hoặc mật khẩu!'
        ]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if logged in
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        echo json_encode([
            'logged_in' => true,
            'username' => $_SESSION['admin_username']
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Logout
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Đã đăng xuất']);
}
?>
