<?php
session_start();
$isLoggedIn = isset($_SESSION['user']);
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
    <a href="#sobre">Sobre</a>
    <a href="#projeto">Projetos</a>
    <a href="#doacoes">Doações</a>
    <a href="#envolva">Envolva-se</a>
    <a href="#criar-evento">Criar Evento</a>
    <a href="#eventosProjetos">Eventos</a>
    <a href="login.php">Login</a>
  </nav>
</header>

<main class="container">

  <h1 class="logo">HUMANI <span>CARE</span></h1>

  <!-- BANNER -->
  <section class="banner">
    <div class="banner-text">
      <p>
        <strong>Junte-se ao movimento de voluntariado ambiental!</strong><br>
        Pequenas ações fazem uma grande diferença.
      </p>
    </div>
    <div class="banner-img">
      <img src="https://media.iatiseguros.com/wp-content/uploads/sites/6/2020/01/20115833/tipos-voluntariado.jpg">
    </div>
  </section>

  <!-- CRIAR EVENTO -->
  <section id="criar-evento">
    <h3>Criar Evento de Voluntariado</h3>

    <form id="eventoForm">
      <label>Nome do Evento:</label>
      <input type="text" id="nome" required>

      <label>Descrição:</label>
      <textarea id="descricao" required></textarea>

      <label>Data:</label>
      <input type="date" id="data" required>

      <label>Local:</label>
      <input type="text" id="local" required>

      <label>Imagem:</label>
      <input type="file" id="imagem" accept="image/*">

      <button type="submit">Criar Evento</button>
    </form>
  </section>

  <!-- EVENTOS -->
  <section id="eventosProjetos">
    <h3 class="titulo-eventos">Eventos Criados</h3>
  </section>

</main>

<footer>
  © 2025 HumaniCare
</footer>

<script>
const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;

const form = document.getElementById('eventoForm');
const containerEventos = document.getElementById('eventosProjetos');

form.addEventListener('submit', function(e) {
  e.preventDefault();

  const nome = document.getElementById('nome').value;
  const descricao = document.getElementById('descricao').value;
  const data = document.getElementById('data').value;
  const local = document.getElementById('local').value;
  const imagem = document.getElementById('imagem').files[0];

  let imagemHTML = '';
  if (imagem) {
    imagemHTML = `<img src="${URL.createObjectURL(imagem)}" class="evento-img">`;
  }

  const evento = document.createElement('div');
  evento.classList.add('evento-card');

  evento.innerHTML = `
    <center>
      <h4>${nome}</h4>
      ${imagemHTML}
      <p><strong>Data:</strong> ${data}</p>
      <p><strong>Local:</strong> ${local}</p>
      <p>${descricao}</p>

      <p>👥 Participantes: <span class="contador">0</span></p>

      <button class="btn-participar">Participar</button>
    </center>
  `;

  containerEventos.appendChild(evento);
  form.reset();

  const botao = evento.querySelector('.btn-participar');
  const contador = evento.querySelector('.contador');

  botao.addEventListener('click', function() {
    if (!isLoggedIn) {
      alert("Primeiro tem que fazer login!");
      return;
    }
    contador.textContent = parseInt(contador.textContent) + 1;
  });

  evento.scrollIntoView({ behavior: "smooth" });
});
</script>

</body>
</html>
