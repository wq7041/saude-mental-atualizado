<?php
require_once 'auth.php';
$usuario = autenticar();
$db = getDB();
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// POST - criar cápsula
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $titulo = $input['titulo'] ?? '';
    $texto = $input['texto'] ?? '';
    $data_abertura = $input['data_abertura'] ?? null;
    if (empty($titulo) || empty($texto) || empty($data_abertura)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Título, texto e data de abertura são obrigatórios.']);
        exit;
    }
    $stmt = $db->prepare("INSERT INTO capsula_tempo (usuario_id, titulo, texto, data_abertura) VALUES (?, ?, ?, ?)");
    $stmt->execute([$usuario['sub'], $titulo, $texto, $data_abertura]);
    echo json_encode(['sucesso' => true, 'id' => $db->lastInsertId()]);
    exit;
}

// GET - listar cápsulas do usuário
if ($method === 'GET') {
    $stmt = $db->prepare("SELECT id, titulo, data_abertura, aberta, data_criacao FROM capsula_tempo WHERE usuario_id = ? ORDER BY data_abertura ASC");
    $stmt->execute([$usuario['sub']]);
    echo json_encode($stmt->fetchAll());
    exit;
}

// PUT - abrir cápsula (se data já passou)
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da cápsula é obrigatório.']);
        exit;
    }
    $stmt = $db->prepare("SELECT * FROM capsula_tempo WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario['sub']]);
    $capsula = $stmt->fetch();
    if (!$capsula) {
        http_response_code(404);
        echo json_encode(['erro' => 'Cápsula não encontrada.']);
        exit;
    }
    if ($capsula['aberta']) {
        echo json_encode(['sucesso' => true, 'conteudo' => $capsula['texto']]);
        exit;
    }
    if (strtotime($capsula['data_abertura']) > time()) {
        http_response_code(403);
        echo json_encode(['erro' => 'Ainda não é possível abrir esta cápsula. Aguarde a data programada.']);
        exit;
    }
    // Abre a cápsula
    $stmt = $db->prepare("UPDATE capsula_tempo SET aberta = 1 WHERE id = ?");
    $stmt->execute([$id]);
    echo json_encode(['sucesso' => true, 'conteudo' => $capsula['texto']]);
    exit;
}

// DELETE - excluir cápsula (opcional)
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da cápsula é obrigatório.']);
        exit;
    }
    $stmt = $db->prepare("DELETE FROM capsula_tempo WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario['sub']]);
    echo json_encode(['sucesso' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);