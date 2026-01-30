<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get server information
$serverIp = $_SERVER['SERVER_ADDR'] ?? $_SERVER['LOCAL_ADDR'] ?? 'localhost';
$serverPort = $_SERVER['SERVER_PORT'] ?? '8000';

// Use HTTP_HOST which includes the hostname/IP and port as seen by the client
// This is more reliable than trying to detect IP manually
$httpHost = $_SERVER['HTTP_HOST'] ?? "{$serverIp}:{$serverPort}";

// Detect protocol
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';

// Build voting URL
$votingUrl = "{$protocol}://{$httpHost}/";

echo json_encode([
    'server_ip' => $serverIp,
    'server_port' => $serverPort,
    'http_host' => $httpHost,
    'voting_url' => $votingUrl,
    'timestamp' => time()
]);
