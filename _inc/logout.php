
<?php
require_once('conexao.php');
try {
    $stmt = $conexao->prepare("UPDATE usuarios SET timestamp=0 WHERE id=?");
    $stmt->execute([$_SESSION['logado']]);
} catch (PDOException $e) {
    // Handle error silently for logout
}
unset($_SESSION['logado']);
unset($_SESSION['errobot']);
setcookie('logado',1,time()-3600);
setcookie('session_id',1,time()-3600);
if(isset($_GET['reason'])) $reason='&reason='.$_GET['reason']; else $reason='';
echo "<script>self.location='?p=login".$reason."'</script>";
?>
