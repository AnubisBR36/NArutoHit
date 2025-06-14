<?php 
require_once('conexao.php');

$q = (int)$_GET['id'];

try {
    // Verificar mensagens não lidas
    $sqlm = $conexao->prepare("SELECT COUNT(id) as conta FROM mensagens WHERE destino = ? AND status = 'naolido'");
    $sqlm->execute([$q]);
    $dbm = $sqlm->fetch(PDO::FETCH_ASSOC);

    if($dbm && $dbm['conta'] > 0){
        echo '<div class="action"><span class="sub2"><a href="?p=messages">'.$dbm['conta'].' nova';
        if($dbm['conta'] > 1) echo 's';
        echo ' mensage';
        if($dbm['conta'] > 1) echo 'ns'; else echo 'm';
        echo '!</a></span></div>';
    }
} catch (PDOException $e) {
    error_log("Erro ao verificar mensagens: " . $e->getMessage());
}

try {
    // Verificar relatórios de ataques
    $sqlr = $conexao->prepare("SELECT COUNT(id) as conta FROM relatorios WHERE inimigoid = ? AND status = 'nao'");
    $sqlr->execute([$q]);
    $dbr = $sqlr->fetch(PDO::FETCH_ASSOC);

    if($dbr && $dbr['conta'] > 0){
        echo '<div class="action"><span class="sub2"><a href="?p=reports">Você foi atacado '.$dbr['conta'].' vez';
        if($dbr['conta'] > 1) echo 'es';
        echo '!</a></span></div>';
    }
} catch (PDOException $e) {
    error_log("Erro ao verificar relatórios: " . $e->getMessage());
}
?>>