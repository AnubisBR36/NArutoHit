<script>
var taijutsu=0;
var ninjutsu=0;
var genjutsu=0;
</script>
<?php
// Buscar equipamentos do usuário
try {
    $stmt = $conexao->prepare("SELECT i.upgrade, t.* FROM inventario i LEFT OUTER JOIN table_itens t ON i.itemid=t.id WHERE i.usuarioid=? AND status='on' ORDER BY categoria");
    $stmt->execute([$db['id']]);
    $sqle = $stmt;
    $equipamentos = $sqle->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sqle = null;
    $equipamentos = [];
}
?>
<div class="box_top">Meus Equipamentos</div>
<div class="box_middle">Itens atualmente equipados.<div class="sep"></div>
    <table width="100%" cellpadding="0" cellspacing="1">
        <?php if(empty($equipamentos)) { ?>
            <tr><td colspan="3"><div class="aviso">Nenhum item encontrado.</div></td></tr>
        <?php } else { 
            foreach($equipamentos as $dbe) { 
                if(isset($db['vip']) && date('Y-m-d H:i:s')<$db['vip'] && isset($dbe['valor'])) {
                    $dbe['valor']=$dbe['valor']-($dbe['valor']*0.15); 
                }
        ?>
        <tr style="background:#323232;">
            <td align="center" width="140"><img src="_img/equipamentos/<?php echo htmlspecialchars($dbe['imagem']); ?>.jpg" /></td>
            <td valign="top" style="padding:5px;">
                <b><?php echo htmlspecialchars($dbe['nome']); ?><?php if($dbe['upgrade']>0) echo ' +'.intval($dbe['upgrade']); ?></b><br />
                <span class="sub2"><?php echo htmlspecialchars($dbe['descricao']); ?></span><br />
                <b><?php if($dbe['taijutsu']>0) echo '<img src="_img/equipamentos/up.png" width="14" height="14" align="absmiddle" /> [+'.intval($dbe['taijutsu']+$dbe['upgrade']).'] em Taijutsu<br />'; ?>
                <?php if($dbe['ninjutsu']>0) echo '<img src="_img/equipamentos/up.png" width="14" height="14" align="absmiddle" /> [+'.intval($dbe['ninjutsu']+$dbe['upgrade']).'] em Ninjutsu<br />'; ?>
                <?php if($dbe['genjutsu']>0) echo '<img src="_img/equipamentos/up.png" width="14" height="14" align="absmiddle" /> [+'.intval($dbe['genjutsu']+$dbe['upgrade']).'] em Genjutsu<br />'; ?></b>
          </td>
            <td align="center" width="20%">
            	<form method="post" action="?p=inventory" onsubmit="subm.value='Carregando...';subm.disabled=true;">
        		<input type="hidden" id="inv_id" name="inv_id" value="<?php echo isset($c) ? $c->encode($dbe['id'],$chaveuniversal) : $dbe['id']; ?>" />
            	<input type="hidden" id="inv_cat" name="inv_cat" value="<?php echo isset($c) ? $c->encode($dbe['categoria'],$chaveuniversal) : $dbe['categoria']; ?>" />
            	<input type="hidden" id="inv_act" name="inv_act" value="<?php echo isset($c) ? $c->encode('off',$chaveuniversal) : 'off'; ?>" />
        		<input type="submit" id="subm" name="subm" class="botao" value="Retirar" />
        		</form>
            </td>
      </tr>
      <script>
	  	if(<?php echo intval($dbe['taijutsu']); ?>>0 && document.getElementById('atrtai')) document.getElementById('atrtai').innerHTML=((document.getElementById('atrtai').innerHTML)*1)+<?php echo intval($dbe['taijutsu']); ?>;
	  	if(<?php echo intval($dbe['ninjutsu']); ?>>0 && document.getElementById('atrnin')) document.getElementById('atrnin').innerHTML=((document.getElementById('atrnin').innerHTML)*1)+<?php echo intval($dbe['ninjutsu']); ?>;
	  	if(<?php echo intval($dbe['genjutsu']); ?>>0 && document.getElementById('atrgen')) document.getElementById('atrgen').innerHTML=((document.getElementById('atrgen').innerHTML)*1)+<?php echo intval($dbe['genjutsu']); ?>;
	  	</script>
        <?php } } ?>
    </table>
</div>
<div class="box_bottom"></div>