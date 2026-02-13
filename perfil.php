<?php
session_start();

// Verificar se o utilizador está logado
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

require_once 'db.php';

$utilizador_id = $_SESSION['user']['utilizador_id'];
$mensagem = '';
$erro = '';

// Processar upload de foto de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto_perfil'])) {
    $foto = $_FILES['foto_perfil'];
    
    // Validar tipo de arquivo
    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $foto['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $tipos_permitidos)) {
        $erro = "Tipo de arquivo inválido. Use apenas JPG, PNG ou GIF.";
    } elseif ($foto['size'] > 5 * 1024 * 1024) {
        $erro = "Arquivo muito grande. Tamanho máximo: 5MB.";
    } else {
        // Criar diretório uploads se não existir
        if (!is_dir("uploads/perfil")) {
            mkdir("uploads/perfil", 0755, true);
        }
        
        // Apagar foto antiga se existir
        $stmt = $pdo->prepare("SELECT foto_perfil FROM utilizador WHERE utilizador_id = :id");
        $stmt->execute([':id' => $utilizador_id]);
        $user_data = $stmt->fetch();
        
        if ($user_data['foto_perfil'] && file_exists("uploads/perfil/" . $user_data['foto_perfil'])) {
            unlink("uploads/perfil/" . $user_data['foto_perfil']);
        }
        
        // Gerar nome único
        $extensao = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $nome_arquivo = "perfil_" . $utilizador_id . "_" . time() . "." . strtolower($extensao);
        
        // Mover arquivo
        if (move_uploaded_file($foto['tmp_name'], "uploads/perfil/" . $nome_arquivo)) {
            // Atualizar base de dados
            $stmt = $pdo->prepare("UPDATE utilizador SET foto_perfil = :foto WHERE utilizador_id = :id");
            $stmt->execute([':foto' => $nome_arquivo, ':id' => $utilizador_id]);
            
            // Atualizar sessão
            $_SESSION['user']['foto_perfil'] = $nome_arquivo;
            
            $mensagem = "Foto de perfil atualizada com sucesso!";
        } else {
            $erro = "Erro ao fazer upload da foto.";
        }
    }
}

// Processar atualização de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_dados'])) {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $telefone = trim($_POST['telefone']);
    $nova_senha = trim($_POST['nova_senha']);
    
    try {
        if (!empty($nova_senha)) {
            // Atualizar com nova senha
            $stmt = $pdo->prepare("UPDATE utilizador SET nome = :nome, email = :email, telefone = :telefone, senha = :senha WHERE utilizador_id = :id");
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone,
                ':senha' => $nova_senha,
                ':id' => $utilizador_id
            ]);
        } else {
            // Atualizar sem alterar senha
            $stmt = $pdo->prepare("UPDATE utilizador SET nome = :nome, email = :email, telefone = :telefone WHERE utilizador_id = :id");
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':telefone' => $telefone,
                ':id' => $utilizador_id
            ]);
        }
        
        // Atualizar sessão
        $_SESSION['user']['nome'] = $nome;
        $_SESSION['user']['email'] = $email;
        $_SESSION['user']['telefone'] = $telefone;
        
        $mensagem = "Dados atualizados com sucesso!";
    } catch (PDOException $e) {
        $erro = "Erro ao atualizar dados: " . $e->getMessage();
    }
}

// Buscar dados atualizados do utilizador
$stmt = $pdo->prepare("SELECT * FROM utilizador WHERE utilizador_id = :id");
$stmt->execute([':id' => $utilizador_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Buscar estatísticas do utilizador
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM evento WHERE utilizador_id = :id");
$stmt->execute([':id' => $utilizador_id]);
$total_eventos_criados = $stmt->fetch()['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM participa WHERE utilizador_id = :id");
$stmt->execute([':id' => $utilizador_id]);
$total_participacoes = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Meu Perfil - HumaniCare</title>
<link rel="stylesheet" href="style.css">
<style>
.perfil-container {
  max-width: 900px;
  margin: 40px auto;
  padding: 0 20px;
}

.perfil-header {
  background: white;
  border: 2px solid #c8c0ae;
  border-radius: 12px;
  padding: 40px;
  margin-bottom: 30px;
  display: flex;
  gap: 40px;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.foto-perfil-container {
  text-align: center;
}

.foto-perfil {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid #58b79d;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  margin-bottom: 15px;
}

.foto-perfil-placeholder {
  width: 150px;
  height: 150px;
  border-radius: 50%;
  background: linear-gradient(135deg, #58b79d, #7a8c3c);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 60px;
  font-weight: bold;
  border: 4px solid #58b79d;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  margin-bottom: 15px;
}

.btn-upload-foto {
  background: #58b79d;
  color: white;
  padding: 8px 16px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.3s;
}

.btn-upload-foto:hover {
  background: #4a9c82;
  transform: translateY(-2px);
}

.perfil-info {
  flex: 1;
}

.perfil-info h2 {
  color: #7a8c3c;
  margin: 0 0 10px 0;
  font-size: 32px;
}

.perfil-info p {
  color: #666;
  margin: 5px 0;
  font-size: 16px;
}

.perfil-stats {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 15px;
  margin-top: 20px;
}

.stat-card {
  background: #f8f8f5;
  padding: 15px;
  border-radius: 8px;
  text-align: center;
  border: 1px solid #e0e0e0;
}

.stat-number {
  font-size: 32px;
  font-weight: bold;
  color: #58b79d;
  display: block;
}

.stat-label {
  color: #666;
  font-size: 14px;
}

.perfil-secao {
  background: white;
  border: 2px solid #c8c0ae;
  border-radius: 12px;
  padding: 35px;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.perfil-secao h3 {
  color: #7a8c3c;
  margin-top: 0;
  border-bottom: 2px solid #c8c0ae;
  padding-bottom: 12px;
  font-size: 24px;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: bold;
  color: #4a4a4a;
  margin-bottom: 8px;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #c8c0ae;
  border-radius: 6px;
  font-size: 16px;
  font-family: inherit;
  transition: all 0.3s;
  background: #fafafa;
  box-sizing: border-box;
}

.form-group input:focus {
  outline: none;
  border-color: #58b79d;
  background: white;
  box-shadow: 0 0 0 3px rgba(88, 183, 157, 0.1);
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.btn-submit {
  background: linear-gradient(135deg, #58b79d 0%, #4a9c82 100%);
  color: white;
  border: none;
  padding: 14px 32px;
  border-radius: 6px;
  font-size: 17px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0 4px 12px rgba(88, 183, 157, 0.3);
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(88, 183, 157, 0.4);
}

.mensagem {
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-weight: bold;
  text-align: center;
}

.mensagem.sucesso {
  background: #d4edda;
  color: #155724;
  border: 1px solid #badfcc;
}

.mensagem.erro {
  background: #ffe5e5;
  color: #c0392b;
  border: 1px solid #c0392b;
}

.btn-voltar {
  display: inline-block;
  background: #7a8c3c;
  color: white;
  padding: 10px 20px;
  border-radius: 6px;
  text-decoration: none;
  font-weight: bold;
  transition: all 0.3s;
  margin-bottom: 20px;
}

.btn-voltar:hover {
  background: #6a7a2c;
  transform: translateY(-2px);
}

@media(max-width: 768px) {
  .perfil-header {
    flex-direction: column;
    text-align: center;
  }
  
  .form-row {
    grid-template-columns: 1fr;
  }
  
  .perfil-stats {
    grid-template-columns: 1fr;
  }
}
</style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="perfil-container">
  <a href="index.php" class="btn-voltar">← Voltar</a>
  
  <?php if ($mensagem): ?>
    <div class="mensagem sucesso"><?php echo htmlspecialchars($mensagem); ?></div>
  <?php endif; ?>
  
  <?php if ($erro): ?>
    <div class="mensagem erro"><?php echo htmlspecialchars($erro); ?></div>
  <?php endif; ?>
  
  <div class="perfil-header">
    <div class="foto-perfil-container">
      <?php if (!empty($usuario['foto_perfil']) && file_exists("uploads/perfil/" . $usuario['foto_perfil'])): ?>
        <img src="uploads/perfil/<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" alt="Foto de Perfil" class="foto-perfil">
      <?php else: ?>
        <div class="foto-perfil-placeholder">
          <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
        </div>
      <?php endif; ?>
      
      <form method="POST" enctype="multipart/form-data" style="display: inline;">
        <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*" style="display: none;" onchange="this.form.submit()">
        <label for="foto_perfil" class="btn-upload-foto">
          📷 Alterar Foto
        </label>
      </form>
    </div>
    
    <div class="perfil-info">
      <h2><?php echo htmlspecialchars($usuario['nome']); ?></h2>
      <p>📧 <?php echo htmlspecialchars($usuario['email']); ?></p>
      <?php if ($usuario['telefone']): ?>
        <p>📱 <?php echo htmlspecialchars($usuario['telefone']); ?></p>
      <?php endif; ?>
      <p>📅 Membro desde <?php echo date('d/m/Y', strtotime($usuario['data_registo'])); ?></p>
      
      <div class="perfil-stats">
        <div class="stat-card">
          <span class="stat-number"><?php echo $total_eventos_criados; ?></span>
          <span class="stat-label">Eventos Criados</span>
        </div>
        <div class="stat-card">
          <span class="stat-number"><?php echo $total_participacoes; ?></span>
          <span class="stat-label">Participações</span>
        </div>
      </div>
    </div>
  </div>
  
  <div class="perfil-secao">
    <h3>Editar Informações</h3>
    
    <form method="POST">
      <input type="hidden" name="atualizar_dados" value="1">
      
      <div class="form-row">
        <div class="form-group">
          <label for="nome">Nome Completo</label>
          <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
        </div>
        
        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
        </div>
      </div>
      
      <div class="form-row">
        <div class="form-group">
          <label for="telefone">Telefone</label>
          <input type="tel" id="telefone" name="telefone" value="<?php echo htmlspecialchars($usuario['telefone'] ?? ''); ?>">
        </div>
        
        <div class="form-group">
          <label for="nova_senha">Nova Palavra-passe (deixe em branco para manter a atual)</label>
          <input type="password" id="nova_senha" name="nova_senha">
        </div>
      </div>
      
      <button type="submit" class="btn-submit">💾 Guardar Alterações</button>
    </form>
  </div>
</div>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

</body>
</html>