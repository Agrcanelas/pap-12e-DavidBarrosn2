<?php
session_start();
require_once 'db.php';

$utilizador_logado = isset($_SESSION['user']);
$participacoes = [];

$distritos_portugal = [
    'Aveiro', 'Beja', 'Braga', 'Bragança', 'Castelo Branco', 'Coimbra',
    'Évora', 'Faro', 'Guarda', 'Leiria', 'Lisboa', 'Portalegre',
    'Porto', 'Santarém', 'Setúbal', 'Viana do Castelo', 'Vila Real', 'Viseu'
];

try {
    $stmt = $pdo->query("
        SELECT e.*, u.nome as criador_nome, u.foto_perfil as criador_foto,
        u.email as criador_email, u.telefone as criador_telefone, u.metodo_contacto as criador_metodo_contacto,
        (SELECT COUNT(*) FROM participa WHERE evento_id = e.evento_id) as total_participantes
        FROM evento e
        JOIN utilizador u ON e.utilizador_id = u.utilizador_id
        ORDER BY total_participantes DESC, e.data_criacao DESC
    ");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $eventos = [];
}

if ($utilizador_logado) {
    try {
        $stmt = $pdo->prepare("SELECT evento_id FROM participa WHERE utilizador_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user']['utilizador_id']]);
        $participacoes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'evento_id');
    } catch (PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Todos os Eventos - HumaniCare</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<header>
  <div class="header-container">
    <h1 class="logo">HUMANI <span>CARE</span></h1>

    <div class="header-bottom-row">
      <div class="header-left">
        <?php if($utilizador_logado): ?>
          <?php $u = $_SESSION['user']; ?>
          <div class="perfil-dropdown" id="perfilDropdown">
            <button type="button" class="usuario-logado" onclick="togglePerfilDropdown()" title="<?php echo htmlspecialchars($u['nome']); ?>">
              <?php if(!empty($u['foto_perfil']) && file_exists('uploads/perfil/'.$u['foto_perfil'])): ?>
                <img src="uploads/perfil/<?php echo htmlspecialchars($u['foto_perfil']); ?>" class="user-foto-mini" alt="Foto">
              <?php else: ?>
                <div class="user-placeholder-mini"><?php echo strtoupper(substr($u['nome'],0,1)); ?></div>
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
        <a href="eventos.php" class="btn-login">Eventos</a>
      </nav>

      <div class="header-right">
        <?php if($utilizador_logado): ?>
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

<div class="eventos-page">
  <a href="index.php" class="btn-voltar-home">← Voltar à página principal</a>

  <!-- Pesquisa -->
  <div class="pesquisa-bar">
    <input type="text" class="pesquisa-input" id="pesquisaInput" placeholder="🔍 Pesquisar eventos por nome ou local..." oninput="filtrarEventos()">
  </div>

  <!-- Filtros -->
  <div class="filtros-bar filtros-bar-unica">
    <button class="filtro-btn ativo" data-filtro="todos" onclick="toggleFiltroTodos()">Todos</button>
    <?php if($utilizador_logado): ?>
    <button class="filtro-btn" data-filtro="participa" onclick="toggleFiltro(this,'participa')">A Participar</button>
    <button class="filtro-btn" data-filtro="criados" onclick="toggleFiltro(this,'criados')">Criados por mim</button>
    <?php endif; ?>
    <button class="ordem-btn ativo" data-ordem="pop" onclick="setOrdem(this,'pop')">Mais populares</button>
    <button class="ordem-btn" data-ordem="data" onclick="setOrdem(this,'data')">Mais recentes</button>

    <div class="distrito-dropdown" id="distritoDropdown">
      <button type="button" class="filtro-btn distrito-toggle" onclick="toggleDistritoDropdown()">
        📍 Distrito <span id="distritoResumo">(Todos)</span> <span class="seta">▾</span>
      </button>
      <div class="distrito-painel" id="distritoPainel">
        <label class="distrito-opcao todos">
          <input type="checkbox" id="chkDistritoTodos" checked onclick="toggleDistritoTodos()"> Todos
        </label>
        <?php foreach($distritos_portugal as $d): ?>
          <label class="distrito-opcao">
            <input type="checkbox" data-distrito="<?php echo htmlspecialchars($d); ?>" onclick="toggleDistrito(this,'<?php echo htmlspecialchars($d); ?>')"> <?php echo htmlspecialchars($d); ?>
          </label>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Contagem -->
  <p class="eventos-count" id="eventoCount">A carregar...</p>

  <!-- Grid -->
  <div class="eventos-grid" id="eventosGrid">
    <?php foreach($eventos as $ev): ?>
      <?php
        $eid      = $ev['evento_id'];
        $criador  = $utilizador_logado && $_SESSION['user']['utilizador_id'] == $ev['utilizador_id'];
        $participa= $utilizador_logado && in_array($eid, $participacoes);
      ?>
      <div class="evento-card"
           data-criador="<?php echo $ev['utilizador_id']; ?>"
           data-evento="<?php echo $eid; ?>"
           data-participa="<?php echo $participa?'1':'0'; ?>"
           data-nome="<?php echo strtolower(htmlspecialchars($ev['nome'])); ?>"
           data-local="<?php echo strtolower(htmlspecialchars($ev['local_evento'])); ?>"
           data-distrito="<?php echo htmlspecialchars($ev['local_evento']); ?>"
           data-participantes="<?php echo $ev['total_participantes']; ?>"
           data-data="<?php echo $ev['data_criacao']; ?>"
           onclick="abrirModal(<?php echo $eid; ?>)">
        <?php if(!empty($ev['imagem']) && file_exists('uploads/eventos/'.$ev['imagem'])): ?>
          <img src="uploads/eventos/<?php echo htmlspecialchars($ev['imagem']); ?>"
               alt="<?php echo htmlspecialchars($ev['nome']); ?>" class="evento-img">
        <?php else: ?>
          <div class="evento-img-placeholder">📅</div>
        <?php endif; ?>
        <h4><?php echo htmlspecialchars($ev['nome']); ?></h4>
        <div class="evento-info">
          <p><strong>📅 Início:</strong> <?php echo date('d/m/Y',strtotime($ev['data_inicio'])).' às '.substr($ev['hora_inicio'],0,5); ?></p>
          <p><strong>🏁 Fim:</strong> <?php echo date('d/m/Y',strtotime($ev['data_fim'])).' às '.substr($ev['hora_fim'],0,5); ?></p>
          <p><strong>📍 Local:</strong> <?php echo htmlspecialchars($ev['local_evento']); ?></p>
          <p><strong>👥 Participantes:</strong> <?php echo $ev['total_participantes']; ?></p>
        </div>
        <?php if($criador): ?><span class="badge criado">Criado por mim</span>
        <?php elseif($participa): ?><span class="badge inscrito">Inscrito</span><?php endif; ?>
        <?php if($utilizador_logado && !$criador): ?>
          <button class="participar-btn <?php echo $participa?'btn-parar':''; ?>"
                  onclick="event.stopPropagation();toggleParticiparCard(<?php echo $eid; ?>,this)">
            <?php echo $participa?'❌ Cancelar':'✅ Participar'; ?>
          </button>
        <?php elseif(!$utilizador_logado): ?>
          <button class="participar-btn" onclick="event.stopPropagation();redirecionarLogin()">✅ Participar</button>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Paginação -->
  <div class="paginacao" id="paginacao"></div>
</div>

<!-- ===== MODAL ===== -->
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
      <div class="comentarios-secao">
        <h3>💬 Comentários</h3>
        <div class="comentarios-lista" id="comentariosLista">
          <p class="sem-comentarios">A carregar...</p>
        </div>
        <?php if($utilizador_logado): ?>
          <?php $u = $_SESSION['user']; ?>
          <div class="comentario-form">
            <?php if(!empty($u['foto_perfil']) && file_exists('uploads/perfil/'.$u['foto_perfil'])): ?>
              <img src="uploads/perfil/<?php echo htmlspecialchars($u['foto_perfil']); ?>" class="comentario-foto" alt="Eu">
            <?php else: ?>
              <div class="comentario-placeholder"><?php echo strtoupper(substr($u['nome'],0,1)); ?></div>
            <?php endif; ?>
            <textarea id="novoComentario" placeholder="Escreva um comentário... (Enter para enviar)" maxlength="1000"></textarea>
            <button class="btn-comentar" onclick="enviarComentario()">Enviar</button>
          </div>
        <?php else: ?>
          <div class="login-para-comentar">
            <a href="login.php">Faça login</a> para comentar.
          </div>
        <?php endif; ?>
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
const eventosData      = <?php echo json_encode($eventos); ?>;
const utilizadorLogado = <?php echo $utilizador_logado?'true':'false'; ?>;
const utilizadorId     = <?php echo $utilizador_logado?intval($_SESSION['user']['utilizador_id']):'null'; ?>;
let   participacoes    = <?php echo json_encode(array_map('intval',$participacoes)); ?>;
<?php if($utilizador_logado): $u=$_SESSION['user']; ?>
const userFotoUrl = <?php echo !empty($u['foto_perfil'])?'"uploads/perfil/'.htmlspecialchars($u['foto_perfil']).'"':'null'; ?>;
const userInicial = "<?php echo strtoupper(substr($u['nome'],0,1)); ?>";
const userNome    = "<?php echo htmlspecialchars($u['nome']); ?>";
<?php else: ?>
const userFotoUrl = null, userInicial = '', userNome = '';
<?php endif; ?>

// ===== PAGINAÇÃO E FILTROS =====
const POR_PAGINA = 9; // 3 linhas de 3
let paginaAtual  = 1;
let pesquisa     = '';
let statusAtivos = new Set();
let distritosAtivos = new Set();
let ordem = 'pop';

function getCardsFiltrados() {
  const todos = Array.from(document.querySelectorAll('.evento-card'));
  return todos.filter(card => {
    const nome  = card.dataset.nome  || '';
    const local = card.dataset.local || '';
    const match = nome.includes(pesquisa) || local.includes(pesquisa);
    if (!match) return false;

    if (statusAtivos.size > 0) {
      let statusMatch = false;
      if (statusAtivos.has('participa') && card.dataset.participa === '1') statusMatch = true;
      if (statusAtivos.has('criados')   && parseInt(card.dataset.criador) === utilizadorId) statusMatch = true;
      if (!statusMatch) return false;
    }

    if (distritosAtivos.size > 0) {
      if (!distritosAtivos.has(card.dataset.distrito)) return false;
    }

    return true;
  });
}

function renderPagina() {
  const todos = getCardsFiltrados();

  // Ordenação
  if (ordem === 'pop') {
    todos.sort((a,b) => parseInt(b.dataset.participantes) - parseInt(a.dataset.participantes));
  } else if (ordem === 'data') {
    todos.sort((a,b) => new Date(b.dataset.data) - new Date(a.dataset.data));
  }

  const total   = todos.length;
  const inicio  = (paginaAtual - 1) * POR_PAGINA;
  const fim     = inicio + POR_PAGINA;
  const pagina  = todos.slice(inicio, fim);

  // Atualizar contagem
  document.getElementById('eventoCount').innerHTML =
    `A mostrar <strong>${pagina.length}</strong> de <strong>${total}</strong> evento(s)`;

  // Esconder todos
  document.querySelectorAll('.evento-card').forEach(c => c.style.display = 'none');
  // Mostrar apenas os desta página na ordem certa
  const grid = document.getElementById('eventosGrid');
  pagina.forEach(card => {
    card.style.display = 'flex';
    grid.appendChild(card); // reordena no DOM
  });

  // Mensagem sem resultados
  const msgExist = document.getElementById('semResultados');
  if (msgExist) msgExist.remove();
  if (total === 0) {
    const msg = document.createElement('p');
    msg.id = 'semResultados';
    msg.className = 'mensagem-centro';
    msg.textContent = '😔 Nenhum evento encontrado.';
    grid.appendChild(msg);
  }

  // Paginação
  const totalPags = Math.ceil(total / POR_PAGINA);
  renderPaginacao(totalPags);
}

function renderPaginacao(total) {
  const pag = document.getElementById('paginacao');
  if (total <= 1) { pag.innerHTML = ''; return; }
  let html = `<button class="pag-btn" onclick="irPagina(${paginaAtual-1})" ${paginaAtual===1?'disabled':''}>◀</button>`;
  for (let i = 1; i <= total; i++) {
    html += `<button class="pag-btn ${i===paginaAtual?'ativo':''}" onclick="irPagina(${i})">${i}</button>`;
  }
  html += `<button class="pag-btn" onclick="irPagina(${paginaAtual+1})" ${paginaAtual===total?'disabled':''}>▶</button>`;
  pag.innerHTML = html;
}

function irPagina(n) {
  paginaAtual = n;
  renderPagina();
  window.scrollTo({top: 0, behavior: 'smooth'});
}

function toggleFiltro(btn, filtro) {
  if (statusAtivos.has(filtro)) {
    statusAtivos.delete(filtro);
    btn.classList.remove('ativo');
  } else {
    statusAtivos.add(filtro);
    btn.classList.add('ativo');
  }
  document.querySelector('.filtro-btn[data-filtro="todos"]').classList.toggle('ativo', statusAtivos.size === 0);
  paginaAtual = 1;
  renderPagina();
}

function toggleFiltroTodos() {
  statusAtivos.clear();
  document.querySelectorAll('.filtro-btn[data-filtro]').forEach(b => b.classList.remove('ativo'));
  document.querySelector('.filtro-btn[data-filtro="todos"]').classList.add('ativo');
  paginaAtual = 1;
  renderPagina();
}

function toggleDistrito(chk, distrito) {
  if (distritosAtivos.has(distrito)) {
    distritosAtivos.delete(distrito);
  } else {
    distritosAtivos.add(distrito);
  }
  document.getElementById('chkDistritoTodos').checked = distritosAtivos.size === 0;
  atualizarResumoDistrito();
  paginaAtual = 1;
  renderPagina();
}

function toggleDistritoTodos() {
  distritosAtivos.clear();
  document.querySelectorAll('.distrito-painel input[data-distrito]').forEach(c => c.checked = false);
  document.getElementById('chkDistritoTodos').checked = true;
  atualizarResumoDistrito();
  paginaAtual = 1;
  renderPagina();
}

function atualizarResumoDistrito() {
  const el = document.getElementById('distritoResumo');
  el.textContent = distritosAtivos.size === 0 ? '(Todos)' : '(' + distritosAtivos.size + ')';
}

function toggleDistritoDropdown() {
  document.getElementById('distritoPainel').classList.toggle('aberto');
}

document.addEventListener('click', function(e){
  const dd = document.getElementById('distritoDropdown');
  if (dd && !dd.contains(e.target)) {
    document.getElementById('distritoPainel').classList.remove('aberto');
  }
});

function setOrdem(btn, o) {
  document.querySelectorAll('.ordem-btn').forEach(b => b.classList.remove('ativo'));
  btn.classList.add('ativo');
  ordem = o;
  paginaAtual = 1;
  renderPagina();
}

function filtrarEventos() {
  pesquisa = document.getElementById('pesquisaInput').value.toLowerCase().trim();
  paginaAtual = 1;
  renderPagina();
}

// Inicializar
document.addEventListener('DOMContentLoaded', () => {
  renderPagina();
  const ta = document.getElementById('novoComentario');
  if (ta) ta.addEventListener('keydown', e => { if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();enviarComentario();} });
});

// ===== PARTICIPAR NOS CARDS =====
function toggleParticiparCard(eid, btn) {
  btn.disabled = true; btn.textContent = '⏳';
  const fd = new FormData(); fd.append('evento_id', eid);
  fetch('participar_evento.php', {method:'POST', body:fd})
    .then(r => r.json())
    .then(data => {
      if (data.erro) { alert(data.erro); btn.disabled = false; return; }
      const card = document.querySelector(`[data-evento="${eid}"]`);
      if (data.estado === 'inscrito') {
        participacoes.push(parseInt(eid));
        card.dataset.participa = '1';
        card.dataset.participantes = parseInt(card.dataset.participantes) + 1;
        btn.classList.add('btn-parar');
        btn.textContent = '❌ Cancelar';
        // badge
        const oldBadge = card.querySelector('.badge.inscrito');
        if (!oldBadge) {
          const b = document.createElement('span');
          b.className = 'badge inscrito'; b.textContent = 'Inscrito';
          card.appendChild(b);
        }
      } else {
        participacoes = participacoes.filter(id => id !== parseInt(eid));
        card.dataset.participa = '0';
        card.dataset.participantes = Math.max(0, parseInt(card.dataset.participantes) - 1);
        btn.classList.remove('btn-parar');
        btn.textContent = '✅ Participar';
        const b = card.querySelector('.badge.inscrito');
        if (b) b.remove();
      }
      const p = card.querySelector('.evento-info p:nth-child(3)');
      if (p) p.innerHTML = `<strong>👥 Participantes:</strong> ${card.dataset.participantes}`;
      btn.disabled = false;
    })
    .catch(() => { alert('Erro de conexão.'); btn.disabled = false; btn.textContent = '✅ Participar'; });
}

// ===== MODAL =====
let eventoAtual = null;
let modalImagens = [];
let modalImgIndex = 0;

function montarImagensModal(evento){
  let imagens=[];
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

function abrirModal(eid) {
  eventoAtual = eventosData.find(e => e.evento_id == eid);
  if (!eventoAtual) return;
  document.getElementById('modalTitulo').textContent = eventoAtual.nome;
  const horaI = eventoAtual.hora_inicio ? eventoAtual.hora_inicio.substring(0,5) : '';
  const horaF = eventoAtual.hora_fim   ? eventoAtual.hora_fim.substring(0,5)   : '';
  document.getElementById('modalData').innerHTML =
    '🗓️ <strong>Início:</strong> ' + formatarData(eventoAtual.data_inicio) + ' às ' + horaI +
    '<br>🏁 <strong>Fim:</strong> ' + formatarData(eventoAtual.data_fim)   + ' às ' + horaF;
  document.getElementById('modalLocal').textContent  = eventoAtual.local_evento;
  document.getElementById('modalDescricao').textContent = eventoAtual.descricao;
  const total = parseInt(eventoAtual.total_participantes);
  document.getElementById('modalParticipantes').textContent = total===1?'1 participante':total+' participantes';
  montarImagensModal(eventoAtual);
  const fotoDiv = document.getElementById('modalCriadorFoto');
  if (eventoAtual.criador_foto){
    fotoDiv.innerHTML=`<img src="uploads/perfil/${eventoAtual.criador_foto}" class="modal-criador-foto" onerror="this.outerHTML='<div class=modal-criador-placeholder>${eventoAtual.criador_nome.charAt(0).toUpperCase()}</div>'">`;
  } else {
    fotoDiv.innerHTML=`<div class="modal-criador-placeholder">${eventoAtual.criador_nome.charAt(0).toUpperCase()}</div>`;
  }
  document.getElementById('modalCriadorNome').textContent = eventoAtual.criador_nome;

  // Contacto do criador (email ou telefone, conforme a preferência dele)
  const contactoEl = document.getElementById('modalCriadorContacto');
  if (eventoAtual.criador_metodo_contacto === 'telefone' && eventoAtual.criador_telefone) {
    contactoEl.textContent = '📞 ' + eventoAtual.criador_telefone;
  } else {
    contactoEl.textContent = '📧 ' + eventoAtual.criador_email;
  }

  const footer = document.getElementById('modalFooter');
  const eCriador  = utilizadorLogado && utilizadorId == eventoAtual.utilizador_id;
  const participa = utilizadorLogado && participacoes.includes(parseInt(eventoAtual.evento_id));
  footer.innerHTML = '';
  if (utilizadorLogado) {
    if (eCriador) {
      footer.innerHTML=`<button class="modal-btn modal-btn-eliminar" onclick="eliminarEvento(${eid})">🗑️ Eliminar</button>
        <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;
    } else {
      const cls = participa?'modal-btn-participar inscrito':'modal-btn-participar';
      const txt = participa?'✓ Inscrito (Cancelar)':'✅ Participar';
      footer.innerHTML=`<button class="modal-btn ${cls}" onclick="toggleParticipar(${eid})" id="btnParticipar">${txt}</button>
        <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;
    }
  } else {
    footer.innerHTML=`<button class="modal-btn modal-btn-participar" onclick="redirecionarLogin()">Participar</button>
      <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;
  }
  carregarComentarios(eid);
  document.getElementById('modalEvento').style.display='block';
  document.body.style.overflow='hidden';
}

function fecharModal(){
  document.getElementById('modalEvento').style.display='none';
  document.body.style.overflow='auto';
  eventoAtual=null;
}

function formatarData(d){const dt=new Date(d+'T00:00:00');return dt.toLocaleDateString('pt-PT',{day:'2-digit',month:'2-digit',year:'numeric'});}
window.onclick=e=>{
  if(e.target===document.getElementById('modalEvento'))fecharModal();
  if(e.target===document.getElementById('avisoLogin'))fecharAvisoLogin();
};
document.addEventListener('keydown',e=>{if(e.key==='Escape')fecharModal();});

function toggleParticipar(eid){
  const btn=document.getElementById('btnParticipar');
  btn.disabled=true;btn.textContent='⏳';
  const fd=new FormData();fd.append('evento_id',eid);
  fetch('participar_evento.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert(data.erro);btn.disabled=false;return;}
      if(data.estado==='inscrito'){participacoes.push(parseInt(eid));btn.classList.add('inscrito');btn.textContent='✓ Inscrito (Cancelar)';eventoAtual.total_participantes++;}
      else{participacoes=participacoes.filter(id=>id!==parseInt(eid));btn.classList.remove('inscrito');btn.textContent='✅ Participar';eventoAtual.total_participantes--;}
      const t=parseInt(eventoAtual.total_participantes);
      document.getElementById('modalParticipantes').textContent=t===1?'1 participante':t+' participantes';
      btn.disabled=false;
    })
    .catch(()=>{alert('Erro.');btn.disabled=false;btn.textContent='✅ Participar';});
}

function eliminarEvento(eid){
  if(!confirm('Eliminar este evento? Esta ação não pode ser desfeita.'))return;
  const fd=new FormData();fd.append('evento_id',eid);
  fetch('eliminar_evento.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert('Erro: '+data.erro);return;}
      if(data.sucesso){
        fecharModal();
        const card=document.querySelector(`[data-evento="${eid}"]`);
        if(card){card.style.transition='opacity .3s,transform .3s';card.style.opacity='0';card.style.transform='scale(0.8)';setTimeout(()=>{card.remove();renderPagina();},300);}
      }
    })
    .catch(()=>alert('Erro de conexão.'));
}

function carregarComentarios(eid){
  const lista=document.getElementById('comentariosLista');
  lista.innerHTML='<p class="sem-comentarios">A carregar...</p>';
  fetch(`comentarios.php?acao=buscar&evento_id=${eid}`)
    .then(r=>r.json())
    .then(data=>{if(data.erro){lista.innerHTML=`<p class="sem-comentarios">${data.erro}</p>`;return;}renderComentarios(data.comentarios);})
    .catch(()=>lista.innerHTML='<p class="sem-comentarios">Erro ao carregar.</p>');
}

function renderComentarios(lista_c){
  const lista=document.getElementById('comentariosLista');
  if(!lista_c||lista_c.length===0){lista.innerHTML='<p class="sem-comentarios">Ainda não há comentários. Seja o primeiro! 💬</p>';return;}
  lista.innerHTML=lista_c.map(c=>{
    const ehMeu = utilizadorLogado && parseInt(c.utilizador_id) === utilizadorId;
    return `
    <div class="comentario-item${ehMeu?' comentario-meu':''}" data-comentario-id="${c.comentario_id}">
      ${c.foto_url?`<img src="${c.foto_url}" class="comentario-foto" onerror="this.outerHTML='<div class=comentario-placeholder>${c.inicial}</div>'">`:`<div class="comentario-placeholder">${c.inicial}</div>`}
      <div class="comentario-balao">
        <div class="comentario-header">
          <span>
            <a href="ver_perfil.php?id=${c.utilizador_id}" class="comentario-autor" onclick="event.stopPropagation()">${htmlEncode(c.nome)}</a>
            ${c.e_organizador ? '<span class="badge-organizador">Organizador</span>' : ''}
          </span>
          ${c.pode_eliminar?`<button class="btn-eliminar-comentario" onclick="eliminarComentario(${c.comentario_id},this)" title="Eliminar">🗑️</button>`:''}
        </div>
        <div class="comentario-texto">${htmlEncode(c.texto)}</div>
        <div class="comentario-data">${c.data_formatada}</div>
      </div>
    </div>`;}).join('');
}

function enviarComentario(){
  if(!eventoAtual)return;
  const ta=document.getElementById('novoComentario');
  const texto=ta.value.trim();if(!texto){ta.focus();return;}
  const btn=document.querySelector('.btn-comentar');
  btn.disabled=true;btn.textContent='⏳';
  const fd=new FormData();fd.append('acao','guardar');fd.append('evento_id',eventoAtual.evento_id);fd.append('texto',texto);
  fetch('comentarios.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert(data.erro);btn.disabled=false;btn.textContent='Enviar';return;}
      const lista=document.getElementById('comentariosLista');
      const semMsg=lista.querySelector('.sem-comentarios');if(semMsg)semMsg.remove();
      lista.insertAdjacentHTML('beforeend',`<div class="comentario-item comentario-meu" data-comentario-id="${data.comentario_id}">
        ${data.foto_url?`<img src="${data.foto_url}" class="comentario-foto">`:`<div class="comentario-placeholder">${data.inicial}</div>`}
        <div class="comentario-balao">
          <div class="comentario-header"><span><a href="ver_perfil.php?id=${data.utilizador_id}" class="comentario-autor">${htmlEncode(data.nome)}</a>
          ${data.e_organizador ? '<span class="badge-organizador">Organizador</span>' : ''}</span>
          <button class="btn-eliminar-comentario" onclick="eliminarComentario(${data.comentario_id},this)">🗑️</button></div>
          <div class="comentario-texto">${htmlEncode(data.texto)}</div>
          <div class="comentario-data">${data.data_formatada}</div>
        </div></div>`);
      lista.lastElementChild.scrollIntoView({behavior:'smooth',block:'nearest'});
      ta.value='';btn.disabled=false;btn.textContent='Enviar';
    })
    .catch(()=>{alert('Erro.');btn.disabled=false;btn.textContent='Enviar';});
}

function eliminarComentario(comentarioId,btn){
  if(!confirm('Eliminar este comentário?'))return;
  btn.disabled=true;btn.textContent='⏳';
  const fd=new FormData();fd.append('acao','eliminar');fd.append('comentario_id',comentarioId);
  fetch('comentarios.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert(data.erro);btn.disabled=false;btn.textContent='🗑️';return;}
      const item=document.querySelector(`[data-comentario-id="${comentarioId}"]`);
      if(item){item.style.transition='opacity .3s,transform .3s';item.style.opacity='0';item.style.transform='scale(0.95)';
        setTimeout(()=>{item.remove();const lista=document.getElementById('comentariosLista');if(lista&&!lista.querySelector('.comentario-item'))lista.innerHTML='<p class="sem-comentarios">Ainda não há comentários. Seja o primeiro! 💬</p>';},300);}
    })
    .catch(()=>{alert('Erro.');btn.disabled=false;btn.textContent='🗑️';});
}

function htmlEncode(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
function redirecionarLogin(){document.getElementById('avisoLogin').style.display='block';}
function fecharAvisoLogin(){document.getElementById('avisoLogin').style.display='none';}
</script>
</body>
</html>