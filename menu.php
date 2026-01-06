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