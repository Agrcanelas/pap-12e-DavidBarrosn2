<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

require_once "db.php";

try {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data = $_POST['data'];
    $local = trim($_POST['local']);
    $utilizador_id = $_SESSION['user']['utilizador_id'];

    if (empty($nome) || empty($descricao) || empty($data) || empty($local)) {
        header("Location: index.php?erro=campos_vazios#criar-evento");
        exit;
    }

    // Criar diretório se não existir
    if (!is_dir("uploads")) {
        mkdir("uploads", 0755, true);
    }
    if (!is_dir("uploads/eventos")) {
        mkdir("uploads/eventos", 0755, true);
    }

    $imagem_nome = null;

    // Processar imagem única
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];

        if ($imagem['error'] === UPLOAD_ERR_OK) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $imagem['tmp_name']);
            finfo_close($finfo);

            $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

            if (!in_array($mime, $tipos_permitidos)) {
                header("Location: index.php?erro=tipo_imagem#criar-evento");
                exit;
            }

            if ($imagem['size'] > 5 * 1024 * 1024) {
                header("Location: index.php?erro=tamanho_imagem#criar-evento");
                exit;
            }

            $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
            $imagem_nome = 'evento_' . uniqid() . '.' . strtolower($extensao);

            if (!move_uploaded_file($imagem['tmp_name'], "uploads/eventos/" . $imagem_nome)) {
                $imagem_nome = null;
            }
        }
    }

    // Inserir evento na base de dados
    $sql = "INSERT INTO evento (nome, descricao, data_evento, local_evento, imagem, utilizador_id, data_criacao)
            VALUES (:nome, :descricao, :data_evento, :local_evento, :imagem, :utilizador_id, NOW())";

    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        ':nome'          => $nome,
        ':descricao'     => $descricao,
        ':data_evento'   => $data,
        ':local_evento'  => $local,
        ':imagem'        => $imagem_nome,
        ':utilizador_id' => $utilizador_id
    ]);

    if ($resultado) {
        header("Location: index.php?sucesso=1#eventosProjetos");
        exit;
    } else {
        throw new Exception("Erro ao executar INSERT");
    }

} catch (PDOException $e) {
    error_log("ERRO BD ao guardar evento: " . $e->getMessage());

    if (!empty($imagem_nome) && file_exists("uploads/eventos/" . $imagem_nome)) {
        unlink("uploads/eventos/" . $imagem_nome);
    }

    header("Location: index.php?erro=bd#criar-evento");
    exit;

} catch (Exception $e) {
    error_log("ERRO GERAL: " . $e->getMessage());

    if (!empty($imagem_nome) && file_exists("uploads/eventos/" . $imagem_nome)) {
        unlink("uploads/eventos/" . $imagem_nome);
    }

    header("Location: index.php?erro=geral#criar-evento");
    exit;
}
?>