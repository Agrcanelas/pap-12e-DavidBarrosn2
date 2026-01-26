<?php
/**
 * Script de teste de conexão à base de dados
 * Use este ficheiro para verificar se tudo está configurado corretamente
 */

echo "<h1>🔍 Teste de Conexão - HumaniCare</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .ok { color: green; background: #d4edda; padding: 10px; margin: 10px 0; border-left: 4px solid green; }
    .erro { color: red; background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid red; }
    .info { color: blue; background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid blue; }
    table { border-collapse: collapse; width: 100%; margin: 20px 0; background: white; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #58b79d; color: white; }
</style>";

// 1. Verificar se o ficheiro db.php existe
echo "<h2>1️⃣ Verificar ficheiro db.php</h2>";
if (file_exists('db.php')) {
    echo "<div class='ok'>✅ Ficheiro db.php encontrado!</div>";
    require_once 'db.php';
} else {
    echo "<div class='erro'>❌ Ficheiro db.php NÃO encontrado! Crie o ficheiro db.php na mesma pasta.</div>";
    exit;
}

// 2. Testar conexão
echo "<h2>2️⃣ Testar conexão à base de dados</h2>";
try {
    if (isset($pdo)) {
        echo "<div class='ok'>✅ Conexão estabelecida com sucesso!</div>";
        echo "<div class='info'>📊 Base de dados: <strong>$db</strong></div>";
        echo "<div class='info'>🖥️ Servidor: <strong>$host</strong></div>";
        echo "<div class='info'>👤 Utilizador: <strong>$user</strong></div>";
    } else {
        echo "<div class='erro'>❌ Variável \$pdo não está definida em db.php</div>";
        exit;
    }
} catch (Exception $e) {
    echo "<div class='erro'>❌ Erro na conexão: " . $e->getMessage() . "</div>";
    exit;
}

// 3. Verificar tabelas
echo "<h2>3️⃣ Verificar tabelas</h2>";
$tabelasNecessarias = ['utilizador', 'evento', 'participa'];
$tabelasEncontradas = [];

try {
    $stmt = $pdo->query("SHOW TABLES");
    $tabelas = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tabelasNecessarias as $tabela) {
        if (in_array($tabela, $tabelas)) {
            echo "<div class='ok'>✅ Tabela <strong>$tabela</strong> existe</div>";
            $tabelasEncontradas[] = $tabela;
        } else {
            echo "<div class='erro'>❌ Tabela <strong>$tabela</strong> NÃO existe! Execute o ficheiro humanicare.sql</div>";
        }
    }
} catch (PDOException $e) {
    echo "<div class='erro'>❌ Erro ao verificar tabelas: " . $e->getMessage() . "</div>";
    exit;
}

// 4. Verificar estrutura das tabelas
if (in_array('evento', $tabelasEncontradas)) {
    echo "<h2>4️⃣ Verificar estrutura da tabela EVENTO</h2>";
    try {
        $stmt = $pdo->query("DESCRIBE evento");
        $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $colunasNecessarias = ['evento_id', 'nome', 'descricao', 'data_evento', 'local_evento', 'imagem', 'utilizador_id'];
        $colunasEncontradas = array_column($colunas, 'Field');
        
        echo "<table>";
        echo "<tr><th>Coluna</th><th>Tipo</th><th>Null</th><th>Chave</th><th>Default</th></tr>";
        foreach ($colunas as $col) {
            echo "<tr>";
            echo "<td>{$col['Field']}</td>";
            echo "<td>{$col['Type']}</td>";
            echo "<td>{$col['Null']}</td>";
            echo "<td>{$col['Key']}</td>";
            echo "<td>{$col['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        foreach ($colunasNecessarias as $coluna) {
            if (in_array($coluna, $colunasEncontradas)) {
                echo "<div class='ok'>✅ Coluna <strong>$coluna</strong> existe</div>";
            } else {
                echo "<div class='erro'>❌ Coluna <strong>$coluna</strong> NÃO existe! A estrutura está incorreta.</div>";
            }
        }
        
    } catch (PDOException $e) {
        echo "<div class='erro'>❌ Erro ao verificar estrutura: " . $e->getMessage() . "</div>";
    }
}

// 5. Contar registos
echo "<h2>5️⃣ Contar registos nas tabelas</h2>";
foreach ($tabelasEncontradas as $tabela) {
    try {
        $stmt = $pdo->query("SELECT COUNT(*) as total FROM $tabela");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $total = $result['total'];
        
        if ($total > 0) {
            echo "<div class='ok'>✅ Tabela <strong>$tabela</strong>: $total registo(s)</div>";
        } else {
            echo "<div class='info'>ℹ️ Tabela <strong>$tabela</strong>: 0 registos (vazia)</div>";
        }
    } catch (PDOException $e) {
        echo "<div class='erro'>❌ Erro ao contar $tabela: " . $e->getMessage() . "</div>";
    }
}

// 6. Verificar pasta uploads
echo "<h2>6️⃣ Verificar pasta uploads</h2>";
if (is_dir('uploads')) {
    if (is_writable('uploads')) {
        echo "<div class='ok'>✅ Pasta 'uploads' existe e tem permissões de escrita</div>";
    } else {
        echo "<div class='erro'>❌ Pasta 'uploads' existe mas NÃO tem permissões de escrita</div>";
    }
} else {
    echo "<div class='info'>ℹ️ Pasta 'uploads' não existe (será criada automaticamente)</div>";
}

// 7. Verificar ficheiros necessários
echo "<h2>7️⃣ Verificar ficheiros do projeto</h2>";
$ficheirosNecessarios = [
    'index.php' => 'Página principal',
    'login.php' => 'Página de login',
    'register.php' => 'Página de registo',
    'logout.php' => 'Script de logout',
    'guardar_evento.php' => 'Script para guardar eventos',
    'style.css' => 'Folha de estilos',
    'db.php' => 'Conexão à base de dados'
];

foreach ($ficheirosNecessarios as $ficheiro => $descricao) {
    if (file_exists($ficheiro)) {
        echo "<div class='ok'>✅ $ficheiro - $descricao</div>";
    } else {
        echo "<div class='erro'>❌ $ficheiro - $descricao NÃO encontrado</div>";
    }
}

// 8. Resumo final
echo "<h2>8️⃣ Resumo Final</h2>";
$erros = substr_count(ob_get_contents(), "class='erro'");
if ($erros == 0) {
    echo "<div class='ok'>";
    echo "<h3>🎉 TUDO PRONTO!</h3>";
    echo "<p>O sistema está corretamente configurado e pronto para usar.</p>";
    echo "<p><a href='index.php' style='background: #58b79d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin-top: 10px;'>Ir para o site</a></p>";
    echo "</div>";
} else {
    echo "<div class='erro'>";
    echo "<h3>⚠️ EXISTEM PROBLEMAS</h3>";
    echo "<p>Foram encontrados <strong>$erros erro(s)</strong>. Por favor, corrija-os antes de continuar.</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p style='color: #666; font-size: 12px;'>⚠️ Apague este ficheiro (teste_conexao.php) após verificar que tudo está a funcionar.</p>";
?>