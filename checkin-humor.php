<?php
require_once 'auth.php';
$usuario = autenticar();
$db = getDB();
header('Content-Type: application/json');

// POST - upsert do check-in diário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $emocao = $input['emocao'] ?? '';
    $data = $input['data'] ?? date('Y-m-d');

    if (empty($emocao)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Emoção é obrigatória.']);
        exit;
    }
    // Upsert: se já existir, atualiza; senão insere.
    $stmt = $db->prepare("INSERT INTO checkin_humor (usuario_id, emocao, data_checkin) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE emocao = VALUES(emocao)");
    $stmt->execute([$usuario['sub'], $emocao, $data]);
    echo json_encode(['sucesso' => true]);
    exit;
}

// GET - obter check-in de uma data
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $data = $_GET['data'] ?? date('Y-m-d');
    $stmt = $db->prepare("SELECT emocao FROM checkin_humor WHERE usuario_id = ? AND data_checkin = ?");
    $stmt->execute([$usuario['sub'], $data]);
    $checkin = $stmt->fetch();
    echo json_encode($checkin ? $checkin : ['emocao' => null]);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);