<?php
session_start();
ob_start();

try {
    // Usar SQLite database - caminho absoluto para evitar duplicação
    $dbFile = __DIR__ . '/../database.sqlite';
    $conexao = new PDO("sqlite:$dbFile");
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // Executar comando para compatibilidade MySQL
    $conexao->exec("PRAGMA foreign_keys = ON");
    
} catch (PDOException $e) {
    // Em caso de erro, redirecionar para página de setup
    if (!file_exists('setup.php')) {
        die("Erro de conexão com o banco de dados: " . $e->getMessage());
    } else {
        header("Location: setup.php");
        exit;
    }
}
