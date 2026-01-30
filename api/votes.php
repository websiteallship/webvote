<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$voteFile = '../data/votes.json';
$sessionFile = '../data/session.json';

// Helper function to detect device type
function getDeviceType($userAgent) {
    if (preg_match('/mobile|android|iphone|ipad|ipod/i', $userAgent)) {
        if (preg_match('/ipad/i', $userAgent)) return 'Tablet (iPad)';
        if (preg_match('/android.*tablet/i', $userAgent)) return 'Tablet (Android)';
        if (preg_match('/iphone/i', $userAgent)) return 'Mobile (iPhone)';
        if (preg_match('/android/i', $userAgent)) return 'Mobile (Android)';
        return 'Mobile';
    }
    return 'Desktop';
}

// Helper function to format IP address for display
function formatIPAddress($ip) {
    if ($ip === '::1') return 'localhost (127.0.0.1)';
    if ($ip === '127.0.0.1') return 'localhost (127.0.0.1)';
    return $ip;
}

// Get current session data
function getCurrentSession() {
    global $sessionFile;
    if (!file_exists($sessionFile)) return null;
    return json_decode(file_get_contents($sessionFile), true);
}

// Get all votes
function getVotes() {
    global $voteFile;
    if (!file_exists($voteFile)) return [];
    return json_decode(file_get_contents($voteFile), true) ?: [];
}

// Check if already voted in current session
function hasVotedInSession($votes, $currentIP, $currentSessionId) {
    foreach ($votes as $v) {
        // Skip votes from other sessions
        if (($v['session_id'] ?? '') !== $currentSessionId) continue;
        
        // Check IP match (raw IP for comparison)
        if (($v['ip_raw'] ?? '') === $currentIP) {
            return true;
        }
    }
    return false;
}

// === REQUEST HANDLERS ===

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Check if this is a vote status check
    if (isset($_GET['check']) && $_GET['check'] == '1') {
        $session = getCurrentSession();
        $currentSessionId = $session['start_time'] ?? null;
        $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
        
        if (!$currentSessionId || $session['status'] !== 'open') {
            echo json_encode(['voted' => false, 'session_active' => false]);
            exit;
        }
        
        $votes = getVotes();
        $hasVoted = hasVotedInSession($votes, $currentIP, $currentSessionId);
        
        echo json_encode([
            'voted' => $hasVoted,
            'session_active' => true,
            'session_id' => $currentSessionId
        ]);
        exit;
    }
    
    // Regular GET - return all votes (for admin)
    if (!file_exists($voteFile)) {
        echo json_encode([]);
    } else {
        readfile($voteFile);
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['voter']) || !isset($input['votes'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    // Get current session
    $session = getCurrentSession();
    $currentSessionId = $session['start_time'] ?? null;
    
    // Check if session is open
    if (!$currentSessionId || $session['status'] !== 'open') {
        echo json_encode(['success' => false, 'message' => 'Phiên bình chọn chưa mở!']);
        exit;
    }

    // Validate if vote file exists
    if (!file_exists($voteFile)) {
        file_put_contents($voteFile, '[]');
    }

    $currentVotes = getVotes();
    $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
    
    // Check duplicate: IP OR Name in same session
    foreach ($currentVotes as $v) {
        // Skip votes from other sessions
        if (($v['session_id'] ?? '') !== $currentSessionId) continue;
        
        $sameIP = (($v['ip_raw'] ?? '') === $currentIP);
        $sameName = (strtolower($v['voter']) === strtolower($input['voter']));
        
        if ($sameIP) {
            echo json_encode([
                'success' => false, 
                'already_voted' => true,
                'message' => 'Thiết bị của bạn đã bình chọn trong phiên này rồi!'
            ]);
            exit;
        }
        
        if ($sameName) {
            echo json_encode([
                'success' => false,
                'already_voted' => true, 
                'message' => 'Tên "' . $input['voter'] . '" đã được sử dụng để bình chọn!'
            ]);
            exit;
        }
    }

    // Add metadata
    $input['session_id'] = $currentSessionId;
    $input['ip_raw'] = $currentIP;
    $input['ip_address'] = formatIPAddress($currentIP);
    $input['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $input['device'] = getDeviceType($_SERVER['HTTP_USER_AGENT'] ?? '');

    $currentVotes[] = $input;
    file_put_contents($voteFile, json_encode($currentVotes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    echo json_encode(['success' => true]);
} 
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Reset votes
    file_put_contents($voteFile, '[]');
    echo json_encode(['success' => true]);
}
?>
