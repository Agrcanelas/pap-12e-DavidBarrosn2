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
  <h1 class="logo">HUMANI <span>CARE</span></h1>
  <div class="header-inner">  
    <?php if(isset($_SESSION['user'])): ?>
      <span class="usuario-logado">Olá, <?php echo htmlspecialchars($_SESSION['user']['nome']); ?></span>
    <?php endif; ?>

    <div class="nav-links">
      <a href="#sobre">Sobre</a>
      <a href="#projeto">Projetos</a>
      <a href="#doacoes">Doações</a>
      <a href="#envolva">Envolva-se</a>
      <a href="#criar-evento">Criar Evento</a>
      <a href="#eventosProjetos">Eventos</a>
      <?php if(isset($_SESSION['user'])): ?>
        <a href="logout.php">Sair</a>
      <?php else: ?>
        <a href="login.php">Login</a>
      <?php endif; ?>
    </div>
  </div>
</header>

<main class="container">

<section class="banner">
  <div class="banner-text">
    <p><strong>Junte-se ao meu movimento de voluntariado ambiental!</strong><br>
     Participe em atividades práticas de preservação, reflorestamento e educação ambiental. 
      Com pequenas ações, pode fazer uma grande diferença, ajudando o planeta hoje 
      e garantindo um futuro sustentável para as próximas gerações.</p>
  </div>
  <div class="banner-img">
    <img src="https://media.iatiseguros.com/wp-content/uploads/sites/6/2020/01/20115833/tipos-voluntariado.jpg">
  </div>
</section>

<section class="grid">
  <div class="card" id="sobre"><h3>Sobre</h3><p>Sou uma pessoa dedicada ao voluntariado e à promoção de práticas sustentáveis..</p></div>
  <div class="card" id="projeto"><h3>Projeto</h3><p>Desenvolvo este projetos de voluntariado com a intensado de ajudar que mais necessita.</p></div>
  <div class="card" id="doacoes"><h3>Doações</h3><p>A sua doação ajudame a continuar o meu trabalho. Cada doação ajuda este website a melhorar.</p></div>
  <div class="card" id="envolva"><h3>Envolva-se</h3><p>Participe em atividades, crie eventos que pense que ajudem a comunidade e o planeta.</p></div>
</section>

<section id="criar-evento">
<?php if(!isset($_SESSION['user'])): ?>
  <p>Para criar eventos faça <a href="login.php">login</a>.</p>
<?php else: ?>
  <h3>Criar Evento</h3>
  <form id="eventoForm">
    <input type="text" id="nome" placeholder="Nome" required>
    <textarea id="descricao" placeholder="Descrição" required></textarea>
    <input type="date" id="data" required>
    <input type="text" id="local" placeholder="Local" required>
    <input type="file" id="imagem" accept="image/*">
    <button type="submit">Criar Evento</button>
  </form>
<?php endif; ?>
</section>

<section id="eventosProjetos">
  <h3 class="titulo-eventos">Eventos</h3>

  <!-- FILTRO -->
  <div class="filtro-eventos">
    <button class="filtro-btn ativo" data-filtro="todos">Todos</button>
    <button class="filtro-btn" data-filtro="criados">Criados</button>
    <button class="filtro-btn" data-filtro="participar">A participar</button>
  </div>
</section>

</main>

<footer>© 2025 HumaniCare</footer>

<script>
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
  if (arquivo) img = `<img src="${URL.createObjectURL(arquivo)}" class="evento-img">`;

  div.innerHTML = `
    <h4>${nome}</h4>
    ${img}
    <p><strong>Data:</strong> ${data}</p>
    <p><strong>Local:</strong> ${local}</p>
    <p>${descricao}</p>
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
      badge.textContent = 'A participar';
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
