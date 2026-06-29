<?php
/**
 * Redefinir senha usando token de recuperação.
 */
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido. Use POST.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$token = trim($input['token'] ?? '');
$nova_senha = $input['senha'] ?? '';

if (empty($token) || empty($nova_senha)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Token e nova senha são obrigatórios.']);
    exit;
}

if (strlen($nova_senha) < 6) {
    http_response_code(400);
    echo json_encode(['erro' => 'A senha deve ter no mínimo 6 caracteres.']);
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT * FROM tokens_recuperacao WHERE token = ? AND usado = 0 AND expira_em > NOW()");
$stmt->execute([$token]);
$registro = $stmt->fetch();

if (!$registro) {
    http_response_code(400);
    echo json_encode(['erro' => 'Token inválido ou expirado.']);
    exit;
}

$senha_hash = password_hash($nova_senha, PASSWORD_BCRYPT);
$stmt = $db->prepare("UPDATE usuarios SET senha_hash = ? WHERE id = ?");
$stmt->execute([$senha_hash, $registro['usuario_id']]);

$stmt = $db->prepare("UPDATE tokens_recuperacao SET usado = 1 WHERE id = ?");
$stmt->execute([$registro['id']]);

echo json_encode(['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso.']);