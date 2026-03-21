<?php
session_start();
header('Content-Type: application/json');
require_once 'db.php';

$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

// ===== BUSCAR COMENTÁRIOS =====
if ($acao === 'buscar') {
    $evento_id = intval($_GET['evento_id'] ?? 0);
    if ($evento_id === 0) {
        echo json_encode(['erro' => 'Evento inválido.']);
        exit;
    }

    try {
        // Verificar primeiro se a tabela existe
        $check = $pdo->query("SHOW TABLES LIKE 'comentario'");
        if (!$check->fetch()) {
            // Criar a tabela automaticamente se não existir
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
                        FOREIGN KEY (evento_id)
                        REFERENCES evento(evento_id)
                        ON DELETE CASCADE ON UPDATE CASCADE,
                    CONSTRAINT fk_coment_utilizador
                        FOREIGN KEY (utilizador_id)
                        REFERENCES utilizador(utilizador_id)
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
            ");
        }

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
        echo json_encode(['erro' => 'Erro ao buscar comentários: ' . $e->getMessage()]);
    }
    exit;
}

// ===== GUARDAR COMENTÁRIO =====
if ($acao === 'guardar') {
    if (!isset($_SESSION['user'])) {
        echo json_encode(['erro' => 'Precisa fazer login para comentar.']);
        exit;
    }

    $evento_id     = intval($_POST['evento_id'] ?? 0);
    $texto         = trim($_POST['texto'] ?? '');
    $utilizador_id = $_SESSION['user']['utilizador_id'];

    if ($evento_id === 0 || empty($texto)) {
        echo json_encode(['erro' => 'Texto vazio.']);
        exit;
    }
    if (mb_strlen($texto) > 1000) {
        echo json_encode(['erro' => 'Máximo 1000 caracteres.']);
        exit;
    }

    try {
        // Garantir que a tabela existe antes de inserir
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
                    FOREIGN KEY (evento_id)
                    REFERENCES evento(evento_id)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                CONSTRAINT fk_coment_utilizador
                    FOREIGN KEY (utilizador_id)
                    REFERENCES utilizador(utilizador_id)
                    ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
        ");

        $stmt = $pdo->prepare("
            INSERT INTO comentario (evento_id, utilizador_id, texto)
            VALUES (:eid, :uid, :texto)
        ");
        $stmt->execute([
            ':eid'   => $evento_id,
            ':uid'   => $utilizador_id,
            ':texto' => $texto
        ]);

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
        echo json_encode(['erro' => 'Erro ao guardar comentário: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['erro' => 'Ação inválida.']);
?>