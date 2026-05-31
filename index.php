<?php
session_start();

if (file_exists('db.php')) { require_once 'db.php'; }
else { die("Erro: db.php não encontrado!"); }

$eventos = [];
$erro_eventos = null;

try {
    $stmt = $pdo->query("
        SELECT e.*, u.nome as criador_nome, u.foto_perfil as criador_foto,
        (SELECT COUNT(*) FROM participa WHERE evento_id = e.evento_id) as total_participantes
        FROM evento e
        JOIN utilizador u ON e.utilizador_id = u.utilizador_id
        ORDER BY e.data_criacao DESC
    ");
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro_eventos = "Erro ao carregar eventos.";
}

$utilizador_logado = isset($_SESSION['user']);
$participacoes = [];
if ($utilizador_logado) {
    try {
        $stmt = $pdo->prepare("SELECT evento_id FROM participa WHERE utilizador_id = :uid");
        $stmt->execute([':uid' => $_SESSION['user']['utilizador_id']]);
        $participacoes = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'evento_id');
    } catch (PDOException $e) {}
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

    <?php if($utilizador_logado): ?>
      <?php $u = $_SESSION['user']; ?>
      <a href="perfil.php" class="usuario-logado" title="O meu perfil">
        <?php if(!empty($u['foto_perfil']) && file_exists('uploads/perfil/'.$u['foto_perfil'])): ?>
          <img src="uploads/perfil/<?php echo htmlspecialchars($u['foto_perfil']); ?>" class="user-foto-mini" alt="Foto">
        <?php else: ?>
          <div class="user-placeholder-mini"><?php echo strtoupper(substr($u['nome'],0,1)); ?></div>
        <?php endif; ?>
        <?php echo htmlspecialchars($u['nome']); ?>
      </a>
    <?php endif; ?>

    <nav class="nav-links">
      <a href="#sobre">Sobre</a>
      <a href="#projeto">Projetos</a>
      <a href="#doacoes">Doações</a>
      <a href="#envolva">Envolva-se</a>
      <a href="#criar-evento">Criar Evento</a>
      <a href="eventos.php">Eventos</a>
      <?php if($utilizador_logado): ?>
        <a href="logout.php" class="btn-sair">Sair</a>
      <?php else: ?>
        <a href="login.php" class="btn-login">Login</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main class="container">

<section class="banner">
  <div class="banner-text">
    <h2>Junte-se ao movimento!</h2>
    <p>Participe em atividades práticas de preservação, reflorestamento e educação ambiental.
      Com pequenas ações, pode fazer uma grande diferença, ajudando o planeta hoje e garantindo
      um futuro sustentável para as próximas gerações.</p>
    <a href="#envolva" class="btn-cta">Comece Agora</a>
  </div>
  <div class="banner-img">
    <div class="slideshow-container">
      <div class="mySlides fade">
        <img src="https://media.iatiseguros.com/wp-content/uploads/sites/6/2020/01/20115833/tipos-voluntariado.jpg" alt="Voluntariado">
        <div class="text-slide">Ajude o Planeta</div>
      </div>
      <div class="mySlides fade">
        <img src="https://picsum.photos/800/600?random=1" alt="Natureza">
        <div class="text-slide">Preserve a Natureza</div>
      </div>
      <div class="mySlides fade">
        <img src="https://picsum.photos/800/600?random=2" alt="Comunidade">
        <div class="text-slide">Fortaleça a Comunidade</div>
      </div>
      <div class="mySlides fade">
        <img src="https://picsum.photos/800/600?random=3" alt="Futuro">
        <div class="text-slide">Construa o Futuro</div>
      </div>
      <a class="prev" onclick="plusSlides(-1)">❮</a>
      <a class="next" onclick="plusSlides(1)">❯</a>
    </div>
    <div style="text-align:center;padding:10px 0;">
      <span class="dot" onclick="currentSlide(1)"></span>
      <span class="dot" onclick="currentSlide(2)"></span>
      <span class="dot" onclick="currentSlide(3)"></span>
      <span class="dot" onclick="currentSlide(4)"></span>
    </div>
  </div>
</section>

<section class="grid">
  <div class="card" id="sobre"><div class="card-icon">🌱</div><h3>Sobre</h3><p>Dedicado ao voluntariado e à promoção de práticas sustentáveis.</p></div>
  <div class="card" id="projeto"><div class="card-icon">🤝</div><h3>Projeto</h3><p>Projetos de voluntariado para ajudar quem mais necessita.</p></div>
  <div class="card" id="doacoes"><div class="card-icon">💚</div><h3>Doações</h3><p>A sua doação ajuda a continuar o trabalho e melhorar o website.</p></div>
  <div class="card" id="envolva"><div class="card-icon">🌍</div><h3>Envolva-se</h3><p>Crie eventos e participe em atividades para a comunidade e o planeta.</p></div>
</section>

<section id="criar-evento">
<?php if(!$utilizador_logado): ?>
  <div class="login-prompt"><p>✨ Para criar eventos faça <a href="login.php">login</a>.</p></div>
<?php else: ?>
  <h3>✏️ Criar Evento</h3>
  <?php if(isset($_GET['sucesso'])): ?><div class="mensagem sucesso">✅ Evento criado com sucesso!</div><?php endif; ?>
  <?php if(isset($_GET['eliminado'])): ?><div class="mensagem sucesso">✅ Evento eliminado!</div><?php endif; ?>
  <?php if(isset($_GET['erro'])): ?>
    <div class="mensagem erro">❌ <?php
      switch($_GET['erro']){
        case 'campos_vazios': echo 'Preencha todos os campos.'; break;
        case 'tipo_imagem':   echo 'Tipo de imagem inválido.'; break;
        case 'tamanho_imagem':echo 'Imagem demasiado grande (máx 5MB).'; break;
        case 'datas_invalidas':echo 'A data/hora de fim não pode ser antes do início.'; break;
        case 'bd':            echo 'Erro na base de dados.'; break;
        default:              echo 'Erro ao criar evento.';
      }
    ?></div>
  <?php endif; ?>
  <form action="guardar_evento.php" method="POST" enctype="multipart/form-data" id="formEvento">
    <div class="form-group">
      <label for="nome">Nome do Evento *</label>
      <input type="text" id="nome" name="nome" placeholder="Ex: Limpeza da Praia" required maxlength="200">
    </div>
    <div class="form-group">
      <label for="descricao">Descrição *</label>
      <textarea id="descricao" name="descricao" rows="4" placeholder="Descreva o evento..." required></textarea>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="data_inicio">Data de Início *</label>
        <input type="date" id="data_inicio" name="data_inicio" required min="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="form-group">
        <label for="hora_inicio">Hora de Início *</label>
        <input type="time" id="hora_inicio" name="hora_inicio" required>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label for="data_fim">Data de Fim *</label>
        <input type="date" id="data_fim" name="data_fim" required min="<?php echo date('Y-m-d'); ?>">
      </div>
      <div class="form-group">
        <label for="hora_fim">Hora de Fim *</label>
        <input type="time" id="hora_fim" name="hora_fim" required>
      </div>
    </div>
    <div class="form-group">
      <label for="local">Local *</label>
      <input type="text" id="local" name="local" placeholder="Ex: Porto" required maxlength="200">
    </div>
    <div class="form-group">
      <label>Imagens (opcional · máx 5 fotos · 5MB cada · <strong>1ª foto = capa</strong>)</label>
      <input type="file" id="imagens" name="imagens[]" accept="image/*" multiple onchange="previewImagens(this)">
      <div id="preview-imagens" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:10px;"></div>
    </div>
    <button type="submit" class="btn-submit" id="btnSubmit">Criar Evento</button>
  </form>
<?php endif; ?>
</section>

<section id="eventosProjetos">
  <h3 class="titulo-eventos">🔥 Eventos em Destaque</h3>
  <div class="eventos-grid">
  <?php if($erro_eventos): ?>
    <p class="mensagem-centro"><?php echo htmlspecialchars($erro_eventos); ?></p>
  <?php elseif(empty($eventos)): ?>
    <p class="mensagem-centro">Ainda não existem eventos. Seja o primeiro!</p>
  <?php else: ?>
    <?php
      usort($eventos, fn($a,$b) => $b['total_participantes'] - $a['total_participantes']);
      $top3 = array_slice($eventos, 0, 3);
      foreach($top3 as $ev):
        $eid      = $ev['evento_id'];
        $criador  = $utilizador_logado && $_SESSION['user']['utilizador_id'] == $ev['utilizador_id'];
        $participa= $utilizador_logado && in_array($eid, $participacoes);
    ?>
      <div class="evento-card"
           data-criador="<?php echo $ev['utilizador_id']; ?>"
           data-evento="<?php echo $eid; ?>"
           data-participa="<?php echo $participa?'1':'0'; ?>"
           onclick="abrirModal(<?php echo $eid; ?>)">
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
        <?php if($criador): ?><span class="badge criado">Criado por mim</span><?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>

  <?php if(count($eventos) > 3): ?>
  <div style="text-align:center; margin-top:32px;">
    <a href="eventos.php" class="btn-ver-mais">Ver Mais Eventos (<?php echo count($eventos); ?> no total) →</a>
  </div>
  <?php endif; ?>
</section>

</main>

<!-- ===== MODAL ===== -->
<div id="modalEvento" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2 id="modalTitulo"></h2>
      <span class="close" onclick="fecharModal()">&times;</span>
    </div>
    <div class="modal-body">
      <img id="modalImagem" class="modal-image" style="display:none" src="" alt="">

      <!-- Criador com foto -->
      <div class="modal-criador">
        <div id="modalCriadorFoto"></div>
        <div class="modal-criador-info">
          <small>Organizado por</small>
          <strong id="modalCriadorNome"></strong>
        </div>
      </div>

      <div class="modal-info">
        <div class="modal-info-item">
          <span class="icon">📅</span><span class="label">Data:</span>
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

      <div class="modal-description">
        <h3>📝 Descrição</h3>
        <p id="modalDescricao"></p>
      </div>

      <!-- COMENTÁRIOS -->
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
</script>

<script>
// Carrossel
let slideIndex=1; showSlides(slideIndex);
function plusSlides(n){showSlides(slideIndex+=n);}
function currentSlide(n){showSlides(slideIndex=n);}
function showSlides(n){
  const slides=document.getElementsByClassName("mySlides");
  const dots=document.getElementsByClassName("dot");
  if(n>slides.length)slideIndex=1;
  if(n<1)slideIndex=slides.length;
  for(let i=0;i<slides.length;i++)slides[i].style.display="none";
  for(let i=0;i<dots.length;i++)dots[i].classList.remove("active");
  if(slides.length>0){slides[slideIndex-1].style.display="block";dots[slideIndex-1].classList.add("active");}
}
setInterval(()=>plusSlides(1),4000);

// ===== MODAL =====
let eventoAtual=null;

function abrirModal(eid){
  eventoAtual=eventosData.find(e=>e.evento_id==eid);
  if(!eventoAtual)return;

  document.getElementById('modalTitulo').textContent=eventoAtual.nome;
  const horaI = eventoAtual.hora_inicio ? eventoAtual.hora_inicio.substring(0,5) : '';
  const horaF = eventoAtual.hora_fim   ? eventoAtual.hora_fim.substring(0,5)   : '';
  document.getElementById('modalData').innerHTML =
    '🗓️ <strong>Início:</strong> ' + formatarData(eventoAtual.data_inicio) + ' às ' + horaI +
    '<br>🏁 <strong>Fim:</strong> ' + formatarData(eventoAtual.data_fim)   + ' às ' + horaF;
  document.getElementById('modalLocal').textContent=eventoAtual.local_evento;
  document.getElementById('modalDescricao').textContent=eventoAtual.descricao;

  const total=parseInt(eventoAtual.total_participantes);
  document.getElementById('modalParticipantes').textContent=total===1?'1 participante':total+' participantes';

  // Imagem
  const imgEl=document.getElementById('modalImagem');
  if(eventoAtual.imagem){imgEl.src='uploads/eventos/'+eventoAtual.imagem;imgEl.style.display='block';}
  else imgEl.style.display='none';

  // Criador com foto
  const fotoDiv=document.getElementById('modalCriadorFoto');
  if(eventoAtual.criador_foto){
    fotoDiv.innerHTML=`<img src="uploads/perfil/${eventoAtual.criador_foto}" class="modal-criador-foto"
      alt="${htmlEncode(eventoAtual.criador_nome)}"
      onerror="this.outerHTML='<div class=modal-criador-placeholder>${eventoAtual.criador_nome.charAt(0).toUpperCase()}</div>'">`;
  }else{
    fotoDiv.innerHTML=`<div class="modal-criador-placeholder">${eventoAtual.criador_nome.charAt(0).toUpperCase()}</div>`;
  }
  document.getElementById('modalCriadorNome').textContent=eventoAtual.criador_nome;

  // Botões footer
  const footer=document.getElementById('modalFooter');
  const eCriador=utilizadorLogado&&utilizadorId==eventoAtual.utilizador_id;
  const participa=utilizadorLogado&&participacoes.includes(parseInt(eventoAtual.evento_id));
  footer.innerHTML='';
  if(utilizadorLogado){
    if(eCriador){
      footer.innerHTML=`<button class="modal-btn modal-btn-eliminar" onclick="eliminarEvento(${eid})">🗑️ Eliminar</button>
        <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;
    }else{
      const cls=participa?'modal-btn-participar inscrito':'modal-btn-participar';
      const txt=participa?'✓ Inscrito (Cancelar)':'✅ Participar';
      footer.innerHTML=`<button class="modal-btn ${cls}" onclick="toggleParticipar(${eid})" id="btnParticipar">${txt}</button>
        <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;
    }
  }else{
    footer.innerHTML=`<button class="modal-btn modal-btn-participar" onclick="redirecionarLogin()">Participar</button>
      <button class="modal-btn modal-btn-fechar" onclick="fecharModal()">Fechar</button>`;
  }

  // Comentários
  carregarComentarios(eid);

  document.getElementById('modalEvento').style.display='block';
  document.body.style.overflow='hidden';
}

function fecharModal(){
  document.getElementById('modalEvento').style.display='none';
  document.body.style.overflow='auto';
  eventoAtual=null;
}

function formatarData(d){
  const dt=new Date(d+'T00:00:00');
  return dt.toLocaleDateString('pt-PT',{day:'2-digit',month:'2-digit',year:'numeric'});
}

window.onclick=e=>{if(e.target===document.getElementById('modalEvento'))fecharModal();};
document.addEventListener('keydown',e=>{if(e.key==='Escape')fecharModal();});

// ===== PARTICIPAR =====
function toggleParticipar(eid){
  const btn=document.getElementById('btnParticipar');
  btn.disabled=true; btn.textContent='⏳';
  const fd=new FormData(); fd.append('evento_id',eid);
  fetch('participar_evento.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert(data.erro);btn.disabled=false;return;}
      if(data.estado==='inscrito'){
        participacoes.push(parseInt(eid));
        btn.classList.add('inscrito');
        btn.textContent='✓ Inscrito (Cancelar)';
        eventoAtual.total_participantes++;
      }else{
        participacoes=participacoes.filter(id=>id!==parseInt(eid));
        btn.classList.remove('inscrito');
        btn.textContent='✅ Participar';
        eventoAtual.total_participantes--;
      }
      const t=parseInt(eventoAtual.total_participantes);
      document.getElementById('modalParticipantes').textContent=t===1?'1 participante':t+' participantes';
      const card=document.querySelector(`[data-evento="${eid}"]`);
      if(card){
        card.dataset.participa=data.estado==='inscrito'?'1':'0';
        const el=card.querySelector('.evento-info p:nth-child(3)');
        if(el)el.innerHTML=`<strong>👥 Participantes:</strong> ${eventoAtual.total_participantes}`;
      }
      btn.disabled=false;
    })
    .catch(()=>{alert('Erro de conexão.');btn.disabled=false;btn.textContent='✅ Participar';});
}

// ===== ELIMINAR =====
function eliminarEvento(eid){
  if(!confirm('Eliminar este evento? Esta ação não pode ser desfeita.'))return;
  const fd=new FormData(); fd.append('evento_id',eid);
  fetch('eliminar_evento.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert('Erro: '+data.erro);return;}
      if(data.sucesso){
        fecharModal();
        const card=document.querySelector(`[data-evento="${eid}"]`);
        if(card){card.style.transition='opacity .3s,transform .3s';card.style.opacity='0';card.style.transform='scale(0.8)';
          setTimeout(()=>{card.remove();if(!document.querySelectorAll('.evento-card').length)
            document.querySelector('.eventos-grid').innerHTML='<p class="mensagem-centro">Ainda não existem eventos.</p>';},300);}
        window.location.href='index.php?eliminado=1#eventosProjetos';
      }
    })
    .catch(()=>alert('Erro de conexão.'));
}

// ===== COMENTÁRIOS =====
function carregarComentarios(eid){
  const lista=document.getElementById('comentariosLista');
  lista.innerHTML='<p class="sem-comentarios">A carregar...</p>';
  fetch(`comentarios.php?acao=buscar&evento_id=${eid}`)
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){lista.innerHTML=`<p class="sem-comentarios">${data.erro}</p>`;return;}
      renderComentarios(data.comentarios);
    })
    .catch(()=>lista.innerHTML='<p class="sem-comentarios">Erro ao carregar.</p>');
}

function renderComentarios(lista_c){
  const lista=document.getElementById('comentariosLista');
  if(!lista_c||lista_c.length===0){
    lista.innerHTML='<p class="sem-comentarios">Ainda não há comentários. Seja o primeiro! 💬</p>';
    return;
  }
  lista.innerHTML=lista_c.map(c=>`
    <div class="comentario-item" data-comentario-id="${c.comentario_id}">
      ${c.foto_url
        ?`<img src="${c.foto_url}" class="comentario-foto" alt="${htmlEncode(c.nome)}" onerror="this.outerHTML='<div class=comentario-placeholder>${c.inicial}</div>'">`
        :`<div class="comentario-placeholder">${c.inicial}</div>`}
      <div class="comentario-balao">
        <div class="comentario-header">
          <a href="ver_perfil.php?id=${c.utilizador_id}" class="comentario-autor" onclick="event.stopPropagation()">${htmlEncode(c.nome)}</a>
          ${c.pode_eliminar
            ?`<button class="btn-eliminar-comentario" onclick="eliminarComentario(${c.comentario_id}, this)" title="Eliminar comentário">🗑️</button>`
            :''}
        </div>
        <div class="comentario-texto">${htmlEncode(c.texto)}</div>
        <div class="comentario-data">${c.data_formatada}</div>
      </div>
    </div>`).join('');
}

function enviarComentario(){
  if(!eventoAtual)return;
  const ta=document.getElementById('novoComentario');
  const texto=ta.value.trim();
  if(!texto){ta.focus();return;}
  const btn=document.querySelector('.btn-comentar');
  btn.disabled=true; btn.textContent='⏳';
  const fd=new FormData();
  fd.append('acao','guardar');
  fd.append('evento_id',eventoAtual.evento_id);
  fd.append('texto',texto);
  fetch('comentarios.php',{method:'POST',body:fd})
    .then(r=>r.json())
    .then(data=>{
      if(data.erro){alert(data.erro);btn.disabled=false;btn.textContent='Enviar';return;}
      const lista=document.getElementById('comentariosLista');
      const semMsg=lista.querySelector('.sem-comentarios');
      if(semMsg)semMsg.remove();
      const novoHtml=`
        <div class="comentario-item" data-comentario-id="${data.comentario_id}">
          ${data.foto_url
            ?`<img src="${data.foto_url}" class="comentario-foto" onerror="this.outerHTML='<div class=comentario-placeholder>${data.inicial}</div>'">`
            :`<div class="comentario-placeholder">${data.inicial}</div>`}
          <div class="comentario-balao">
            <div class="comentario-header">
              <a href="ver_perfil.php?id=${data.utilizador_id}" class="comentario-autor" onclick="event.stopPropagation()">${htmlEncode(data.nome)}</a>
              <button class="btn-eliminar-comentario" onclick="eliminarComentario(${data.comentario_id}, this)" title="Eliminar comentário">🗑️</button>
            </div>
            <div class="comentario-texto">${htmlEncode(data.texto)}</div>
            <div class="comentario-data">${data.data_formatada}</div>
          </div>
        </div>`;
      lista.insertAdjacentHTML('beforeend',novoHtml);
      lista.lastElementChild.scrollIntoView({behavior:'smooth',block:'nearest'});
      ta.value=''; btn.disabled=false; btn.textContent='Enviar';
    })
    .catch(()=>{alert('Erro.');btn.disabled=false;btn.textContent='Enviar';});
}

// Enter para enviar
document.addEventListener('DOMContentLoaded',()=>{
  const ta=document.getElementById('novoComentario');
  if(ta)ta.addEventListener('keydown',e=>{if(e.key==='Enter'&&!e.shiftKey){e.preventDefault();enviarComentario();}});
});

// ===== ELIMINAR COMENTÁRIO =====
function eliminarComentario(comentarioId, btn) {
  if (!confirm('Eliminar este comentário?')) return;
  btn.disabled = true;
  btn.textContent = '⏳';
  const fd = new FormData();
  fd.append('acao', 'eliminar');
  fd.append('comentario_id', comentarioId);
  fetch('comentarios.php', { method: 'POST', body: fd })
    .then(r => r.json())
    .then(data => {
      if (data.erro) { alert(data.erro); btn.disabled = false; btn.textContent = '🗑️'; return; }
      const item = document.querySelector(`[data-comentario-id="${comentarioId}"]`);
      if (item) {
        item.style.transition = 'opacity .3s, transform .3s';
        item.style.opacity = '0';
        item.style.transform = 'scale(0.95)';
        setTimeout(() => {
          item.remove();
          const lista = document.getElementById('comentariosLista');
          if (lista && !lista.querySelector('.comentario-item')) {
            lista.innerHTML = '<p class="sem-comentarios">Ainda não há comentários. Seja o primeiro! 💬</p>';
          }
        }, 300);
      }
    })
    .catch(() => { alert('Erro de conexão.'); btn.disabled = false; btn.textContent = '🗑️'; });
}

function htmlEncode(s){
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function redirecionarLogin(){
  if(confirm('Precisa de login para participar. Ir para login?'))window.location.href='login.php';
}

// Validação form
<?php if($utilizador_logado): ?>
const formEvento=document.getElementById('formEvento');
const btnSubmit=document.getElementById('btnSubmit');
if(formEvento){
  formEvento.addEventListener('submit',function(e){
    if(!document.getElementById('nome').value.trim()||!document.getElementById('descricao').value.trim()||
       !document.getElementById('data').value||!document.getElementById('local').value.trim()){
      e.preventDefault();alert('Preencha todos os campos obrigatórios.');return;
    }
    const imgs=document.getElementById('imagens');
    if(imgs.files.length>5){e.preventDefault();alert('Máximo 5 imagens.');return;}
    for(let f of imgs.files){if(f.size>5*1024*1024){e.preventDefault();alert('Cada imagem tem máx 5MB.');return;}}
    btnSubmit.disabled=true;btnSubmit.textContent='A criar...';
  });
}
<?php endif; ?>

function previewImagens(input){
  const preview=document.getElementById('preview-imagens');
  preview.innerHTML='';
  if(!input.files||!input.files.length)return;
  const max=Math.min(input.files.length,5);
  for(let i=0;i<max;i++){
    const reader=new FileReader();
    const idx=i;
    reader.onload=e=>{
      const wrap=document.createElement('div');
      wrap.style.cssText='position:relative;display:inline-block;';
      const label=idx===0?'<span style="position:absolute;top:4px;left:4px;background:#58b79d;color:white;font-size:11px;padding:2px 6px;border-radius:4px;font-weight:bold;">CAPA</span>':'';
      wrap.innerHTML=label+`<img src="${e.target.result}" style="width:100px;height:80px;object-fit:cover;border-radius:6px;border:2px solid ${idx===0?'#58b79d':'#c8c0ae'};" alt="Preview ${idx+1}">`;
      preview.appendChild(wrap);
    };
    reader.readAsDataURL(input.files[i]);
  }
}

// Auto-hide mensagens
setTimeout(()=>{
  document.querySelectorAll('.mensagem').forEach(m=>{
    m.style.transition='opacity .5s';m.style.opacity='0';
    setTimeout(()=>m.remove(),500);
  });
},5000);

// Scroll suave
document.querySelectorAll('a[href^="#"]').forEach(a=>{
  a.addEventListener('click',function(e){
    const href=this.getAttribute('href');
    if(href!=='#'){e.preventDefault();const t=document.querySelector(href);if(t)t.scrollIntoView({behavior:'smooth',block:'start'});}
  });
});
</script>
</body>
</html>