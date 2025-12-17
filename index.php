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
  <nav>
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
  </nav>
</header>

<main class="container">
  <h1 class="logo">HUMANI <span>CARE</span></h1>

  <section class="banner">
    <div class="banner-text">
      <p><strong>Junte-se ao meu movimento de voluntariado ambiental!</strong><br>
      Participe em atividades práticas de preservação, reflorestamento e educação ambiental. 
      Com pequenas ações, pode fazer uma grande diferença, ajudando o planeta hoje 
      e garantindo um futuro sustentável para as próximas gerações.</p>
    </div>
    <div class="banner-img">
      <img src="https://media.iatiseguros.com/wp-content/uploads/sites/6/2020/01/20115833/tipos-voluntariado.jpg" alt="Voluntariado Img Principal">
    </div>
  </section>

  <section class="grid">
    <div class="card" id="sobre">
      <h3>Sobre</h3>
      <p>Sou uma pessoa dedicada ao voluntariado e à promoção de práticas sustentáveis.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
    <div class="card" id="projeto">
      <h3>Projeto</h3>
      <p>Desenvolvo este projetos de voluntariado com a intensado de ajudar que mais necessita.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
    <div class="card" id="doacoes">
      <h3>Doações</h3>
      <p>A sua doação ajudame a continuar o meu trabalho. Cada doação ajuda este website a melhorar.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
    <div class="card" id="envolva">
      <h3>Envolva-se</h3>
      <p>Participe em atividades, crie eventos que pense que ajudem a comunidade e o planeta.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
  </section>

  <section id="criar-evento">
    <?php if(!isset($_SESSION['user'])): ?>
      <h3>Login Necessário</h3>
      <p>Para criar um evento, por favor <a href="login.php">faça login</a>.</p>
    <?php else: ?>
      <h3>Criar Evento de Voluntariado</h3>
      <form id="eventoForm">
        <label for="nome">Nome do Evento:</label>
        <input type="text" id="nome" name="nome" required>

        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" rows="4" required></textarea>

        <label for="data">Data:</label>
        <input type="date" id="data" name="data" required>

        <label for="local">Local:</label>
        <input type="text" id="local" name="local" required>

        <label for="imagem">Imagem do Evento:</label>
        <input type="file" id="imagem" name="imagem" accept="image/*">

        <button type="submit">Criar Evento</button>
      </form>
      <p>Está logado como: <?php echo htmlspecialchars($_SESSION['user']['nome']); ?></p>
    <?php endif; ?>
  </section>

  <section id="eventosProjetos">
    <h3 class="titulo-eventos">Eventos Criados</h3>
  </section>

</main>

<footer>
  © 2025 Site criado por David B.Criado em HTML.
</footer>

<script>
const form = document.getElementById('eventoForm');
const containerEventos = document.getElementById('eventosProjetos');

function criarEvento(nome, descricao, data, local, arquivo) {
  const div = document.createElement('div');
  div.classList.add('evento-card');

  let imagemHTML = '';
  if (arquivo) {
    const urlImagem = URL.createObjectURL(arquivo);
    imagemHTML = `<img src="${urlImagem}" alt="${nome}" class="evento-img">`;
  }

  div.innerHTML = `
    <center>
      <h4>${nome}</h4>
      ${imagemHTML}
      <p><strong>Data:</strong> ${data} <strong>Local:</strong> ${local}</p>
      <p><strong>Descrição:</strong> ${descricao}</p>
      <button class="participar-btn">Participar</button>
      <p>Pessoas a participar: <span class="contador">0</span></p>
    </center>
  `;

  const btn = div.querySelector('.participar-btn');
  const contador = div.querySelector('.contador');

  btn.addEventListener('click', () => {
    contador.textContent = parseInt(contador.textContent) + 1;
  });

  containerEventos.appendChild(div);
  div.scrollIntoView({behavior:"smooth"});
}

if (form) {
  form.addEventListener('submit', function(e) {
    e.preventDefault();

    const nome = document.getElementById('nome').value;
    const descricao = document.getElementById('descricao').value;
    const data = document.getElementById('data').value;
    const local = document.getElementById('local').value;
    const imagemInput = document.getElementById('imagem');
    const arquivo = imagemInput.files[0];

    criarEvento(nome, descricao, data, local, arquivo);

    form.reset();
  });
}
</script>

</body>
</html>
