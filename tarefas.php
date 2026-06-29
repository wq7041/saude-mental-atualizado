<?php
require_once 'auth.php';
$usuario = autenticar();
$db = getDB();
$method = $_SERVER['REQUEST_METHOD'];

header('Content-Type: application/json');

// GET - listar tarefas (com busca opcional)
if ($method === 'GET') {
    $busca = $_GET['busca'] ?? '';
    $filtro = $_GET['filtro'] ?? 'todas'; // todas, pendentes, concluidas
    $ordem = $_GET['ordem'] ?? 'data';
$busca = $_GET['busca'] ?? '';

if (!empty($busca)) {
    $sql .= " AND (titulo LIKE ? OR descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}
// Tarefas fixadas primeiro
$sql .= " ORDER BY fixada DESC, ";

    $sql = "SELECT * FROM tarefas WHERE usuario_id = ?";
    $params = [$usuario['sub']];

    if (!empty($busca)) {
        $sql .= " AND (titulo LIKE ? OR descricao LIKE ?)";
        $params[] = "%$busca%";
        $params[] = "%$busca%";
    }
    if ($filtro === 'pendentes') {
        $sql .= " AND concluida = 0";
    } elseif ($filtro === 'concluidas') {
        $sql .= " AND concluida = 1";
    }

    switch ($ordem) {
        case 'prioridade':
            $sql .= " ORDER BY FIELD(prioridade, 'alta','media','baixa'), data_prevista ASC";
            break;
        default:
            $sql .= " ORDER BY data_prevista ASC";
    }

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $tarefas = $stmt->fetchAll();
    echo json_encode($tarefas);
    exit;
}

// POST - criar nova tarefa
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $titulo = $input['titulo'] ?? 'Sem título';
    $descricao = $input['descricao'] ?? '';
    $data_prevista = $input['data_prevista'] ?? null;
    $hora_prevista = $input['hora_prevista'] ?? null;
    $prioridade = $input['prioridade'] ?? 'media';
    $categoria = $input['categoria'] ?? 'geral';
    $fixada = $input['fixada'] ?? false;

    $stmt = $db->prepare("INSERT INTO tarefas (usuario_id, titulo, descricao, data_prevista, hora_prevista, prioridade, categoria, fixada) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$usuario['sub'], $titulo, $descricao, $data_prevista, $hora_prevista, $prioridade, $categoria, $fixada ? 1 : 0]);
    echo json_encode(['sucesso' => true, 'id' => $db->lastInsertId()]);
    exit;
}

// PUT - atualizar tarefa
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = $input['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da tarefa é obrigatório.']);
        exit;
    }

    // Verifica se a tarefa pertence ao usuário
    $stmt = $db->prepare("SELECT id FROM tarefas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario['sub']]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['erro' => 'Tarefa não encontrada.']);
        exit;
    }

    $campos = [];
    $valores = [];
    foreach (['titulo','descricao','data_prevista','hora_prevista','prioridade','categoria','fixada','concluida'] as $campo) {
        if (isset($input[$campo])) {
            $campos[] = "$campo = ?";
            $valores[] = $input[$campo];
        }
    }
    if (empty($campos)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nenhum campo para atualizar.']);
        exit;
    }

    $valores[] = $id;
    $sql = "UPDATE tarefas SET " . implode(', ', $campos) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($valores);
    echo json_encode(['sucesso' => true]);
    exit;
}

// DELETE - excluir tarefa
if ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['erro' => 'ID da tarefa é obrigatório.']);
        exit;
    }
    $stmt = $db->prepare("DELETE FROM tarefas WHERE id = ? AND usuario_id = ?");
    $stmt->execute([$id, $usuario['sub']]);
    echo json_encode(['sucesso' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);