<?php
$atual=date('Y-m-d H:i:s');
if($db['missao']>0){ echo "<script>self.location='?p=busymission'</script>"; exit; }
if($db['hunt']>0){ echo "<script>self.location='?p=busyhunt'</script>"; exit; }
if($db['treino']>0){ echo "<script>self.location='?p=busytrain'</script>"; exit; }
if((isset($_GET['p']))&&($_GET['p']<>'train')){
	if(date('Y-m-d H:i:s')<$db['penalidade_fim']){ echo "<script>self.location='?p=penalty'</script>"; exit; }
}
try {
    $stmt = $conexao->prepare("SELECT count(id) AS conta FROM salas WHERE usuarioid=:usuarioid AND fim>:atual");
    $stmt->bindParam(':usuarioid', $db['id'], PDO::PARAM_INT);
    $stmt->bindParam(':atual', $atual, PDO::PARAM_STR);
    $stmt->execute();
    $dbs = $stmt->fetch(PDO::FETCH_ASSOC);
    $num_salas = $stmt->rowCount();

    // Se o usuário tem uma sala ativa mas não está na school, room, learn, elements, schooltrain, chakra, ou discover
    $school_pages = array('school', 'room', 'learn', 'elements', 'schooltrain', 'chakra', 'discover');
    $current_page = isset($_GET['p']) ? $_GET['p'] : '';
    
    if($dbs && ($num_salas > 0) && !in_array($current_page, $school_pages)){
        // Liberar a sala automaticamente
        try {
            $clear_stmt = $conexao->prepare("UPDATE salas SET usuarioid=0, fim='0000-00-00 00:00:00' WHERE usuarioid=?");
            $clear_stmt->execute([$db['id']]);
        } catch (PDOException $e) {
            // Handle clear error silently
        }
    }
} catch (PDOException $e) {
    // Handle database error (e.g., log it, display a user-friendly message)
    echo "Database error: " . $e->getMessage();
    exit; // Or redirect to an error page
}
?>