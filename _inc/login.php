<div class="box_top">Login</div>
<div class="box_middle">Digite seu login e senha nos campos abaixo para acessar o jogo.<div class="sep"></div>
	<?php if(isset($_GET['erro'])){
		switch($_GET['erro']){
			case 'ban': $msg='Conta banida.'; break;
			case 1: $msg='Digite uma <b>SENHA</b> v&aacute;lida.'; break;
			case 2: $msg='Nenhum usu&aacute;rio encontrado com o login informado.'; break;
			case 3: $msg='Senha digitada incorreta.'; break;
			case 4: if(isset($_GET['date'])){ $data=$c->decode($_GET['date'],$chaveuniversal); $ex=explode('_',$data); $data=explode('-',$ex[0]); } else $data='0000-00-00_00:00:00'; $msg='Sua conta está em período de férias.<br />Não é possível realizar o login até o dia <b>'.$data[2].'/'.$data[1].'/'.$data[0].', às '.$ex[1].' horas</b>.'; break;
			//case 5: $msg='Sua conta ainda está inativa.<br />Verifique o email de ativação enviado pelo sistema.'; break;
		}
		echo '<div class="aviso">'.$msg.'</div><div class="sep"></div>';
	} ?>
    <?php if(isset($_GET['reason'])) echo '<div class="aviso">Você foi deslogado pois outro usuário acessou sua conta.</div><div class="sep"></div>'; ?>
    <?php if(isset($_GET['ban'])) echo '<div class="aviso">Conta banida.</div><div class="sep"></div>'; ?>
    <fieldset>
    	<legend>Dados de Acesso</legend>
        	<form method="post" action="?p=login" id="login" name="login" style="background:url(_img/login<?php echo rand(1,2); ?>.jpg) no-repeat right;" onsubmit="subm.value='Carregando...';subm.disabled=true;">
            	<span class="destaque">Servidor:</span><br />
                <select>
                	<option onclick="location.href='http://www.narutohit.net/?p=login'" selected="selected">Servidor 01</option>
                    <option onclick="location.href='http://servidor02.narutohit.net/?p=login'">Servidor 02</option>
                </select><br />
                <span class="sub2">Escolha o servidor em que deseja jogar.</span><br /><br />
            	<span class="destaque">Login:</span><br />
                <input type="text" id="login_login" name="login_login" maxlength="15" onfocus="className='input'" onblur="className=''" /><br />
                <span class="sub2">Digite seu login de acesso (nome do usu&aacute;rio).</span><br /><br />
                <span class="destaque">Senha:</span><br />
                <input type="password" id="login_senha" name="login_senha" maxlength="15" onfocus="className='input'" onblur="className=''" /><br />
                <span class="sub2">Digite sua senha de acesso.</span><br /><br />
                <input type="submit" id="subm" name="subm" class="botao" value="Acessar" /><br /><br />
                <a href="?p=terms">Registrar no narutoHIT</a> | <a href="?p=recover">Nova Senha</a>
            </form>
	</fieldset>
</div>
<div class="box_bottom"></div>
<script>document.forms[0].login_login.focus()</script>
<?php
if(!defined('__INCLUDED__')){
    define('__INCLUDED__', true);
}
?>