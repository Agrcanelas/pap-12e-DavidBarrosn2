<!doctype html>
<html lang="pt-PT">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>HumaniCare</title>

  <!-- Liga o ficheiro CSS externo com os estilos visuais -->
  <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- Cabeçalho do site com o menu de navegação -->
  <header>
    <nav>
      <a href="#" class="active">Página inicial</a>
      <a href="#sobre">Sobre</a>
      <a href="#projeto">Projetos</a>
      
      <a href="#doacoes">Doações</a>
      <a href="#envolva">Envolva-se</a>
      <a href="#criar-evento">Criar Evento</a>
      <a href="#eventosProjetos">Eventos</a>
      <a href="login.php">Login</a>
    </nav>
  </header>

  <!-- Conteúdo principal do site -->
  <main class="container">

    <!-- Título principal (logo do site) -->
    <h1 class="logo">HUMANI <span>CARE</span></h1>

    <!-- Banner principal com texto e imagem -->
    <section class="banner">
      <div class="banner-text">
    <p>
  <strong>
    Junte-se ao meu movimento de voluntariado ambiental!</strong><br>
 Participe em atividades práticas de preservação, reflorestamento e educação ambiental. 
Com pequenas ações, pode fazer uma grande diferença, ajudando o planeta hoje 
e garantindo um futuro sustentável para as próximas gerações.
</p>
      </div>
      <div class="banner-img">
        <img src="
        https://media.iatiseguros.com/wp-content/uploads/sites/6/2020/01/20115833/tipos-voluntariado.jpg" alt="Voluntariado Img Principal">
      </div>
    </section>

    <!-- Secção com quatro cartões informativos -->
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
        <p>Participe em atividades,crie eventos que pense que ajude a comunidade para ajudar o planeta.</p>
        <a href="#" class="link-mais">Mais</a>
      </div>
    </section>

    <!-- Formulário para criar novos eventos -->
    <section id="criar-evento">
      <h3>Criar Evento de Voluntariado</h3>

      <form id="eventoForm">
        <!-- Campo do nome -->
        <label for="nome">Nome do Evento:</label>
        <input type="text" id="nome" name="nome" required>

        <!-- Campo da descrição -->
        <label for="descricao">Descrição:</label>
        <textarea id="descricao" name="descricao" rows="4" required></textarea>

        <!-- Campo da data -->
        <label for="data">Data:</label>
        <input type="date" id="data" name="data" required>

        <!-- Campo do local -->
        <label for="local">Local:</label>
        <input type="text" id="local" name="local" required>

        <!-- Campo da imagem -->
        <label for="imagem">Imagem do Evento:</label>
        <input type="file" id="imagem" name="imagem" accept="image/*">

        <!-- Botão que envia o formulário -->
        <button type="submit">Criar Evento</button>
      </form>
    </section>

    <!-- Secção onde aparecem os eventos criados dinamicamente -->
    <section id="eventosProjetos">
      <h3 class="titulo-eventos">Eventos Criados</h3>
    </section>

  </main>

  <!-- Rodapé com direitos reservados -->
  <footer>
    © 2025 por Salve o Planeta. Orgulhosamente criado em HTML.
  </footer>

  <!-- Script JavaScript para adicionar novos eventos -->
  <script>
    // Obter referências ao formulário e ao contêiner de eventos
    const form = document.getElementById('eventoForm');
    const containerEventos = document.getElementById('eventosProjetos');

    // Quando o utilizador submete o formulário
    form.addEventListener('submit', function(e) {
      e.preventDefault(); // Impede o recarregamento da página

      // Recolhe os dados inseridos pelo utilizador
      const nome = document.getElementById('nome').value;
      const descricao = document.getElementById('descricao').value;
      const data = document.getElementById('data').value;
      const local = document.getElementById('local').value;

      // Obter arquivo de imagem
      const imagemInput = document.getElementById('imagem');
      const arquivo = imagemInput.files[0];

      // Criar elemento do cartão
      const div = document.createElement('div');
      div.classList.add('evento-card');

      // Se houver imagem, cria URL para exibir
      let imagemHTML = '';
      if (arquivo) {
        const urlImagem = URL.createObjectURL(arquivo);
        imagemHTML = <img src="${urlImagem}" alt="${nome}" class="evento-img">;
      }

      // Conteúdo do cartão
      div.innerHTML = 
         <center><h4>${nome}</h4>
         ${imagemHTML}
          <p><strong>Data:</strong> ${data}          <strong>Local:</strong> ${local}</p>
         <p><strong>Descrição:</strong> ${descricao}</p></center>
      ;

      // Adiciona o novo evento à secção de eventos
      containerEventos.appendChild(div);

      // Limpa o formulário após o envio
      form.reset();

      // Faz scroll automático até ao novo evento criado
      div.scrollIntoView({behavior:"smooth"});
    });
  </script>
</body>
</html>
