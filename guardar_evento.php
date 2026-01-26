<?php
session_start();
require_once "db.php";

// Verificar se o utilizador está logado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// Verificar se é um POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}

try {
    // Obter dados do formulário
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data = $_POST['data'];
    $local = trim($_POST['local']);
    $utilizador_id = $_SESSION['user']['utilizador_id'];
    
    // Validar dados
    if (empty($nome) || empty($descricao) || empty($data) || empty($local)) {
        header("Location: index.php?erro=campos_vazios#criar-evento");
        exit;
    }
    
    // Processar imagem (se enviada)
    $imagem_nome = null;
    
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];
        
        // Validar tipo de arquivo
        $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($imagem['type'], $tipos_permitidos)) {
            header("Location: index.php?erro=tipo_imagem#criar-evento");
            exit;
        }
        
        // Validar tamanho (máx 5MB)
        if ($imagem['size'] > 5 * 1024 * 1024) {
            header("Location: index.php?erro=tamanho_imagem#criar-evento");
            exit;
        }
        
        // Criar diretório uploads se não existir
        if (!is_dir("uploads")) {
            mkdir("uploads", 0755, true);
        }
        
        // Gerar nome único para a imagem
        $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
        $imagem_nome = uniqid('evento_') . '.' . $extensao;
        
        // Mover arquivo
        if (!move_uploaded_file($imagem['tmp_name'], "uploads/" . $imagem_nome)) {
            header("Location: index.php?erro=upload#criar-evento");
            exit;
        }
    }
    
    // Inserir na base de dados
    $sql = "INSERT INTO evento (nome, descricao, data_evento, local_evento, imagem, utilizador_id)
            VALUES (:nome, :descricao, :data_evento, :local_evento, :imagem, :utilizador_id)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nome' => $nome,
        ':descricao' => $descricao,
        ':data_evento' => $data,
        ':local_evento' => $local,
        ':imagem' => $imagem_nome,
        ':utilizador_id' => $utilizador_id
    ]);
    
    // Redirecionar com sucesso
    header("Location: index.php?sucesso=1#eventosProjetos");
    exit;
    
} catch (PDOException $e) {
    // Log do erro (em produção, use um sistema de log adequado)
    error_log("Erro ao guardar evento: " . $e->getMessage());
    
    // Apagar imagem se foi feito upload
    if (isset($imagem_nome) && file_exists("uploads/" . $imagem_nome)) {
        unlink("uploads/" . $imagem_nome);
    }
    
    header("Location: index.php?erro=bd#criar-evento");
    exit;
}
?>