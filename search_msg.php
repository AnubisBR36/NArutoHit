<?php require_once('_inc/conexao.php'); ?>
<?php
function vn($numero){
	if(!is_numeric($numero)){
			echo "<script>self.location='?p=home'</script>";
			exit;
	}
}
$chaveuniversal='hgfdhgfd';
require_once('_inc/Encrypt.php');
$c=new C_Encrypt();
if((!isset($_GET['id']))or(!isset($_GET['key']))){ echo "<script>self.location='index.php?p=home'</script>"; exit; }
$q=$_GET['id'];
$key=$c->decode($_GET['key'],$chaveuniversal);
vn($q); vn($key);

try {
    $stmt_update = $conexao->prepare("UPDATE mensagens SET status='lido' WHERE id=?");
    $stmt_update->execute([$q]);
    
    $sqlmm = $conexao->prepare("SELECT m.*,u.usuario user_origem,u2.usuario user_destino FROM mensagens m LEFT JOIN usuarios u ON m.origem=u.id LEFT JOIN usuarios u2 ON m.destino=u2.id WHERE m.id=?");
    $sqlmm->execute([$q]);
    $dbmm = $sqlmm->fetch(PDO::FETCH_ASSOC);
    
    if(!$dbmm) {
        echo "<script>alert('Mensagem não encontrada!'); self.location='index.php?p=messages'</script>";
        exit;
    }
} catch (PDOException $e) {
    error_log("Erro ao buscar mensagem: " . $e->getMessage());
    echo "<script>self.location='index.php?p=home'</script>";
    exit;
}
if(($dbmm['origem']<>$key)&&($dbmm['destino']<>$key)){ echo "<script>self.location='index.php?p=home'</script>"; exit; }
if(strpos($dbmm['msg'],'senha')==true) $aviso='<tr><td colspan="2" style="text-align:justify;"><br /><b>FOI DETECTADO QUE A MENSAGEM ACIMA POSSUI A PALAVRA <u>SENHA</u> EM SEU CONTEXTO. LEMBRE-SE QUE <u>JAMAIS</u> REQUISITAREMOS SUA SENHA DE ACESSO. PARA SUA SEGURANÇA, E PARA A SEGURANÇA DOS DEMAIS JOGADORES, ESTA MENSAGEM TAMBÉM FOI ENVIADA PARA NOSSA EQUIPE. CASO SEJA UMA TENTATIVA DE ROUBO DE SUA SENHA, O USUÁRIO QUE A ENVIOU SERÁ BANIDO DO JOGO.</b></td></tr>'; else $aviso='';
$ex=explode(' ',$dbmm['data']);
$data=explode('-',$ex[0]);
$msg=str_replace(array('<p>','</p>'),array('','<br />'),$dbmm['msg']);
if($dbmm['origem']==0) $or='narutoHIT'; else $or=$dbmm['user_origem'];
echo '
<div class="modalExemplo" style="width:450px;">
<div class="city_div">
<table width="100%" cellpadding="0" cellspacing="1">
  <tr>
    <td align="left"><b>Data:</b></td>
    <td align="left">'.$data[2].'/'.$data[1].'/'.$data[0].', às '.$ex[1].'</td>
  </tr>
  <tr>
    <td width="100" align="left"><b>De:</b></td>
    <td width="385" align="left">'.$or.'</td>
  </tr>
  <tr>
    <td align="left"><b>Para:</b></td>
    <td align="left">'.$dbmm['user_destino'].'</td>
  </tr>
  <tr>
    <td align="left"><b>Assunto:</b></td>
    <td align="left">'.$dbmm['assunto'].'</td>
  </tr>
  <tr>
    <td colsan="2">&nbsp;</td>
  </tr>
  <tr>
    <td align="left" valign="top"><b>Mensagem:</b></td>
    <td align="left">'.nl2br($msg).'</td>
  </tr>';
  if(strpos($dbmm['msg'],'senha')==true) echo '
  <tr>
  	<td colspan="2" style="text-align:justify;font-size:10px;color:#CC0000;"><br /><b>FOI DETECTADO QUE A MENSAGEM ACIMA POSSUI A PALAVRA <u>SENHA</u> EM SEU CONTEXTO. LEMBRE-SE QUE <u>JAMAIS</u> REQUISITAREMOS SUA SENHA DE ACESSO. PARA SUA SEGURAN&Ccedil;A, E PARA A SEGURAN&Ccedil;A DOS DEMAIS JOGADORES, ESTA MENSAGEM TAMB&Eacute;M FOI ENVIADA PARA NOSSA EQUIPE. CASO SEJA UMA TENTATIVA DE ROUBO DE SUA SENHA, O USU&Aacute;RIO QUE A ENVIOU SER&Aacute; BANIDO DO JOGO.</b>
	</td>
  </tr>';
  echo '
</table>
</div>
<div align="center" style="margin-top:7px;"><a href="index.php?p=messages&aba=escrever&destiny='.$dbmm['user_origem'].'&subject=RE:'.$dbmm['assunto'].'" style="color:#666666;"><img src="_img/refresh.png" border="0" align="absmiddle" width="12" height="12" /> Responder</a> | <a href="#" rel="modalclose" style="color:#666666;"><img src="_img/close.jpg" border="0" align="absmiddle" width="12" height="12" /> Fechar</a></div>
</div>';
?>
<?php
require_once('_inc/conexao.php');
require_once('_inc/trava.php');

if(!isset($_GET['id']) || !isset($_GET['key'])) {
    die('Parâmetros inválidos');
}

$message_id = (int)$_GET['id'];

try {
    // Verificar se a mensagem pertence ao usuário logado
    $stmt = $conexao->prepare("SELECT m.*, u.usuario as remetente FROM mensagens m LEFT JOIN usuarios u ON m.origem = u.id WHERE m.id = ? AND m.destino = ?");
    $stmt->execute([$message_id, $db['id']]);
    $message = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$message) {
        die('Mensagem não encontrada');
    }
    
    // Marcar como lida
    $stmt_update = $conexao->prepare("UPDATE mensagens SET status = 'lido' WHERE id = ?");
    $stmt_update->execute([$message_id]);
    
} catch (PDOException $e) {
    die('Erro ao carregar mensagem');
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Visualizar Mensagem</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background-color: #1a1a1a; color: #fff; }
        .message-container { background-color: #2a2a2a; padding: 20px; border-radius: 5px; }
        .message-header { border-bottom: 1px solid #444; padding-bottom: 10px; margin-bottom: 10px; }
        .message-content { line-height: 1.6; }
        .close-button { background-color: #666; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 15px; }
        .close-button:hover { background-color: #888; }
    </style>
</head>
<body>
    <div class="message-container">
        <div class="message-header">
            <h3>Mensagem de: <?php echo htmlspecialchars($message['remetente'] ?: 'narutoHIT'); ?></h3>
            <p><strong>Assunto:</strong> <?php echo htmlspecialchars($message['assunto']); ?></p>
            <p><strong>Data:</strong> <?php echo date('d/m/Y H:i:s', strtotime($message['data'])); ?></p>
        </div>
        <div class="message-content">
            <?php echo nl2br(htmlspecialchars($message['mensagem'])); ?>
        </div>
        <button class="close-button" onclick="window.close()">Fechar</button>
    </div>
</body>
</html>
