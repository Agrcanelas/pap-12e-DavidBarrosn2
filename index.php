<?php
session_start();

if (file_exists('db.php')) {
    require_once 'db.php';
} else {
    die("Erro: Ficheiro db.php não encontrado!");
}

// ---------- Buscar eventos ----------
$eventos = [];
$erro_eventos = null;

try {
    $stmt = $pdo->query("
        SELECT e.*, u.nome as criador_nome,
        (SELECT COUNT(*) FROM participa WHERE evento_id = e.evento_id) as total_participantes
        FROM evento e 
        JOIN utilizador u ON e.utilizador_id = u.utilizador_id 
        ORDER BY e.data_criacao DESC
    ");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro_eventos = "Erro ao carregar eventos.";
    error_log("Erro BD: " . $e->getMessage());
}

$utilizador_logado = isset($_SESSION['user']);

// ---------- Buscar participações do utilizador logado ----------
$participacoes = [];
if ($utilizador_logado) {
    try {
        $stmt = $pdo->prepare(
            "SELECT evento_id FROM participa WHERE utilizador_id = :uid"
        );
        $stmt->execute([':uid' => $_SESSION['user']['utilizador_id']]);
        $participacoes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'evento_id');
    } catch (PDOException $e) {
        error_log("Erro ao buscar participações: " . $e->getMessage());
    }
}
?>
<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>HumaniCare</title>
  <link rel="stylesheet" href="style.css">
  <style>
    /* ===== ESTILOS DO POP-UP ===== */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.6);
      backdrop-filter: blur(4px);
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background: white;
      margin: 3% auto;
      padding: 0;
      border-radius: 16px;
      width: 90%;
      max-width: 700px;
      box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
      animation: slideDown 0.4s ease;
      overflow: hidden;
    }

    @keyframes slideDown {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal-header {
      background: linear-gradient(135deg, #58b79d 0%, #4a9c82 100%);
      color: white;
      padding: 24px 30px;
      position: relative;
    }

    .modal-header h2 {
      margin: 0;
      font-size: 26px;
      padding-right: 40px;
    }

    .close {
      color: white;
      position: absolute;
      right: 20px;
      top: 20px;
      font-size: 32px;
      font-weight: bold;
      cursor: pointer;
      width: 36px;
      height: 36px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      transition: all 0.3s ease;
      background: rgba(255, 255, 255, 0.2);
    }

    .close:hover,
    .close:focus {
      background: rgba(255, 255, 255, 0.3);
      transform: rotate(90deg);
    }

    .modal-body {
      padding: 30px;
    }

    .modal-image {
      width: 100%;
      height: 300px;
      object-fit: cover;
      border-radius: 12px;
      margin-bottom: 24px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    .modal-info {
      margin-bottom: 20px;
    }

    .modal-info-item {
      display: flex;
      align-items: start;
      margin-bottom: 16px;
      padding: 12px;
      background: #f8f8f5;
      border-radius: 8px;
      border-left: 4px solid #58b79d;
    }

    .modal-info-item .icon {
      font-size: 20px;
      margin-right: 12px;
      min-width: 24px;
    }

    .modal-info-item .label {
      font-weight: bold;
      color: #4a4a4a;
      margin-right: 8px;
    }

    .modal-info-item .value {
      color: #555;
      flex: 1;
    }

    .modal-description {
      background: #f8f8f5;
      padding: 20px;
      border-radius: 12px;
      border: 2px solid #e0e0e0;
      margin-bottom: 24px;
    }

    .modal-description h3 {
      margin: 0 0 12px 0;
      color: #7a8c3c;
      font-size: 18px;
    }

    .modal-description p {
      margin: 0;
      line-height: 1.7;
      color: #555;
      text-align: justify;
      white-space: pre-line;
    }

    .modal-footer {
      padding: 20px 30px;
      background: #f8f8f5;
      border-top: 2px solid #e0e0e0;
      display: flex;
      gap: 12px;
      justify-content: flex-end;
    }

    .modal-btn {
      padding: 12px 28px;
      border-radius: 8px;
      border: none;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: all 0.3s ease;
      font-family: inherit;
    }

    .modal-btn-participar {
      background: linear-gradient(135deg, #58b79d 0%, #4a9c82 100%);
      color: white;
      box-shadow: 0 4px 12px rgba(88, 183, 157, 0.3);
    }

    .modal-btn-participar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(88, 183, 157, 0.4);
    }

    .modal-btn-participar.inscrito {
      background: linear-gradient(135deg, #c0392b, #a0301f);
    }

    .modal-btn-participar.inscrito:hover {
      background: linear-gradient(135deg, #a0301f, #8b2818);
    }

    .modal-btn-eliminar {
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      color: white;
      box-shadow: 0 4px 12px rgba(231, 76, 60, 0.3);
    }

    .modal-btn-eliminar:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(231, 76, 60, 0.4);
    }

    .modal-btn-fechar {
      background: #e0e0e0;
      color: #4a4a4a;
    }

    .modal-btn-fechar:hover {
      background: #d0d0d0;
    }

    .modal-btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
      transform: none !important;
    }

    .participantes-count {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 14px;
      font-weight: bold;
      color: #58b79d;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      margin-top: 12px;
    }

    /* Efeito de clique nos cards */
    .evento-card {
      cursor: pointer;
    }

    .evento-card:active {
      transform: scale(0.98);
    }

    /* Badge de eliminação */
    .badge.eliminar-badge {
      background: linear-gradient(135deg, #e74c3c, #c0392b);
      left: 12px;
      right: auto;
    }
  </style>
</head>
<body>

<header>
  <div class="header-container">
    <h1 class="logo">HUMANI <span>CARE</span></h1>
    
    <?php if($utilizador_logado): ?>
      <div class="usuario-logado">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        Olá, <?php echo htmlspecialchars($_SESSION['user']['nome']); ?>
      </div>
    <?php endif; ?>

    <nav class="nav-links">
      <a href="#sobre">Sobre</a>
      <a href="#projeto">Projetos</a>
      <a href="#doacoes">Doações</a>
      <a href="#envolva">Envolva-se</a>
      <a href="#criar-evento">Criar Evento</a>
      <a href="#eventosProjetos">Eventos</a>
      <?php if($utilizador_logado): ?>
        <a href="logout.php" class="btn-sair">Sair</a>
      <?php else: ?>
        <a href="login.php" class="btn-login">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">

<!-- ===== BANNER ===== -->
<section class="banner">
  <div class="banner-text">
    <h2>Junte-se ao movimento!</h2>
    <p>Participe em atividades práticas de preservação, reflorestamento e educação ambiental. 
      Com pequenas ações, pode fazer uma grande diferença, ajudando o planeta hoje 
      e garantindo um futuro sustentável para as próximas gerações.</p>
    <a href="#envolva" class="btn-cta">Comece Agora</a>
  </div>
  
  <div class="banner-img">
    <div class="slideshow-container">
      <div class="mySlides fade">
        <img src="https://media.iatiseguros.com/wp-content/uploads/sites/6/2020/01/20115833/tipos-voluntariado.jpg" alt="Voluntariado">
        <div class="text-slide">Ajude o Planeta</div>
      </div>
      <div class="mySlides fade">
        <img src="https://picsum.photos/800/600?random=1" alt="Natureza">
        <div class="text-slide">Preserve a Natureza</div>
      </div>
      <div class="mySlides fade">
        <img src="https://picsum.photos/800/600?random=2" alt="Comunidade">
        <div class="text-slide">Fortaleça a Comunidade</div>
      </div>
      <div class="mySlides fade">
        <img src="https://picsum.photos/800/600?random=3" alt="Futuro">
        <div class="text-slide">Construa o Futuro</div>
      </div>
      <a class="prev" onclick="plusSlides(-1)">❮</a>
      <a class="next" onclick="plusSlides(1)">❯</a>
    </div>
    <div style="text-align:center; padding: 10px 0;">
      <span class="dot" onclick="currentSlide(1)"></span>
      <span class="dot" onclick="currentSlide(2)"></span>
      <span class="dot" onclick="currentSlide(3)"></span>
      <span class="dot" onclick="currentSlide(4)"></span>
    </div>
  </div>
</section>

<!-- ===== CARDS INFO ===== -->
<section class="grid">
  <div class="card" id="sobre">
    <div class="card-icon">🌱</div>
    <h3>Sobre</h3>
    <p>Sou uma pessoa dedicada ao voluntariado e à promoção de práticas sustentáveis.</p>
  </div>
  <div class="card" id="projeto">
    <div class="card-icon">🤝</div>
    <h3>Projeto</h3>
    <p>Desenvolvo projetos de voluntariado com a intenção de ajudar quem mais necessita.</p>
  </div>
  <div class="card" id="doacoes">
    <div class="card-icon">💚</div>
    <h3>Doações</h3>
    <p>A sua doação ajuda-me a continuar o meu trabalho. Cada doação ajuda este website a melhorar.</p>
  </div>
  <div class="card" id="envolva">
    <div class="card-icon">🌍</div>
    <h3>Envolva-se</h3>
    <p>Participe em atividades, crie eventos que pense que ajudem a comunidade e o planeta.</p>
  </div>
</section>

<!-- ===== CRIAR EVENTO ===== -->
<section id="criar-evento">
<?php if(!$utilizador_logado): ?>
  <div class="login-prompt">
    <p>✨ Para criar eventos faça <a href="login.php">login</a>.</p>
  </div>
<?php else: ?>
  <h3>✏️ Criar Evento</h3>
  
  <?php if(isset($_GET['sucesso']) && $_GET['sucesso'] == '1'): ?>
    <div class="mensagem sucesso">✅ Evento criado com sucesso!</div>
  <?php endif; ?>
  
  <?php if(isset($_GET['eliminado']) && $_GET['eliminado'] == '1'): ?>
    <div class="mensagem sucesso">✅ Evento eliminado com sucesso!</div>
  <?php endif; ?>
  
  <?php if(isset($_GET['erro'])): ?>
    <div class="mensagem erro">
      ❌ 
      <?php 
        switch($_GET['erro']) {
          case 'campos_vazios':    echo 'Preencha todos os campos obrigatórios.'; break;
          case 'tipo_imagem':      echo 'Tipo de imagem inválido. Use JPG, PNG ou GIF.'; break;
          case 'tamanho_imagem':   echo 'Imagem muito grande. Máximo 5MB.'; break;
          case 'upload':           echo 'Erro ao fazer upload da imagem.'; break;
          case 'bd':               echo 'Erro ao guardar na base de dados. Verifique a conexão.'; break;
          default:                 echo 'Erro ao criar evento. Tente novamente.';
        }
      ?>
    </div>
  <?php endif; ?>
  
  <form action="guardar_evento.php" method="POST" enctype="multipart/form-data" id="formEvento">
    <div class="form-group">
      <label for="nome">Nome do Evento *</label>
      <input type="text" id="nome" name="nome" placeholder="Ex: Limpeza da Praia" required maxlength="200">
    </div>
    <div class="form-group">
      <label for="descricao">Descrição *</label>
      <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva o evento..." required></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="data">Data *</label>
        <input type="date" id="data" name="data" required min="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="form-group">
        <label for="local">Local *</label>
        <input type="text" id="local" name="local" placeholder="Ex: Porto" required maxlength="200">
      </div>
    </div>
    <div class="form-group">
      <label for="imagem">Imagem (opcional - máx 5MB)</label>
      <input type="file" id="imagem" name="imagem" accept="image/jpeg,image/jpg,image/png,image/gif">
      <small style="color: #666; font-size: 13px;">Formatos aceites: JPG, PNG, GIF</small>
    </div>
    <button type="submit" class="btn-submit" id="btnSubmit">Criar Evento</button>
  </form>
<?php endif; ?>
</section>

<!-- ===== EVENTOS ===== -->
<section id="eventosProjetos">
  <h3 class="titulo-eventos">📅 Eventos</h3>

  <?php if($utilizador_logado): ?>
  <div class="filtro-eventos">
    <button class="filtro-btn ativo" data-filtro="todos">Todos</button>
    <button class="filtro-btn" data-filtro="participa">A participar</button>
    <button class="filtro-btn" data-filtro="criados">Criados por mim</button>
  </div>
  <?php endif; ?>

  <div class="eventos-grid">
  <?php if($erro_eventos): ?>
    <p class="mensagem-centro"><?php echo htmlspecialchars($erro_eventos); ?></p>
  <?php elseif(empty($eventos)): ?>
    <p class="mensagem-centro">
      Ainda não existem eventos criados. Seja o primeiro a criar um!
    </p>
  <?php else: ?>
    <?php foreach($eventos as $evento): ?>
      <?php
        $eid          = $evento['evento_id'];
        $é_criador    = $utilizador_logado && ($_SESSION['user']['utilizador_id'] == $evento['utilizador_id']);
        $participa_em = $utilizador_logado && in_array($eid, $participacoes);
      ?>
      <div class="evento-card"
           data-criador="<?php echo htmlspecialchars($evento['utilizador_id']); ?>"
           data-evento="<?php echo $eid; ?>"
           data-participa="<?php echo $participa_em ? '1' : '0'; ?>"
           onclick="abrirModal(<?php echo $eid; ?>)">

        <?php if(!empty($evento['imagem']) && file_exists('uploads/' . $evento['imagem'])): ?>
          <img src="uploads/<?php echo htmlspecialchars($evento['imagem']); ?>" 
               alt="<?php echo htmlspecialchars($evento['nome']); ?>" 
               class="evento-img">
        <?php endif; ?>
        
        <h4><?php echo htmlspecialchars($evento['nome']); ?></h4>
        
        <div class="evento-info">
          <p><strong>📅 Data:</strong> <?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?></p>
          <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($evento['local_evento']); ?></p>
          <p><strong>👥 Participantes:</strong> <?php echo $evento['total_participantes']; ?></p>
        </div>

        <?php if($é_criador): ?>
          <span class="badge criado">Criado por mim</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</section>

</main>

<!-- ===== MODAL DE DETALHES ===== -->
<div id="modalEvento" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitulo"></h2>
      <span class="close" onclick="fecharModal()">&times;</span>
    </div>
    <div class="modal-body">
      <img id="modalImagem" class="modal-image" style="display:none;" src="" alt="">
      
      <div class="modal-info">
        <div class="modal-info-item">
          <span class="icon">📅</span>
          <span class="label">Data:</span>
          <span class="value" id="modalData"></span>
        </div>
        <div class="modal-info-item">
          <span class="icon">📍</span>
          <span class="label">Local:</span>
          <span class="value" id="modalLocal"></span>
        </div>
        <div class="modal-info-item">
          <span class="icon">👤</span>
          <span class="label">Criado por:</span>
          <span class="value" id="modalCriador"></span>
        </div>
      </div>

      <div class="participantes-count">
        <span>👥</span>
        <span id="modalParticipantes">0 participantes</span>
      </div>

      <div class="modal-description">
        <h3>📝 Descrição</h3>
        <p id="modalDescricao"></p>
      </div>
    </div>
    <div class="modal-footer" id="modalFooter">
      <!-- Botões serão inseridos dinamicamente -->
    </div>
  </div>
</div>

<footer>
  <p>© 2026 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

<!-- ===== DADOS DOS EVENTOS (JSON) ===== -->
<script>
const eventosData = <?php echo json_encode($eventos); ?>;
const utilizadorLogado = <?php echo $utilizador_logado ? 'true' : 'false'; ?>;
const utilizadorId = <?php echo $utilizador_logado ? intval($_SESSION['user']['utilizador_id']) : 'null'; ?>;
const participacoes = <?php echo json_encode($participacoes); ?>;
</script>

<script>
// ===== CARROSSEL =====
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) { showSlides(slideIndex += n); }
function currentSlide(n) { showSlides(slideIndex = n); }

function showSlides(n) {
  const slides = document.getElementsByClassName("mySlides");
  const dots   = document.getElementsByClassName("dot");
  if (n > slides.length) slideIndex = 1;
  if (n < 1)            slideIndex = slides.length;
  for (let i = 0; i < slides.length; i++) slides[i].style.display = "none";
  for (let i = 0; i < dots.length; i++)   dots[i].classList.remove("active");
  if (slides.length > 0) {
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].classList.add("active");
  }
}
setInterval(() => plusSlides(1), 4000);

// ===== MODAL =====
let eventoAtual = null;

function abrirModal(eventoId) {
  eventoAtual = eventosData.find(e => e.evento_id == eventoId);
  if (!eventoAtual) return;

  // Preencher dados
  document.getElementById('modalTitulo').textContent = eventoAtual.nome;
  document.getElementById('modalData').textContent = formatarData(eventoAtual.data_evento);
  document.getElementById('modalLocal').textContent = eventoAtual.local_evento;
  document.getElementById('modalCriador').textContent = eventoAtual.criador_nome;
  document.getElementById('modalDescricao').textContent = eventoAtual.descricao;
  
  const participantesText = eventoAtual.total_participantes == 1 
    ? '1 participante' 
    : eventoAtual.total_participantes + ' participantes';
  document.getElementById('modalParticipantes').textContent = participantesText;

  // Imagem
  const imgEl = document.getElementById('modalImagem');
  if (eventoAtual.imagem) {
    imgEl.src = 'uploads/' + eventoAtual.imagem;
    imgEl.style.display = 'block';
  } else {
    imgEl.style.display = 'none';
  }

  // Botões do footer
  const footer = document.getElementById('modalFooter');
  footer.innerHTML = '';

  const éCriador = utilizadorLogado && utilizadorId == eventoAtual.utilizador_id;
  const participa = utilizadorLogado && participacoes.includes(eventoAtual.evento_id);

  if (utilizadorLogado) {
    if (éCriador) {
      // Botão eliminar para o criador
      footer.innerHTML = `
        <button class="modal-btn modal-btn-eliminar" onclick="eliminarEvento(${eventoId})">
          🗑️ Eliminar Evento
        </button>
        <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>
      `;
    } else {
      // Botão participar para outros utilizadores
      const btnClass = participa ? 'modal-btn-participar inscrito' : 'modal-btn-participar';
      const btnText = participa ? '✓ Já Inscrito (Cancelar)' : 'Participar neste Evento';
      footer.innerHTML = `
        <button class="modal-btn ${btnClass}" onclick="toggleParticiparModal(${eventoId})" id="btnParticiparModal">
          ${btnText}
        </button>
        <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>
      `;
    }
  } else {
    // Não logado
    footer.innerHTML = `
      <button class="modal-btn modal-btn-participar" onclick="redirecionarLogin()">
        Participar neste Evento
      </button>
      <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>
    `;
  }

  document.getElementById('modalEvento').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function fecharModal() {
  document.getElementById('modalEvento').style.display = 'none';
  document.body.style.overflow = 'auto';
  eventoAtual = null;
}

function formatarData(data) {
  const d = new Date(data + 'T00:00:00');
  return d.toLocaleDateString('pt-PT', { day: '2-digit', month: '2-digit', year: 'numeric' });
}

// Fechar ao clicar fora
window.onclick = function(event) {
  const modal = document.getElementById('modalEvento');
  if (event.target == modal) {
    fecharModal();
  }
}

// Fechar com ESC
document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    fecharModal();
  }
});

// ===== PARTICIPAR NO MODAL =====
function toggleParticiparModal(eventoId) {
  const btn = document.getElementById('btnParticiparModal');
  btn.disabled = true;
  btn.textContent = '...';

  const formData = new FormData();
  formData.append('evento_id', eventoId);

  fetch('participar_evento.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.erro) {
        alert(data.erro);
        btn.disabled = false;
        return;
      }

      if (data.estado === 'inscrito') {
        participacoes.push(eventoId);
        btn.classList.add('inscrito');
        btn.textContent = '✓ Já Inscrito (Cancelar)';
        
        // Atualizar contador
        eventoAtual.total_participantes++;
      } else {
        const index = participacoes.indexOf(eventoId);
        if (index > -1) participacoes.splice(index, 1);
        btn.classList.remove('inscrito');
        btn.textContent = 'Participar neste Evento';
        
        // Atualizar contador
        eventoAtual.total_participantes--;
      }

      // Atualizar display de participantes
      const participantesText = eventoAtual.total_participantes == 1 
        ? '1 participante' 
        : eventoAtual.total_participantes + ' participantes';
      document.getElementById('modalParticipantes').textContent = participantesText;

      // Atualizar o card também
      const card = document.querySelector(`[data-evento="${eventoId}"]`);
      if (card) {
        card.dataset.participa = data.estado === 'inscrito' ? '1' : '0';
        const participantesEl = card.querySelector('.evento-info p:nth-child(3)');
        if (participantesEl) {
          participantesEl.innerHTML = `<strong>👥 Participantes:</strong> ${eventoAtual.total_participantes}`;
        }
      }

      btn.disabled = false;
    })
    .catch(() => {
      alert('Erro de conexão. Tente novamente.');
      btn.disabled = false;
      btn.textContent = 'Participar neste Evento';
    });
}

// ===== ELIMINAR EVENTO =====
function eliminarEvento(eventoId) {
  if (!confirm('Tem a certeza que deseja eliminar este evento?\n\nEsta ação não pode ser desfeita.')) {
    return;
  }

  const formData = new FormData();
  formData.append('evento_id', eventoId);

  fetch('eliminar_evento.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
      if (data.erro) {
        alert('Erro: ' + data.erro);
        return;
      }

      if (data.sucesso) {
        fecharModal();
        // Remover o card da página
        const card = document.querySelector(`[data-evento="${eventoId}"]`);
        if (card) {
          card.style.opacity = '0';
          card.style.transform = 'scale(0.8)';
          setTimeout(() => {
            card.remove();
            // Verificar se ainda há eventos
            if (document.querySelectorAll('.evento-card').length === 0) {
              document.querySelector('.eventos-grid').innerHTML = 
                '<p class="mensagem-centro">Ainda não existem eventos criados. Seja o primeiro a criar um!</p>';
            }
          }, 300);
        }
        
        // Mostrar mensagem de sucesso
        window.location.href = 'index.php?eliminado=1#eventosProjetos';
      }
    })
    .catch(() => {
      alert('Erro de conexão. Tente novamente.');
    });
}

// ===== VALIDAÇÃO FORMULÁRIO =====
<?php if($utilizador_logado): ?>
const formEvento = document.getElementById('formEvento');
const btnSubmit  = document.getElementById('btnSubmit');

if (formEvento) {
  formEvento.addEventListener('submit', function(e) {
    const nome      = document.getElementById('nome').value.trim();
    const descricao = document.getElementById('descricao').value.trim();
    const data      = document.getElementById('data').value;
    const local     = document.getElementById('local').value.trim();

    if (!nome || !descricao || !data || !local) {
      e.preventDefault();
      alert('Por favor, preencha todos os campos obrigatórios.');
      return false;
    }

    const imagem = document.getElementById('imagem');
    if (imagem.files.length > 0) {
      const file = imagem.files[0];
      if (file.size > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('A imagem é muito grande. Máximo 5MB.');
        return false;
      }
      if (!['image/jpeg','image/jpg','image/png','image/gif'].includes(file.type)) {
        e.preventDefault();
        alert('Tipo de ficheiro inválido. Use JPG, PNG ou GIF.');
        return false;
      }
    }

    btnSubmit.disabled = true;
    btnSubmit.textContent = 'A criar evento...';
  });
}
<?php endif; ?>

// ===== FILTRO DE EVENTOS =====
<?php if($utilizador_logado): ?>
document.querySelectorAll('.filtro-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('ativo'));
    this.classList.add('ativo');

    const filtro = this.dataset.filtro;
    document.querySelectorAll('.evento-card').forEach(card => {
      const criador   = parseInt(card.dataset.criador);
      const participa = card.dataset.participa === '1';
      let mostrar = true;

      if      (filtro === 'criados')   mostrar = (criador === utilizadorId);
      else if (filtro === 'participa') mostrar = participa;

      card.style.display = mostrar ? '' : 'none';
    });
  });
});
<?php endif; ?>

// ===== REDIRECIONAR PARA LOGIN =====
function redirecionarLogin() {
  if (confirm('Precisa fazer login para participar. Deseja ir para a página de login?')) {
    window.location.href = 'login.php';
  }
}

// ===== REMOVER MENSAGENS APÓS 5s =====
setTimeout(() => {
  document.querySelectorAll('.mensagem').forEach(msg => {
    msg.style.transition = 'opacity 0.5s';
    msg.style.opacity = '0';
    setTimeout(() => msg.remove(), 500);
  });
}, 5000);

// ===== SCROLL SUAVE =====
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener('click', function(e) {
    const href = this.getAttribute('href');
    if (href !== '#') {
      e.preventDefault();
      const target = document.querySelector(href);
      if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
  });
});
</script>

</body>
</html>