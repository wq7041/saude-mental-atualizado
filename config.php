<?php
/**
 * Configuração central: conexão com banco MySQL e funções JWT.
 * Dados preenchidos conforme Infinity Free.
 */
define('DB_HOST', 'sql208.infinityfree.com');
define('DB_NAME', 'if0_42298841_site');
define('DB_USER', 'if0_42298841');
define('DB_PASS', 'AMZMI9h6tNaU');
define('JWT_SECRET', 'M3nt3Em3qu1l1bri0@2026!Seguranca'); // Troque se quiser

/**
 * Retorna uma conexão PDO com o banco de dados.
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
            // Define o fuso horário da sessão MySQL para Brasília (UTC-3)
            $pdo->exec("SET time_zone = '-03:00'");
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro de conexão com o banco de dados.']);
            exit;
        }
    }
    return $pdo;
}
/**
 * Gera um token JWT (HS256).
 */
function gerarJWT(array $payload): string {
    $header = base64_encode(json_encode(['typ' => 'JWT', 'alg' => 'HS256']));
    $payload = base64_encode(json_encode($payload));
    $signature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    return "$header.$payload.$signature";
}

/**
 * Valida um token JWT e retorna o payload ou false.
 */
function validarJWT(string $token): array|false {
    $parts = explode('.', $token);
    if (count($parts) !== 3) return false;
    [$header, $payload, $signature] = $parts;
    $validSignature = base64_encode(hash_hmac('sha256', "$header.$payload", JWT_SECRET, true));
    if (!hash_equals($validSignature, $signature)) return false;
    $data = json_decode(base64_decode($payload), true);
    if (isset($data['exp']) && $data['exp'] < time()) return false;
    return $data;
}