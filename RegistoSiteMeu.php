<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>registo Restaurante</title>
</head>
<body>


<div class="container" id="conteudo">
<?php
require 'ligaBD.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo = $_POST["codigo"];
    $nome = $_POST["nome"];
    $password = $_POST["password"];


    $stmt0 = $conn->prepare("SELECT nomeutilizador FROM utilizadores WHERE codutilizador = ?");
    $stmt0->bind_param("s", $codigo);
    $stmt0->execute();
    $stmt0->store_result();
    
    if ($stmt0->num_rows > 0) {
        echo "<h3>Utilizador já existente na Base de dados. <a href=LoginSiteMeu.php?pagina=index>Prima para continuar</a></h3>";
        exit();
    } else {
        $stmt = $conn->prepare("INSERT INTO utilizadores (codutilizador, nomeutilizador, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $codigo, $nome, $password);
        
        if ($stmt->execute()) {
            header("Location: sitepap.php?pagina=index");
            exit();
        } else {
            echo "<h3>Erro ao registar utilizador.</h3>";
        }
        $stmt->close();
        $stmt0->close();
    }
  

    
    
}
$conn->close();
?>

<form method="post" style="max-width: 300px; margin: auto; text-align: center; padding: 20px; border: 1px solid #ccc; border-radius: 10px; background-color: #f9f9f9;">
    <h2>Registo</h2>
    <label>Código de Utilizador:</label>
    <input type="text" name="codigo" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <br>
    <label>Nome:</label>
    <input type="text" name="nome" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <br>
    <label>Password:</label>
    <input type="password" name="password" required style="width: 100%; padding: 8px; margin: 5px 0;">
    <br>
    <button type="submit" style="background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer; border-radius: 5px;">Registar</button>
</form>
</body>
</html>