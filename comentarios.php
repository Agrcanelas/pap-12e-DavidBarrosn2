<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// ===== BUSCAR COMENTÁRIOS =====
if ($acao === 'buscar') {
    $evento_id = intval($_GET['evento_id'] ?? 0);
    if ($evento_id === 0) { echo json_encode(['erro' => 'Evento inválido.']); exit; }

    try {
        $stmt = $pdo->prepare("
            SELECT c.comentario_id, c.texto, c.data_comentario,
                   u.utilizador_id, u.nome, u.foto_perfil
            FROM comentario c
            JOIN utilizador u ON c.utilizador_id = u.utilizador_id
            WHERE c.evento_id = :eid
            ORDER BY c.data_comentario ASC
        ");
        $stmt->execute([':eid' => $evento_id]);
        $comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($comentarios as &$c) {
            $c['data_formatada'] = date('d/m/Y H:i', strtotime($c['data_comentario']));
            $c['foto_url']       = !empty($c['foto_perfil']) ? 'uploads/perfil/' . $c['foto_perfil'] : null;
            $c['inicial']        = strtoupper(substr($c['nome'], 0, 1));
        }

        echo json_encode(['comentarios' => $comentarios]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao buscar comentários.']);
    }
    exit;
}

// ===== GUARDAR COMENTÁRIO =====
if ($acao === 'guardar') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['erro' => 'Precisa fazer login para comentar.']); exit;
    }

    $evento_id     = intval($_POST['evento_id'] ?? 0);
    $texto         = trim($_POST['texto'] ?? '');
    $utilizador_id = $_SESSION['user']['utilizador_id'];

    if ($evento_id === 0 || empty($texto)) {
        echo json_encode(['erro' => 'Texto vazio.']); exit;
    }
    if (mb_strlen($texto) > 1000) {
        echo json_encode(['erro' => 'Máximo 1000 caracteres.']); exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO comentario (evento_id, utilizador_id, texto) VALUES (:eid, :uid, :texto)");
        $stmt->execute([':eid' => $evento_id, ':uid' => $utilizador_id, ':texto' => $texto]);

        $u        = $_SESSION['user'];
        $foto_url = !empty($u['foto_perfil']) ? 'uploads/perfil/' . $u['foto_perfil'] : null;

        echo json_encode([
            'sucesso'        => true,
            'comentario_id'  => $pdo->lastInsertId(),
            'texto'          => $texto,
            'nome'           => $u['nome'],
            'foto_url'       => $foto_url,
            'inicial'        => strtoupper(substr($u['nome'], 0, 1)),
            'data_formatada' => date('d/m/Y H:i'),
            'utilizador_id'  => $utilizador_id
        ]);
    } catch (PDOException $e) {
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            echo json_encode(['erro' => 'Execute o ficheiro adicionar_comentarios.sql no phpMyAdmin primeiro!']);
        } else {
            echo json_encode(['erro' => 'Erro ao guardar comentário.']);
        }
    }
    exit;
}

echo json_encode(['erro' => 'Ação inválida.']);
?>