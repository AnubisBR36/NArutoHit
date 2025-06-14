
<?php
require_once('Encrypt.php');
$c=new C_Encrypt();

if(isset($_POST['fir_avatar'])){
	$avatar=$c->decode($_POST['fir_avatar'],$chaveuniversal);
	vn($avatar);
	
	try {
		// Atualizar avatar do usuário
		$stmt = $conexao->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
		$stmt->execute([$avatar, $db['id']]);
		
		// Verificar se a tabela personagens existe e inserir registro se necessário
		$stmt_check = $conexao->prepare("SELECT name FROM sqlite_master WHERE type='table' AND name='personagens'");
		$stmt_check->execute();
		$table_exists = $stmt_check->fetch();
		
		if($table_exists) {
			// Verificar se já existe um registro para este usuário
			$stmt_exists = $conexao->prepare("SELECT COUNT(*) FROM personagens WHERE usuarioid = ?");
			$stmt_exists->execute([$db['id']]);
			$user_exists = $stmt_exists->fetchColumn();
			
			if(!$user_exists) {
				$stmt_insert = $conexao->prepare("INSERT INTO personagens (usuarioid) VALUES (?)");
				$stmt_insert->execute([$db['id']]);
			}
		}
		
		// Corrigir nome de usuário removendo espaços
		$novo = str_replace(' ','_',$db['usuario']);
		if($db['usuario'] != $novo) {
			$stmt_update = $conexao->prepare("UPDATE usuarios SET usuario = ? WHERE id = ?");
			$stmt_update->execute([$novo, $db['id']]);
		}
		
		echo "<script>self.location='?p=home'</script>";
		
	} catch (PDOException $e) {
		error_log("Erro no first.php: " . $e->getMessage());
		echo "<script>alert('Erro ao salvar avatar. Tente novamente.'); self.location='?p=first';</script>";
	}
}
?>
<div class="box_top">Primeiro Login</div>
<div class="box_middle">Seja bem-vindo ao narutoHIT! Como este é seu primeiro login no jogo, queremos que você escolha um avatar para representação. Marque uma das 9 opções abaixo, e clique no botão para confirmar. Lembramos que esta função não interfere na força da sua conta. A troca de avatares pode ser feita uma vez por dia (ilimitado para jogadores VIP).<div class="sep"></div>
	<form method="post" action="?p=first" onsubmit="subm.value='Carregando...';subm.disabled=true;">
	<fieldset><legend>Avatar</legend>
    <div align="center">
    <table width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td width="150" align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/1.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar1').checked=true" /></td>
        <td width="150" align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/2.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar2').checked=true" /></td>
        <td width="150" align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/3.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar3').checked=true" /></td>
      </tr>
      <tr>
        <td align="center"><input type="radio" id="fir_avatar1" name="fir_avatar" value="<?php echo $c->encode('1',$chaveuniversal); ?>" checked="checked" /></td>
        <td align="center"><input type="radio" id="fir_avatar2" name="fir_avatar" value="<?php echo $c->encode('2',$chaveuniversal); ?>" /></td>
        <td align="center"><input type="radio" id="fir_avatar3" name="fir_avatar" value="<?php echo $c->encode('3',$chaveuniversal); ?>" /></td>
      </tr>
      <tr>
        <td colspan="3" align="center"><div class="sep"></div></td>
        </tr>
      <tr>
        <td align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/4.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar4').checked=true" /></td>
        <td align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/5.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar5').checked=true" /></td>
        <td align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/6.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar6').checked=true" /></td>
      </tr>
      <tr>
        <td align="center"><input type="radio" id="fir_avatar4" name="fir_avatar" value="<?php echo $c->encode('4',$chaveuniversal); ?>" /></td>
        <td align="center"><input type="radio" id="fir_avatar5" name="fir_avatar" value="<?php echo $c->encode('5',$chaveuniversal); ?>" /></td>
        <td align="center"><input type="radio" id="fir_avatar6" name="fir_avatar" value="<?php echo $c->encode('6',$chaveuniversal); ?>" /></td>
      </tr>
      <tr>
        <td colspan="3" align="center"><div class="sep"></div></td>
        </tr>
      <tr>
        <td align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/7.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar7').checked=true" /></td>
        <td align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/8.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar8').checked=true" /></td>
        <td align="center" bgcolor="#444444"><img src="_img/personagens/<?php echo $db['personagem']; ?>/9.jpg" width="130" height="120" onclick="document.getElementById('fir_avatar9').checked=true" /></td>
      </tr>
      <tr>
        <td align="center"><input type="radio" id="fir_avatar7" name="fir_avatar" value="<?php echo $c->encode('7',$chaveuniversal); ?>" /></td>
        <td align="center"><input type="radio" id="fir_avatar8" name="fir_avatar" value="<?php echo $c->encode('8',$chaveuniversal); ?>" /></td>
        <td align="center"><input type="radio" id="fir_avatar9" name="fir_avatar" value="<?php echo $c->encode('9',$chaveuniversal); ?>" /></td>
      </tr>
    </table>
    <div class="sep"></div>
    <input type="submit" id="subm" name="subm" class="botao" value="Confirmar" />
    </div>
    </fieldset>
    </form>
</div>
<div class="box_bottom"></div>
