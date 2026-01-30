<?php
require_once 'auth_middleware.php';
require_once 'file_lock.php';

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

function getSession()
{
    global $sessionFile;
    $default = [
        'status' => 'closed',
        'start_time' => null,
        'end_time' => null,
        'duration_minutes' => 5
    ];
    return readJsonWithLock($sessionFile, $default);
}

function saveSession($data)
{
    global $sessionFile;
    return writeJsonWithLock($sessionFile, $data);
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

    // Add vote count for current session
    $votesFile = '../data/votes.json';
    $allVotes = json_decode(file_get_contents($votesFile), true) ?: [];
    $currentSessionId = $session['start_time'] ?? null;

    $sessionVotes = array_filter($allVotes, function ($v) use ($currentSessionId) {
        return ($v['session_id'] ?? '') === $currentSessionId;
    });

    $session['vote_count'] = count($sessionVotes);

    echo json_encode($session);
} elseif ($method === 'POST') {
    // Admin only
    requireAdmin();

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['action'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

    $default = [
        'status' => 'closed',
        'start_time' => null,
        'end_time' => null,
        'duration_minutes' => 5
    ];

    if ($input['action'] === 'open') {
        // Atomic update để tránh race condition
        $success = atomicJsonUpdate($sessionFile, function ($session) use ($input) {
            $duration = isset($input['duration']) ? intval($input['duration']) : 5;
            $now = time();

            $session['status'] = 'open';
            $session['start_time'] = date('Y-m-d H:i:s', $now);
            $session['end_time'] = date('Y-m-d H:i:s', $now + ($duration * 60));
            $session['duration_minutes'] = $duration;

            return $session;
        }, $default);

        if ($success) {
            $session = getSession();
            echo json_encode(['success' => true, 'session' => $session]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update session']);
        }
    } elseif ($input['action'] === 'close') {
        // Atomic update để tránh race condition
        $success = atomicJsonUpdate($sessionFile, function ($session) {
            $session['status'] = 'closed';
            // KHÔNG xóa start_time và end_time
            // Để giữ kết quả của phiên vừa kết thúc hiển thị trên Live page
            // Chỉ reset khi mở phiên MỚI (action = 'open')

            return $session;
        }, $default);

        if ($success) {
            $session = getSession();
            echo json_encode(['success' => true, 'session' => $session]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update session']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
    }
}
?>