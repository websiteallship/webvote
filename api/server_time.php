<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Trả về server time hiện tại
echo json_encode([
    'server_time' => time(),
    'server_datetime' => date('Y-m-d H:i:s')
]);
?>