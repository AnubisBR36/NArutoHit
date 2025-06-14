
<?php
require_once('verificar.php');

// Verificar se há mensagens não lidas
try {
    $sqlcount = $conexao->prepare("SELECT COUNT(id) as total FROM mensagens WHERE destino = ? AND status = 'naolido'");
    $sqlcount->execute([$db['id']]);
    $countrow = $sqlcount->fetch(PDO::FETCH_ASSOC);
    $mensagens_nao_lidas = $countrow ? $countrow['total'] : 0;
} catch (PDOException $e) {
    error_log("Erro ao contar mensagens não lidas: " . $e->getMessage());
    $mensagens_nao_lidas = 0;
}

// Verificar total de mensagens
try {
    $sqltotal = $conexao->prepare("SELECT COUNT(id) as total FROM mensagens WHERE destino = ?");
    $sqltotal->execute([$db['id']]);
    $totalrow = $sqltotal->fetch(PDO::FETCH_ASSOC);
    $total_mensagens = $totalrow ? $totalrow['total'] : 0;
} catch (PDOException $e) {
    error_log("Erro ao contar total de mensagens: " . $e->getMessage());
    $total_mensagens = 0;
}

// Handle message sending
if(isset($_POST['sub2']) && isset($_POST['msg_destino']) && isset($_POST['msg_assunto']) && isset($_POST['msg_msg'])) {
    $destinos = explode(',', $_POST['msg_destino']);
    $assunto = trim($_POST['msg_assunto']);
    $mensagem = trim($_POST['msg_msg']);
    $origem = $db['id'];
    
    if(empty($assunto) || empty($mensagem)) {
        echo "<div class='aviso'>Por favor, preencha todos os campos.</div>";
    } elseif(count($destinos) > 10) {
        echo "<div class='aviso'>Máximo de 10 destinatários por mensagem.</div>";
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
                    $stmt_send = $conexao->prepare("INSERT INTO mensagens (origem, destino, assunto, msg, data, status) VALUES (?, ?, ?, ?, datetime('now'), 'naolido')");
                    $stmt_send->execute([$origem, $destino_user['id'], $assunto, $mensagem]);
                    $enviadas++;
                } else {
                    $erros++;
                }
            } catch (PDOException $e) {
                error_log("Erro ao enviar mensagem: " . $e->getMessage());
                $erros++;
            }
        }
        
        if($enviadas > 0) {
            echo "<div class='aviso'>$enviadas mensagem(ns) enviada(s) com sucesso!</div>";
            if($erros > 0) {
                echo "<div class='aviso'>$erros usuário(s) não encontrado(s).</div>";
            }
            echo "<script>setTimeout(function(){ self.location='?p=messages&type=r'; }, 2000);</script>";
            return;
        } else {
            echo "<div class='aviso'>Nenhum usuário encontrado.</div>";
        }
    }
}

// Determinar tipo de página
$type = isset($_GET['type']) ? $_GET['type'] : 'r';
?>

<div class="box_top">Sistema de Mensagens</div>
<div class="box_middle">
    <div style="text-align: center; margin-bottom: 10px;">
        <a href="?p=messages&type=r" <?php if($type == 'r') echo 'style="font-weight: bold;"'; ?>>Mensagens Recebidas (<?php echo $total_mensagens; ?>)</a> | 
        <a href="?p=messages&type=e" <?php if($type == 'e') echo 'style="font-weight: bold;"'; ?>>Mensagens Enviadas</a> | 
        <a href="?p=messages&type=form" <?php if($type == 'form') echo 'style="font-weight: bold;"'; ?>>Enviar Mensagem</a>
    </div>
    <div class="sep"></div>
    
    <?php
    // Handle actions first
    if($type == 'delete' && isset($_GET['id'])) {
        $delete_id = (int)$_GET['id'];
        try {
            $delete_stmt = $conexao->prepare("DELETE FROM mensagens WHERE id=? AND (destino=? OR origem=?)");
            $delete_stmt->execute([$delete_id, $db['id'], $db['id']]);
            echo "<div class='aviso'>Mensagem excluída com sucesso!</div>";
            echo "<script>setTimeout(function(){ self.location='?p=messages'; }, 1500);</script>";
        } catch (PDOException $e) {
            echo "<div class='aviso'>Erro ao excluir mensagem.</div>";
        }
        return;
    }
    
    if($type == 'view') {
        include('messages_view.php');
    } elseif($type == 'form') {
        include('messages_form.php');
    } elseif($type == 'e') {
        include('messages_e.php');
    } else {
        include('messages.php');
    }
    ?>
</div>
<div class="box_bottom"></div>
