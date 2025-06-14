<?php
// Processar envio de mensagem
if(isset($_POST['sub2']) && isset($_POST['msg_destino']) && isset($_POST['msg_assunto']) && isset($_POST['msg_msg'])) {
    $destinos = explode(',', $_POST['msg_destino']);
    $assunto = trim($_POST['msg_assunto']);
    $mensagem = trim($_POST['msg_msg']);
    $origem = $db['id'];

    if(empty($assunto) || empty($mensagem)) {
        $msg_resultado = '<div class="aviso">Por favor, preencha todos os campos.</div>';
    } elseif(count($destinos) > 10) {
        $msg_resultado = '<div class="aviso">Máximo de 10 destinatários por mensagem.</div>';
    } else {
        $enviadas = 0;
        $erros = 0;

        foreach($destinos as $destino_nome) {
            $destino_nome = trim($destino_nome);
            if(empty($destino_nome)) continue;

            try {
                $stmt_user = $conexao->prepare("SELECT id FROM usuarios WHERE usuario = ?");
                $stmt_user->execute([$destino_nome]);
                $destino_user = $stmt_user->fetch(PDO::FETCH_ASSOC);

                if($destino_user) {
                    try {
            // Debug: Verificar dados antes de inserir
            error_log("Enviando mensagem - Origem: " . $db['id'] . ", Destino: " . $destino_user['id'] . ", Assunto: " . $assunto);

            // Inserir mensagem com data atual
            $stmt_send = $conexao->prepare("INSERT INTO mensagens (origem, destino, assunto, msg, data, status) VALUES (?, ?, ?, ?, datetime('now'), 'naolido')");
            $result = $stmt_send->execute([$origem, $destino_user['id'], $assunto, $mensagem]);

            if($result) {
                // Verificar se foi realmente inserido
                $lastId = $conexao->lastInsertId();
                error_log("Mensagem inserida com ID: " . $lastId);
                $enviadas++;
            } else {
                $errorInfo = $stmt_send->errorInfo();
                error_log("Erro ao inserir mensagem: " . print_r($errorInfo, true));
                $erros++;
            }
        } catch (PDOException $e) {
            error_log("Exceção ao enviar mensagem: " . $e->getMessage());
             $erros++;
        }
                   
                } else {
                    $erros++;
                }
            } catch (PDOException $e) {
                $erros++;
            }
        }

        if($enviadas > 0) {
            $msg_resultado = "<div class='aviso'>$enviadas mensagem(ns) enviada(s) com sucesso!</div>";
            if($erros > 0) {
                $msg_resultado .= "<div class='aviso'>$erros usuário(s) não encontrado(s).</div>";
            }
            echo $msg_resultado . "<script>setTimeout(function(){ self.location='?p=messages&type=r'; }, 2000);</script>";
        } else {
            $msg_resultado = '<div class="aviso">Nenhum usuário encontrado.</div>';
        }
    }
}
?>

<div class="box_top">Enviar Mensagem</div>
<div class="box_middle" style="display:<?php if((isset($_GET['destiny']))or(isset($_GET['subject']))) echo 'none'; else echo 'block'; ?>;">
	<div style="border:1px dotted #999999;padding:5px;text-align:center;" id="div1">
    <a href="#" onclick="document.getElementById('div1').style.display='none';document.getElementById('div2').style.display='block';">Clique aqui para enviar uma mensagem.</a>
    <?php 
    if(isset($msg_resultado)) {
        echo '<div class="sep"></div>' . $msg_resultado;
    }
    if(isset($_GET['msg'])){
		switch($_GET['msg']){
			case 0: $msg='Mensagem enviada com sucesso!'; break;
			case 1: $msg='Usuário não encontrado.'; break;
		}
	echo '<div class="sep"></div><div class="aviso">'.$msg.'</div>'; } ?>
    </div>
</div>
<div id="div2" class="box_middle" style="display:<?php if((isset($_GET['destiny']))or(isset($_GET['subject']))) echo 'block'; else echo 'none'; ?>;">Utilize este formulário para enviar mensagens à outros jogadores. Para formatar o texto, utilize os ícones da barra de formatação. Você pode inserir imagens e links, clicando nos últimos botões da barra, e preenchendo os campos solicitados.<div class="sep"></div>
	<?php if(isset($_GET['msg'])){
		switch($_GET['msg']){
			case 0: $msg='Mensagem enviada com sucesso!'; break;
			case 1: $msg='Usuário não encontrado.'; break;
		}
	echo '<div class="aviso">'.$msg.'</div><div class="sep"></div>'; } ?>
    <fieldset><legend>Enviar Mensagem</legend>
    	<form method="post" action="?p=messages&type=form" onsubmit="subm.value='Carregando...';subm.disabled=true;">
       	  <input type="hidden" id="msg_origem" name="msg_origem" value="<?php echo $db['usuario']; ?>" />
            <span class="destaque">Destino(s) da Mensagem:</span><br />
            <input type="text" id="msg_destino" name="msg_destino" maxlength="159" onfocus="className='input'" onblur="className=''" <?php if(isset($_GET['destiny'])) echo 'value="'.$_GET['destiny'].'"'; ?>/><br />
            <span class="sub2">Digite o nome dos usuários que receberão sua mensagem (para mais de um usuário, separe por vírgula - máximo de 10 usuários por mensagem).</span><br /><div class="sep"></div>
            <span class="destaque">Assunto da Mensagem:</span><br />
            <input type="text" id="msg_assunto" name="msg_assunto" maxlength="60" onfocus="className='input'" onblur="className=''" <?php if(isset($_GET['subject'])) echo 'value="'.$_GET['subject'].'"'; ?>/><br />
            <span class="sub2">Digite o assunto da mensagem.</span><br /><div class="sep"></div>
            <span class="destaque">Mensagem:</span>
            <textarea id="msg_msg" name="msg_msg" style="width:100%;"></textarea>
            <span class="sub2">Mensagem a ser enviada. Apenas os primeiros 2048 caracteres serão válidos.</span>
            <div class="sep"></div>
            <div align="center"><input type="submit" id="subm" name="sub2" class="botao" value="Enviar Mensagem"></div>
        </form>
    </fieldset></div>
<div class="box_bottom"></div>
```