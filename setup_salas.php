<?php
require_once('_inc/conexao.php');

try {
    // Verificar se já existem salas
    $check = $conexao->prepare("SELECT COUNT(*) as total FROM salas");
    $check->execute();
    $result = $check->fetch(PDO::FETCH_ASSOC);
    
    if($result['total'] == 0) {
        // Criar as 5 salas
        for($i = 1; $i <= 5; $i++) {
            $stmt = $conexao->prepare("INSERT INTO salas (id, usuarioid, fim) VALUES (?, 0, '0000-00-00 00:00:00')");
            $stmt->execute([$i]);
        }
        echo "5 salas criadas com sucesso!";
    } else {
        echo "Salas já existem na base de dados.";
    }
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
}
?>
