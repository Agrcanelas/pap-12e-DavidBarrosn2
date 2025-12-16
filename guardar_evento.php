<?php
session_start();
require "db.php";

if (!isset($_SESSION['user'])) {
    die("Acesso negado");
}

$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$data = $_POST['data'];
$local = $_POST['local'];
$utilizador_id = $_SESSION['user']['utilizador_id'];

$imagem_nome = null;

if (!empty($_FILES['imagem']['name'])) {
    if (!is_dir("uploads")) {
        mkdir("uploads");
    }

    $imagem_nome = uniqid() . "_" . $_FILES['imagem']['name'];
    move_uploaded_file($_FILES['imagem']['tmp_name'], "uploads/" . $imagem_nome);
}

$sql = "INSERT INTO evento (nome, descricao, data_evento, local_evento, imagem, utilizador_id)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$nome, $descricao, $data, $local, $imagem_nome, $utilizador_id]);

header("Location: index.php#eventosProjetos");
