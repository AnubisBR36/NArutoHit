<?php
require_once('../_inc/conexao.php');

echo "<h2>🔍 Verificação e Criação de Tabelas SQLite</h2>";

// Definir estrutura das tabelas essenciais
$tabelas_necessarias = [
    'usuarios' => "
        CREATE TABLE usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            senha VARCHAR(32) NOT NULL,
            email VARCHAR(100),
            avatar INTEGER DEFAULT 0,
            personagem VARCHAR(50) DEFAULT '',
            vila INTEGER DEFAULT 1,
            nivel INTEGER DEFAULT 1,
            exp INTEGER DEFAULT 0,
            expmax INTEGER DEFAULT 100,
            energia INTEGER DEFAULT 50,
            energiamax INTEGER DEFAULT 50,
            yens INTEGER DEFAULT 500,
            yens_fat INTEGER DEFAULT 0,
            taijutsu INTEGER DEFAULT 10,
            ninjutsu INTEGER DEFAULT 10,
            genjutsu INTEGER DEFAULT 10,
            status VARCHAR(20) DEFAULT 'ativo',
            renegado INTEGER DEFAULT 0,
            orgid INTEGER DEFAULT 0,
            vip DATETIME DEFAULT '0000-00-00 00:00:00',
            vip_inicio DATETIME DEFAULT '0000-00-00 00:00:00',
            missao INTEGER DEFAULT 0,
            missao_fim DATETIME DEFAULT '0000-00-00 00:00:00',
            hunt INTEGER DEFAULT 0,
            treino INTEGER DEFAULT 0,
            penalidade_fim DATETIME DEFAULT '0000-00-00 00:00:00',
            doujutsu INTEGER DEFAULT 0,
            doujutsu_nivel INTEGER DEFAULT 0,
            doujutsu_exp INTEGER DEFAULT 0,
            doujutsu_expmax INTEGER DEFAULT 0,
            config_radio INTEGER DEFAULT 1,
            loginip INTEGER DEFAULT 0,
            registro DATETIME DEFAULT CURRENT_TIMESTAMP,
            vitorias INTEGER DEFAULT 0,
            derrotas INTEGER DEFAULT 0,
            empates INTEGER DEFAULT 0,
            ban_fim DATETIME DEFAULT '2000-01-01 00:00:00',
            ban_motivo TEXT DEFAULT '',
            adm INTEGER DEFAULT 0
        )
    ",
    
    'block' => "
        CREATE TABLE block (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            ip VARCHAR(45) NOT NULL,
            tentativas INTEGER DEFAULT 1,
            timestamp INTEGER NOT NULL
        )
    ",
    
    'organizacoes' => "
        CREATE TABLE organizacoes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nome VARCHAR(100) NOT NULL,
            nivel INTEGER DEFAULT 1,
            exp INTEGER DEFAULT 0,
            yens INTEGER DEFAULT 0,
            lider INTEGER,
            vila INTEGER DEFAULT 1,
            logo VARCHAR(255) DEFAULT '',
            descricao TEXT
        )
    ",
    
    'mensagens' => "
        CREATE TABLE mensagens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            de INTEGER NOT NULL,
            para INTEGER NOT NULL,
            assunto VARCHAR(255),
            mensagem TEXT,
            lida INTEGER DEFAULT 0,
            data DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ",
    
    'amigos' => "
        CREATE TABLE amigos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario1 INTEGER NOT NULL,
            usuario2 INTEGER NOT NULL,
            status VARCHAR(20) DEFAULT 'pendente',
            data DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ",
    
    'inventario' => "
        CREATE TABLE inventario (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuarioid INTEGER NOT NULL,
            itemid INTEGER NOT NULL,
            quantidade INTEGER DEFAULT 1
        )
    ",
    
    'relatorios' => "
        CREATE TABLE relatorios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            de INTEGER NOT NULL,
            contra INTEGER NOT NULL,
            motivo TEXT,
            status VARCHAR(20) DEFAULT 'pendente',
            data DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ",
    
    'players_positions' => "
        CREATE TABLE players_positions (
            player_id INTEGER PRIMARY KEY,
            current_page_id INTEGER NOT NULL DEFAULT 1,
            x INTEGER NOT NULL DEFAULT 10,
            y INTEGER NOT NULL DEFAULT 10,
            last_move_time DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ",
    
    'maps_pages' => "
        CREATE TABLE maps_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(100) NOT NULL,
            grid_data TEXT,
            north_page_id INTEGER NULL,
            south_page_id INTEGER NULL,
            east_page_id INTEGER NULL,
            west_page_id INTEGER NULL
        )
    "
];

echo "<p>Verificando estrutura do banco de dados...</p>";

$tabelas_criadas = 0;
$tabelas_existentes = 0;

foreach($tabelas_necessarias as $nome_tabela => $sql_criacao) {
    try {
        // Verificar se a tabela existe
        $stmt = $conexao->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$nome_tabela]);
        
        if($stmt->fetch()) {
            echo "✅ Tabela '<strong>$nome_tabela</strong>' já existe<br>";
            $tabelas_existentes++;
        } else {
            // Criar a tabela
            $conexao->exec($sql_criacao);
            echo "🆕 Tabela '<strong>$nome_tabela</strong>' criada com sucesso<br>";
            $tabelas_criadas++;
        }
        
    } catch(Exception $e) {
        echo "❌ Erro ao processar tabela '$nome_tabela': " . $e->getMessage() . "<br>";
    }
}

echo "<br><hr><br>";

// Inserir dados básicos se necessário
try {
    // Verificar se existe usuário admin
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE usuario = 'admin'");
    $stmt->execute();
    
    if(!$stmt->fetch()) {
        echo "<p>Criando usuário administrador padrão...</p>";
        
        $stmt = $conexao->prepare("
            INSERT INTO usuarios (usuario, senha, email, nivel, energia, energiamax, yens, status, adm) 
            VALUES ('admin', ?, 'admin@narutohit.com', 99, 999, 999, 999999, 'ativo', 1)
        ");
        $stmt->execute([md5('admin123')]);
        
        echo "✅ Usuário admin criado (Senha: admin123)<br>";
    }
    
    // Inserir página inicial do mapa se não existir
    $stmt = $conexao->prepare("SELECT id FROM maps_pages WHERE id = 1");
    $stmt->execute();
    
    if(!$stmt->fetch()) {
        $conexao->exec("
            INSERT INTO maps_pages (id, name, grid_data) 
            VALUES (1, 'Vila da Folha - Centro', '')
        ");
        echo "✅ Página inicial do mapa criada<br>";
    }
    
} catch(Exception $e) {
    echo "⚠ Aviso ao inserir dados iniciais: " . $e->getMessage() . "<br>";
}

echo "<br><div style='background: #e8f5e8; padding: 15px; border: 1px solid #4CAF50;'>";
echo "<h3>📊 Resumo da Verificação</h3>";
echo "• <strong>Tabelas existentes:</strong> $tabelas_existentes<br>";
echo "• <strong>Tabelas criadas:</strong> $tabelas_criadas<br>";
echo "• <strong>Total de tabelas:</strong> " . count($tabelas_necessarias) . "<br>";

if($tabelas_criadas > 0) {
    echo "<br>✨ <strong>$tabelas_criadas tabela(s) foram criadas automaticamente!</strong>";
}

echo "</div>";

echo "<br>";
echo "<a href='../index.php'>🎮 Ir para o jogo</a> | ";
echo "<a href='verificar_banco.php'>🔍 Verificar dados do banco</a> | ";
echo "<a href='criar_contas_teste.php'>🧪 Criar contas de teste</a>";
?>
