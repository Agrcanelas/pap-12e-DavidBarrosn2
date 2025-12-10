<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Conexão BD
    $dsn = "mysql:host=localhost;dbname=humanicare;charset=utf8mb4";
    $db_user = "root";
    $db_pass = ""; // altera se necessário

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica utilizador pelo nome + email
        $stmt = $pdo->prepare("SELECT * FROM utilizador WHERE nome = :nome AND email = :email");
        $stmt->execute(['nome' => $nome, 'email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($password === $user['senha']) {
                $_SESSION['user'] = $user;
                header("Location: index.php");
                exit;
            } else {
                $erro = "Palavra-passe incorreta.";
            }
        } else {
            header("Location: register.php");
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
    <title>Login - Humani Care</title>
    <link rel="stylesheet" href="stylelogin.css?v=2"> <!-- Evita cache -->
</head>
<body>
<header>
    <nav>
        <a href="#sobre">Sobre</a>
        <a href="#projeto">Projetos</a>
        <a href="#doacoes">Doações</a>
        <a href="#envolva">Envolva-se</a>
        <a href="#criar-evento">Criar Evento</a>
        <a href="#eventosProjetos">Eventos</a>
        <a href="login.php">Login</a>
    </nav>
</header>

<main class="container">
    <!-- Título principal (logo do site) -->
    <h1 class="logo">HUMANI <span>CARE</span></h1>

    <div class="login-box">
        <h2>Login</h2>

        <?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>

        <form method="POST" action="">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">Email</label>
            <input type="text" id="email" name="email" required>

            <label for="password">Palavra-passe</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>

        <div class="extra-links">
            <p><a href="#">Esqueceu a palavra-passe?</a></p>
            <p><a href="register.php">Criar nova conta</a></p>
        </div>
    </div>
</main>
</body>
</html>
