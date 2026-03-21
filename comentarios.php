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
        // Garantir que a tabela existe
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS comentario (
                comentario_id   INT NOT NULL AUTO_INCREMENT,
                evento_id       INT NOT NULL,
                utilizador_id   INT NOT NULL,
                texto           TEXT NOT NULL,
                data_comentario TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (comentario_id),
                INDEX idx_evento     (evento_id),
                INDEX idx_utilizador (utilizador_id),
                INDEX idx_data       (data_comentario),
                CONSTRAINT fk_coment_evento
                    FOREIGN KEY (evento_id) REFERENCES evento(evento_id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_coment_utilizador
                    FOREIGN KEY (utilizador_id) REFERENCES utilizador(utilizador_id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $utilizador_logado_id = isset($_SESSION['user']) ? intval($_SESSION['user']['utilizador_id']) : 0;

        // Buscar criador do evento
        $stmtEv = $pdo->prepare("SELECT utilizador_id FROM evento WHERE evento_id = :eid");
        $stmtEv->execute([':eid' => $evento_id]);
        $criador_evento_id = intval($stmtEv->fetchColumn());

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
            // Pode eliminar se for o autor do comentário OU o criador do evento
            $c['pode_eliminar']  = $utilizador_logado_id > 0 && (
                intval($c['utilizador_id']) === $utilizador_logado_id ||
                $criador_evento_id === $utilizador_logado_id
            );
        }

        echo json_encode(['comentarios' => $comentarios]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao buscar comentarios.']);
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
        echo json_encode(['erro' => 'Maximo 1000 caracteres.']); exit;
    }

    try {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS comentario (
                comentario_id   INT NOT NULL AUTO_INCREMENT,
                evento_id       INT NOT NULL,
                utilizador_id   INT NOT NULL,
                texto           TEXT NOT NULL,
                data_comentario TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (comentario_id),
                INDEX idx_evento     (evento_id),
                INDEX idx_utilizador (utilizador_id),
                INDEX idx_data       (data_comentario),
                CONSTRAINT fk_coment_evento
                    FOREIGN KEY (evento_id) REFERENCES evento(evento_id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_coment_utilizador
                    FOREIGN KEY (utilizador_id) REFERENCES utilizador(utilizador_id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

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
            'utilizador_id'  => $utilizador_id,
            'pode_eliminar'  => true
        ]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao guardar comentario.']);
    }
    exit;
}

// ===== ELIMINAR COMENTÁRIO =====
if ($acao === 'eliminar') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['erro' => 'Precisa fazer login.']); exit;
    }

    $comentario_id = intval($_POST['comentario_id'] ?? 0);
    $utilizador_id = intval($_SESSION['user']['utilizador_id']);

    if ($comentario_id === 0) {
        echo json_encode(['erro' => 'Comentario invalido.']); exit;
    }

    try {
        // Buscar autor do comentário e criador do evento
        $stmt = $pdo->prepare("
            SELECT c.utilizador_id AS autor_id, e.utilizador_id AS criador_evento_id
            FROM comentario c
            JOIN evento e ON c.evento_id = e.evento_id
            WHERE c.comentario_id = :cid
        ");
        $stmt->execute([':cid' => $comentario_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(['erro' => 'Comentario nao encontrado.']); exit;
        }

        $e_autor          = intval($row['autor_id'])          === $utilizador_id;
        $e_criador_evento = intval($row['criador_evento_id']) === $utilizador_id;

        if (!$e_autor && !$e_criador_evento) {
            echo json_encode(['erro' => 'Nao tem permissao para eliminar este comentario.']); exit;
        }

        $stmt = $pdo->prepare("DELETE FROM comentario WHERE comentario_id = :cid");
        $stmt->execute([':cid' => $comentario_id]);

        echo json_encode(['sucesso' => true]);
    } catch (PDOException $e) {
        echo json_encode(['erro' => 'Erro ao eliminar comentario.']);
    }
    exit;
}

echo json_encode(['erro' => 'Acao invalida.']);
?>