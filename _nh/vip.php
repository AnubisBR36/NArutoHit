
<?php
function SomarData($data, $dias, $meses, $ano){
   /*www.brunogross.com*/
   //passe a data no formato dd/mm/yyyy 
   $data = explode("/", $data);
   $newData = date("d/m/Y", mktime(0, 0, 0, $data[1] + $meses,
     $data[0] + $dias, $data[2] + $ano) );
   return $newData;
}

if(isset($_GET['accept'])){
    try {
        $stmt = $conexao->prepare("SELECT * FROM vip WHERE id = ?");
        $stmt->execute([$_GET['accept']]);
        $db = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($db) {
            $stmt_user = $conexao->prepare("SELECT vip FROM usuarios WHERE id = ?");
            $stmt_user->execute([$db['usuarioid']]);
            $dbu = $stmt_user->fetch(PDO::FETCH_ASSOC);
            
            if($dbu) {
                $ex = explode(' ', $dbu['vip']);
                if(date('Y-m-d H:i:s') < $dbu['vip']){
                    $hunt = 0;
                    $data = explode('-', $ex[0]);
                } else {
                    $hunt = 6;
                    $data = explode('-', date('Y-m-d'));
                }
                $dias = $_GET['days'];
                $novadata = SomarData($data[2].'/'.$data[1].'/'.$data[0], $dias, 0, 0); 
                $data = explode('/', $novadata);
                $vipfim = $data[2].'-'.$data[1].'-'.$data[0].' '.$ex[1];
                
                $stmt_update = $conexao->prepare("UPDATE usuarios SET vip = ?, hunt_restantes = hunt_restantes + ? WHERE id = ?");
                $stmt_update->execute([$vipfim, $hunt, $db['usuarioid']]);
                
                $stmt_vip = $conexao->prepare("UPDATE vip SET status = 'entregue', obs = 'Confirmação válida.<br />VIP de 45 dias entregue ao usuário.<br />Obrigado.' WHERE id = ?");
                $stmt_vip->execute([$_GET['accept']]);
                
                echo "<script>self.location='?p=vip&msg=1'</script>";
            }
        }
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
}

if(isset($_GET['reject'])){
    try {
        $stmt = $conexao->prepare("UPDATE vip SET status = 'cancelado', obs = 'Confirmação não existe.' WHERE id = ?");
        $stmt->execute([$_GET['reject']]);
        echo "<script>self.location='?p=vip'</script>";
    } catch (Exception $e) {
        echo "Erro: " . $e->getMessage();
    }
}

try {
    $stmt = $conexao->prepare("SELECT * FROM vip WHERE status = 'analise' ORDER BY id ASC");
    $stmt->execute();
    $vip_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $vip_results = [];
    echo "Erro ao consultar VIP: " . $e->getMessage();
}
?>

<?php if(isset($_GET['msg'])) echo '<div class="aviso">VIP confirmada.</div><div class="sep"></div>'; ?>

<div class="box_top">Gerenciamento VIP</div>
<div class="box_middle">
    <table width="100%">
        <tr>
            <td><b>Autenticação</b></td>
            <td><b>Usuário ID</b></td>
            <td><b>Meio</b></td>
            <td><b>Ações</b></td>
        </tr>
        <?php if(empty($vip_results)): ?>
        <tr>
            <td colspan="4">Nenhuma confirmação pendente.</td>
        </tr>
        <?php else: ?>
        <?php foreach($vip_results as $vip_item): ?>
        <tr class="table_dados" style="background:#323232;" onmouseover="style.background='#2C2C2C'" onmouseout="style.background='#323232'">
            <td><?php echo htmlspecialchars($vip_item['autenticacao']); ?></td>
            <td><?php echo htmlspecialchars($vip_item['usuarioid']); ?></td>
            <td><?php echo htmlspecialchars($vip_item['meio']); ?></td>
            <td>
                <a href="?p=vip&accept=<?php echo $vip_item['id']; ?>&days=30" onClick="if(confirm('Confirmar 30 dias?')==false) return false;">Aceitar 30 dias</a> |
                <a href="?p=vip&accept=<?php echo $vip_item['id']; ?>&days=45" onClick="if(confirm('Confirmar 45 dias?')==false) return false;">Aceitar 45 dias</a> |
                <a href="?p=vip&reject=<?php echo $vip_item['id']; ?>" onClick="if(confirm('Recusar confirmação?')==false) return false;">Recusar</a>
            </td>
        </tr>
        <?php endforeach; ?>
        <?php endif; ?>
    </table>
</div>
<div class="box_bottom"></div>
