<?php
session_start();

// Simulação: utilizador deslogado
$_SESSION['usuario'] = null;

// Inicializar array de eventos se não existir
if(!isset($_SESSION['eventos'])) {
    $_SESSION['eventos'] = [];
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
    
    <nav class="nav-links">
      <a href="#sobre">Sobre</a>
      <a href="#projeto">Projetos</a>
      <a href="#doacoes">Doações</a>
      <a href="#envolva">Envolva-se</a>
      <a href="#criar-evento">Criar Evento</a>
      <a href="#eventosProjetos">Eventos</a>
      <a href="login.php" class="btn-login">Login</a>
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
    <div class="login-prompt">
      <p>✨ Para criar eventos faça <a href="login.php">login</a>.</p>
    </div>
  </section>

  <section id="eventosProjetos">
    <h3 class="titulo-eventos">📅 Eventos Criados</h3>
    
    <?php if(empty($_SESSION['eventos'])): ?>
      <p style="grid-column: 1 / -1; text-align: center; color: #777; padding: 40px 20px;">
        Ainda não existem eventos criados. Seja o primeiro a criar um!
      </p>
    <?php else: ?>
      <?php foreach($_SESSION['eventos'] as $evento): ?>
        <div class="evento-card">
          <?php if($evento['imagem']): ?>
            <img src="<?php echo htmlspecialchars($evento['imagem']); ?>" alt="<?php echo htmlspecialchars($evento['nome']); ?>" class="evento-img">
          <?php endif; ?>
          <h4><?php echo htmlspecialchars($evento['nome']); ?></h4>
          <div class="evento-info">
            <p><strong>📅 Data:</strong> <?php echo htmlspecialchars($evento['data']); ?></p>
            <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($evento['local']); ?></p>
            <p class="evento-desc"><?php echo htmlspecialchars($evento['descricao']); ?></p>
          </div>
          <button class="participar-btn">Participar</button>
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

// ========== BOTÃO PARTICIPAR ==========
document.querySelectorAll('.participar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    alert('Faça login para participar neste evento!');
    window.location.href = 'login.php';
  });
});
</script>

</body>
</html>