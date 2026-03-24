<?php

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
$baseUrl = "{$scheme}://{$host}/api_php";

function apiRequest(string $method, string $url, ?array $data = null): array
{
    $ch = curl_init($url);

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 10,
    ]);

    if ($data !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    return [
        'status' => $httpCode,
        'response' => json_decode($response, true),
        'raw' => $response,
        'error' => $error,
    ];
}

function printTest(string $title, string $method, string $url, array $result): void
{
    $statusColor = $result['status'] >= 200 && $result['status'] < 300 ? '#4caf50' : '#f44336';

    echo "<div style='background:#1e1e2e;border:1px solid #333;border-radius:8px;padding:16px;margin-bottom:16px;'>";
    echo "<h3 style='color:#89b4fa;margin:0 0 8px;'>{$title}</h3>";
    echo "<p style='color:#cdd6f4;margin:4px 0;'><strong style='color:#fab387;'>{$method}</strong> <code style='color:#a6e3a1;'>{$url}</code></p>";
    echo "<p style='color:#cdd6f4;margin:4px 0;'>Status: <span style='color:{$statusColor};font-weight:bold;'>{$result['status']}</span></p>";
    echo "<pre style='background:#181825;color:#cdd6f4;padding:12px;border-radius:6px;overflow-x:auto;font-size:14px;'>";
    echo htmlspecialchars(json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "</pre>";
    echo "</div>";
}

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>API PHP - Testes</title></head>";
echo "<body style='font-family:Inter,sans-serif;background:#11111b;color:#cdd6f4;padding:32px;max-width:900px;margin:auto;'>";
echo "<h1 style='color:#cba6f7;text-align:center;'>Testes da API REST - CRUD Users</h1>";
echo "<p style='text-align:center;color:#a6adc8;'>Executando testes automatizados via cURL</p>";
echo "<hr style='border-color:#333;margin:24px 0;'>";

echo "<h2 style='color:#f9e2af;'>Verificacao do Banco de Dados</h2>";

try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::connect();
    echo "<div style='background:#1e1e2e;border:1px solid #4caf50;border-radius:8px;padding:16px;margin-bottom:16px;'>";
    echo "<p style='color:#4caf50;font-weight:bold;'>Conexao com o banco 'api_php' OK.</p>";
    echo "</div>";
} catch (Exception $e) {
    echo "<div style='background:#1e1e2e;border:1px solid #f44336;border-radius:8px;padding:16px;margin-bottom:16px;'>";
    echo "<p style='color:#f44336;font-weight:bold;'>Erro na conexao: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p style='color:#fab387;'>Execute o schema.sql no phpMyAdmin para criar o banco.</p>";
    echo "</div>";
    echo "</body></html>";
    exit;
}

echo "<h2 style='color:#f9e2af;'>Testes CRUD</h2>";

$newUser = [
    'name' => 'Ismael Gomes',
    'email' => 'ismael_' . time() . '@email.com',
    'hair_color' => 'preto',
    'eye_color' => 'castanho',
    'height' => 1.70,
    'weight' => 50,
];

$result = apiRequest('POST', "$baseUrl/users", $newUser);
printTest('1. Criar usuario (POST)', 'POST', '/users', $result);

$createdId = $result['response']['id'] ?? null;

$result = apiRequest('GET', "$baseUrl/users");
printTest('2. Listar todos os usuarios (GET)', 'GET', '/users', $result);

if ($createdId) {
    $result = apiRequest('GET', "$baseUrl/users/$createdId");
    printTest("3. Buscar usuario ID={$createdId} (GET)", 'GET', "/users/{$createdId}", $result);
}

if ($createdId) {
    $updatedUser = [
        'name' => 'Ismael Gomes Atualizado',
        'email' => 'ismael_atualizado_' . time() . '@email.com',
        'hair_color' => 'preto',
        'eye_color' => 'castanho escuro',
        'height' => 1.72,
        'weight' => 52,
    ];

    $result = apiRequest('PUT', "$baseUrl/users/$createdId", $updatedUser);
    printTest("4. Atualizar usuario ID={$createdId} (PUT)", 'PUT', "/users/{$createdId}", $result);

    $result = apiRequest('GET', "$baseUrl/users/$createdId");
    printTest("4b. Verificar atualizacao (GET)", 'GET', "/users/{$createdId}", $result);

    $duplicateUser = [
        'name' => 'Email Duplicado',
        'email' => $updatedUser['email'],
        'hair_color' => 'preto',
        'eye_color' => 'castanho',
        'height' => 1.70,
        'weight' => 51,
    ];

    $result = apiRequest('POST', "$baseUrl/users", $duplicateUser);
    printTest('4c. Criar com email duplicado - deve ser 409 (POST)', 'POST', '/users', $result);
}

if ($createdId) {
    $result = apiRequest('DELETE', "$baseUrl/users/$createdId");
    printTest("5. Excluir usuario ID={$createdId} (DELETE)", 'DELETE', "/users/{$createdId}", $result);

    $result = apiRequest('GET', "$baseUrl/users/$createdId");
    printTest('5b. Verificar exclusao - deve ser 404 (GET)', 'GET', "/users/{$createdId}", $result);
}

$result = apiRequest('GET', "$baseUrl/users/999999");
printTest('6. Buscar ID inexistente - deve ser 404 (GET)', 'GET', '/users/999999', $result);

$invalidUser = ['name' => 'Sem Email'];
$result = apiRequest('POST', "$baseUrl/users", $invalidUser);
printTest('7. Criar sem campos obrigatorios - deve ser 422 (POST)', 'POST', '/users', $result);

echo "<hr style='border-color:#333;margin:24px 0;'>";
echo "<h2 style='color:#a6e3a1;text-align:center;'>Testes finalizados</h2>";
echo "<p style='text-align:center;color:#a6adc8;'>Acesse diretamente as URLs para testar manualmente:</p>";
echo "<ul style='color:#89b4fa;font-size:16px;line-height:2;'>";
echo "<li><a href='{$baseUrl}/users' style='color:#89b4fa;'>GET /users</a> - Lista todos</li>";
echo "<li><a href='{$baseUrl}/users/1' style='color:#89b4fa;'>GET /users/1</a> - Busca por ID</li>";
echo "<li>POST /users - Use Postman ou cURL</li>";
echo "<li>PUT /users/1 - Use Postman ou cURL</li>";
echo "<li>DELETE /users/1 - Use Postman ou cURL</li>";
echo "</ul>";
echo "</body></html>";
