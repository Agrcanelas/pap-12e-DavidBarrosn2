<?php
session_start();
header('Content-Type: application/json');

// Só permite logged-in users
if (!isset($_SESSION['user'])) {
    echo json_encode(['erro' => 'Precisa fazer login.']);
    exit;
}

require_once 'db.php';

$utilizador_id = $_SESSION['user']['utilizador_id'];
$evento_id     = intval($_POST['evento_id'] ?? 0);

if ($evento_id === 0) {
    echo json_encode(['erro' => 'Evento inválido.']);
    exit;
}

try {
    // Verificar se já está inscrito
    $stmt = $pdo->prepare(
        "SELECT 1 FROM participa WHERE evento_id = :eid AND utilizador_id = :uid"
    );
    $stmt->execute([':eid' => $evento_id, ':uid' => $utilizador_id]);
    $existe = $stmt->fetch();

    if ($existe) {
        // Remover inscrição
        $pdo->prepare(
            "DELETE FROM participa WHERE evento_id = :eid AND utilizador_id = :uid"
        )->execute([':eid' => $evento_id, ':uid' => $utilizador_id]);

        echo json_encode(['estado' => 'removido']);
    } else {
        // Adicionar inscrição
        $pdo->prepare(
            "INSERT INTO participa (evento_id, utilizador_id) VALUES (:eid, :uid)"
        )->execute([':eid' => $evento_id, ':uid' => $utilizador_id]);

        echo json_encode(['estado' => 'inscrito']);
    }
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro no servidor.']);
}
?>