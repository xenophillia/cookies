<?php
// Generic logger for all pages in the project.
// Appends one JSON object per line into logs.txt (newline-delimited JSON).

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
if ($raw === false || trim($raw) === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Empty request body']);
    exit;
}

$data = json_decode($raw, true);
if ($data === null) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON']);
    exit;
}

// add some automatic metadata if missing
if (!isset($data['timestamp'])) {
    $data['timestamp'] = gmdate('c');
}

$data['_remote_addr'] = $_SERVER['REMOTE_ADDR'] ?? null;
$data['_user_agent']  = $_SERVER['HTTP_USER_AGENT'] ?? null;

$line = json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
file_put_contents(__DIR__ . '/logs.txt', $line, FILE_APPEND | LOCK_EX);

echo json_encode(['status' => 'ok']);
