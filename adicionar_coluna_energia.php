<?php
require_once('_inc/conexao.php');

echo "<h2>🔧 Adicionando coluna energia_ultima_atualizacao</h2>";

try {
    // Verificar se a coluna já existe
    $stmt = $conexao->prepare("PRAGMA table_info(usuarios)");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $column_exists = false;
    foreach ($columns as $column) {
        if ($column['name'] == 'energia_ultima_atualizacao') {
            $column_exists = true;
            break;
        }
    }
    
    if (!$column_exists) {
        echo "<p>📝 Adicionando coluna energia_ultima_atualizacao...</p>";
        
        $conexao->exec("ALTER TABLE usuarios ADD COLUMN energia_ultima_atualizacao DATETIME DEFAULT CURRENT_TIMESTAMP");
        
        echo "<p style='color: green;'>✅ Coluna energia_ultima_atualizacao adicionada com sucesso!</p>";
        
        // Atualizar todos os usuários com timestamp atual
        $conexao->exec("UPDATE usuarios SET energia_ultima_atualizacao = CURRENT_TIMESTAMP WHERE energia_ultima_atualizacao IS NULL");
        
        echo "<p style='color: green;'>✅ Timestamps de energia atualizados para todos os usuários!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ️ Coluna energia_ultima_atualizacao já existe!</p>";
    }
    
    // Verificar a estrutura atualizada
    echo "<h3>📋 Estrutura atual da tabela usuarios:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Nome</th><th>Tipo</th><th>Padrão</th></tr>";
    
    $stmt = $conexao->prepare("PRAGMA table_info(usuarios)");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($column['name']) . "</td>";
        echo "<td>" . htmlspecialchars($column['type']) . "</td>";
        echo "<td>" . htmlspecialchars($column['dflt_value']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
