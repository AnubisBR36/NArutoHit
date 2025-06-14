<?php
require_once('_inc/conexao.php');

echo "<h2>Debug do Sistema de Mensagens</h2>";

// Simular dados de usuário logado para teste
$teste_user_id = 1; // ID do usuário de teste
$teste_destino = 'Anubisbr'; // Nome do destinatário

echo "<h3>1. Testando conexão com banco de dados:</h3>";
try {
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "✅ Conexão OK - Total de usuários: " . $result['total'] . "<br>";
} catch (PDOException $e) {
    echo "❌ Erro na conexão: " . $e->getMessage() . "<br>";
}

echo "<h3>2. Verificando se usuário destinatário existe:</h3>";
try {
    $stmt_user = $conexao->prepare("SELECT id, usuario FROM usuarios WHERE usuario = ?");
    $stmt_user->execute([$teste_destino]);
    $destino_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
    
    if($destino_user) {
        echo "✅ Usuário encontrado: ID=" . $destino_user['id'] . ", Nome=" . $destino_user['usuario'] . "<br>";
        $destino_id = $destino_user['id'];
    } else {
        echo "❌ Usuário '$teste_destino' não encontrado<br>";
        
        // Listar alguns usuários disponíveis
        $stmt_list = $conexao->prepare("SELECT id, usuario FROM usuarios LIMIT 5");
        $stmt_list->execute();
        $usuarios = $stmt_list->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Usuários disponíveis:<br>";
        foreach($usuarios as $user) {
            echo "- ID: " . $user['id'] . ", Nome: " . $user['usuario'] . "<br>";
        }
        exit;
    }
} catch (PDOException $e) {
    echo "❌ Erro ao buscar usuário: " . $e->getMessage() . "<br>";
    exit;
}

echo "<h3>3. Tentando inserir mensagem de teste:</h3>";
try {
    $assunto = "Teste de Debug";
    $mensagem = "Esta é uma mensagem de teste para verificar se a inserção está funcionando.";
    $origem = $teste_user_id;
    
    echo "Dados da mensagem:<br>";
    echo "- Origem: $origem<br>";
    echo "- Destino: $destino_id<br>";
    echo "- Assunto: $assunto<br>";
    echo "- Mensagem: $mensagem<br><br>";
    
    $stmt_send = $conexao->prepare("INSERT INTO mensagens (origem, destino, assunto, msg, data, status) VALUES (?, ?, ?, ?, datetime('now'), 'naolido')");
    $resultado = $stmt_send->execute([$origem, $destino_id, $assunto, $mensagem]);
    
    if($resultado) {
        $message_id = $conexao->lastInsertId();
        echo "✅ Mensagem inserida com sucesso! ID: $message_id<br>";
        
        // Verificar se foi realmente inserida
        $stmt_check = $conexao->prepare("SELECT * FROM mensagens WHERE id = ?");
        $stmt_check->execute([$message_id]);
        $inserted_msg = $stmt_check->fetch(PDO::FETCH_ASSOC);
        
        if($inserted_msg) {
            echo "✅ Verificação: Mensagem encontrada no banco<br>";
            echo "Detalhes: <pre>" . print_r($inserted_msg, true) . "</pre>";
        } else {
            echo "❌ Erro: Mensagem não encontrada após inserção<br>";
        }
    } else {
        echo "❌ Falha ao inserir mensagem<br>";
        $errorInfo = $stmt_send->errorInfo();
        echo "Erro SQL: " . print_r($errorInfo, true) . "<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro ao inserir mensagem: " . $e->getMessage() . "<br>";
}

echo "<h3>4. Verificando todas as mensagens na tabela:</h3>";
try {
    $stmt_all = $conexao->prepare("SELECT m.*, u1.usuario as remetente, u2.usuario as destinatario FROM mensagens m 
                                   LEFT JOIN usuarios u1 ON m.origem = u1.id 
                                   LEFT JOIN usuarios u2 ON m.destino = u2.id 
                                   ORDER BY m.data DESC LIMIT 10");
    $stmt_all->execute();
    $all_messages = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
    
    if(count($all_messages) > 0) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Data</th><th>Remetente</th><th>Destinatário</th><th>Assunto</th><th>Status</th></tr>";
        
        foreach($all_messages as $msg) {
            echo "<tr>";
            echo "<td>" . $msg['id'] . "</td>";
            echo "<td>" . $msg['data'] . "</td>";
            echo "<td>" . ($msg['remetente'] ?? 'ID:' . $msg['origem']) . "</td>";
            echo "<td>" . ($msg['destinatario'] ?? 'ID:' . $msg['destino']) . "</td>";
            echo "<td>" . $msg['assunto'] . "</td>";
            echo "<td>" . $msg['status'] . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "❌ Nenhuma mensagem encontrada na tabela<br>";
    }
    
} catch (PDOException $e) {
    echo "❌ Erro ao listar mensagens: " . $e->getMessage() . "<br>";
}

echo "<h3>5. Testando formulário de envio:</h3>";
?>

<form method="post" action="">
    <input type="hidden" name="teste_envio" value="1">
    <p><strong>Destinatário:</strong> <input type="text" name="destino" value="<?php echo $teste_destino; ?>"></p>
    <p><strong>Assunto:</strong> <input type="text" name="assunto" value="Teste Manual"></p>
    <p><strong>Mensagem:</strong><br><textarea name="mensagem" rows="3" cols="50">Mensagem de teste enviada pelo formulário de debug.</textarea></p>
    <p><input type="submit" value="Enviar Mensagem de Teste"></p>
</form>

<?php
if(isset($_POST['teste_envio'])) {
    echo "<h4>Resultado do teste de envio:</h4>";
    
    $destino_nome = trim($_POST['destino']);
    $assunto = trim($_POST['assunto']);
    $mensagem = trim($_POST['mensagem']);
    
    try {
        $stmt_user = $conexao->prepare("SELECT id FROM usuarios WHERE usuario = ?");
        $stmt_user->execute([$destino_nome]);
        $destino_user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        
        if($destino_user) {
            $stmt_send = $conexao->prepare("INSERT INTO mensagens (origem, destino, assunto, msg, data, status) VALUES (?, ?, ?, ?, datetime('now'), 'naolido')");
            $resultado = $stmt_send->execute([$teste_user_id, $destino_user['id'], $assunto, $mensagem]);
            
            if($resultado) {
                echo "✅ Mensagem enviada com sucesso!<br>";
            } else {
                echo "❌ Falha ao enviar mensagem<br>";
            }
        } else {
            echo "❌ Usuário '$destino_nome' não encontrado<br>";
        }
    } catch (PDOException $e) {
        echo "❌ Erro: " . $e->getMessage() . "<br>";
    }
}

echo "<br><a href='index.php'>← Voltar ao jogo</a>";
?>
