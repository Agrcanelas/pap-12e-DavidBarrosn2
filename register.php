<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $dsn = "mysql:host=localhost;dbname=humanicare;charset=utf8mb4";
    $db_user = "root";
    $db_pass = "";

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $pdo->prepare("SELECT * FROM utilizador WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $erro = "Já existe um utilizador com este email.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO utilizador (nome, email, senha) VALUES (:nome, :email, :senha)");
            $stmt->execute([
                'nome' => $nome,
                'email' => $email,
                'senha' => $password
            ]);
            $_SESSION['user'] = [
                'nome' => $nome,
                'email' => $email
            ];
            header("Location: index.php");
            exit;
        }
    } catch (PDOException $e) {
        $erro = "Erro de conexão: " . $e->getMessage();
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
/* Estilos específicos para a página de registo */
body {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

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
  max-width: 450px;
  padding: 40px;
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

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  font-weight: bold;
  color: #4a4a4a;
  margin-bottom: 8px;
  font-size: 15px;
}

.login-box input[type="text"],
.login-box input[type="password"] {
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

.login-box input:focus {
  outline: none;
  border-color: #58b79d;
  background: white;
  box-shadow: 0 0 0 3px rgba(88, 183, 157, 0.1);
}

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

.extra-links a {
  text-decoration: none;
  color: #58b79d;
  font-weight: bold;
  transition: color 0.3s;
}

.extra-links a:hover {
  color: #4a9c82;
  text-decoration: underline;
}

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

.page-title span {
  color: #9dbb52;
}
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

    <form method="POST" action="">
      <div class="form-group">
        <label for="nome">Nome</label>
        <input type="text" id="nome" name="nome" required>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="text" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="password">Palavra-passe</label>
        <input type="password" id="password" name="password" required>
      </div>

      <button type="submit" class="btn-submit">Registar</button>
    </form>

    <div class="extra-links">
      <p>Já tem conta? <a href="login.php">Faça login</a></p>
    </div>
  </div>
</main>

<footer>
  <p>© 2025 HumaniCare - Juntos por um futuro melhor 🌿</p>
</footer>

</body>
</html>