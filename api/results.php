<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$performersFile = '../data/performers.json';
$votesFile = '../data/votes.json';
$sessionFile = '../data/session.json';

$performers = json_decode(file_get_contents($performersFile), true) ?: [];
$allVotes = json_decode(file_get_contents($votesFile), true) ?: [];

// Get current session
$session = json_decode(file_get_contents($sessionFile), true) ?: [];
$currentSessionId = $session['start_time'] ?? null;

// Filter votes for current session only
$votes = array_filter($allVotes, function ($v) use ($currentSessionId) {
    return ($v['session_id'] ?? '') === $currentSessionId;
});

// Initialize scores - use string keys for consistency
$scores = [];
foreach ($performers as $p) {
    $id = strval($p['id']); // Convert to string for consistent key
    $scores[$id] = [
        'id' => $p['id'],
        'name' => $p['name'],
        'performer' => $p['performer'],
        'image' => $p['image'],
        'color' => $p['color'] ?? '#6366f1',
        'score' => 0
    ];
}

// Calculate scores
// Rank 1 = 3pts, Rank 2 = 2pts, Rank 3 = 1pt
foreach ($votes as $v) {
    $rank1 = strval($v['votes']['rank1'] ?? '');
    $rank2 = strval($v['votes']['rank2'] ?? '');
    $rank3 = strval($v['votes']['rank3'] ?? '');

    if (isset($scores[$rank1]))
        $scores[$rank1]['score'] += 3;
    if (isset($scores[$rank2]))
        $scores[$rank2]['score'] += 2;
    if (isset($scores[$rank3]))
        $scores[$rank3]['score'] += 1;
}

// Sort by score desc
usort($scores, function ($a, $b) {
    return $b['score'] <=> $a['score'];
});

echo json_encode([
    'total_votes' => count($votes),
    'session_id' => $currentSessionId,
    'results' => array_values($scores)
]);
?>