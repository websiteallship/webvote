<?php
require_once 'auth_middleware.php';
require_once 'file_lock.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

$dataFile = '../data/performers.json';

function getPerformers()
{
    global $dataFile;
    return readJsonWithLock($dataFile, []);
}

function savePerformers($data)
{
    global $dataFile;
    return writeJsonWithLock($dataFile, $data);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    echo json_encode(getPerformers());
} elseif ($method === 'POST') {
    // Admin only
    requireAdmin();

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // Sanitize input to prevent XSS
    if (isset($input['name'])) {
        $input['name'] = htmlspecialchars($input['name'], ENT_QUOTES, 'UTF-8');
    }
    if (isset($input['performer'])) {
        $input['performer'] = htmlspecialchars($input['performer'], ENT_QUOTES, 'UTF-8');
    }
    if (isset($input['image'])) {
        $input['image'] = htmlspecialchars($input['image'], ENT_QUOTES, 'UTF-8');
    }

    // Atomic update để tránh race condition
    $success = atomicJsonUpdate($dataFile, function ($performers) use ($input) {
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
        return $performers;
    }, []);

    if ($success) {
        $performers = getPerformers();
        echo json_encode(['success' => true, 'data' => $performers]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update performers']);
    }
} elseif ($method === 'DELETE') {
    // Admin only
    requireAdmin();

    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        exit;
    }

    // Atomic update để tránh race condition
    $success = atomicJsonUpdate($dataFile, function ($performers) use ($id) {
        $performers = array_filter($performers, function ($p) use ($id) {
            return $p['id'] != $id;
        });
        return array_values($performers);
    }, []);

    if ($success) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete performer']);
    }
}
?>