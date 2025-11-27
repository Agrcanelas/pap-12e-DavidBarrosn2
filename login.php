<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Conexão BD
    $dsn = "mysql:host=localhost;dbname=humani_care;charset=utf8mb4";
    $db_user = "root";
    $db_pass = ""; // altera se necessário

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Verifica utilizador pelo nome + email
        $stmt = $pdo->prepare("SELECT * FROM users WHERE nome = :nome AND email = :email");
        $stmt->execute(['nome' => $nome, 'email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Verifica password (hash)
            if (password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                header("Location: index.php");
                exit;
            } else {
                $erro = "Palavra-passe incorreta.";
            }
        } else {
            // Não existe → Criar conta
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
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>
<header>
    <h1>HUMANI CARE</h1>
</header>

<div class="container">
    <div class="login-box">
        <h2>Login</h2>

        <!-- Exibe erro se existir -->
        <?php if (!empty($erro)) echo "<p class='erro'>$erro</p>"; ?>

        <form method="POST" action="">
            <label for="nome">Nome</label>
            <input type="text" id="nome" name="nome" required>

            <label for="email">Email:</label>
            <input type="text" id="email" name="email" required>

            <label for="password">Palavra-passe:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Entrar</button>
        </form>

        <div class="extra-links">
            <p><a href="#">Esqueceu a palavra-passe?</a></p>
            <p><a href="register.php">Criar nova conta</a></p>
        </div>
    </div>
</div>
</body>
</html>
