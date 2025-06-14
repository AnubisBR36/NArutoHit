<?php
require_once('../_inc/conexao.php');

// Verificar se o usuário está logado
if(!isset($_SESSION['logado']) && !isset($_SESSION['userid']) && !isset($_SESSION['uid'])) {
    echo "<script>window.location.href='../index.php';</script>";
    exit;
}

// Determinar o ID do usuário logado
$user_id = $_SESSION['logado'] ?? $_SESSION['userid'] ?? $_SESSION['uid'];

// Buscar dados do usuário logado
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$user_id]);
$usuario_logado = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar se o usuário é administrador ou moderador
if(!$usuario_logado || ($usuario_logado['adm'] != 1 && $usuario_logado['adm'] != 2)) {
    echo "<div class='box_top'>Acesso Negado</div>";
    echo "<div class='box_middle'>Você não tem permissão para acessar esta área.</div>";
    echo "<div class='box_bottom'></div>";
    exit;
}

$is_admin = ($usuario_logado['adm'] == 1);
$is_mod = ($usuario_logado['adm'] == 2);

// Processar ações
if(isset($_POST['action'])) {
    if($_POST['action'] == 'ban_user' && isset($_POST['user_id']) && isset($_POST['ban_days']) && isset($_POST['ban_motivo'])) {
        $user_id_ban = (int)$_POST['user_id'];
        $ban_days = (int)$_POST['ban_days'];
        $ban_motivo = trim($_POST['ban_motivo']);

        if($ban_days > 0 && !empty($ban_motivo)) {
            $ban_fim = date('Y-m-d H:i:s', time() + ($ban_days * 24 * 60 * 60));

            $stmt = $conexao->prepare("UPDATE usuarios SET status = 'banido', ban_fim = ?, ban_motivo = ? WHERE id = ?");
            if($stmt->execute([$ban_fim, $ban_motivo, $user_id_ban])) {
                echo "<div style='color: green; margin: 10px 0;'>✅ Usuário banido com sucesso!</div>";
            }
        }
    }

    if($_POST['action'] == 'unban_user' && isset($_POST['user_id'])) {
        $user_id_unban = (int)$_POST['user_id'];

        $stmt = $conexao->prepare("UPDATE usuarios SET status = 'ativo', ban_fim = '2000-01-01 00:00:00', ban_motivo = '' WHERE id = ?");
        if($stmt->execute([$user_id_unban])) {
            echo "<div style='color: green; margin: 10px 0;'>✅ Usuário desbanido com sucesso!</div>";
        }
    }

    if($_POST['action'] == 'edit_user' && $is_admin && isset($_POST['user_id'])) {
        $user_id_edit = (int)$_POST['user_id'];
        $energia = (int)$_POST['energia'];
        $taijutsu = (int)$_POST['taijutsu'];
        $ninjutsu = (int)$_POST['ninjutsu'];
        $genjutsu = (int)$_POST['genjutsu'];
        $exp = (int)$_POST['exp'];
        $nivel = (int)$_POST['nivel'];
        $yens = (int)$_POST['yens'];
        $personagem = $_POST['personagem'];
        $vila = (int)$_POST['vila'];
        $vitorias = (int)$_POST['vitorias'];
        $derrotas = (int)$_POST['derrotas'];
        $empates = (int)$_POST['empates'];

        $stmt = $conexao->prepare("UPDATE usuarios SET energia = ?, taijutsu = ?, ninjutsu = ?, genjutsu = ?, exp = ?, nivel = ?, yens = ?, personagem = ?, vila = ?, vitorias = ?, derrotas = ?, empates = ? WHERE id = ?");
        if($stmt->execute([$energia, $taijutsu, $ninjutsu, $genjutsu, $exp, $nivel, $yens, $personagem, $vila, $vitorias, $derrotas, $empates, $user_id_edit])) {
            echo "<div style='color: green; margin: 10px 0;'>✅ Usuário editado com sucesso!</div>";
        }
    }
}

// Buscar usuário para editar se especificado
$edit_user = null;
if(isset($_GET['edit']) && $is_admin) {
    $edit_id = (int)$_GET['edit'];
    $stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Buscar lista de usuários
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;

$where_clause = "";
$params = [];
if(!empty($search)) {
    $where_clause = "WHERE usuario LIKE ?";
    $params[] = "%$search%";
}

$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM usuarios $where_clause");
$stmt->execute($params);
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_users / $per_page);

$stmt = $conexao->prepare("SELECT * FROM usuarios $where_clause ORDER BY id DESC LIMIT ? OFFSET ?");
$params[] = $per_page;
$params[] = $offset;
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="box_top">
    🛡️ Painel de Administração 
    <?php if($is_admin): ?>
        (Administrador)
    <?php else: ?>
        (Moderador)
    <?php endif; ?>
</div>
<div class="box_middle">

    <?php if($edit_user && $is_admin): ?>
        <!-- Formulário de Edição -->
        <div style="border: 2px solid #4CAF50; padding: 15px; margin: 10px 0; background: #f0fff0;">
            <h3>📝 Editando: <?php echo htmlspecialchars($edit_user['usuario']); ?></h3>

            <div style="display: flex; align-items: center; margin: 10px 0;">
                <?php
                $avatar_path = '../_img/personagens/no_avatar.jpg';
                if($edit_user['personagem'] && $edit_user['avatar']) {
                    $avatar_path = "../_img/personagens/" . $edit_user['personagem'] . "/" . $edit_user['avatar'] . ".jpg";
                }
                ?>
                <img src="<?php echo $avatar_path; ?>" style="width: 64px; height: 64px; margin-right: 15px;">
                <div>
                    <strong>Usuário:</strong> <?php echo htmlspecialchars($edit_user['usuario']); ?><br>
                    <strong>Vila:</strong> 
                    <?php 
                    $vilas = [1 => 'Folha', 2 => 'Areia', 3 => 'Névoa', 4 => 'Pedra', 5 => 'Nuvem', 6 => 'Som', 7 => 'Chuva', 8 => 'Akatsuki'];
                    echo $vilas[$edit_user['vila']] ?? 'Desconhecida';
                    ?><br>
                    <strong>Status:</strong> <?php echo ucfirst($edit_user['status']); ?>
                </div>
            </div>

            <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <input type="hidden" name="action" value="edit_user">
                <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">

                <div>
                    <h4>⚡ Atributos de Combate</h4>
                    <label>Energia: <input type="number" name="energia" value="<?php echo $edit_user['energia']; ?>" min="0" max="1000"></label><br>
                    <label>Taijutsu: <input type="number" name="taijutsu" value="<?php echo $edit_user['taijutsu']; ?>" min="1" max="999"></label><br>
                    <label>Ninjutsu: <input type="number" name="ninjutsu" value="<?php echo $edit_user['ninjutsu']; ?>" min="1" max="999"></label><br>
                    <label>Genjutsu: <input type="number" name="genjutsu" value="<?php echo $edit_user['genjutsu']; ?>" min="1" max="999"></label><br>
                </div>

                <div>
                    <h4>📊 Progressão</h4>
                    <label>Nível: <input type="number" name="nivel" value="<?php echo $edit_user['nivel']; ?>" min="1" max="999"></label><br>
                    <label>Experiência: <input type="number" name="exp" value="<?php echo $edit_user['exp']; ?>" min="0"></label><br>
                    <label>Yens: <input type="number" name="yens" value="<?php echo $edit_user['yens']; ?>" min="0"></label><br>
                </div>

                <div>
                    <h4>👤 Personagem & Vila</h4>
                    <label>Personagem: 
                        <select name="personagem">
                            <option value="naruto" <?php echo $edit_user['personagem'] == 'naruto' ? 'selected' : ''; ?>>Naruto</option>
                            <option value="sasuke" <?php echo $edit_user['personagem'] == 'sasuke' ? 'selected' : ''; ?>>Sasuke</option>
                            <option value="sakura" <?php echo $edit_user['personagem'] == 'sakura' ? 'selected' : ''; ?>>Sakura</option>
                            <option value="kakashi" <?php echo $edit_user['personagem'] == 'kakashi' ? 'selected' : ''; ?>>Kakashi</option>
                        </select>
                    </label><br>

                    <label>Vila: 
                        <select name="vila">
                            <?php foreach($vilas as $id => $nome): ?>
                                <option value="<?php echo $id; ?>" <?php echo $edit_user['vila'] == $id ? 'selected' : ''; ?>><?php echo $nome; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label><br>
                </div>

                <div>
                    <h4>🏆 Estatísticas</h4>
                    <label>Vitórias: <input type="number" name="vitorias" value="<?php echo $edit_user['vitorias']; ?>" min="0"></label><br>
                    <label>Derrotas: <input type="number" name="derrotas" value="<?php echo $edit_user['derrotas']; ?>" min="0"></label><br>
                    <label>Empates: <input type="number" name="empates" value="<?php echo $edit_user['empates']; ?>" min="0"></label><br>
                </div>

                <div style="grid-column: 1 / -1; text-align: center;">
                    <button type="submit" style="background: #4CAF50; color: white; padding: 10px 20px; border: none; cursor: pointer;">💾 Salvar Alterações</button>
                    <a href="?p=adm" style="background: #f44336; color: white; padding: 10px 20px; text-decoration: none; margin-left: 10px;">❌ Cancelar</a>
                </div>
            </form>
        </div>
    <?php else: ?>

        <!-- Ferramentas de Administração -->
        <div style="background: #e3f2fd; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #2196F3;">
            <h3>🛠️ Ferramentas de Administração</h3>
            <p>Ferramentas para gerenciamento do banco de dados e sistema.</p>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px;">
                <a href="verificar_banco.php" style="background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; text-align: center;">
                    🔍 Verificar Banco
                </a>
                <a href="verificar_tabelas_sqlite.php" style="background: #FF9800; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; text-align: center;">
                    🔧 Verificar Tabelas
                </a>
                <a href="criar_contas_teste.php" style="background: #9C27B0; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; text-align: center;">
                    🧪 Criar Contas Teste
                </a>
                <a href="criar_contas_teste_mapa.php" style="background: #607D8B; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; text-align: center;">
                    🗺️ Conta Teste Mapa
                </a>
                <a href="limpar_ip.php" style="background: #FFC107; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; text-align: center;">
                    🧹 Limpar IPs
                </a>
                <a href="limpar_banco.php" style="background: #F44336; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; text-align: center;">
                    🗑️ Limpar Banco
                </a>
            </div>
        </div>

        <!-- Sistema de Notícias -->
        <div style="background: #e8f5e8; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #4CAF50;">
            <h3>📰 Sistema de Notícias</h3>
            <p>Gerencie as notícias que aparecem no site do jogo.</p>
            <a href="../news/admin.php" style="background: #2196F3; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px; margin-right: 10px;">
                🛠️ Administrar Notícias
            </a>
            <a href="../news/index.php" style="background: #4CAF50; color: white; padding: 10px 15px; text-decoration: none; border-radius: 3px;">
                👁️ Ver Notícias
            </a>
        </div>

        <!-- Busca de Usuários -->
        <form method="GET" style="margin: 10px 0;">
            <input type="hidden" name="p" value="adm">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Buscar usuário..." style="padding: 5px; width: 200px;">
            <button type="submit" style="padding: 5px 10px;">🔍 Buscar</button>
        </form>

        <!-- Lista de Usuários -->
        <div style="overflow-x: auto;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f0f0f0;">
                        <th>ID</th>
                        <th>Usuário</th>
                        <th>Nível</th>
                        <th>Vila</th>
                        <th>Status</th>
                        <th>Yens</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['usuario']); ?></td>
                            <td><?php echo $user['nivel']; ?></td>
                            <td><?php echo $vilas[$user['vila']] ?? 'N/A'; ?></td>
                            <td>
                                <?php if($user['status'] == 'banido'): ?>
                                    <span style="color: red;">Banido</span>
                                    <?php if($user['ban_fim'] > date('Y-m-d H:i:s')): ?>
                                        <br><small>Até: <?php echo date('d/m/Y H:i', strtotime($user['ban_fim'])); ?></small>
                                        <br><small>Motivo: <?php echo htmlspecialchars($user['ban_motivo']); ?></small>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color: green;"><?php echo ucfirst($user['status']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($user['yens']); ?></td>
                            <td>
                                <?php if($is_admin): ?>
                                    <a href="?p=adm&edit=<?php echo $user['id']; ?>" style="background: #2196F3; color: white; padding: 3px 8px; text-decoration: none; margin: 2px;">✏️ Editar</a>
                                <?php endif; ?>

                                <?php if($user['status'] == 'banido'): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="unban_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" style="background: #4CAF50; color: white; padding: 3px 8px; border: none; cursor: pointer;">✅ Desbanir</button>
                                    </form>
                                <?php else: ?>
                                    <button onclick="showBanForm(<?php echo $user['id']; ?>, '<?php echo addslashes($user['usuario']); ?>')" style="background: #f44336; color: white; padding: 3px 8px; border: none; cursor: pointer;">🔨 Banir</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <?php if($total_pages > 1): ?>
            <div style="text-align: center; margin: 20px 0;">
                <?php for($i = 1; $i <= $total_pages; $i++): ?>
                    <?php if($i == $page): ?>
                        <strong><?php echo $i; ?></strong>
                    <?php else: ?>
                        <a href="?p=adm&page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                    <?php if($i < $total_pages) echo " | "; ?>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>
<div class="box_bottom"></div>

<!-- Modal de Ban -->
<div id="banModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 20px; border-radius: 5px; max-width: 400px; width: 90%;">
        <h3>🔨 Banir Usuário</h3>
        <form method="POST" id="banForm">
            <input type="hidden" name="action" value="ban_user">
            <input type="hidden" name="user_id" id="ban_user_id">

            <p><strong>Usuário:</strong> <span id="ban_username"></span></p>

            <label>Dias de ban: 
                <select name="ban_days" required>
                    <option value="1">1 dia</option>
                    <option value="3">3 dias</option>
                    <option value="7">7 dias (1 semana)</option>
                    <option value="15">15 dias</option>
                    <option value="30">30 dias (1 mês)</option>
                    <option value="90">90 dias (3 meses)</option>
                    <option value="365">365 dias (1 ano)</option>
                    <option value="3650">10 anos (permanente)</option>
                </select>
            </label><br><br>

            <label>Motivo do ban:<br>
                <textarea name="ban_motivo" required style="width: 100%; height: 80px;" placeholder="Descreva o motivo do banimento..."></textarea>
            </label><br><br>

            <button type="submit" style="background: #f44336; color: white; padding: 10px 15px; border: none; cursor: pointer;">🔨 Confirmar Ban</button>
            <button type="button" onclick="closeBanModal()" style="background: #757575; color: white; padding: 10px 15px; border: none; cursor: pointer; margin-left: 10px;">❌ Cancelar</button>
        </form>
    </div>
</div>

<script>
function showBanForm(userId, username) {
    document.getElementById('ban_user_id').value = userId;
    document.getElementById('ban_username').textContent = username;
    document.getElementById('banModal').style.display = 'block';
}

function closeBanModal() {
    document.getElementById('banModal').style.display = 'none';
}

// Fechar modal ao clicar fora
document.getElementById('banModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeBanModal();
    }
});
</script>
