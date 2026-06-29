<?php
/**
 * Middleware de autenticação – compatível com Infinity Free.
 */
require_once __DIR__ . '/config.php';

function autenticar(): array {
    $token = null;

    // Tenta obter o cabeçalho Authorization de todas as formas possíveis
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } else {
        $authHeader = '';
    }

    if (preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
        $token = $matches[1];
    }

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token de autenticação não fornecido.']);
        exit;
    }

    $payload = validarJWT($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode(['erro' => 'Token inválido ou expirado.']);
        exit;
    }

    return $payload;
}