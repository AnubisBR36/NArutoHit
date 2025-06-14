<?php
require_once('../_inc/conexao.php');

echo "<h2>Criar Conta de Teste para Mapa</h2>";

// Verificar se já existe a conta de teste do mapa
$stmt = $conexao->prepare("SELECT id FROM usuarios WHERE usuario = 'maptest'");
$stmt->execute();
$conta_existente = $stmt->fetch(PDO::FETCH_ASSOC);

if($conta_existente) {
    echo "<div style='color: orange;'>⚠ Conta 'maptest' já existe (ID: {$conta_existente['id']})</div>";
    echo "<a href='../index.php'>← Voltar ao jogo</a>";
    exit;
}

echo "<p>Criando conta de teste para o mapa...</p>";

try {
    // Criar conta especializada para teste de mapa/batalhas
    $stmt = $conexao->prepare("
        INSERT INTO usuarios 
        (usuario, senha, email, personagem, avatar, vila, nivel, energia, energiamax, yens, taijutsu, ninjutsu, genjutsu, status, exp, expmax, vitorias, derrotas, empates) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        'maptest', // usuário
        md5('map123'), // senha
        'maptest@narutohit.com', // email
        'kakashi', // personagem
        1, // avatar
        1, // vila (Folha)
        25, // nível alto para testes
        100, // energia
        100, // energia máxima
        10000, // yens
        75, // taijutsu alto
        75, // ninjutsu alto
        75, // genjutsu alto
        1500, // exp
        2000, // exp máxima
        10, // vitórias
        5, // derrotas
        2 // empates
    ]);
    
    $user_id = $conexao->lastInsertId();
    
    echo "✅ Conta de teste do mapa criada com sucesso!<br>";
    echo "<strong>Usuário:</strong> maptest<br>";
    echo "<strong>Senha:</strong> map123<br>";
    echo "<strong>ID:</strong> $user_id<br>";
    echo "<strong>Personagem:</strong> Kakashi<br>";
    echo "<strong>Vila:</strong> Folha<br>";
    echo "<strong>Nível:</strong> 25<br>";
    
    // Verificar se existe tabela de posições do mapa e inserir posição inicial
    try {
        $conexao->exec("CREATE TABLE IF NOT EXISTS players_positions (
            player_id INTEGER PRIMARY KEY,
            current_page_id INTEGER NOT NULL DEFAULT 1,
            x INTEGER NOT NULL DEFAULT 10,
            y INTEGER NOT NULL DEFAULT 10,
            last_move_time DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        
        $stmt = $conexao->prepare("INSERT OR REPLACE INTO players_positions (player_id, current_page_id, x, y) VALUES (?, 1, 10, 10)");
        $stmt->execute([$user_id]);
        
        echo "✅ Posição no mapa configurada<br>";
    } catch(Exception $e) {
        echo "⚠ Aviso: Não foi possível configurar posição no mapa: " . $e->getMessage() . "<br>";
    }
    
} catch(Exception $e) {
    echo "❌ Erro ao criar conta de teste: " . $e->getMessage() . "<br>";
}

echo "<br><strong>Conta especializada para testes de mapa criada!</strong><br>";
echo "<p>Use esta conta para testar sistemas de batalha e navegação no mapa.</p>";
echo "<a href='../index.php'>🎮 Ir para o jogo</a> | ";
echo "<a href='verificar_banco.php'>🔍 Verificar banco</a>";
?>
