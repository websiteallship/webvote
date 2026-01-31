<?php
require_once 'auth_middleware.php';
header('Content-Type: application/json');

// Only admin can clear cache
requireAdmin();

$versionFile = '../data/version.txt';

// Read current version
$current = (int) @file_get_contents($versionFile) ?: 1;

// Increment version
$new = $current + 1;

// Write new version
file_put_contents($versionFile, $new);

echo json_encode([
    'success' => true,
    'old_version' => $current,
    'new_version' => $new,
    'message' => 'Cache cleared! All users will download fresh files.'
]);
