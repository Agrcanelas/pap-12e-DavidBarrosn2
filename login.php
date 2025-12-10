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
            // Verifica password simples (BD não usa hash)
            if ($password === $user['senha']) {
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
