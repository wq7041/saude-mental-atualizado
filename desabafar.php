<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

// GET - listar posts públicos (não requer autenticação)
if ($method === 'GET') {
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
    $stmt = $db->prepare("SELECT texto, data_postagem FROM desabafar WHERE ativo = 1 ORDER BY data_postagem DESC LIMIT ?");
    $stmt->execute([$limite]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST - criar post (autenticado)
if ($method === 'POST') {
    require_once 'auth.php';
    $usuario = autenticar();
    $input = json_decode(file_get_contents('php://input'), true);
    $texto = trim($input['texto'] ?? '');
    if (empty($texto)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Texto não pode ser vazio.']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO desabafar (usuario_id, texto) VALUES (?, ?)");
    $stmt->execute([$usuario['sub'], $texto]);
    echo json_encode(['sucesso' => true, 'id' => $db->lastInsertId()]);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);