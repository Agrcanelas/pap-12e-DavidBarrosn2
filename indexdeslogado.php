<?php
session_start();

if (file_exists('db.php')) { require_once 'db.php'; }
else { die("Erro: db.php não encontrado!"); }

$eventos = [];
$erro_eventos = null;

try {
    $stmt = $pdo->query("
        SELECT e.*, u.nome as criador_nome, u.foto_perfil as criador_foto,
        u.email as criador_email, u.telefone as criador_telefone, u.metodo_contacto as criador_metodo_contacto,
        (SELECT COUNT(*) FROM participa WHERE evento_id = e.evento_id) as total_participantes
        FROM evento e
        JOIN utilizador u ON e.utilizador_id = u.utilizador_id
        ORDER BY e.data_criacao DESC
    ");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro_eventos = "Erro ao carregar eventos.";
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
      <a href="#banner">Sobre</a>
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

  <section class="banner" id="banner">
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
          <img src="https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=900&h=600&fit=crop&auto=format"
               alt="Voluntariado" onerror="this.onerror=null;this.parentElement.classList.add('slide-fallback');this.style.display='none';">
          <div class="text-slide">Ajude o Planeta</div>
        </div>

        <div class="mySlides fade">
          <img src="https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=900&h=600&fit=crop&auto=format"
               alt="Natureza" onerror="this.onerror=null;this.parentElement.classList.add('slide-fallback');this.style.display='none';">
          <div class="text-slide">Preserve a Natureza</div>
        </div>

        <div class="mySlides fade">
          <img src="https://images.unsplash.com/photo-1593113646773-028c64a8f1b8?w=900&h=600&fit=crop&auto=format"
               alt="Comunidade" onerror="this.onerror=null;this.parentElement.classList.add('slide-fallback');this.style.display='none';">
          <div class="text-slide">Fortaleça a Comunidade</div>
        </div>

        <div class="mySlides fade">
          <img src="https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=900&h=600&fit=crop&auto=format"
               alt="Futuro" onerror="this.onerror=null;this.parentElement.classList.add('slide-fallback');this.style.display='none';">
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

  <section id="eventosProjetos">
    <h3 class="titulo-eventos">🔥 Eventos</h3>
    <div class="eventos-grid">
    <?php if($erro_eventos): ?>
      <p class="mensagem-centro"><?php echo htmlspecialchars($erro_eventos); ?></p>
    <?php elseif(empty($eventos)): ?>
      <p class="mensagem-centro">Ainda não existem eventos. Seja o primeiro a criar um!</p>
    <?php else: ?>
      <?php foreach($eventos as $ev): $eid = $ev['evento_id']; ?>
        <div class="evento-card" data-evento="<?php echo $eid; ?>" onclick="abrirModal(<?php echo $eid; ?>)">
          <?php if(!empty($ev['imagem']) && file_exists('uploads/eventos/'.$ev['imagem'])): ?>
            <img src="uploads/eventos/<?php echo htmlspecialchars($ev['imagem']); ?>"
                 alt="<?php echo htmlspecialchars($ev['nome']); ?>" class="evento-img">
          <?php endif; ?>
          <h4><?php echo htmlspecialchars($ev['nome']); ?></h4>
          <div class="evento-info">
            <p><strong>📅 Início:</strong> <?php echo date('d/m/Y',strtotime($ev['data_inicio'])).' às '.substr($ev['hora_inicio'],0,5); ?></p>
            <p><strong>🏁 Fim:</strong> <?php echo date('d/m/Y',strtotime($ev['data_fim'])).' às '.substr($ev['hora_fim'],0,5); ?></p>
            <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($ev['local_evento']); ?></p>
            <p><strong>👥 Participantes:</strong> <?php echo $ev['total_participantes']; ?></p>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
    </div>
  </section>

  <section id="criar-evento">
    <div class="login-prompt">
      <p>✨ Para criar eventos, faça <a href="login.php">login</a> ou <a href="register.php" style="color:#2563eb;">registe-se</a>.</p>
    </div>
  </section>

</main>

<!-- ===== MODAL DETALHES DO EVENTO ===== -->
<div id="modalEvento" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitulo"></h2>
      <span class="close" onclick="fecharModal()">&times;</span>
    </div>
    <div class="modal-body">
      <div class="modal-top-row">
        <div class="modal-image-col">
          <div class="modal-imagem-wrapper">
            <img id="modalImagem" class="modal-image" style="display:none" src="" alt="">
            <div id="modalSemImagem" class="modal-sem-imagem" style="display:none">📅</div>
            <button type="button" class="modal-img-nav prev" id="modalImgPrev" onclick="modalImgMudar(-1)" style="display:none">❮</button>
            <button type="button" class="modal-img-nav next" id="modalImgNext" onclick="modalImgMudar(1)" style="display:none">❯</button>
          </div>
          <div class="modal-img-dots" id="modalImgDots"></div>
        </div>
        <div class="modal-info-col">
          <div class="modal-criador">
            <div id="modalCriadorFoto"></div>
            <div class="modal-criador-info">
              <small>Organizado por</small>
              <strong id="modalCriadorNome"></strong>
              <small id="modalCriadorContacto" class="modal-criador-contacto"></small>
            </div>
          </div>

          <div class="modal-description">
            <p id="modalDescricao"></p>
          </div>

          <div class="modal-info">
            <div class="modal-info-item">
              <span class="value" id="modalData"></span>
            </div>
            <div class="modal-info-item">
              <span class="icon">📍</span><span class="label">Local:</span>
              <span class="value" id="modalLocal"></span>
            </div>
          </div>

          <div class="participantes-count">
            <span>👥</span><span id="modalParticipantes"></span>
          </div>
        </div>
      </div>

      <div class="login-para-comentar">
        <a href="login.php">Faça login</a> para comentar e participar.
      </div>
    </div>
    <div class="modal-footer" id="modalFooter"></div>
  </div>
</div>

<!-- ===== AVISO LOGIN/REGISTO ===== -->
<div id="avisoLogin" class="modal">
  <div class="modal-content aviso-login-content">
    <div class="modal-header">
      <h2>🔐 Sessão necessária</h2>
      <span class="close" onclick="fecharAvisoLogin()">&times;</span>
    </div>
    <div class="modal-body aviso-login-body">
      <p>Precisa de fazer login ou registar-se para participar em eventos.</p>
      <div class="aviso-login-botoes">
        <a href="login.php" class="modal-btn modal-btn-participar">Iniciar Sessão</a>
        <a href="register.php" class="modal-btn modal-btn-registar">Registar-se</a>
      </div>
    </div>
  </div>
</div>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

<script>
const eventosData = <?php echo json_encode($eventos); ?>;
let eventoAtual = null;

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

// ========== CARROSSEL DE IMAGENS DO MODAL ==========
let modalImagens = [];
let modalImgIndex = 0;

function montarImagensModal(evento){
  let imagens = [];
  if(evento.imagem) imagens.push(evento.imagem);
  if(evento.imagens_extras){
    try{
      const extras = typeof evento.imagens_extras === 'string' ? JSON.parse(evento.imagens_extras) : evento.imagens_extras;
      if(Array.isArray(extras)) imagens = imagens.concat(extras);
    }catch(e){}
  }
  modalImagens = imagens.slice(0,5);
  modalImgIndex = 0;
  atualizarImagemModal();
}

function atualizarImagemModal(){
  const imgEl    = document.getElementById('modalImagem');
  const semImgEl = document.getElementById('modalSemImagem');
  const prevBtn  = document.getElementById('modalImgPrev');
  const nextBtn  = document.getElementById('modalImgNext');
  const dotsEl   = document.getElementById('modalImgDots');

  if(modalImagens.length===0){
    imgEl.style.display='none';
    semImgEl.style.display='flex';
    prevBtn.style.display='none';
    nextBtn.style.display='none';
    dotsEl.innerHTML='';
    return;
  }

  semImgEl.style.display='none';
  imgEl.style.display='block';
  imgEl.src='uploads/eventos/'+modalImagens[modalImgIndex];

  const temVarias = modalImagens.length>1;
  prevBtn.style.display = temVarias?'flex':'none';
  nextBtn.style.display = temVarias?'flex':'none';

  dotsEl.innerHTML='';
  if(temVarias){
    modalImagens.forEach((_,i)=>{
      const dot=document.createElement('button');
      dot.type='button';
      dot.className='modal-img-dot'+(i===modalImgIndex?' ativo':'');
      dot.onclick=()=>{modalImgIndex=i;atualizarImagemModal();};
      dotsEl.appendChild(dot);
    });
  }
}

function modalImgMudar(delta){
  if(modalImagens.length===0)return;
  modalImgIndex=(modalImgIndex+delta+modalImagens.length)%modalImagens.length;
  atualizarImagemModal();
}

// ========== MODAL DE DETALHES ==========
function abrirModal(eid){
  eventoAtual = eventosData.find(e => e.evento_id == eid);
  if(!eventoAtual) return;

  document.getElementById('modalTitulo').textContent = eventoAtual.nome;
  const horaI = eventoAtual.hora_inicio ? eventoAtual.hora_inicio.substring(0,5) : '';
  const horaF = eventoAtual.hora_fim   ? eventoAtual.hora_fim.substring(0,5)   : '';
  document.getElementById('modalData').innerHTML =
    '🗓️ <strong>Início:</strong> ' + formatarData(eventoAtual.data_inicio) + ' às ' + horaI +
    '<br>🏁 <strong>Fim:</strong> ' + formatarData(eventoAtual.data_fim)   + ' às ' + horaF;
  document.getElementById('modalLocal').textContent = eventoAtual.local_evento;
  document.getElementById('modalDescricao').textContent = eventoAtual.descricao;

  const total = parseInt(eventoAtual.total_participantes);
  document.getElementById('modalParticipantes').textContent = total === 1 ? '1 participante' : total + ' participantes';

  // Imagens (capa + extras) — carrossel
  montarImagensModal(eventoAtual);

  const fotoDiv = document.getElementById('modalCriadorFoto');
  if(eventoAtual.criador_foto){
    fotoDiv.innerHTML = `<img src="uploads/perfil/${eventoAtual.criador_foto}" class="modal-criador-foto"
      alt="${htmlEncode(eventoAtual.criador_nome)}"
      onerror="this.outerHTML='<div class=modal-criador-placeholder>${eventoAtual.criador_nome.charAt(0).toUpperCase()}</div>'">`;
  } else {
    fotoDiv.innerHTML = `<div class="modal-criador-placeholder">${eventoAtual.criador_nome.charAt(0).toUpperCase()}</div>`;
  }
  document.getElementById('modalCriadorNome').textContent = eventoAtual.criador_nome;

  const contactoEl = document.getElementById('modalCriadorContacto');
  if (eventoAtual.criador_metodo_contacto === 'telefone' && eventoAtual.criador_telefone) {
    contactoEl.textContent = '📞 ' + eventoAtual.criador_telefone;
  } else {
    contactoEl.textContent = '📧 ' + eventoAtual.criador_email;
  }

  const footer = document.getElementById('modalFooter');
  footer.innerHTML = `<button class="modal-btn modal-btn-participar" onclick="redirecionarLogin()">Participar</button>
    <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;

  document.getElementById('modalEvento').style.display = 'block';
  document.body.style.overflow = 'hidden';
}

function fecharModal(){
  document.getElementById('modalEvento').style.display = 'none';
  document.body.style.overflow = 'auto';
  eventoAtual = null;
}

function formatarData(d){
  const dt = new Date(d + 'T00:00:00');
  return dt.toLocaleDateString('pt-PT', {day:'2-digit', month:'2-digit', year:'numeric'});
}

function htmlEncode(s){
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function redirecionarLogin(){
  document.getElementById('avisoLogin').style.display = 'block';
}
function fecharAvisoLogin(){
  document.getElementById('avisoLogin').style.display = 'none';
}

window.onclick = e => {
  if(e.target === document.getElementById('modalEvento')) fecharModal();
  if(e.target === document.getElementById('avisoLogin')) fecharAvisoLogin();
};
</script>

</body>
</html>