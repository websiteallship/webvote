<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$uploadDir = '../uploads/';

// Create uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_FILES['image'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No file uploaded']);
        exit;
    }

    $file = $_FILES['image'];
    
    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, PNG, GIF, WEBP allowed.']);
        exit;
    }

    // Validate file size (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File too large. Max 5MB.']);
        exit;
    }

    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_') . '.' . $extension;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Return relative URL
        $url = 'uploads/' . $filename;
        echo json_encode([
            'success' => true,
            'url' => $url,
            'filename' => $filename
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    }
}
?>
