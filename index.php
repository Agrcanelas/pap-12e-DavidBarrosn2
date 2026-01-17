<?php
session_start();
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
  <form id="eventoForm">
    <div class="form-group">
      <label for="nome">Nome do Evento</label>
      <input type="text" id="nome" placeholder="Ex: Limpeza da Praia" required>
    </div>
    <div class="form-group">
      <label for="descricao">Descrição</label>
      <textarea id="descricao" placeholder="Descreva o evento..." required></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="data">Data</label>
        <input type="date" id="data" required>
      </div>
      <div class="form-group">
        <label for="local">Local</label>
        <input type="text" id="local" placeholder="Ex: Porto" required>
      </div>
    </div>
    <div class="form-group">
      <label for="imagem">Imagem (opcional)</label>
      <input type="file" id="imagem" accept="image/*">
    </div>
    <button type="submit" class="btn-submit">Criar Evento</button>
  </form>
<?php endif; ?>
</section>

<section id="eventosProjetos">
  <h3 class="titulo-eventos">📅 Eventos</h3>

  <div class="filtro-eventos">
    <button class="filtro-btn ativo" data-filtro="todos">Todos</button>
    <button class="filtro-btn" data-filtro="criados">Criados</button>
    <button class="filtro-btn" data-filtro="participar">A participar</button>
  </div>
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

// ========== EVENTOS ==========
const form = document.getElementById('eventoForm');
const containerEventos = document.getElementById('eventosProjetos');
let eventos = [];

function renderEventos(filtro = 'todos') {
  document.querySelectorAll('.evento-card').forEach(e => e.remove());

  eventos.forEach(ev => {
    if (filtro === 'criados' && ev.tipo !== 'criado') return;
    if (filtro === 'participar' && !ev.participando) return;
    containerEventos.appendChild(ev.el);
  });
}

function criarEvento(nome, descricao, data, local, arquivo) {
  const div = document.createElement('div');
  div.className = 'evento-card';

  let img = '';
  if (arquivo) img = `<img src="${URL.createObjectURL(arquivo)}" class="evento-img" alt="${nome}">`;

  div.innerHTML = `
    ${img}
    <h4>${nome}</h4>
    <div class="evento-info">
      <p><strong>📅 Data:</strong> ${new Date(data).toLocaleDateString('pt-PT')}</p>
      <p><strong>📍 Local:</strong> ${local}</p>
      <p class="evento-desc">${descricao}</p>
    </div>
    <button class="participar-btn">Participar</button>
    <span class="badge criado">Criado</span>
  `;

  const evento = { tipo:'criado', participando:false, el:div };

  const btn = div.querySelector('.participar-btn');

  btn.onclick = () => {
    evento.participando = !evento.participando;
    let badge = div.querySelector('.badge.participar');

    if (evento.participando) {
      btn.textContent = 'Parar de participar';
      btn.classList.add('btn-parar');

      if (!badge) {
        badge = document.createElement('span');
        badge.className = 'badge participar';
        badge.textContent = 'A Participar';
        div.appendChild(badge);
      }
    } else {
      btn.textContent = 'Participar';
      btn.classList.remove('btn-parar');
      if (badge) badge.remove();
    }
  };

  eventos.push(evento);
  renderEventos();
}

if (form) {
  form.onsubmit = e => {
    e.preventDefault();
    criarEvento(nome.value, descricao.value, data.value, local.value, imagem.files[0]);
    form.reset();
  };
}

document.querySelectorAll('.filtro-btn').forEach(btn => {
  btn.onclick = () => {
    document.querySelectorAll('.filtro-btn').forEach(b=>b.classList.remove('ativo'));
    btn.classList.add('ativo');
    renderEventos(btn.dataset.filtro);
  };
});
</script>

</body>
</html>