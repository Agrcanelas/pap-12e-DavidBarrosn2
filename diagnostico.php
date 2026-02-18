<?php
echo "<h1>🔍 DIAGNÓSTICO - HumaniCare</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .ok { color: green; background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid green; }
    .erro { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
    .info { color: blue; background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid blue; }
    .aviso { color: orange; background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid orange; }
    pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";

require_once 'db.php';

echo "<h2>✅ CHECKLIST - O QUE FOI FEITO?</h2>";

// 1. Verificar se a coluna foto_perfil existe
echo "<h3>1️⃣ Coluna foto_perfil na tabela utilizador</h3>";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM utilizador LIKE 'foto_perfil'");
    $col = $stmt->fetch();
    
    if ($col) {
        echo "<div class='ok'>✅ Coluna 'foto_perfil' existe!</div>";
    } else {
        echo "<div class='erro'>❌ Coluna 'foto_perfil' NÃO existe! Você executou o SQL?</div>";
        echo "<div class='info'>Execute este comando no phpMyAdmin:<pre>ALTER TABLE utilizador ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER email;</pre></div>";
    }
} catch (Exception $e) {
    echo "<div class='erro'>❌ Erro: " . $e->getMessage() . "</div>";
}

// 2. Verificar coluna imagem na tabela evento
echo "<h3>2️⃣ Coluna imagem na tabela evento</h3>";
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM evento LIKE 'imagem'");
    $col = $stmt->fetch();
    if ($col) {
        echo "<div class='ok'>✅ Coluna 'imagem' existe na tabela evento!</div>";
    } else {
        echo "<div class='erro'>❌ Coluna 'imagem' NÃO existe na tabela evento!</div>";
    }
} catch (Exception $e) {
    echo "<div class='erro'>❌ Erro: " . $e->getMessage() . "</div>";
}

// 3. Verificar se os arquivos existem
echo "<h3>3️⃣ Arquivos PHP</h3>";

$arquivos = [
    'perfil.php' => 'Página de perfil',
    'evento_detalhes.php' => 'Página de detalhes do evento',
    'menu.php' => 'Menu (deve ter link para perfil)',
    'guardar_evento.php' => 'Script de guardar eventos'
];

foreach ($arquivos as $arquivo => $desc) {
    if (file_exists($arquivo)) {
        echo "<div class='ok'>✅ $arquivo existe - $desc</div>";
        
        // Verificar conteúdo específico
        $conteudo = file_get_contents($arquivo);
        
        if ($arquivo === 'menu.php') {
            if (strpos($conteudo, 'perfil.php') !== false) {
                echo "<div class='ok'>   ✓ Menu tem link para perfil.php</div>";
            } else {
                echo "<div class='erro'>   ✗ Menu NÃO tem link para perfil.php!</div>";
            }
            
            if (strpos($conteudo, 'foto_perfil') !== false) {
                echo "<div class='ok'>   ✓ Menu mostra foto de perfil</div>";
            } else {
                echo "<div class='aviso'>   ⚠ Menu não mostra foto de perfil</div>";
            }
        }
        
        if ($arquivo === 'guardar_evento.php') {
            if (strpos($conteudo, 'imagem' !== false) {
                echo "<div class='ok'>   ✓ Suporta upload de imagem</div>";
            } else {
                echo "<div class='aviso'>   ⚠ Pode não suportar upload de imagem</div>";
            }
        }
    } else {
        echo "<div class='erro'>❌ $arquivo NÃO existe!</div>";
    }
}

// Criar pastas automaticamente se não existirem
if (!is_dir('uploads/perfil')) { mkdir('uploads/perfil', 0755, true); }
if (!is_dir('uploads/eventos')) { mkdir('uploads/eventos', 0755, true); }

// 4. Verificar pastas
echo "<h3>4️⃣ Pastas de Upload</h3>";

$pastas = [
    'uploads' => 'Pasta principal',
    'uploads/perfil' => 'Fotos de perfil',
    'uploads/eventos' => 'Imagens de eventos'
];

foreach ($pastas as $pasta => $desc) {
    if (is_dir($pasta)) {
        $perm = substr(sprintf('%o', fileperms($pasta)), -4);
        echo "<div class='ok'>✅ $pasta existe - $desc (Permissões: $perm)</div>";
        
        if (is_writable($pasta)) {
            echo "<div class='ok'>   ✓ Pasta tem permissão de escrita</div>";
        } else {
            echo "<div class='erro'>   ✗ Pasta NÃO tem permissão de escrita!</div>";
        }
    } else {
        echo "<div class='erro'>❌ $pasta NÃO existe!</div>";
        echo "<div class='info'>   Crie a pasta: mkdir $pasta</div>";
    }
}

// 5. Verificar index.php
echo "<h3>5️⃣ Arquivo index.php</h3>";

if (file_exists('index.php')) {
    $conteudo = file_get_contents('index.php');
    
    echo "<div class='ok'>✅ index.php existe</div>";
    
    // Verificar input múltiplo
    if (strpos($conteudo, 'name="imagens[]"') !== false) {
        echo "<div class='ok'>   ✓ Formulário suporta upload de imagem (name=\"imagem\")</div>";
    } elseif (strpos($conteudo, 'name="imagem"') !== false) {
        echo "<div class='aviso'>   ⚠ Formulário ainda usa input único (name=\"imagem\")</div>";
        echo "<div class='info'>   Precisa alterar para name=\"imagem\" e adicionar 'multiple'</div>";
    }
    
    // Verificar função de preview
    if (strpos($conteudo, 'previewImagens') !== false) {
        echo "<div class='ok'>   ✓ Tem função JavaScript previewImagens()</div>";
    } else {
        echo "<div class='aviso'>   ⚠ Não tem função de preview das imagens</div>";
    }
    
    // Verificar link para evento_detalhes
    if (strpos($conteudo, 'evento_detalhes.php') !== false) {
        echo "<div class='ok'>   ✓ Tem link para evento_detalhes.php</div>";
    } else {
        echo "<div class='aviso'>   ⚠ Não tem link para evento_detalhes.php</div>";
        echo "<div class='info'>   Os títulos dos eventos devem ser clicáveis</div>";
    }
    
} else {
    echo "<div class='erro'>❌ index.php NÃO existe!</div>";
}

// 6. Testar sessão
echo "<h3>6️⃣ Sessão do Utilizador</h3>";

session_start();

if (isset($_SESSION['user'])) {
    echo "<div class='ok'>✅ Utilizador está logado</div>";
    echo "<div class='info'>👤 Nome: " . htmlspecialchars($_SESSION['user']['nome']) . "</div>";
    
    if (isset($_SESSION['user']['foto_perfil'])) {
        echo "<div class='ok'>   ✓ Sessão tem campo foto_perfil</div>";
    } else {
        echo "<div class='aviso'>   ⚠ Sessão não tem campo foto_perfil (faça logout e login novamente)</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Nenhum utilizador logado</div>";
}

// 7. Resumo
echo "<h2>📋 RESUMO</h2>";

$erros = substr_count(ob_get_contents(), "class='erro'");
$avisos = substr_count(ob_get_contents(), "class='aviso'");

if ($erros == 0 && $avisos == 0) {
    echo "<div class='ok'><h3>🎉 TUDO CERTO!</h3><p>Todas as alterações foram feitas corretamente!</p></div>";
} else {
    echo "<div class='aviso'><h3>⚠️ ATENÇÃO</h3>";
    echo "<p><strong>$erros erro(s)</strong> encontrado(s)</p>";
    echo "<p><strong>$avisos aviso(s)</strong> encontrado(s)</p>";
    echo "<p>Corrija os problemas acima para que tudo funcione!</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #58b79d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voltar ao site</a></p>";
?>