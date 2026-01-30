<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Return admin status based on session
echo json_encode([
    'is_admin' => isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true,
    'username' => $_SESSION['admin_username'] ?? null
]);
?>