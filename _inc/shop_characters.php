
<?php
try {
    $sqls = $conexao->prepare("SELECT * FROM table_personagens WHERE nivel<=? ORDER BY nivel ASC");
    $sqls->execute([$db['nivel']]);
    $dbs = $sqls->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo '<tr><td colspan="3"><div class="aviso">Erro ao carregar personagens ou nenhum personagem encontrado.</div></td></tr>';
    echo '</table>';
    return;
}

if(!$dbs) {
    echo '<tr><td colspan="3"><div class="aviso">Nenhum personagem encontrado.</div></td></tr>';
    echo '</table>';
    return;
}

try {
    $sqlp = $conexao->prepare("SELECT * FROM personagens WHERE usuarioid=?");
    $sqlp->execute([$db['id']]);
    $dbp = $sqlp->fetch(PDO::FETCH_ASSOC);
    if(!$dbp) {
        $dbp = array(); // Inicializar array vazio como fallback
    }
} catch (PDOException $e) {
    $dbp = array();
}

require_once('funcoes.php');
?>
<table width="100%" cellpadding="0" cellspacing="1">
    <?php $count=0; do{ 
        // Verificar se o personagem existe no array antes de acessar
        $personagem_desbloqueado = isset($dbp[$dbs['personagem']]) ? $dbp[$dbs['personagem']] : 0;
        if($personagem_desbloqueado == 0){ $count++; ?>
    <tr style="background:#323232;" onmouseover="style.background='#2C2C2C'" onmouseout="style.background='#323232'">
    	<td align="center" width="200"><img src="_img/personagens/unlock_<?php echo $dbs['personagem']; ?>.jpg" /></td>
        <td align="center" valign="middle" style="padding:5px;">
        	<b>Personagem</b><br />
        	<span class="sub2"><?php fpersonagem($dbs['personagem']); ?></span><br />
      </td>
      <td align="center" width="30%">
        	<b>Nível Mínimo</b><br />
            <span class="sub2">Nível <?php echo $dbs['nivel']; ?></span><br /><br />
            <form method="post" action="?p=shop" onsubmit="subm.value='Carregando...';subm.disabled=true;">
            <input type="hidden" id="char_id" name="char_id" value="<?php echo $c->encode($dbs['id'],$chaveuniversal); ?>" />
            <input type="hidden" id="char_nivel" name="char_nivel" value="<?php echo $c->encode($dbs['nivel'],$chaveuniversal); ?>" />
            <input type="hidden" id="char_char" name="char_char" value="<?php echo $c->encode($dbs['personagem'],$chaveuniversal); ?>" />
            <input type="submit" id="subm" name="subm" class="botao" value="Desbloquear" />
            </form>
        </td>
  </tr>
    <?php }} while($dbs=$sqls->fetch(PDO::FETCH_ASSOC)); ?>
    <?php if($count==0){ echo '<tr><td colspan="3"><div class="aviso">Nenhum personagem encontrado.</div></td></tr>'; } ?>
</table>
<?php
// PDO automatically frees result sets
?>
