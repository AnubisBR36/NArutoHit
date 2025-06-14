<?php
require_once('_inc/conexao.php');

echo "<h2>Verificação da Tabela Mensagens</h2>";

try {
    // Verificar se a tabela mensagens existe
    $stmt = $conexao->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='mensagens'");
    $stmt->execute();
    $table_exists = $stmt->fetch();
    
    if(!$table_exists) {
        echo "<p style='color: red;'>❌ Tabela 'mensagens' não existe! Criando...</p>";
        
        // Criar tabela mensagens
        $create_table = "
        CREATE TABLE mensagens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            data DATETIME NOT NULL,
            origem INTEGER NOT NULL,
            destino INTEGER NOT NULL,
            assunto VARCHAR(60) NOT NULL,
            msg TEXT NOT NULL,
            status VARCHAR(10) NOT NULL DEFAULT 'naolido'
        )";
        
        $conexao->exec($create_table);
        echo "<p style='color: green;'>✅ Tabela 'mensagens' criada com sucesso!</p>";
        
        // Criar índices para performance
        $conexao->exec("CREATE INDEX idx_mensagens_origem ON mensagens(origem)");
        $conexao->exec("CREATE INDEX idx_mensagens_destino ON mensagens(destino)");
        $conexao->exec("CREATE INDEX idx_mensagens_status ON mensagens(status)");
        
        echo "<p style='color: green;'>✅ Índices criados com sucesso!</p>";
    } else {
        echo "<p style='color: green;'>✅ Tabela 'mensagens' existe!</p>";
    }
    
    // Verificar estrutura da tabela
    $stmt = $conexao->prepare("PRAGMA table_info(mensagens)");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Estrutura da tabela mensagens:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Nome</th><th>Tipo</th><th>Não Nulo</th><th>Padrão</th><th>PK</th></tr>";
    
    foreach($columns as $column) {
        echo "<tr>";
        echo "<td>" . $column['name'] . "</td>";
        echo "<td>" . $column['type'] . "</td>";
        echo "<td>" . ($column['notnull'] ? 'Sim' : 'Não') . "</td>";
        echo "<td>" . ($column['dflt_value'] ?: 'NULL') . "</td>";
        echo "<td>" . ($column['pk'] ? 'Sim' : 'Não') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Verificar quantas mensagens existem
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM mensagens");
    $stmt->execute();
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h3>Total de mensagens no banco: " . $count['total'] . "</h3>";
    
    // Verificar se há mensagens do usuário logado (se estiver logado)
    if(isset($db['id'])) {
        $stmt = $conexao->prepare("SELECT COUNT(*) as enviadas FROM mensagens WHERE origem = ?");
        $stmt->execute([$db['id']]);
        $enviadas = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $stmt = $conexao->prepare("SELECT COUNT(*) as recebidas FROM mensagens WHERE destino = ?");
        $stmt->execute([$db['id']]);
        $recebidas = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<p>Mensagens enviadas pelo usuário atual: " . $enviadas['enviadas'] . "</p>";
        echo "<p>Mensagens recebidas pelo usuário atual: " . $recebidas['recebidas'] . "</p>";
        
        // Mostrar últimas 5 mensagens
        $stmt = $conexao->prepare("SELECT * FROM mensagens WHERE origem = ? OR destino = ? ORDER BY data DESC LIMIT 5");
        $stmt->execute([$db['id'], $db['id']]);
        $mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($mensagens) > 0) {
            echo "<h3>Últimas 5 mensagens:</h3>";
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Data</th><th>Origem</th><th>Destino</th><th>Assunto</th><th>Status</th></tr>";
            
            foreach($mensagens as $msg) {
                echo "<tr>";
                echo "<td>" . $msg['id'] . "</td>";
                echo "<td>" . $msg['data'] . "</td>";
                echo "<td>" . $msg['origem'] . "</td>";
                echo "<td>" . $msg['destino'] . "</td>";
                echo "<td>" . $msg['assunto'] . "</td>";
                echo "<td>" . $msg['status'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Erro: " . $e->getMessage() . "</p>";
}

echo "<br><a href='index.php'>← Voltar ao jogo</a>";
?>
