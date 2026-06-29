<?php
/**
 * Endpoint de login com email e senha.
 */
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$email = trim($input['email'] ?? '');
$senha = $input['senha'] ?? '';

if (empty($email) || empty($senha)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Email e senha são obrigatórios.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT id, nome, email, senha_hash FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario || !password_verify($senha, $usuario['senha_hash'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Email ou senha inválidos.']);
    exit;
}

// Atualiza último login
$stmt = $db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
$stmt->execute([$usuario['id']]);

// Gera token
$token = gerarJWT([
    'sub'   => $usuario['id'],
    'nome'  => $usuario['nome'],
    'email' => $usuario['email'],
    'iat'   => time(),
    'exp'   => time() + (60 * 60 * 24 * 7)
]);

echo json_encode([
    'sucesso' => true,
    'token'   => $token,
    'usuario' => [
        'id'    => $usuario['id'],
        'nome'  => $usuario['nome'],
        'email' => $usuario['email']
    ]
]);