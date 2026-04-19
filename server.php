<?php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

if ($uri === '/') {
    header('Content-Type: application/json');
    http_response_code(200);

    echo json_encode([
        'ok' => true,
        'service' => 'ViperPro Backend API',
        'status' => 'running',
        'health' => '/healthz.txt',
        'admin' => '/admin',
        'api_base' => '/api',
    ]);

    exit;
}

if ($uri === '/healthz.txt') {
    header('Content-Type: text/plain');
    http_response_code(200);
    echo 'OK';
    exit;
}

if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

require_once __DIR__ . '/public/index.php';
