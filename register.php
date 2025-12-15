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
<title>Register - Humani Care</title>
<link rel="stylesheet" href="stylelogin.css?v=4">
</head>
<body>
<header>
<nav>
    <a href="indexdeslogado.php">Pagina principal</a>
    <a href="#sobre" class="login-required">Sobre</a>
    <a href="#projeto" class="login-required">Projetos</a>
    <a href="#doacoes" class="login-required">Doações</a>
    <a href="#envolva" class="login-required">Envolva-se</a>
    <a href="#criar-evento" class="login-required">Criar Evento</a>
    <a href="#eventosProjetos" class="login-required">Eventos</a>
    <a href="login.php">Login</a>
</nav>
</header>

<main class="container">
<h1 class="logo">HUMANI <span>CARE</span></h1>

<div class="login-box">
<h2>Criar Conta</h2>

<?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>

<form method="POST" action="">
    <label for="nome">Nome</label>
    <input type="text" id="nome" name="nome" required>

    <label for="email">Email</label>
    <input type="text" id="email" name="email" required>

    <label for="password">Palavra-passe</label>
    <input type="password" id="password" name="password" required>

    <button type="submit">Registar</button>
</form>

<div class="extra-links">
    <p><a href="login.php">Já tem conta? Faça login</a></p>
</div>
</div>
</main>

<script>
const isLoggedIn = <?php echo isset($_SESSION['user']) ? 'true' : 'false'; ?>;

document.addEventListener("DOMContentLoaded", function() {
    const loginRequiredLinks = document.querySelectorAll(".login-required");

    loginRequiredLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            if (!isLoggedIn) {
                e.preventDefault();
                alert("Primeiro tem que fazer login!");
            }
        });
    });
});
</script>

</body>
</html>
