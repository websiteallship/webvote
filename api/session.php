<?php
// Set timezone to Vietnam
date_default_timezone_set('Asia/Ho_Chi_Minh');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$sessionFile = '../data/session.json';

function getSession() {
    global $sessionFile;
    if (!file_exists($sessionFile)) {
        $default = [
            'status' => 'closed',
            'start_time' => null,
            'end_time' => null,
            'duration_minutes' => 5
        ];
        file_put_contents($sessionFile, json_encode($default, JSON_PRETTY_PRINT));
        return $default;
    }
    $json = file_get_contents($sessionFile);
    return json_decode($json, true);
}

function saveSession($data) {
    global $sessionFile;
    file_put_contents($sessionFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $session = getSession();
    
    // Auto-close if expired
    if ($session['status'] === 'open' && $session['end_time']) {
        if (time() > strtotime($session['end_time'])) {
            $session['status'] = 'expired';
            saveSession($session);
        }
    }
    
    echo json_encode($session);
}
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $session = getSession();

    if ($input['action'] === 'open') {
        $duration = isset($input['duration']) ? intval($input['duration']) : 5;
        $now = time();
        
        $session['status'] = 'open';
        $session['start_time'] = date('Y-m-d H:i:s', $now);
        $session['end_time'] = date('Y-m-d H:i:s', $now + ($duration * 60));
        $session['duration_minutes'] = $duration;
        
        saveSession($session);
        echo json_encode(['success' => true, 'session' => $session]);
    }
    elseif ($input['action'] === 'close') {
        $session['status'] = 'closed';
        $session['start_time'] = null;
        $session['end_time'] = null;
        
        saveSession($session);
        echo json_encode(['success' => true, 'session' => $session]);
    }
    else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
}
?>
