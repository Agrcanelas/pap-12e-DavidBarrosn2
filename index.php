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
        SELECT e.*, u.nome as criador_nome 
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
$participacoes = []; // array de evento_id nos quais o utilizador participa
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

  <!-- THIS is the grid container for the cards -->
  <div class="eventos-grid">
  <?php if($erro_eventos): ?>
    <p class="mensagem-centro erro-eventos">
      <?php echo htmlspecialchars($erro_eventos); ?>
    </p>
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
           data-participa="<?php echo $participa_em ? '1' : '0'; ?>">

        <?php if(!empty($evento['imagem']) && file_exists('uploads/' . $evento['imagem'])): ?>
          <img src="uploads/<?php echo htmlspecialchars($evento['imagem']); ?>" 
               alt="<?php echo htmlspecialchars($evento['nome']); ?>" 
               class="evento-img">
        <?php endif; ?>
        
        <h4><?php echo htmlspecialchars($evento['nome']); ?></h4>
        
        <div class="evento-info">
          <p><strong>📅 Data:</strong> <?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?></p>
          <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($evento['local_evento']); ?></p>
          <p><strong>👤 Criador:</strong> <?php echo htmlspecialchars($evento['criador_nome']); ?></p>
          <p class="evento-desc"><?php echo nl2br(htmlspecialchars($evento['descricao'])); ?></p>
        </div>

        <!-- Badge "Criado por mim" -->
        <?php if($é_criador): ?>
          <span class="badge criado">Criado por mim</span>
        <?php endif; ?>

        <!-- Botão Participar / Já Inscrito  (não aparece nos eventos que o próprio criou) -->
        <?php if($utilizador_logado && !$é_criador): ?>
          <button class="participar-btn <?php echo $participa_em ? 'btn-parar' : ''; ?>"
                  onclick="toggleParticipacao(this)">
            <?php echo $participa_em ? '✓ Já Inscrito' : 'Participar'; ?>
          </button>
        <?php elseif(!$utilizador_logado): ?>
          <button class="participar-btn" onclick="redirecionarLogin()">
            Participar
          </button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div><!-- fim eventos-grid -->
</section>

</main>

<footer>
  <p>© 2026 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

<!-- ============================================================
     SCRIPTS
     ============================================================ -->
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

// ===== FILTRO DE EVENTOS (Todos / A participar / Criados por mim) =====
<?php if($utilizador_logado): ?>
const utilizadorId = <?php echo intval($_SESSION['user']['utilizador_id']); ?>;

document.querySelectorAll('.filtro-btn').forEach(btn => {
  btn.addEventListener('click', function() {
    // highlight
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('ativo'));
    this.classList.add('ativo');

    const filtro = this.dataset.filtro;
    document.querySelectorAll('.evento-card').forEach(card => {
      const criador   = parseInt(card.dataset.criador);
      const participa = card.dataset.participa === '1';
      let mostrar = true;

      if      (filtro === 'criados')   mostrar = (criador === utilizadorId);
      else if (filtro === 'participa') mostrar = participa;
      // 'todos' → mostrar = true (já definido)

      card.style.display = mostrar ? '' : 'none';
    });
  });
});
<?php endif; ?>

// ===== PARTICIPAR / CANCELAR (AJAX) =====
<?php if($utilizador_logado): ?>
function toggleParticipacao(btn) {
  const card     = btn.closest('.evento-card');
  const eventoId = card.dataset.evento;

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
        btn.textContent = 'Participar';
        return;
      }

      if (data.estado === 'inscrito') {
        card.dataset.participa = '1';
        btn.classList.add('btn-parar');
        btn.textContent = '✓ Já Inscrito';
      } else {
        card.dataset.participa = '0';
        btn.classList.remove('btn-parar');
        btn.textContent = 'Participar';
      }
      btn.disabled = false;
    })
    .catch(() => {
      alert('Erro de conexão. Tente novamente.');
      btn.disabled = false;
      btn.textContent = 'Participar';
    });
}
<?php endif; ?>

// ===== REDIRECIONAR PARA LOGIN (utilizador não logado) =====
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