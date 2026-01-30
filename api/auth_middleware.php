<?php
// Middleware kiểm tra quyền admin
session_start();

function requireAdmin()
{
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Yêu cầu đăng nhập với quyền quản trị viên'
        ]);
        exit;
    }
}
?>