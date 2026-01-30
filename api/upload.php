<?php
require_once 'auth_middleware.php';
require_once 'simple_csrf.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Chỉ admin mới được upload
requireAdmin();

$uploadDir = '../uploads/';

// Create uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Function để validate file bằng magic bytes
function validateImageByMagicBytes($filepath)
{
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $filepath);
    finfo_close($finfo);

    $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    return in_array($mimeType, $allowedMimes);
}

// Function để validate extension
function validateImageExtension($filename)
{
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    return in_array($extension, $allowedExtensions);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    validateRequestOrigin();

    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Không có file được upload']);
        exit;
    }

    $file = $_FILES['image'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Lỗi khi upload file']);
        exit;
    }

    // Validate file extension
    if (!validateImageExtension($file['name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Định dạng file không hợp lệ. Chỉ chấp nhận JPG, PNG, GIF, WEBP']);
        exit;
    }

    // Validate MIME type (client-provided, can be spoofed)
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Loại file không hợp lệ']);
        exit;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File quá lớn. Tối đa 5MB']);
        exit;
    }

    // Validate actual file content using magic bytes
    if (!validateImageByMagicBytes($file['tmp_name'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nội dung file không phải là ảnh hợp lệ']);
        exit;
    }

    // Generate safe filename (không dùng extension từ user)
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $realMimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    // Map MIME type to extension
    $mimeToExt = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];

    $safeExtension = $mimeToExt[$realMimeType] ?? 'jpg';
    $filename = uniqid('img_', true) . '.' . $safeExtension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Set proper permissions
        chmod($filepath, 0644);

        // Return relative URL
        $url = 'uploads/' . $filename;
        echo json_encode([
            'success' => true,
            'url' => $url,
            'filename' => $filename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Không thể lưu file']);
    }
}
?>