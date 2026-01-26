<?php
session_start();
require_once 'db.php';

// Buscar eventos da base de dados
$eventos = [];
try {
    $stmt = $pdo->query("
        SELECT e.*, u.nome as criador_nome 
        FROM evento e 
        JOIN utilizador u ON e.utilizador_id = u.utilizador_id 
        ORDER BY e.data_criacao DESC
    ");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro_eventos = "Erro ao carregar eventos: " . $e->getMessage();
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
    
    <?php if(isset($_SESSION['user'])): ?>
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
      <?php if(isset($_SESSION['user'])): ?>
        <a href="logout.php" class="btn-sair">Sair</a>
      <?php else: ?>
        <a href="login.php" class="btn-login">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">

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

<section id="criar-evento">
<?php if(!isset($_SESSION['user'])): ?>
  <div class="login-prompt">
    <p>✨ Para criar eventos faça <a href="login.php">login</a>.</p>
  </div>
<?php else: ?>
  <h3>✏️ Criar Evento</h3>
  
  <?php if(isset($_GET['sucesso'])): ?>
    <div class="mensagem sucesso">✅ Evento criado com sucesso!</div>
  <?php endif; ?>
  
  <?php if(isset($_GET['erro'])): ?>
    <div class="mensagem erro">❌ Erro ao criar evento. Tente novamente.</div>
  <?php endif; ?>
  
  <form action="guardar_evento.php" method="POST" enctype="multipart/form-data">
    <div class="form-group">
      <label for="nome">Nome do Evento</label>
      <input type="text" id="nome" name="nome" placeholder="Ex: Limpeza da Praia" required>
    </div>
    <div class="form-group">
      <label for="descricao">Descrição</label>
      <textarea id="descricao" name="descricao" placeholder="Descreva o evento..." required></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="data">Data</label>
        <input type="date" id="data" name="data" required>
      </div>
      <div class="form-group">
        <label for="local">Local</label>
        <input type="text" id="local" name="local" placeholder="Ex: Porto" required>
      </div>
    </div>
    <div class="form-group">
      <label for="imagem">Imagem (opcional)</label>
      <input type="file" id="imagem" name="imagem" accept="image/*">
    </div>
    <button type="submit" class="btn-submit">Criar Evento</button>
  </form>
<?php endif; ?>
</section>

<section id="eventosProjetos">
  <h3 class="titulo-eventos">📅 Eventos</h3>

  <div class="filtro-eventos">
    <button class="filtro-btn ativo" data-filtro="todos">Todos</button>
    <button class="filtro-btn" data-filtro="criados">Criados por mim</button>
  </div>

  <?php if(isset($erro_eventos)): ?>
    <p style="grid-column: 1 / -1; text-align: center; color: #c0392b; padding: 20px;">
      <?php echo htmlspecialchars($erro_eventos); ?>
    </p>
  <?php elseif(empty($eventos)): ?>
    <p style="grid-column: 1 / -1; text-align: center; color: #777; padding: 40px 20px;">
      Ainda não existem eventos criados. Seja o primeiro a criar um!
    </p>
  <?php else: ?>
    <?php foreach($eventos as $evento): ?>
      <div class="evento-card" data-criador="<?php echo $evento['utilizador_id']; ?>">
        <?php if($evento['imagem']): ?>
          <img src="uploads/<?php echo htmlspecialchars($evento['imagem']); ?>" 
               alt="<?php echo htmlspecialchars($evento['nome']); ?>" 
               class="evento-img">
        <?php endif; ?>
        
        <h4><?php echo htmlspecialchars($evento['nome']); ?></h4>
        
        <div class="evento-info">
          <p><strong>📅 Data:</strong> <?php echo date('d/m/Y', strtotime($evento['data_evento'])); ?></p>
          <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($evento['local_evento']); ?></p>
          <p><strong>👤 Criador:</strong> <?php echo htmlspecialchars($evento['criador_nome']); ?></p>
          <p class="evento-desc"><?php echo htmlspecialchars($evento['descricao']); ?></p>
        </div>
        
        <?php if(isset($_SESSION['user']) && $_SESSION['user']['utilizador_id'] == $evento['utilizador_id']): ?>
          <span class="badge criado">Criado por mim</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

</main>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

<script>
// ========== CARROSSEL ==========
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
  showSlides(slideIndex += n);
}

function currentSlide(n) {
  showSlides(slideIndex = n);
}

function showSlides(n) {
  let i;
  let slides = document.getElementsByClassName("mySlides");
  let dots = document.getElementsByClassName("dot");
  
  if (n > slides.length) {slideIndex = 1}
  if (n < 1) {slideIndex = slides.length}
  
  for (i = 0; i < slides.length; i++) {
    slides[i].style.display = "none";
  }
  
  for (i = 0; i < dots.length; i++) {
    dots[i].className = dots[i].className.replace(" active", "");
  }
  
  if (slides.length > 0) {
    slides[slideIndex-1].style.display = "block";
    dots[slideIndex-1].className += " active";
  }
}

// Auto-play
setInterval(function() {
  plusSlides(1);
}, 4000);

// ========== FILTRO DE EVENTOS ==========
<?php if(isset($_SESSION['user'])): ?>
const utilizadorId = <?php echo $_SESSION['user']['utilizador_id']; ?>;
<?php else: ?>
const utilizadorId = null;
<?php endif; ?>

document.querySelectorAll('.filtro-btn').forEach(btn => {
  btn.onclick = () => {
    document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('ativo'));
    btn.classList.add('ativo');
    
    const filtro = btn.dataset.filtro;
    const cards = document.querySelectorAll('.evento-card');
    
    cards.forEach(card => {
      const criadorId = parseInt(card.dataset.criador);
      
      if (filtro === 'todos') {
        card.style.display = 'block';
      } else if (filtro === 'criados') {
        if (utilizadorId && criadorId === utilizadorId) {
          card.style.display = 'block';
        } else {
          card.style.display = 'none';
        }
      }
    });
  };
});

// Remover mensagens de sucesso/erro após 5 segundos
setTimeout(() => {
  const mensagens = document.querySelectorAll('.mensagem');
  mensagens.forEach(msg => {
    msg.style.transition = 'opacity 0.5s';
    msg.style.opacity = '0';
    setTimeout(() => msg.remove(), 500);
  });
}, 5000);
</script>

</body>
</html>