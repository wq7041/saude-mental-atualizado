<?php
/**
 * Solicitar recuperação de senha.
 * Gera token e (em produção) envia por email.
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

if (empty($email)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Email é obrigatório.']);
    exit;
}

$db = getDB();

$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
$usuario = $stmt->fetch();

if (!$usuario) {
    // Não revelamos se o email existe
    echo json_encode(['sucesso' => true, 'mensagem' => 'Se o email existir, um link de recuperação será enviado.']);
    exit;
}

// Remove tokens antigos
$stmt = $db->prepare("DELETE FROM tokens_recuperacao WHERE usuario_id = ?");
$stmt->execute([$usuario['id']]);

// Gera novo token
$token = bin2hex(random_bytes(32));
$expira_em = date('Y-m-d H:i:s', strtotime('+1 hour'));

$stmt = $db->prepare("INSERT INTO tokens_recuperacao (usuario_id, token, expira_em) VALUES (?, ?, ?)");
$stmt->execute([$usuario['id'], $token, $expira_em]);

echo json_encode([
    'sucesso'  => true,
    'mensagem' => 'Token gerado. Em produção seria enviado por email.',
    'token'    => $token  // Remova em produção!
]);