<?php
session_start();
require_once 'db.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$evento_id = intval($_GET['id']);

try {
    $stmt = $pdo->prepare("
        SELECT e.*, u.nome as criador_nome, u.foto_perfil as criador_foto,
        (SELECT COUNT(*) FROM participa WHERE evento_id = e.evento_id) as total_participantes
        FROM evento e 
        JOIN utilizador u ON e.utilizador_id = u.utilizador_id 
        WHERE e.evento_id = :id
    ");
    $stmt->execute([':id' => $evento_id]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$evento) {
        header("Location: index.php?erro=evento_nao_encontrado");
        exit;
    }

    // Buscar participantes
    $stmt = $pdo->prepare("
        SELECT u.nome, u.foto_perfil
        FROM participa p
        JOIN utilizador u ON p.utilizador_id = u.utilizador_id
        WHERE p.evento_id = :id
        ORDER BY p.data_participacao DESC
    ");
    $stmt->execute([':id' => $evento_id]);
    $participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $esta_inscrito = false;
    $pode_editar = false;

    if (isset($_SESSION['user'])) {
        $stmt = $pdo->prepare("SELECT 1 FROM participa WHERE evento_id = :eid AND utilizador_id = :uid");
        $stmt->execute([
            ':eid' => $evento_id,
            ':uid' => $_SESSION['user']['utilizador_id']
        ]);
        $esta_inscrito = (bool)$stmt->fetch();
        $pode_editar = ($evento['utilizador_id'] == $_SESSION['user']['utilizador_id']);
    }

} catch (PDOException $e) {
    error_log("Erro ao buscar evento: " . $e->getMessage());
    header("Location: index.php?erro=bd");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($evento['nome']); ?> - HumaniCare</title>
<link rel="stylesheet" href="style.css">
<style>
.evento-detalhes-container {
  max-width: 1100px;
  margin: 40px auto;
  padding: 0 20px;
}

.evento-header {
  background: white;
  border: 2px solid #c8c0ae;
  border-radius: 12px;
  overflow: hidden;
  margin-bottom: 30px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.imagem-evento {
  width: 100%;
  height: 450px;
  object-fit: cover;
}

.sem-imagem {
  width: 100%;
  height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #e0e0e0;
  color: #999;
  font-size: 64px;
}

.evento-info-principal {
  padding: 35px;
}

.evento-titulo {
  color: #7a8c3c;
  margin: 0 0 20px 0;
  font-size: 36px;
  border-bottom: 2px solid #c8c0ae;
  padding-bottom: 15px;
}

.evento-meta {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 20px;
  margin-bottom: 30px;
}

.meta-item {
  background: #f8f8f5;
  padding: 15px;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.meta-icon { font-size: 24px; margin-bottom: 8px; display: block; }
.meta-label { font-size: 13px; color: #666; display: block; margin-bottom: 5px; }
.meta-valor { font-size: 18px; font-weight: bold; color: #333; }

.evento-descricao {
  background: #f8f8f5;
  padding: 25px;
  border-radius: 8px;
  margin-bottom: 30px;
  line-height: 1.8;
  text-align: justify;
}

.criador-info {
  display: flex;
  align-items: center;
  gap: 15px;
  padding: 20px;
  background: #f8f8f5;
  border-radius: 8px;
  margin-bottom: 30px;
}

.criador-foto {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #58b79d;
}

.criador-placeholder {
  width: 60px;
  height: 60px;
  border-radius: 50%;
  background: linear-gradient(135deg, #58b79d, #7a8c3c);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 24px;
  font-weight: bold;
  border: 3px solid #58b79d;
}

.criador-detalhes h4 { margin: 0 0 5px 0; color: #7a8c3c; }
.criador-detalhes p  { margin: 0; color: #666; font-size: 14px; }

.acoes-evento {
  display: flex;
  gap: 15px;
  margin-bottom: 30px;
  flex-wrap: wrap;
}

.btn-acao {
  flex: 1;
  min-width: 200px;
  padding: 16px 24px;
  border: none;
  border-radius: 8px;
  font-size: 17px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s;
  text-align: center;
  text-decoration: none;
  display: inline-block;
}

.btn-participar {
  background: linear-gradient(135deg, #58b79d, #4a9c82);
  color: white;
  box-shadow: 0 4px 12px rgba(88, 183, 157, 0.3);
}

.btn-participar:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(88, 183, 157, 0.4); }

.btn-cancelar {
  background: linear-gradient(135deg, #c0392b, #a0301f);
  color: white;
}

.btn-cancelar:hover { transform: translateY(-2px); }

.btn-editar {
  background: linear-gradient(135deg, #7a8c3c, #9dbb52);
  color: white;
}

.btn-editar:hover { transform: translateY(-2px); }

.btn-voltar {
  background: #f0f0f0;
  color: #333;
}

.btn-voltar:hover { background: #e0e0e0; transform: translateY(-2px); }

.participantes-secao {
  background: white;
  border: 2px solid #c8c0ae;
  border-radius: 12px;
  padding: 30px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.participantes-secao h3 {
  color: #7a8c3c;
  margin-top: 0;
  border-bottom: 2px solid #c8c0ae;
  padding-bottom: 12px;
}

.participantes-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 15px;
  margin-top: 20px;
}

.participante-card {
  text-align: center;
  padding: 15px;
  background: #f8f8f5;
  border-radius: 8px;
  transition: all 0.3s;
}

.participante-card:hover { background: #f0f0f0; transform: translateY(-2px); }

.participante-foto {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #58b79d;
  margin-bottom: 10px;
}

.participante-placeholder {
  width: 70px;
  height: 70px;
  border-radius: 50%;
  background: linear-gradient(135deg, #58b79d, #7a8c3c);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 28px;
  font-weight: bold;
  border: 3px solid #58b79d;
  margin: 0 auto 10px;
}

.participante-nome { font-weight: bold; color: #333; font-size: 14px; }

@media(max-width: 768px) {
  .imagem-evento { height: 300px; }
  .evento-meta { grid-template-columns: 1fr; }
  .acoes-evento { flex-direction: column; }
  .btn-acao { width: 100%; }
}
</style>
</head>
<body>

<?php include 'menu.php'; ?>

<div class="evento-detalhes-container">
  <div class="evento-header">

    <?php if (!empty($evento['imagem'])): ?>
      <img src="uploads/eventos/<?php echo htmlspecialchars($evento['imagem']); ?>"
           alt="<?php echo htmlspecialchars($evento['nome']); ?>"
           class="imagem-evento">
    <?php else: ?>
      <div class="sem-imagem">📅</div>
    <?php endif; ?>

    <div class="evento-info-principal">
      <h1 class="evento-titulo"><?php echo htmlspecialchars($evento['nome']); ?></h1>

      <div class="evento-meta">
        <div class="meta-item">
          <span class="meta-icon">📅</span>
          <span class="meta-label">Data</span>
          <span class="meta-valor"><?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?></span>
        </div>
        <div class="meta-item">
          <span class="meta-icon">📍</span>
          <span class="meta-label">Local</span>
          <span class="meta-valor"><?php echo htmlspecialchars($evento['local_evento']); ?></span>
        </div>
        <div class="meta-item">
          <span class="meta-icon">👥</span>
          <span class="meta-label">Participantes</span>
          <span class="meta-valor"><?php echo $evento['total_participantes']; ?></span>
        </div>
        <div class="meta-item">
          <span class="meta-icon">📌</span>
          <span class="meta-label">Criado em</span>
          <span class="meta-valor"><?php echo date('d/m/Y', strtotime($evento['data_criacao'])); ?></span>
        </div>
      </div>

      <div class="criador-info">
        <?php if (!empty($evento['criador_foto'])): ?>
          <img src="uploads/perfil/<?php echo htmlspecialchars($evento['criador_foto']); ?>"
               alt="<?php echo htmlspecialchars($evento['criador_nome']); ?>"
               class="criador-foto">
        <?php else: ?>
          <div class="criador-placeholder">
            <?php echo strtoupper(substr($evento['criador_nome'], 0, 1)); ?>
          </div>
        <?php endif; ?>
        <div class="criador-detalhes">
          <h4>Organizado por</h4>
          <p><?php echo htmlspecialchars($evento['criador_nome']); ?></p>
        </div>
      </div>

      <div class="evento-descricao">
        <strong>Sobre o evento:</strong><br><br>
        <?php echo nl2br(htmlspecialchars($evento['descricao'])); ?>
      </div>

      <div class="acoes-evento">
        <a href="index.php" class="btn-acao btn-voltar">← Voltar</a>

        <?php if (isset($_SESSION['user'])): ?>
          <?php if ($pode_editar): ?>
            <button class="btn-acao btn-editar" onclick="alert('Funcionalidade de edição em desenvolvimento!')">
              ✏️ Editar Evento
            </button>
          <?php else: ?>
            <?php if ($esta_inscrito): ?>
              <button class="btn-acao btn-cancelar" onclick="participarEvento(<?php echo $evento_id; ?>, this)">
                ❌ Cancelar Participação
              </button>
            <?php else: ?>
              <button class="btn-acao btn-participar" onclick="participarEvento(<?php echo $evento_id; ?>, this)">
                ✅ Participar
              </button>
            <?php endif; ?>
          <?php endif; ?>
        <?php else: ?>
          <a href="login.php" class="btn-acao btn-participar">🔐 Fazer login para participar</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <?php if (!empty($participantes)): ?>
    <div class="participantes-secao">
      <h3>👥 Participantes (<?php echo count($participantes); ?>)</h3>
      <div class="participantes-grid">
        <?php foreach ($participantes as $p): ?>
          <div class="participante-card">
            <?php if (!empty($p['foto_perfil'])): ?>
              <img src="uploads/perfil/<?php echo htmlspecialchars($p['foto_perfil']); ?>"
                   alt="<?php echo htmlspecialchars($p['nome']); ?>"
                   class="participante-foto">
            <?php else: ?>
              <div class="participante-placeholder">
                <?php echo strtoupper(substr($p['nome'], 0, 1)); ?>
              </div>
            <?php endif; ?>
            <div class="participante-nome"><?php echo htmlspecialchars($p['nome']); ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

<script>
function participarEvento(eventoId, botao) {
  botao.disabled = true;
  botao.textContent = '⏳ Processando...';

  fetch('participar_evento.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'evento_id=' + eventoId
  })
  .then(response => response.json())
  .then(data => {
    if (data.erro) {
      alert(data.erro);
      botao.disabled = false;
    } else {
      location.reload();
    }
  })
  .catch(() => {
    alert('Erro ao processar. Tente novamente.');
    botao.disabled = false;
  });
}
</script>

</body>
</html>