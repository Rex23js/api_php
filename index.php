<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/routes/Router.php';
require_once __DIR__ . '/routes/api.php';

// Configurações de cabeçalho para API
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Trata requisições OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Obtém a URI e o método HTTP
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Remove o prefixo do projeto da URI (ajuste conforme necessário)
$basePath = '/api_php';
$uri = str_replace($basePath, '', $uri);
$uri = $uri ?: '/';

// Despacha a rota
$router = new Router();
registerRoutes($router);
$router->dispatch($method, $uri);
