<?php
require_once 'auth.php';
$usuario = autenticar();
$db = getDB();
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// GET - listar entradas
if ($method === 'GET') {
    $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
    $stmt = $db->prepare("SELECT * FROM diario_emocional WHERE usuario_id = ? ORDER BY data_registro DESC LIMIT ?");
    $stmt->execute([$usuario['sub'], $limite]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST - nova entrada
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $emocao = $input['emocao'] ?? '';
    $texto = $input['texto'] ?? '';
    if (empty($emocao)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Emoção é obrigatória.']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO diario_emocional (usuario_id, emocao, texto) VALUES (?, ?, ?)");
    $stmt->execute([$usuario['sub'], $emocao, $texto]);
    echo json_encode(['sucesso' => true, 'id' => $db->lastInsertId()]);
    exit;
}

// DELETE - remover entrada
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da entrada é obrigatório.']);
        exit;
    }
    $stmt = $db->prepare("DELETE FROM diario_emocional WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario['sub']]);
    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['erro' => 'Entrada não encontrada.']);
        exit;
    }
    echo json_encode(['sucesso' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);