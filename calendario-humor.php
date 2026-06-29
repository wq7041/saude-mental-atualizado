<?php
require_once __DIR__ . '/auth.php';
$usuario = autenticar();
$db = getDB();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $mes = $_GET['mes'] ?? date('m');
    $ano = $_GET['ano'] ?? date('Y');
    $stmt = $db->prepare("
        SELECT DAY(data_checkin) as dia, emocao
        FROM checkin_humor
        WHERE usuario_id = ? AND MONTH(data_checkin) = ? AND YEAR(data_checkin) = ?
    ");
    $stmt->execute([$usuario['sub'], $mes, $ano]);
    $dados = [];
    while ($row = $stmt->fetch()) {
        $dados[(int)$row['dia']] = $row['emocao'];
    }
    echo json_encode($dados);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);