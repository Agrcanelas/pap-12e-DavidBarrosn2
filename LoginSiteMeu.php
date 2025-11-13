<?php
session_start();
require 'ligaBD.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST["codigo"];
    $password = $_POST["password"];
    
    $stmt = $conn->prepare("SELECT nomeutilizador FROM utilizadores WHERE codutilizador = ? AND password = ?");
    $stmt->bind_param("ss", $codigo, $password);
    $stmt->execute();
    $stmt->store_result();
    
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($nome);
        $stmt->fetch();
        $_SESSION["user"] = ["codutilizador" => $codigo, "nomeutilizador" => $nome];
        header("Location: sitepap.php");
        exit();
    } else {
        echo "<h3>Utilizador não encontrado. <a href='RegistoSiteMEu.php'>Registe-se aqui</a></h3>";
    }
    $stmt->close();
}
$conn->close();
?>

<form method="post" style="max-width: 300px; margin: auto; text-align: center; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9;">
    <h2>Login</h2>
    <label>Código de Utilizador:</label>
    <input type="text" name="codigo" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <br>
    <label>Password:</label>
    <input type="password" name="password" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <br>
    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 5px;">Entrar</button>
</form>
