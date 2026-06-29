<?php
require_once 'auth.php';
$usuario = autenticar();
$db = getDB();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $tipo = $_GET['tipo'] ?? 'tudo';

    $dados = ['usuario_id' => $usuario['sub']];

    if ($tipo === 'diario' || $tipo === 'tudo') {
        $stmt = $db->prepare("SELECT emocao, texto, data_registro FROM diario_emocional WHERE usuario_id = ? ORDER BY data_registro DESC");
        $stmt->execute([$usuario['sub']]);
        $dados['diario'] = $stmt->fetchAll();
    }

    if ($tipo === 'tarefas' || $tipo === 'tudo') {
        $stmt = $db->prepare("SELECT * FROM tarefas WHERE usuario_id = ? ORDER BY data_prevista DESC");
        $stmt->execute([$usuario['sub']]);
        $dados['tarefas'] = $stmt->fetchAll();
    }

    if ($tipo === 'humor' || $tipo === 'tudo') {
        $stmt = $db->prepare("SELECT data_checkin, emocao FROM checkin_humor WHERE usuario_id = ? ORDER BY data_checkin DESC");
        $stmt->execute([$usuario['sub']]);
        $dados['checkin_humor'] = $stmt->fetchAll();
    }

    echo json_encode($dados);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);