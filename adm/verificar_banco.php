<?php
require_once('../_inc/conexao.php');

echo "<h2>🔍 Status do Banco de Dados</h2>";

try {
    // Verificar usuários
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM usuarios");
    $stmt->execute();
    $total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    echo "<div style='background: #f0f8ff; padding: 10px; margin: 10px 0; border-left: 4px solid #2196F3;'>";
    echo "<h3>👥 Usuários</h3>";
    echo "Total de usuários: <strong>$total_usuarios</strong><br>";
    
    if($total_usuarios > 0) {
        // Mostrar últimos usuários criados
        $stmt = $conexao->prepare("SELECT usuario, nivel, vila, status FROM usuarios ORDER BY id DESC LIMIT 5");
        $stmt->execute();
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<br><strong>Últimos usuários:</strong><br>";
        $vilas = [1 => 'Folha', 2 => 'Areia', 3 => 'Névoa', 4 => 'Pedra', 5 => 'Nuvem', 6 => 'Som', 7 => 'Chuva', 8 => 'Akatsuki'];
        
        foreach($usuarios as $user) {
            $vila_nome = $vilas[$user['vila']] ?? 'Desconhecida';
            $status_color = $user['status'] == 'ativo' ? 'green' : 'red';
            echo "• {$user['usuario']} (Nv.{$user['nivel']}) - $vila_nome - <span style='color: $status_color'>{$user['status']}</span><br>";
        }
    }
    echo "</div>";
    
    // Verificar IPs bloqueados
    try {
        $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM block");
        $stmt->execute();
        $total_ips = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        echo "<div style='background: #fff3cd; padding: 10px; margin: 10px 0; border-left: 4px solid #ffc107;'>";
        echo "<h3>🚫 IPs Bloqueados</h3>";
        echo "Total de IPs bloqueados: <strong>$total_ips</strong><br>";
        
        if($total_ips > 0) {
            $stmt = $conexao->prepare("SELECT ip, tentativas, timestamp FROM block ORDER BY timestamp DESC LIMIT 3");
            $stmt->execute();
            $ips = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<br><strong>IPs recentemente bloqueados:</strong><br>";
            foreach($ips as $ip_info) {
                $data = date('d/m/Y H:i:s', $ip_info['timestamp']);
                echo "• {$ip_info['ip']} ({$ip_info['tentativas']} tentativas) - $data<br>";
            }
            
            echo "<br><a href='limpar_ip.php' style='color: #dc3545;'>🧹 Limpar todos os IPs bloqueados</a>";
        }
        echo "</div>";
        
    } catch(Exception $e) {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
        echo "<h3>🚫 IPs Bloqueados</h3>";
        echo "Erro: " . $e->getMessage();
        echo "</div>";
    }
    
    // Verificar outras tabelas importantes
    $tabelas_info = [
        'organizacoes' => 'Organizações',
        'mensagens' => 'Mensagens',
        'amigos' => 'Amizades',
        'inventario' => 'Inventários',
        'relatorios' => 'Relatórios'
    ];
    
    echo "<div style='background: #d1ecf1; padding: 10px; margin: 10px 0; border-left: 4px solid #17a2b8;'>";
    echo "<h3>📊 Outras Tabelas</h3>";
    
    foreach($tabelas_info as $tabela => $nome) {
        try {
            $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM $tabela");
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            echo "• $nome: <strong>$total</strong> registros<br>";
        } catch(Exception $e) {
            echo "• $nome: <span style='color: red;'>Tabela não encontrada</span><br>";
        }
    }
    echo "</div>";
    
} catch(Exception $e) {
    echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0; border-left: 4px solid #dc3545;'>";
    echo "<h3>❌ Erro</h3>";
    echo "Erro: " . $e->getMessage();
    echo "</div>";
}

echo "<br><div style='text-align: center;'>";
echo "<a href='../index.php' style='background: #28a745; color: white; padding: 10px 15px; text-decoration: none; margin: 5px;'>🎮 Ir para o jogo</a> ";
echo "<a href='verificar_tabelas_sqlite.php' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; margin: 5px;'>🔧 Verificar tabelas</a> ";
echo "<a href='criar_contas_teste.php' style='background: #6f42c1; color: white; padding: 10px 15px; text-decoration: none; margin: 5px;'>🧪 Criar contas teste</a> ";
echo "<a href='limpar_banco.php' style='background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; margin: 5px;'>🗑️ Limpar banco</a>";
echo "</div>";
?>
