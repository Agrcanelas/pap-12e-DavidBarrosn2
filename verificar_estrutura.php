<?php
require_once 'db.php';

echo "<h1>🔍 Verificar Estrutura da Tabela EVENTO</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    table { border-collapse: collapse; width: 100%; background: white; margin: 20px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #58b79d; color: white; }
    .erro { color: red; background: #ffe5e5; padding: 15px; margin: 10px 0; border-left: 5px solid red; }
    .ok { color: green; background: #d4edda; padding: 15px; margin: 10px 0; border-left: 5px solid green; }
    .info { background: #d1ecf1; padding: 15px; margin: 10px 0; border-left: 5px solid #0c5460; }
    pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
</style>";

try {
    // 1. Verificar se a tabela existe
    echo "<h2>1️⃣ Verificar se a tabela EVENTO existe</h2>";
    $stmt = $pdo->query("SHOW TABLES LIKE 'evento'");
    $existe = $stmt->fetch();
    
    if ($existe) {
        echo "<div class='ok'>✅ Tabela EVENTO existe</div>";
    } else {
        echo "<div class='erro'>❌ Tabela EVENTO NÃO existe! Execute o ficheiro humanicare.sql</div>";
        exit;
    }
    
    // 2. Mostrar estrutura completa
    echo "<h2>2️⃣ Estrutura Completa da Tabela</h2>";
    $stmt = $pdo->query("DESCRIBE evento");
    $colunas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr>";
    echo "<th>Campo</th>";
    echo "<th>Tipo</th>";
    echo "<th>Null</th>";
    echo "<th>Chave</th>";
    echo "<th>Default</th>";
    echo "<th>Extra</th>";
    echo "</tr>";
    
    foreach ($colunas as $col) {
        echo "<tr>";
        echo "<td><strong>" . htmlspecialchars($col['Field']) . "</strong></td>";
        echo "<td>" . htmlspecialchars($col['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Null']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($col['Default'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($col['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // 3. Verificar colunas necessárias
    echo "<h2>3️⃣ Verificar Colunas Necessárias</h2>";
    $colunasNecessarias = [
        'evento_id' => 'ID do evento',
        'nome' => 'Nome do evento',
        'descricao' => 'Descrição',
        'data_evento' => 'Data do evento',
        'local_evento' => 'Local do evento',
        'imagem' => 'Imagem',
        'utilizador_id' => 'ID do criador',
        'data_criacao' => 'Data de criação'
    ];
    
    $colunasEncontradas = array_column($colunas, 'Field');
    $erros = 0;
    
    foreach ($colunasNecessarias as $coluna => $descricao) {
        if (in_array($coluna, $colunasEncontradas)) {
            echo "<div class='ok'>✅ <strong>$coluna</strong> - $descricao</div>";
        } else {
            echo "<div class='erro'>❌ <strong>$coluna</strong> - $descricao NÃO ENCONTRADA!</div>";
            $erros++;
        }
    }
    
    // 4. Verificar se tem "titulo" em vez de "nome"
    if (in_array('titulo', $colunasEncontradas) && !in_array('nome', $colunasEncontradas)) {
        echo "<div class='erro'>";
        echo "<h3>⚠️ PROBLEMA ENCONTRADO!</h3>";
        echo "<p>A tabela tem a coluna <strong>'titulo'</strong> mas o formulário usa <strong>'nome'</strong>.</p>";
        echo "<p><strong>Solução:</strong> Execute este SQL para corrigir:</p>";
        echo "<pre>ALTER TABLE evento CHANGE titulo nome VARCHAR(200) NOT NULL;</pre>";
        echo "</div>";
    }
    
    // 5. Mostrar CREATE TABLE
    echo "<h2>4️⃣ Comando CREATE TABLE Atual</h2>";
    $stmt = $pdo->query("SHOW CREATE TABLE evento");
    $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<pre>" . htmlspecialchars($createTable['Create Table']) . "</pre>";
    
    // 6. Testar INSERT
    echo "<h2>5️⃣ Testar INSERT (Simulação)</h2>";
    
    $sql_teste = "INSERT INTO evento (nome, descricao, data_evento, local_evento, imagem, utilizador_id, data_criacao)
                  VALUES (:nome, :descricao, :data_evento, :local_evento, :imagem, :utilizador_id, NOW())";
    
    echo "<div class='info'>";
    echo "<p><strong>SQL que será usado:</strong></p>";
    echo "<pre>" . htmlspecialchars($sql_teste) . "</pre>";
    echo "</div>";
    
    try {
        $stmt = $pdo->prepare($sql_teste);
        echo "<div class='ok'>✅ SQL preparado com sucesso - o INSERT deve funcionar!</div>";
    } catch (PDOException $e) {
        echo "<div class='erro'>❌ Erro ao preparar SQL: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
    // 7. Contar eventos existentes
    echo "<h2>6️⃣ Eventos Existentes</h2>";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM evento");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if ($total > 0) {
        echo "<div class='ok'>📊 Existem <strong>$total</strong> evento(s) na base de dados</div>";
        
        // Mostrar últimos eventos
        echo "<h3>Últimos 5 eventos:</h3>";
        $stmt = $pdo->query("SELECT evento_id, nome, data_evento, utilizador_id FROM evento ORDER BY evento_id DESC LIMIT 5");
        $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Data</th><th>Criador ID</th></tr>";
        foreach ($eventos as $ev) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($ev['evento_id']) . "</td>";
            echo "<td>" . htmlspecialchars($ev['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($ev['data_evento']) . "</td>";
            echo "<td>" . htmlspecialchars($ev['utilizador_id']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='info'>ℹ️ Ainda não existem eventos na base de dados</div>";
    }
    
    // 8. Verificar utilizadores
    echo "<h2>7️⃣ Verificar Utilizadores</h2>";
    $stmt = $pdo->query("SELECT utilizador_id, nome, email FROM utilizador LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($users) > 0) {
        echo "<div class='ok'>✅ Existem utilizadores na base de dados</div>";
        echo "<table>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th></tr>";
        foreach ($users as $u) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($u['utilizador_id']) . "</td>";
            echo "<td>" . htmlspecialchars($u['nome']) . "</td>";
            echo "<td>" . htmlspecialchars($u['email']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='erro'>❌ Não existem utilizadores! Execute o SQL com os dados de teste.</div>";
    }
    
    // Resumo final
    echo "<h2>8️⃣ Resumo</h2>";
    if ($erros == 0) {
        echo "<div class='ok'>";
        echo "<h3>✅ Estrutura Correta!</h3>";
        echo "<p>A tabela está configurada corretamente. Se ainda houver erro ao criar eventos:</p>";
        echo "<ol>";
        echo "<li>Tente criar um evento</li>";
        echo "<li>Veja a mensagem de erro completa em guardar_evento.php</li>";
        echo "<li>Copie o erro e mostre-me</li>";
        echo "</ol>";
        echo "</div>";
    } else {
        echo "<div class='erro'>";
        echo "<h3>⚠️ Problemas Encontrados</h3>";
        echo "<p>Corrija os erros acima antes de continuar.</p>";
        echo "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='erro'>";
    echo "<h3>❌ Erro de Conexão</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #58b79d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Voltar ao site</a></p>";
?>