<?php
require_once('../_inc/conexao.php');

echo "<h2>🧹 Limpeza de IPs Bloqueados</h2>";

try {
    // Verificar se a tabela block existe
    $stmt = $conexao->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='block'");
    $stmt->execute();
    
    if(!$stmt->fetch()) {
        echo "<div style='color: orange;'>⚠ Tabela 'block' não encontrada no banco de dados</div>";
        echo "<a href='verificar_tabelas_sqlite.php'>🔧 Verificar e criar tabelas</a><br>";
        echo "<a href='../index.php'>← Voltar ao jogo</a>";
        exit;
    }
    
    // Contar IPs bloqueados antes da limpeza
    $count_stmt = $conexao->prepare("SELECT COUNT(*) as total FROM block");
    $count_stmt->execute();
    $total_ips = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    if($total_ips == 0) {
        echo "<div style='color: green;'>✅ Não há IPs bloqueados para remover</div>";
    } else {
        echo "<p>Encontrados <strong>$total_ips</strong> IPs bloqueados. Removendo...</p>";
        
        // Mostrar alguns IPs que serão removidos (para log)
        $stmt = $conexao->prepare("SELECT ip, tentativas, timestamp FROM block LIMIT 5");
        $stmt->execute();
        $ips_exemplo = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if($ips_exemplo) {
            echo "<p><strong>Exemplos de IPs que serão removidos:</strong></p>";
            echo "<ul>";
            foreach($ips_exemplo as $ip_info) {
                $data = date('d/m/Y H:i:s', $ip_info['timestamp']);
                echo "<li>{$ip_info['ip']} ({$ip_info['tentativas']} tentativas) - Bloqueado em: $data</li>";
            }
            if($total_ips > 5) {
                echo "<li>... e mais " . ($total_ips - 5) . " IPs</li>";
            }
            echo "</ul>";
        }
        
        // Limpar todos os IPs bloqueados
        $conexao->exec("DELETE FROM block");
        
        echo "<div style='color: green; font-weight: bold;'>✅ Todos os $total_ips IPs bloqueados foram removidos com sucesso!</div>";
        
        // Resetar contador de auto incremento se necessário
        $conexao->exec("DELETE FROM sqlite_sequence WHERE name='block'");
    }
    
    echo "<br><p><strong>Limpeza de IPs concluída!</strong></p>";
    echo "<p>Agora todos os usuários podem tentar fazer login novamente, mesmo que tenham sido bloqueados anteriormente.</p>";
    
} catch(Exception $e) {
    echo "<div style='color: red;'>❌ <strong>Erro durante a limpeza:</strong> " . $e->getMessage() . "</div>";
}

echo "<br>";
echo "<a href='../index.php'>🎮 Ir para o jogo</a> | ";
echo "<a href='verificar_banco.php'>🔍 Verificar banco</a> | ";
echo "<a href='limpar_banco.php'>🗑️ Limpar todas as contas</a>";
?>
