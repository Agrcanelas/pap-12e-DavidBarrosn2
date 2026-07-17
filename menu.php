<header>
  <div class="header-container">
    <h1 class="logo">HUMANI <span>CARE</span></h1>

    <div class="header-bottom-row">
      <div class="header-left">
        <?php if(isset($_SESSION['user'])): ?>
          <?php $u = $_SESSION['user']; ?>
          <div class="perfil-dropdown" id="perfilDropdown">
            <button type="button" class="usuario-logado" onclick="togglePerfilDropdown()" title="<?php echo htmlspecialchars($u['nome']); ?>">
              <?php if (!empty($u['foto_perfil']) && file_exists('uploads/perfil/' . $u['foto_perfil'])): ?>
                <img src="uploads/perfil/<?php echo htmlspecialchars($u['foto_perfil']); ?>" alt="Foto de Perfil" class="user-foto-mini">
              <?php else: ?>
                <div class="user-placeholder-mini"><?php echo strtoupper(substr($u['nome'], 0, 1)); ?></div>
              <?php endif; ?>
            </button>
            <div class="perfil-dropdown-menu" id="perfilDropdownMenu">
              <a href="perfil.php">⚙️ Definições</a>
              <a href="logout.php">🚪 Terminar Sessão</a>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <nav class="nav-links">
        <a href="index.php#banner">Sobre</a>
        <a href="index.php#projeto">Projetos</a>
        <a href="index.php#doacoes">Doações</a>
        <a href="index.php#envolva">Envolva-se</a>
        <a href="index.php#criar-evento">Criar Evento</a>
        <a href="eventos.php">Eventos</a>
      </nav>

      <div class="header-right">
        <?php if(isset($_SESSION['user'])): ?>
          <a href="logout.php" class="btn-sair">Sair</a>
        <?php else: ?>
          <a href="login.php" class="btn-login">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</header>

<script>
function togglePerfilDropdown(){
  document.getElementById('perfilDropdownMenu').classList.toggle('aberto');
}
document.addEventListener('click', function(e){
  const dd = document.getElementById('perfilDropdown');
  if (dd && !dd.contains(e.target)) {
    const menu = document.getElementById('perfilDropdownMenu');
    if (menu) menu.classList.remove('aberto');
  }
});
</script>