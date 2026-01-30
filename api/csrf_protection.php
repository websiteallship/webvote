<?php
// CSRF Protection Helper

session_start();

// Tạo CSRF token
function generateCsrfToken()
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Lấy CSRF token hiện tại
function getCsrfToken()
{
    return $_SESSION['csrf_token'] ?? null;
}

// Verify CSRF token
function verifyCsrfToken($token)
{
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }

    // Sử dụng hash_equals để tránh timing attacks
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Middleware: Require CSRF token cho POST/PUT/DELETE requests
function requireCsrfToken()
{
    $method = $_SERVER['REQUEST_METHOD'];

    // Chỉ check cho các method thay đổi dữ liệu
    if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        return;
    }

    // Lấy token từ header hoặc POST data
    $token = null;

    // Ưu tiên lấy từ header
    if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    }
    // Fallback: Lấy từ POST data
    elseif (isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }
    // Fallback: Lấy từ JSON body
    else {
        $input = json_decode(file_get_contents('php://input'), true);
        if (isset($input['csrf_token'])) {
            $token = $input['csrf_token'];
        }
    }

    if (!$token || !verifyCsrfToken($token)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'CSRF token không hợp lệ hoặc đã hết hạn'
        ]);
        exit;
    }
}
?>