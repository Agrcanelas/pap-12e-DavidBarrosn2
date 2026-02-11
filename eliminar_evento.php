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
$evento_id = intval($_POST['evento_id'] ?? 0);

if ($evento_id === 0) {
    echo json_encode(['erro' => 'Evento inválido.']);
    exit;
}

try {
    // Verificar se o utilizador é o criador do evento
    $stmt = $pdo->prepare(
        "SELECT utilizador_id, imagem FROM evento WHERE evento_id = :eid"
    );
    $stmt->execute([':eid' => $evento_id]);
    $evento = $stmt->fetch();

    if (!$evento) {
        echo json_encode(['erro' => 'Evento não encontrado.']);
        exit;
    }

    if ($evento['utilizador_id'] != $utilizador_id) {
        echo json_encode(['erro' => 'Não tem permissão para eliminar este evento.']);
        exit;
    }

    // Eliminar a imagem se existir
    if ($evento['imagem'] && file_exists('uploads/' . $evento['imagem'])) {
        unlink('uploads/' . $evento['imagem']);
    }

    // Eliminar o evento (as participações são eliminadas automaticamente devido ao CASCADE)
    $stmt = $pdo->prepare("DELETE FROM evento WHERE evento_id = :eid");
    $stmt->execute([':eid' => $evento_id]);

    echo json_encode(['sucesso' => true, 'mensagem' => 'Evento eliminado com sucesso!']);

} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro no servidor.']);
}
?>