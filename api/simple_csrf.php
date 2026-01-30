<?php
// Simple CSRF Protection cho internal use
// Kiểm tra Origin và Referer headers

function validateRequestOrigin()
{
    // Chỉ áp dụng cho POST/PUT/DELETE/PATCH
    $method = $_SERVER['REQUEST_METHOD'];
    if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
        return true;
    }

    // Lấy origin từ request
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';

    // Lấy host hiện tại
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $expectedOrigin = $protocol . '://' . $currentHost;

    // Kiểm tra origin hoặc referer
    $validOrigin = ($origin === $expectedOrigin) ||
        (strpos($referer, $expectedOrigin) === 0);

    if (!$validOrigin) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Request không hợp lệ (CSRF protection)'
        ]);
        exit;
    }

    return true;
}
?>