<?php
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

$data = json_decode($body, true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Data musí být JSON pole, přijato: ' . substr($body, 0, 80)]);
    exit;
}

$target = __DIR__ . '/listiny_data.json';

// Záloha předchozí verze
if (file_exists($target)) {
    copy($target, $target . '.bak');
}

$json = json_encode($data, JSON_UNESCAPED_UNICODE);
$written = file_put_contents($target, $json);

if ($written === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'Nelze zapsat soubor — zkontrolujte oprávnění pro zápis',
        'target_path' => $target,
        'dir_writable' => is_writable(__DIR__),
        'file_exists' => file_exists($target),
        'file_writable' => file_exists($target) ? is_writable($target) : 'neexistuje'
    ]);
    exit;
}

echo json_encode([
    'ok' => true,
    'records' => count($data),
    'message' => 'listiny_data.json aktualizován (' . count($data) . ' záznamů)',
    'path' => $target,
    'bytes' => $written
]);
