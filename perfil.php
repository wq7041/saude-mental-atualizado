<?php
require_once __DIR__ . '/auth.php';
$usuario = autenticar();
$db = getDB();
header('Content-Type: application/json');
$method = $_SERVER['REQUEST_METHOD'];

// GET - obter perfil
if ($method === 'GET') {
    $stmt = $db->prepare("SELECT id, nome, email, foto_url, bio, data_criacao, ultimo_login, tema_preferido, xp_total FROM usuarios WHERE id = ?");
    $stmt->execute([$usuario['sub']]);
    $perfil = $stmt->fetch();
    if (!$perfil) {
        http_response_code(404);
        echo json_encode(['erro' => 'Usuário não encontrado.']);
        exit;
    }
    echo json_encode($perfil);
    exit;
}

// PUT - atualizar perfil
if ($method === 'PUT' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }

    $campos = [];
    $valores = [];

    if (isset($input['nome'])) {
        $campos[] = "nome = ?";
        $valores[] = trim($input['nome']);
    }
    if (isset($input['bio'])) {
        $campos[] = "bio = ?";
        $valores[] = trim($input['bio']);
    }
    if (isset($input['tema_preferido'])) {
        $campos[] = "tema_preferido = ?";
        $valores[] = $input['tema_preferido'];
    }
    if (isset($input['foto_base64']) && !empty($input['foto_base64'])) {
        $campos[] = "foto_url = ?";
        $valores[] = $input['foto_base64'];
    } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $imagem = file_get_contents($_FILES['foto']['tmp_name']);
        $base64 = 'data:' . $_FILES['foto']['type'] . ';base64,' . base64_encode($imagem);
        $campos[] = "foto_url = ?";
        $valores[] = $base64;
    } elseif (isset($input['foto_url'])) {
        $campos[] = "foto_url = ?";
        $valores[] = $input['foto_url'];
    }

    if (empty($campos)) {
        http_response_code(400);
        echo json_encode(['erro' => 'Nenhum campo para atualizar.']);
        exit;
    }

    $valores[] = $usuario['sub'];
    $sql = "UPDATE usuarios SET " . implode(', ', $campos) . " WHERE id = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute($valores);
    echo json_encode(['sucesso' => true, 'mensagem' => 'Perfil atualizado.']);
    exit;
}

http_response_code(405);
echo json_encode(['erro' => 'Método não permitido.']);