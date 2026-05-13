<?php
// Jednoduchá ochrana heslem — změňte na vlastní heslo
define('UPLOAD_PASSWORD', 'otakar1253');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Pouze POST']);
    exit;
}

$body = file_get_contents('php://input');
if (!$body) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Prázdná data']);
    exit;
}

$payload = json_decode($body, true);
if (!$payload || !isset($payload['password']) || !isset($payload['data'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Chybí password nebo data']);
    exit;
}

if ($payload['password'] !== UPLOAD_PASSWORD) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Špatné heslo']);
    exit;
}

$data = $payload['data'];
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Data musí být pole']);
    exit;
}

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
$target = __DIR__ . '/listiny_data.json';

// Záloha předchozí verze
if (file_exists($target)) {
    copy($target, $target . '.bak');
}

if (file_put_contents($target, $json) === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Nelze zapsat soubor — zkontrolujte oprávnění']);
    exit;
}

echo json_encode([
    'ok' => true,
    'records' => count($data),
    'message' => 'listiny_data.json aktualizován (' . count($data) . ' záznamů)'
]);
