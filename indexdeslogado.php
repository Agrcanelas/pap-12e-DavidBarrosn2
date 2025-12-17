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
  <h1 class="logo">HUMANI <span>CARE</span></h1>
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
      <p>Desenvolvo este projeto de voluntariado com a intenção de ajudar quem mais necessita.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
    <div class="card" id="doacoes">
      <h3>Doações</h3>
      <p>A sua doação ajuda-me a continuar o meu trabalho. Cada doação ajuda este website a melhorar.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
    <div class="card" id="envolva">
      <h3>Envolva-se</h3>
      <p>Participe em atividades, crie eventos que ajudem a comunidade e o planeta.</p>
      <a href="#" class="link-mais">Mais</a>
    </div>
  </section>

  <section id="criar-evento">
    <h3>Login Necessário</h3>
    <p>Para criar um evento, por favor <a href="login.php">faça login</a>.</p>
  </section>

  <section id="eventosProjetos">
    <h3 class="titulo-eventos">Eventos Criados</h3>
    <?php foreach($_SESSION['eventos'] as $evento): ?>
      <div class="evento-card">
        <h4><?php echo $evento['nome']; ?></h4>
        <?php if($evento['imagem']): ?>
          <img src="<?php echo $evento['imagem']; ?>" alt="<?php echo $evento['nome']; ?>" class="evento-img">
        <?php endif; ?>
        <p><strong>Data:</strong> <?php echo $evento['data']; ?> <strong>Local:</strong> <?php echo $evento['local']; ?></p>
        <p><strong>Descrição:</strong> <?php echo $evento['descricao']; ?></p>
        <button class="participar-btn">Participar</button>
        <p>Pessoas a participar: <span class="contador">0</span></p>
      </div>
    <?php endforeach; ?>
  </section>

</main>

<footer>
  © 2025 por David B. Criado em PHP.
</footer>

<script>
document.querySelectorAll('.participar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const contador = btn.nextElementSibling.querySelector('.contador');
    contador.textContent = parseInt(contador.textContent) + 1;
  });
});
</script>

</body>
</html>
