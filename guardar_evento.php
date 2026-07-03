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
    $nome          = trim($_POST['nome']);
    $descricao     = trim($_POST['descricao']);
    $data_inicio   = $_POST['data_inicio'];
    $hora_inicio   = $_POST['hora_inicio'];
    $data_fim      = $_POST['data_fim'];
    $hora_fim      = $_POST['hora_fim'];
    $local         = trim($_POST['local']);
    $utilizador_id = $_SESSION['user']['utilizador_id'];

    if (empty($nome) || empty($descricao) || empty($data_inicio) || empty($hora_inicio) ||
        empty($data_fim) || empty($hora_fim) || empty($local)) {
        header("Location: index.php?erro=campos_vazios#criar-evento");
        exit;
    }

    // Validar: data de fim não pode ser antes de data de início
    $dt_inicio = strtotime("$data_inicio $hora_inicio");
    $dt_fim    = strtotime("$data_fim $hora_fim");
    if ($dt_fim < $dt_inicio) {
        header("Location: index.php?erro=datas_invalidas#criar-evento");
        exit;
    }

    // Criar diretórios
    if (!is_dir("uploads"))         mkdir("uploads",         0755, true);
    if (!is_dir("uploads/eventos")) mkdir("uploads/eventos", 0755, true);

    $imagem_capa    = null;   // 1ª foto = capa
    $imagens_extras = [];     // restantes

    // Processar até 5 imagens (name="imagens[]")
    if (!empty($_FILES['imagens']['name'][0])) {
        $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        $total = min(count($_FILES['imagens']['name']), 5);

        for ($i = 0; $i < $total; $i++) {
            if ($_FILES['imagens']['error'][$i] !== UPLOAD_ERR_OK) continue;

            $tmp  = $_FILES['imagens']['tmp_name'][$i];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $tmp);
            finfo_close($finfo);

            if (!in_array($mime, $tipos_permitidos)) {
                header("Location: index.php?erro=tipo_imagem#criar-evento");
                exit;
            }
            if ($_FILES['imagens']['size'][$i] > 5 * 1024 * 1024) {
                header("Location: index.php?erro=tamanho_imagem#criar-evento");
                exit;
            }

            $ext       = strtolower(pathinfo($_FILES['imagens']['name'][$i], PATHINFO_EXTENSION));
            $nome_file = 'evento_' . uniqid() . '.' . $ext;

            if (move_uploaded_file($tmp, "uploads/eventos/" . $nome_file)) {
                if ($i === 0) {
                    $imagem_capa = $nome_file;
                } else {
                    $imagens_extras[] = $nome_file;
                }
            }
        }
    }

    $imagens_json = !empty($imagens_extras) ? json_encode($imagens_extras) : null;

    $sql = "INSERT INTO evento
              (nome, descricao, data_inicio, hora_inicio, data_fim, hora_fim,
               local_evento, imagem, imagens_extras, utilizador_id, data_criacao)
            VALUES
              (:nome, :descricao, :data_inicio, :hora_inicio, :data_fim, :hora_fim,
               :local_evento, :imagem, :imagens_extras, :utilizador_id, NOW())";

    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        ':nome'          => $nome,
        ':descricao'     => $descricao,
        ':data_inicio'   => $data_inicio,
        ':hora_inicio'   => $hora_inicio,
        ':data_fim'      => $data_fim,
        ':hora_fim'      => $hora_fim,
        ':local_evento'  => $local,
        ':imagem'        => $imagem_capa,
        ':imagens_extras'=> $imagens_json,
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
    if (!empty($imagem_capa) && file_exists("uploads/eventos/" . $imagem_capa))
        unlink("uploads/eventos/" . $imagem_capa);
    foreach ($imagens_extras as $img)
        if (file_exists("uploads/eventos/" . $img)) unlink("uploads/eventos/" . $img);
    // Mostra o erro real da BD (temporário, para diagnóstico).
    // Depois de confirmares que está tudo a funcionar, podes voltar a usar
    // apenas: header("Location: index.php?erro=bd#criar-evento"); exit;
    header("Location: index.php?erro=bd&detalhe=" . urlencode($e->getMessage()) . "#criar-evento");
    exit;

} catch (Exception $e) {
    error_log("ERRO GERAL: " . $e->getMessage());
    header("Location: index.php?erro=geral#criar-evento");
    exit;
}
?>