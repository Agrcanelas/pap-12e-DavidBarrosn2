<?php
session_start();

// Ativar exibição de erros para debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

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

// Incluir conexão à BD
require_once "db.php";

try {
    // Obter dados do formulário
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data = $_POST['data'];
    $local = trim($_POST['local']);
    $utilizador_id = $_SESSION['user']['utilizador_id'];
    
    // DEBUG: Mostrar dados recebidos
    echo "<!-- DEBUG INFO:<br>";
    echo "Nome: " . htmlspecialchars($nome) . "<br>";
    echo "Descrição: " . htmlspecialchars($descricao) . "<br>";
    echo "Data: " . htmlspecialchars($data) . "<br>";
    echo "Local: " . htmlspecialchars($local) . "<br>";
    echo "Utilizador ID: " . htmlspecialchars($utilizador_id) . "<br>";
    echo "-->";
    
    // Validar dados
    if (empty($nome) || empty($descricao) || empty($data) || empty($local)) {
        header("Location: index.php?erro=campos_vazios#criar-evento");
        exit;
    }
    
    // Processar imagem (se enviada)
    $imagem_nome = null;
    
    if (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];
        
        // DEBUG: Info da imagem
        echo "<!-- DEBUG IMAGEM:<br>";
        echo "Nome: " . htmlspecialchars($imagem['name']) . "<br>";
        echo "Tipo: " . htmlspecialchars($imagem['type']) . "<br>";
        echo "Tamanho: " . $imagem['size'] . " bytes<br>";
        echo "Erro: " . $imagem['error'] . "<br>";
        echo "-->";
        
        // Validar tipo de arquivo
        $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $imagem['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, $tipos_permitidos)) {
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
            if (!mkdir("uploads", 0755, true)) {
                header("Location: index.php?erro=criar_pasta#criar-evento");
                exit;
            }
        }
        
        // Gerar nome único para a imagem
        $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
        $imagem_nome = uniqid('evento_') . '.' . strtolower($extensao);
        
        // Mover arquivo
        if (!move_uploaded_file($imagem['tmp_name'], "uploads/" . $imagem_nome)) {
            header("Location: index.php?erro=upload#criar-evento");
            exit;
        }
        
        echo "<!-- Imagem guardada: " . htmlspecialchars($imagem_nome) . " -->";
    }
    
    // Verificar se a conexão PDO está ativa
    if (!isset($pdo)) {
        throw new Exception("Conexão PDO não está definida!");
    }
    
    // Preparar SQL - IMPORTANTE: usar os nomes corretos das colunas
    $sql = "INSERT INTO evento (nome, descricao, data_evento, local_evento, imagem, utilizador_id, data_criacao)
            VALUES (:nome, :descricao, :data_evento, :local_evento, :imagem, :utilizador_id, NOW())";
    
    echo "<!-- SQL: " . htmlspecialchars($sql) . " -->";
    
    // Preparar statement
    $stmt = $pdo->prepare($sql);
    
    // Executar com os parâmetros
    $resultado = $stmt->execute([
        ':nome' => $nome,
        ':descricao' => $descricao,
        ':data_evento' => $data,
        ':local_evento' => $local,
        ':imagem' => $imagem_nome,
        ':utilizador_id' => $utilizador_id
    ]);
    
    if ($resultado) {
        $evento_id = $pdo->lastInsertId();
        echo "<!-- Evento criado com ID: " . $evento_id . " -->";
        
        // Redirecionar com sucesso
        header("Location: index.php?sucesso=1#eventosProjetos");
        exit;
    } else {
        // Se execute retornar false
        $errorInfo = $stmt->errorInfo();
        echo "<!-- Erro PDO: " . print_r($errorInfo, true) . " -->";
        throw new Exception("Execute retornou false");
    }
    
} catch (PDOException $e) {
    // Erro de base de dados
    $erro_msg = $e->getMessage();
    $erro_code = $e->getCode();
    
    // Log detalhado do erro
    error_log("ERRO AO GUARDAR EVENTO:");
    error_log("Código: " . $erro_code);
    error_log("Mensagem: " . $erro_msg);
    error_log("Stack: " . $e->getTraceAsString());
    
    // Mostrar erro na página (apenas para debug - remover em produção)
    echo "<h2>Erro ao guardar evento</h2>";
    echo "<p><strong>Código:</strong> " . htmlspecialchars($erro_code) . "</p>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($erro_msg) . "</p>";
    echo "<p><strong>Ficheiro:</strong> " . htmlspecialchars($e->getFile()) . "</p>";
    echo "<p><strong>Linha:</strong> " . htmlspecialchars($e->getLine()) . "</p>";
    echo "<hr>";
    echo "<p>Por favor, copie esta informação e envie para diagnóstico.</p>";
    echo "<p><a href='index.php'>Voltar</a></p>";
    
    // Apagar imagem se foi feito upload
    if (isset($imagem_nome) && file_exists("uploads/" . $imagem_nome)) {
        unlink("uploads/" . $imagem_nome);
    }
    
    // Comentado para mostrar erro completo
    // header("Location: index.php?erro=bd#criar-evento");
    exit;
    
} catch (Exception $e) {
    // Outros erros
    echo "<h2>Erro geral</h2>";
    echo "<p><strong>Mensagem:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<hr>";
    echo "<p><a href='index.php'>Voltar</a></p>";
    
    // Apagar imagem se foi feito upload
    if (isset($imagem_nome) && file_exists("uploads/" . $imagem_nome)) {
        unlink("uploads/" . $imagem_nome);
    }
    exit;
}
?>