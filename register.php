<?php
session_start();

// Domínios de email aceites
$dominios_permitidos = ['gmail.com', 'yahoo.com'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome            = trim($_POST['nome']);
    $email           = trim($_POST['email']);
    $password        = $_POST['password'];
    $telefone        = trim($_POST['telefone'] ?? '');
    $metodo_contacto = $_POST['metodo_contacto'] ?? 'email';

    // Se não preencheu telefone, só pode ficar com email como forma de contacto
    if ($telefone === '') {
        $metodo_contacto = 'email';
    }
    if (!in_array($metodo_contacto, ['email', 'telefone'])) {
        $metodo_contacto = 'email';
    }

    // Validar domínio do email (@gmail.com ou @yahoo.com)
    $email_valido = false;
    if (strpos($email, '@') !== false) {
        $dominio = strtolower(trim(substr($email, strpos($email, '@') + 1)));
        if (in_array($dominio, $dominios_permitidos)) {
            $email_valido = true;
        }
    }

    if (!$email_valido) {
        $erro = "Email inválido. O email tem de ser @gmail.com ou @yahoo.com.";
    }

    $dsn     = "mysql:host=localhost;dbname=humanicare;charset=utf8mb4";
    $db_user = "root";
    $db_pass = "";

    if (!isset($erro)) {
    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verificar se email já existe
        $stmt = $pdo->prepare("SELECT * FROM utilizador WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $erro = "Já existe um utilizador com este email.";
        } else {
            $foto_perfil = null;

            // Processar foto de perfil
            if (!empty($_FILES['foto_perfil']['name'])) {
                $foto = $_FILES['foto_perfil'];

                if ($foto['error'] === UPLOAD_ERR_OK) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime  = finfo_file($finfo, $foto['tmp_name']);
                    finfo_close($finfo);

                    $tipos_permitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

                    if (!in_array($mime, $tipos_permitidos)) {
                        $erro = "Tipo de imagem inválido. Use JPG, PNG ou GIF.";
                    } elseif ($foto['size'] > 5 * 1024 * 1024) {
                        $erro = "Imagem muito grande. Máximo 5MB.";
                    } else {
                        if (!is_dir("uploads/perfil")) {
                            mkdir("uploads/perfil", 0755, true);
                        }
                        $extensao    = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
                        $foto_perfil = 'perfil_' . uniqid() . '.' . $extensao;

                        if (!move_uploaded_file($foto['tmp_name'], "uploads/perfil/" . $foto_perfil)) {
                            $foto_perfil = null;
                        }
                    }
                }
            }

            if (!isset($erro)) {
                $stmt = $pdo->prepare("INSERT INTO utilizador (nome, email, senha, foto_perfil, telefone, metodo_contacto) VALUES (:nome, :email, :senha, :foto_perfil, :telefone, :metodo_contacto)");
                $stmt->execute([
                    'nome'            => $nome,
                    'email'           => $email,
                    'senha'           => $password,
                    'foto_perfil'     => $foto_perfil,
                    'telefone'        => $telefone !== '' ? $telefone : null,
                    'metodo_contacto' => $metodo_contacto
                ]);

                $utilizador_id = $pdo->lastInsertId();

                $_SESSION['user'] = [
                    'utilizador_id'   => $utilizador_id,
                    'nome'            => $nome,
                    'email'           => $email,
                    'foto_perfil'     => $foto_perfil,
                    'telefone'        => $telefone !== '' ? $telefone : null,
                    'metodo_contacto' => $metodo_contacto
                ];

                header("Location: index.php");
                exit;
            }
        }
    } catch (PDOException $e) {
        $erro = "Erro de conexão: " . $e->getMessage();
    }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-PT">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registar - HumaniCare</title>
<link rel="stylesheet" href="style.css">
<style>
body { display: flex; flex-direction: column; min-height: 100vh; }

.container {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
}

.login-box {
  background-color: white;
  width: 100%;
  max-width: 650px;
  padding: 50px;
  border: 2px solid #c8c0ae;
  border-radius: 12px;
  box-shadow: 0 4px 20px rgba(0,0,0,0.15);
  margin-top: 20px;
}

.login-box h2 {
  text-align: center;
  color: #7a8c3c;
  margin-bottom: 30px;
  font-size: 28px;
  border-bottom: 2px solid #c8c0ae;
  padding-bottom: 15px;
}

.form-group { margin-bottom: 20px; }

.form-group label {
  display: block;
  font-weight: bold;
  color: #4a4a4a;
  margin-bottom: 8px;
  font-size: 15px;
}

.login-box input[type="text"],
.login-box input[type="password"],
.login-box input[type="tel"],
.login-box select {
  width: 100%;
  padding: 12px 16px;
  border-radius: 6px;
  border: 2px solid #c8c0ae;
  font-size: 16px;
  font-family: inherit;
  transition: all 0.3s ease;
  background: #fafafa;
  box-sizing: border-box;
}

.login-box input:focus,
.login-box select:focus {
  outline: none;
  border-color: #58b79d;
  background: white;
  box-shadow: 0 0 0 3px rgba(88, 183, 157, 0.1);
}

.campo-hint {
  display: block;
  font-size: 12px;
  color: #999;
  margin-top: 6px;
}

/* --- Área de upload de foto --- */
.foto-upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
  padding: 24px;
  border: 2px dashed #c8c0ae;
  border-radius: 10px;
  background: #fafafa;
  transition: border-color 0.3s, background 0.3s;
  cursor: pointer;
}

.foto-upload-area:hover {
  border-color: #58b79d;
  background: #f0faf7;
}

#preview-img {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #58b79d;
  display: none;
}

.foto-placeholder {
  width: 110px;
  height: 110px;
  border-radius: 50%;
  background: linear-gradient(135deg, #58b79d, #7a8c3c);
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-size: 40px;
}

.foto-upload-area input[type="file"] { display: none; }

.btn-escolher-foto {
  background: #58b79d;
  color: white;
  padding: 9px 20px;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: bold;
  transition: all 0.3s;
}

.btn-escolher-foto:hover { background: #4a9c82; }

.foto-hint { font-size: 13px; color: #999; text-align: center; }

/* --- Botão submit --- */
.btn-submit {
  width: 100%;
  background: linear-gradient(135deg, #58b79d 0%, #4a9c82 100%);
  color: white;
  border: none;
  padding: 14px;
  border-radius: 6px;
  font-size: 17px;
  font-weight: bold;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(88, 183, 157, 0.3);
  margin-top: 10px;
}

.btn-submit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 16px rgba(88, 183, 157, 0.4);
}

.extra-links {
  text-align: center;
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid #e0e0e0;
}

.extra-links a { text-decoration: none; color: #58b79d; font-weight: bold; }
.extra-links a:hover { color: #4a9c82; text-decoration: underline; }

.erro {
  color: #c0392b;
  background: #ffe5e5;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  text-align: center;
  font-weight: bold;
  border: 1px solid #c0392b;
}

.page-title {
  text-align: center;
  color: #7a8c3c;
  font-size: 42px;
  margin: 20px 0;
  letter-spacing: 2px;
}

.page-title span { color: #9dbb52; }
</style>
</head>
<body>

<header>
  <div class="header-container">
    <nav class="nav-links">
      <a href="indexdeslogado.php">Página principal</a>
      <a href="indexdeslogado.php#sobre">Sobre</a>
      <a href="indexdeslogado.php#projeto">Projetos</a>
      <a href="indexdeslogado.php#doacoes">Doações</a>
      <a href="indexdeslogado.php#envolva">Envolva-se</a>
      <a href="indexdeslogado.php#eventosProjetos">Eventos</a>
      <a href="login.php">Login</a>
    </nav>
  </div>
</header>

<main class="container">
  <h1 class="page-title">HUMANI <span>CARE</span></h1>

  <div class="login-box">
    <h2>Criar Conta</h2>

    <?php if (!empty($erro)): ?>
      <p class="erro"><?php echo htmlspecialchars($erro); ?></p>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">

      <!-- Foto de Perfil -->
      <div class="form-group">
        <label>Foto de Perfil <span style="color:#999; font-weight:normal;">(opcional)</span></label>
        <div class="foto-upload-area" onclick="document.getElementById('foto_perfil').click()">
          <div class="foto-placeholder" id="foto-placeholder">👤</div>
          <img id="preview-img" src="" alt="Preview da foto">
          <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*" onchange="previewFoto(this)">
          <button type="button" class="btn-escolher-foto">📷 Escolher Foto</button>
          <span class="foto-hint">JPG, PNG ou GIF · Máx. 5MB</span>
        </div>
      </div>

      <!-- Nome -->
      <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" placeholder="O seu nome completo" required
               value="<?php echo htmlspecialchars($_POST['nome'] ?? ''); ?>">
      </div>

      <!-- Email -->
      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" name="email" placeholder="exemplo@gmail.com" required
               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        <span class="campo-hint">Apenas emails @gmail.com ou @yahoo.com</span>
      </div>

      <!-- Telefone -->
      <div class="form-group">
        <label for="telefone">Número de Telemóvel <span style="color:#999; font-weight:normal;">(opcional)</span></label>
        <input type="tel" id="telefone" name="telefone" placeholder="912345678"
               value="<?php echo htmlspecialchars($_POST['telefone'] ?? ''); ?>"
               oninput="atualizarMetodoContacto()">
      </div>

      <!-- Forma de contacto preferida -->
      <div class="form-group">
        <label for="metodo_contacto">Forma de contacto preferida</label>
        <select id="metodo_contacto" name="metodo_contacto">
          <option value="email" selected>Email</option>
          <option value="telefone" id="opcao_telefone" disabled>Telemóvel</option>
        </select>
        <span class="campo-hint" id="hint_contacto">Sem telemóvel indicado, só pode escolher o email.</span>
      </div>

      <!-- Palavra-passe -->
      <div class="form-group">
        <label for="password">Palavra-passe</label>
        <input type="password" id="password" name="password" placeholder="Crie uma palavra-passe" required>
      </div>

      <button type="submit" class="btn-submit">Registar</button>
    </form>

    <div class="extra-links">
      <p>Já tem conta? <a href="login.php">Fazer login</a></p>
    </div>
  </div>
</main>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

<script>
function previewFoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      const img         = document.getElementById('preview-img');
      const placeholder = document.getElementById('foto-placeholder');
      img.src           = e.target.result;
      img.style.display = 'block';
      placeholder.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}

function atualizarMetodoContacto() {
  const telefone     = document.getElementById('telefone').value.trim();
  const select       = document.getElementById('metodo_contacto');
  const opcaoTel      = document.getElementById('opcao_telefone');
  const hint          = document.getElementById('hint_contacto');

  if (telefone === '') {
    opcaoTel.disabled = true;
    select.value = 'email';
    hint.textContent = 'Sem telemóvel indicado, só pode escolher o email.';
  } else {
    opcaoTel.disabled = false;
    hint.textContent = 'Escolha como prefere ser contactado.';
  }
}

// Garantir o estado correto ao carregar (ex: quando há erro de validação e o form é reapresentado)
document.addEventListener('DOMContentLoaded', atualizarMetodoContacto);
</script>

</body>
</html>