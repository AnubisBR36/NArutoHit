<?php
// Database setup script
try {
    $dbFile = 'database.sqlite';
    
    // Conectar ao SQLite
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar tabela de usuários
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
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
            registro DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Criar tabela de organizações
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS organizacoes (
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
    ");
    
    // Criar tabela de mensagens
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mensagens (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            de INTEGER NOT NULL,
            para INTEGER NOT NULL,
            assunto VARCHAR(255),
            mensagem TEXT,
            lida INTEGER DEFAULT 0,
            data DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    
    // Criar tabela de salas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS salas (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tipo VARCHAR(50),
            usuarioid INTEGER DEFAULT 0,
            inicio DATETIME DEFAULT '0000-00-00 00:00:00',
            fim DATETIME DEFAULT '0000-00-00 00:00:00'
        )
    ");
    
    // Inserir salas padrão
    $pdo->exec("
        INSERT OR IGNORE INTO salas (id, tipo) VALUES 
        (1, 'escola_1'),
        (2, 'escola_2'),
        (3, 'escola_3'),
        (4, 'escola_4'),
        (5, 'escola_5')
    ");
    
    // Criar usuário administrador padrão
    $senhaAdmin = md5('admin123');
    $stmt = $pdo->prepare("
        INSERT OR REPLACE INTO usuarios 
        (id, usuario, senha, email, avatar, nivel, energia, energiamax, yens, status) 
        VALUES (1, 'admin', ?, 'admin@narutohit.com', 1, 99, 999, 999, 999999, 'ativo')
    ");
    $stmt->execute([$senhaAdmin]);
    
    echo "<h2>✅ Setup do banco de dados concluído com sucesso!</h2>";
    echo "<p>Banco de dados SQLite criado: <strong>$dbFile</strong></p>";
    echo "<p>Usuário administrador criado:</p>";
    echo "<ul>";
    echo "<li><strong>Usuário:</strong> admin</li>";
    echo "<li><strong>Senha:</strong> admin123</li>";
    echo "</ul>";
    echo "<p><a href='index.php'>🎮 Acessar o jogo</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Erro ao configurar banco de dados:</h2>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Verifique as permissões de escrita no diretório.</p>";
}
?>
