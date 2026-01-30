<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$dataFile = '../data/performers.json';

function getPerformers() {
    global $dataFile;
    if (!file_exists($dataFile)) return [];
    $json = file_get_contents($dataFile);
    return json_decode($json, true) ?: [];
}

function savePerformers($data) {
    global $dataFile;
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(getPerformers());
} 
elseif ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    $performers = getPerformers();
    
    if (isset($input['id'])) {
        // Edit existing
        foreach ($performers as &$p) {
            if ($p['id'] == $input['id']) {
                $p = array_merge($p, $input);
                break;
            }
        }
    } else {
        // Add new
        $newId = count($performers) > 0 ? max(array_column($performers, 'id')) + 1 : 1;
        $input['id'] = $newId;
        $performers[] = $input;
    }

    savePerformers($performers);
    echo json_encode(['success' => true, 'data' => $performers]);
}
elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        exit;
    }

    $performers = getPerformers();
    $performers = array_filter($performers, function($p) use ($id) {
        return $p['id'] != $id;
    });

    savePerformers(array_values($performers));
    echo json_encode(['success' => true]);
}
?>
