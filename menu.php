<header>
  <nav>
    <?php if(isset($_SESSION['user'])): ?>
      <!-- LINK CLICÁVEL PARA O PERFIL -->
      <a href="perfil.php" class="usuario-logado" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
        <?php if (!empty($_SESSION['user']['foto_perfil']) && file_exists('uploads/perfil/' . $_SESSION['user']['foto_perfil'])): ?>
          <img src="uploads/perfil/<?php echo htmlspecialchars($_SESSION['user']['foto_perfil']); ?>" 
               alt="Foto de Perfil" 
               style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 2px solid white; box-shadow: 0 2px 6px rgba(0,0,0,0.2);">
        <?php else: ?>
          <!-- Placeholder quando não tem foto -->
          <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #58b79d, #7a8c3c); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px; border: 2px solid white;">
            <?php echo strtoupper(substr($_SESSION['user']['nome'], 0, 1)); ?>
          </div>
        <?php endif; ?>
        Olá, <?php echo htmlspecialchars($_SESSION['user']['nome']); ?>
      </a>
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