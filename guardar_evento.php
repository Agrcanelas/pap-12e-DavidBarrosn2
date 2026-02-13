<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar se o utilizador está logado
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
    // Obter dados do formulário
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    $data = $_POST['data'];
    $local = trim($_POST['local']);
    $utilizador_id = $_SESSION['user']['utilizador_id'];
    
    // Validar dados básicos
    if (empty($nome) || empty($descricao) || empty($data) || empty($local)) {
        header("Location: index.php?erro=campos_vazios#criar-evento");
        exit;
    }
    
    // Criar diretórios se não existirem
    if (!is_dir("uploads")) {
        mkdir("uploads", 0755, true);
    }
    if (!is_dir("uploads/eventos")) {
        mkdir("uploads/eventos", 0755, true);
    }
    
    // Arrays para armazenar imagens
    $imagens_guardadas = [];
    $primeira_imagem = null;
    
    // Processar múltiplas imagens (até 5)
    if (!empty($_FILES['imagens']['name'][0])) {
        $total_imagens = count($_FILES['imagens']['name']);
        
        // Limitar a 5 imagens
        $max_imagens = min($total_imagens, 5);
        
        for ($i = 0; $i < $max_imagens; $i++) {
            // Verificar se não houve erro no upload
            if ($_FILES['imagens']['error'][$i] === UPLOAD_ERR_OK) {
                $imagem_tmp = $_FILES['imagens']['tmp_name'][$i];
                $imagem_nome_original = $_FILES['imagens']['name'][$i];
                $imagem_size = $_FILES['imagens']['size'][$i];
                
                // Validar tipo MIME
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $imagem_tmp);
                finfo_close($finfo);
                
                $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!in_array($mime, $tipos_permitidos)) {
                    // Pular esta imagem se o tipo for inválido
                    continue;
                }
                
                // Validar tamanho (máx 5MB)
                if ($imagem_size > 5 * 1024 * 1024) {
                    // Pular esta imagem se for muito grande
                    continue;
                }
                
                // Gerar nome único
                $extensao = pathinfo($imagem_nome_original, PATHINFO_EXTENSION);
                $imagem_nome = 'evento_' . uniqid() . '_' . $i . '.' . strtolower($extensao);
                
                // Mover arquivo
                if (move_uploaded_file($imagem_tmp, "uploads/eventos/" . $imagem_nome)) {
                    $imagens_guardadas[] = [
                        'nome' => $imagem_nome,
                        'ordem' => $i
                    ];
                    
                    // A primeira imagem é a capa
                    if ($i === 0) {
                        $primeira_imagem = $imagem_nome;
                    }
                }
            }
        }
    }
    // Se não houver múltiplas imagens, tentar upload único (compatibilidade)
    elseif (!empty($_FILES['imagem']['name'])) {
        $imagem = $_FILES['imagem'];
        
        if ($imagem['error'] === UPLOAD_ERR_OK) {
            // Validar tipo
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $imagem['tmp_name']);
            finfo_close($finfo);
            
            $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($mime, $tipos_permitidos)) {
                header("Location: index.php?erro=tipo_imagem#criar-evento");
                exit;
            }
            
            // Validar tamanho
            if ($imagem['size'] > 5 * 1024 * 1024) {
                header("Location: index.php?erro=tamanho_imagem#criar-evento");
                exit;
            }
            
            // Gerar nome único
            $extensao = pathinfo($imagem['name'], PATHINFO_EXTENSION);
            $imagem_nome = 'evento_' . uniqid() . '.' . strtolower($extensao);
            
            // Mover arquivo
            if (move_uploaded_file($imagem['tmp_name'], "uploads/eventos/" . $imagem_nome)) {
                $primeira_imagem = $imagem_nome;
                $imagens_guardadas[] = [
                    'nome' => $imagem_nome,
                    'ordem' => 0
                ];
            }
        }
    }
    
    // Inserir evento na base de dados
    $sql = "INSERT INTO evento (nome, descricao, data_evento, local_evento, imagem, utilizador_id, data_criacao)
            VALUES (:nome, :descricao, :data_evento, :local_evento, :imagem, :utilizador_id, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $resultado = $stmt->execute([
        ':nome' => $nome,
        ':descricao' => $descricao,
        ':data_evento' => $data,
        ':local_evento' => $local,
        ':imagem' => $primeira_imagem, // Imagem da capa (primeira)
        ':utilizador_id' => $utilizador_id
    ]);
    
    if ($resultado) {
        $evento_id = $pdo->lastInsertId();
        
        // Inserir todas as imagens na tabela evento_imagem (se a tabela existir)
        if (!empty($imagens_guardadas)) {
            try {
                $sql_img = "INSERT INTO evento_imagem (evento_id, nome_ficheiro, ordem) 
                           VALUES (:evento_id, :nome_ficheiro, :ordem)";
                $stmt_img = $pdo->prepare($sql_img);
                
                foreach ($imagens_guardadas as $img) {
                    $stmt_img->execute([
                        ':evento_id' => $evento_id,
                        ':nome_ficheiro' => $img['nome'],
                        ':ordem' => $img['ordem']
                    ]);
                }
            } catch (PDOException $e) {
                // Se a tabela evento_imagem não existir, apenas log (não falhar)
                error_log("Aviso: Tabela evento_imagem pode não existir. " . $e->getMessage());
            }
        }
        
        // Sucesso - redirecionar
        header("Location: index.php?sucesso=1#eventosProjetos");
        exit;
    } else {
        throw new Exception("Erro ao executar INSERT");
    }
    
} catch (PDOException $e) {
    // Erro de base de dados
    error_log("ERRO BD ao guardar evento: " . $e->getMessage());
    
    // Apagar imagens se foram enviadas
    if (!empty($imagens_guardadas)) {
        foreach ($imagens_guardadas as $img) {
            $path = "uploads/eventos/" . $img['nome'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
    
    header("Location: index.php?erro=bd#criar-evento");
    exit;
    
} catch (Exception $e) {
    // Outros erros
    error_log("ERRO GERAL: " . $e->getMessage());
    
    // Apagar imagens se foram enviadas
    if (!empty($imagens_guardadas)) {
        foreach ($imagens_guardadas as $img) {
            $path = "uploads/eventos/" . $img['nome'];
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }
    
    header("Location: index.php?erro=geral#criar-evento");
    exit;
}
?>