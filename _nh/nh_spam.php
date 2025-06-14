
<?php
// Buscar dados de spam
try {
    $stmt = $conexao->prepare("SELECT * FROM spam ORDER BY id ASC");
    $stmt->execute();
    $spam_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $spam_results = [];
    echo "Erro ao consultar spam: " . $e->getMessage();
}

if(isset($_GET['del']) && isset($_GET['id'])){
    try {
        $stmt = $conexao->prepare("DELETE FROM spam WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        echo "<script>self.location='?p=spam&msg=2'</script>";
    } catch (Exception $e) {
        echo "Erro ao deletar: " . $e->getMessage();
    }
}

if(isset($_GET['ban']) && isset($_GET['userid'])){
    try {
        // Primeiro deletar o usuário
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$_GET['userid']]);
        
        // Depois deletar registros de spam do usuário
        $stmt = $conexao->prepare("DELETE FROM spam WHERE usuarioid = ?");
        $stmt->execute([$_GET['userid']]);
        
        echo "<script>self.location='?p=spam&msg=1'</script>";
    } catch (Exception $e) {
        echo "Erro ao banir usuário: " . $e->getMessage();
    }
}

// Mostrar mensagens
if(isset($_GET['msg'])){
    switch($_GET['msg']){
        case 1: $msg='Usuário banido.'; break;
        case 2: $msg='Excluído.'; break;
    }
    echo '<div class="aviso">'.$msg.'</div>';
}
?>

<div class="box_top">Gerenciamento de Spam</div>
<div class="box_middle">
    <table width="500" cellpadding="0" cellspacing="1">
        <tr class="table_titulo">
            <td>Usuário</td>
            <td>Informante</td>
            <td>Ações</td>
        </tr>
        <?php if(empty($spam_results)): ?>
        <tr>
            <td colspan="3">
                <div class="aviso">Nenhum registro encontrado.</div>
            </td>
        </tr>
        <?php else: ?>
        <?php foreach($spam_results as $spam_item): ?>
        <tr class="table_dados" style="background:#323232">
            <td><?php echo htmlspecialchars($spam_item['usuario'] ?? 'N/A'); ?></td>
            <td><?php echo htmlspecialchars($spam_item['informante'] ?? 'N/A'); ?></td>
            <td>
                <a href="?p=view&view=<?php echo urlencode($spam_item['usuario'] ?? ''); ?>">Visualizar</a> | 
                <a href="?p=spam&ban=1&userid=<?php echo $spam_item['usuarioid']; ?>" onClick="if(confirm('Confirma banimento?')==false) return false;">Banir</a> | 
                <a href="?p=spam&del=1&id=<?php echo $spam_item['id']; ?>" onClick="if(confirm('Confirma exclusão?')==false) return false;">Apagar</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<div class="box_bottom"></div>

<?php
// Código para recuperação de senha (comentado para segurança)
/*
$senha='leancaio';
$mensagem='<b>Senha: </b>'.$senha;
$assunto='Recuperação de Senha';
$remetente='contato@narutohit.net';
$headers = implode ( "\n",array ( "From: $remetente","Subject: $assunto","Return-Path: $remetente","MIME-Version: 1.0","X-Priority: 3","Content-Type: text/html" ) );
//mysql_query("UPDATE usuarios SET senha='".md5($senha)."' WHERE usuario='".$_POST['rec_usuario']."'");
mail('leander_90@hotmail.com',$assunto,$mensagem,$headers);
*/
?>
