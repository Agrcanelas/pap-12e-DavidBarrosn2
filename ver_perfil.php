<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$perfil_id = intval($_GET['id']);

// Se for o próprio utilizador logado, redirecionar para perfil.php
if (isset($_SESSION['user']) && $_SESSION['user']['utilizador_id'] == $perfil_id) {
    header("Location: perfil.php");
    exit;
}

try {
    // Buscar dados do utilizador
    $stmt = $pdo->prepare("
        SELECT utilizador_id, nome, foto_perfil, data_registo
        FROM utilizador WHERE utilizador_id = :id
    ");
    $stmt->execute([':id' => $perfil_id]);
    $perfil = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$perfil) {
        header("Location: index.php");
        exit;
    }

    // Estatísticas
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM evento WHERE utilizador_id = :id");
    $stmt->execute([':id' => $perfil_id]);
    $total_eventos = $stmt->fetch()['total'];

    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM participa WHERE utilizador_id = :id");
    $stmt->execute([':id' => $perfil_id]);
    $total_participacoes = $stmt->fetch()['total'];

    // Eventos criados
    $stmt = $pdo->prepare("
        SELECT e.evento_id, e.nome, e.data_evento, e.local_evento, e.imagem,
        (SELECT COUNT(*) FROM participa WHERE evento_id = e.evento_id) as total_p
        FROM evento e
        WHERE e.utilizador_id = :id
        ORDER BY e.data_criacao DESC
    ");
    $stmt->execute([':id' => $perfil_id]);
    $eventos_criados = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?php echo htmlspecialchars($perfil['nome']); ?> - HumaniCare</title>
<link rel="stylesheet" href="style.css">
<style>
.perfil-container { max-width: 860px; margin: 36px auto; padding: 0 20px; }

.perfil-header {
  background: white; border: 2px solid #c8c0ae;
  border-radius: 14px; padding: 36px 40px;
  margin-bottom: 28px; display: flex;
  gap: 36px; align-items: center;
  box-shadow: 0 4px 14px rgba(0,0,0,0.09);
}

.foto-perfil-pub {
  width: 130px; height: 130px; border-radius: 50%;
  object-fit: cover; border: 4px solid #58b79d;
  box-shadow: 0 4px 14px rgba(0,0,0,0.13); flex-shrink: 0;
}
.foto-placeholder-pub {
  width: 130px; height: 130px; border-radius: 50%;
  background: linear-gradient(135deg,#58b79d,#7a8c3c);
  display: flex; align-items: center; justify-content: center;
  color: white; font-size: 54px; font-weight: bold;
  border: 4px solid #58b79d; flex-shrink: 0;
}

.perfil-info h2 { color: #7a8c3c; margin: 0 0 8px; font-size: 30px; }
.perfil-info p  { color: #666; margin: 4px 0; font-size: 15px; }

.perfil-stats {
  display: grid; grid-template-columns: repeat(2,1fr);
  gap: 14px; margin-top: 18px;
}
.stat-card {
  background: #f8f8f5; padding: 14px;
  border-radius: 8px; text-align: center;
  border: 1px solid #e0e0e0;
}
.stat-number { font-size: 30px; font-weight: bold; color: #58b79d; display: block; }
.stat-label  { color: #666; font-size: 13px; }

.perfil-secao {
  background: white; border: 2px solid #c8c0ae;
  border-radius: 14px; padding: 30px;
  margin-bottom: 28px;
  box-shadow: 0 4px 14px rgba(0,0,0,0.09);
}
.perfil-secao h3 {
  color: #7a8c3c; margin-top: 0;
  border-bottom: 2px solid #c8c0ae;
  padding-bottom: 10px; font-size: 22px;
}

.eventos-pub-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
  gap: 16px; margin-top: 16px;
}
.evento-pub-card {
  background: #f8f8f5; border: 2px solid #e0e0e0;
  border-radius: 10px; overflow: hidden;
  transition: all .3s; cursor: pointer;
}
.evento-pub-card:hover { border-color: #58b79d; transform: translateY(-4px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
.evento-pub-img { width: 100%; height: 130px; object-fit: cover; }
.evento-pub-info { padding: 12px 14px; }
.evento-pub-info h4 { margin: 0 0 6px; color: #58b79d; font-size: 15px; }
.evento-pub-info p  { margin: 3px 0; font-size: 13px; color: #666; }

.btn-voltar {
  display: inline-block; background: #7a8c3c; color: white;
  padding: 9px 18px; border-radius: 6px; text-decoration: none;
  font-weight: bold; transition: all .3s; margin-bottom: 18px;
}
.btn-voltar:hover { background: #6a7a2c; transform: translateY(-2px); }

.sem-eventos { color: #aaa; font-size: 14px; text-align: center; padding: 20px; }

@media(max-width:600px){
  .perfil-header{flex-direction:column;text-align:center;}
  .perfil-stats{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="perfil-container">
  <a href="javascript:history.back()" class="btn-voltar">← Voltar</a>

  <div class="perfil-header">
    <?php if(!empty($perfil['foto_perfil']) && file_exists('uploads/perfil/'.$perfil['foto_perfil'])): ?>
      <img src="uploads/perfil/<?php echo htmlspecialchars($perfil['foto_perfil']); ?>"
           alt="<?php echo htmlspecialchars($perfil['nome']); ?>" class="foto-perfil-pub">
    <?php else: ?>
      <div class="foto-placeholder-pub"><?php echo strtoupper(substr($perfil['nome'],0,1)); ?></div>
    <?php endif; ?>

    <div class="perfil-info">
      <h2><?php echo htmlspecialchars($perfil['nome']); ?></h2>
      <p>📅 Membro desde <?php echo date('d/m/Y',strtotime($perfil['data_registo'])); ?></p>
      <div class="perfil-stats">
        <div class="stat-card">
          <span class="stat-number"><?php echo $total_eventos; ?></span>
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
    <h3>📅 Eventos criados por <?php echo htmlspecialchars($perfil['nome']); ?></h3>
    <?php if(empty($eventos_criados)): ?>
      <p class="sem-eventos">Este utilizador ainda não criou nenhum evento.</p>
    <?php else: ?>
      <div class="eventos-pub-grid">
        <?php foreach($eventos_criados as $ev): ?>
          <div class="evento-pub-card" onclick="window.location='index.php'">
            <?php if(!empty($ev['imagem']) && file_exists('uploads/eventos/'.$ev['imagem'])): ?>
              <img src="uploads/eventos/<?php echo htmlspecialchars($ev['imagem']); ?>"
                   alt="<?php echo htmlspecialchars($ev['nome']); ?>" class="evento-pub-img">
            <?php endif; ?>
            <div class="evento-pub-info">
              <h4><?php echo htmlspecialchars($ev['nome']); ?></h4>
              <p>📅 <?php echo date('d/m/Y',strtotime($ev['data_evento'])); ?></p>
              <p>📍 <?php echo htmlspecialchars($ev['local_evento']); ?></p>
              <p>👥 <?php echo $ev['total_p']; ?> participantes</p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>
</body>
</html>