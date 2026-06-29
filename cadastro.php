<?php
/**
 * Endpoint para cadastro de novo usuário.
 */
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$nome = trim($input['nome'] ?? '');
$email = trim($input['email'] ?? '');
$senha = $input['senha'] ?? '';

// Validações
if (empty($nome) || empty($email) || empty($senha)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Nome, email e senha são obrigatórios.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Formato de email inválido.']);
    exit;
}

if (strlen($senha) < 6) {
    http_response_code(400);
    echo json_encode(['erro' => 'A senha deve ter no mínimo 6 caracteres.']);
    exit;
}

$db = getDB();

// Verifica se o email já está cadastrado
$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    http_response_code(409);
    echo json_encode(['erro' => 'Este email já está cadastrado.']);
    exit;
}

// Insere novo usuário
$senha_hash = password_hash($senha, PASSWORD_BCRYPT);
$stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
$stmt->execute([$nome, $email, $senha_hash]);
$usuario_id = $db->lastInsertId();

// Gera token JWT para login automático
$token = gerarJWT([
    'sub'   => $usuario_id,
    'nome'  => $nome,
    'email' => $email,
    'iat'   => time(),
    'exp'   => time() + (60 * 60 * 24 * 7) // 7 dias
]);

echo json_encode([
    'sucesso'  => true,
    'token'    => $token,
    'usuario'  => [
        'id'    => $usuario_id,
        'nome'  => $nome,
        'email' => $email
    ]
]);