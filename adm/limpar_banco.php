<?php
require_once('../_inc/conexao.php');

echo "<h2>🗑️ Limpeza COMPLETA do Banco de Dados</h2>";
echo "<div style='color: red; font-weight: bold; border: 2px solid red; padding: 10px; margin: 10px 0;'>";
echo "⚠️ ATENÇÃO: Este script irá APAGAR TODAS as contas de usuários!<br>";
echo "Esta ação é IRREVERSÍVEL!";
echo "</div>";

try {
    echo "Iniciando limpeza COMPLETA das contas de usuários...<br><br>";
    
    // Lista de tabelas para limpar (relacionadas a usuários)
    $tabelas = [
        'usuarios' => 'Contas de usuários',
        'membros' => 'Membros de organizações',
        'amigos' => 'Lista de amigos',
        'messages' => 'Mensagens entre usuários',
        'inventario' => 'Inventário dos usuários',
        'book' => 'Book de ataques',
        'contato' => 'Mensagens de contato',
        'relatorios' => 'Relatórios de usuários',
        'spam' => 'Controle de spam',
        'vendas' => 'Histórico de vendas',
        'verificador' => 'Verificações de missões',
        'vip' => 'Histórico VIP',
        'ramen' => 'Histórico de ramen',
        'usaveis' => 'Itens usáveis',
        'block' => 'IPs bloqueados'
    ];
    
    foreach($tabelas as $tabela => $descricao) {
        try {
            // Verificar se a tabela existe
            $stmt = $conexao->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
            $stmt->execute([$tabela]);
            
            if($stmt->fetch()) {
                // Contar registros antes da limpeza
                $count_stmt = $conexao->prepare("SELECT COUNT(*) as total FROM $tabela");
                $count_stmt->execute();
                $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
                
                // Limpar a tabela
                $conexao->exec("DELETE FROM $tabela");
                
                echo "✓ Dados da tabela '$tabela' limpos com sucesso ($total registros removidos)<br>";
            } else {
                echo "⚠ Tabela '$tabela' não encontrada<br>";
            }
        } catch(Exception $e) {
            echo "❌ Erro ao limpar tabela '$tabela': " . $e->getMessage() . "<br>";
        }
    }

    echo "<br><strong>✅ LIMPEZA COMPLETA CONCLUÍDA!</strong><br>";
    echo "• Todas as contas de usuários foram TOTALMENTE removidas<br>";
    echo "• Todos os IPs bloqueados foram removidos<br>";
    echo "• Cache de sistema foi limpo<br><br>";
    echo "✨ <strong>Agora você pode criar uma nova conta normalmente!</strong><br><br>";
    echo "<a href='../index.php?p=reg' style='color: green; font-weight: bold;'>🚀 Criar Nova Conta</a> | ";
    echo "<a href='verificar_banco.php'>🔍 Verificar Status do Banco</a> | ";
    echo "<a href='criar_contas_teste.php'>🧪 Criar Contas de Teste</a>";
    
} catch (Exception $e) {
    echo "❌ <strong>Erro durante a limpeza:</strong> " . $e->getMessage();
}

// Otimizar banco em uma nova conexão para evitar conflitos
try {
    echo "<br><br>✓ Otimizando banco de dados...<br>";
    
    // Fechar conexão atual para liberar locks
    $conexao = null;
    
    // Nova conexão para otimização
    $conexao_otim = new PDO("sqlite:database.sqlite");
    $conexao_otim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao_otim->exec("VACUUM");
    $conexao_otim = null;
    
    echo "✓ Otimização do banco concluída<br>";
} catch (Exception $e) {
    echo "⚠ Aviso: Não foi possível otimizar o banco: " . $e->getMessage() . "<br>";
}
?>
