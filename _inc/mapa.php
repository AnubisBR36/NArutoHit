<?php
if(!isset($_SESSION['logado'])) {
    header("Location: index.php?p=login");
    exit;
}




// Verificar se o jogador tem posição no mapa
$stmt = $conexao->prepare("SELECT * FROM players_positions WHERE player_id = ?");
$stmt->execute([$_SESSION['logado']]);
$position = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não tem posição, colocar em Konoha
if(!$position) {
    $stmt = $conexao->prepare("INSERT INTO players_positions (player_id, current_page_id, x, y) VALUES (?, 1, 10, 10)");
    $stmt->execute([$_SESSION['logado']]);
    $position = ['player_id' => $_SESSION['logado'], 'current_page_id' => 1, 'x' => 10, 'y' => 10];
}

// Buscar informações da página atual
$stmt = $conexao->prepare("SELECT * FROM maps_pages WHERE id = ?");
$stmt->execute([$position['current_page_id']]);
$current_page = $stmt->fetch(PDO::FETCH_ASSOC);

// Se a página não existe, colocar o jogador em Konoha (página 1)
if(!$current_page) {
    $stmt = $conexao->prepare("UPDATE players_positions SET current_page_id = 1 WHERE player_id = ?");
    $stmt->execute([$_SESSION['logado']]);

    // Buscar página de Konoha
    $stmt = $conexao->prepare("SELECT * FROM maps_pages WHERE id = 1");
    $stmt->execute();
    $current_page = $stmt->fetch(PDO::FETCH_ASSOC);

    // Se ainda não existe, criar página básica
    if(!$current_page) {
        $stmt = $conexao->prepare("INSERT INTO maps_pages (id, name, grid_data) VALUES (1, 'Konoha', '{\"type\":\"vila\",\"obstacles\":[]}')");
        $stmt->execute();
        $current_page = ['id' => 1, 'name' => 'Konoha', 'grid_data' => '{"type":"vila","obstacles\":[]}', 'north_page_id' => null, 'south_page_id' => null, 'east_page_id' => null, 'west_page_id' => null];
    }
}

// Buscar outros jogadores na mesma página
$stmt = $conexao->prepare("
    SELECT pp.*, u.usuario, u.avatar, u.personagem 
    FROM players_positions pp 
    JOIN usuarios u ON pp.player_id = u.id 
    WHERE pp.current_page_id = ? AND pp.player_id != ?
");
$stmt->execute([$position['current_page_id'], $_SESSION['logado']]);
$other_players = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Processar movimento se enviado
if(isset($_POST['move_x']) && isset($_POST['move_y'])) {
    $new_x = intval($_POST['move_x']);
    $new_y = intval($_POST['move_y']);

    // Verificar cooldown
    $last_move = strtotime($position['last_move_time']);
    $now = time();
    if($now - $last_move >= 1.5) {
        // Verificar se o movimento é válido (máximo 2 quadrados)
        $distance = abs($new_x - $position['x']) + abs($new_y - $position['y']);
        if($distance <= 2 && $new_x >= 0 && $new_x < 20 && $new_y >= 0 && $new_y < 20) {
            $stmt = $conexao->prepare("UPDATE players_positions SET x = ?, y = ?, last_move_time = CURRENT_TIMESTAMP WHERE player_id = ?");
            $stmt->execute([$new_x, $new_y, $_SESSION['logado']]);
            $position['x'] = $new_x;
            $position['y'] = $new_y;
        }
    }
}

// Processar mudança de página
if(isset($_POST['change_page'])) {
    $direction = $_POST['change_page'];
    $new_page_id = null;

    switch($direction) {
        case 'north': $new_page_id = $current_page['north_page_id']; break;
        case 'south': $new_page_id = $current_page['south_page_id']; break;
        case 'east': $new_page_id = $current_page['east_page_id']; break;
        case 'west': $new_page_id = $current_page['west_page_id']; break;
    }

    if($new_page_id) {
        $stmt = $conexao->prepare("UPDATE players_positions SET current_page_id = ? WHERE player_id = ?");
        $stmt->execute([$new_page_id, $_SESSION['logado']]);
        header("Location: index.php?p=mapa");
        exit;
    }
}
?>

<div class="box_top">Mapa - <?php echo $current_page['name']; ?></div>
<div class="box_middle">
    <div style="position: relative; width: 420px; margin: 0 auto;">
        <!-- Botões de navegação -->
        <?php if($current_page['north_page_id']): ?>
        <div style="text-align: center; margin-bottom: 5px;">
            <form method="post" style="display: inline;">
                <input type="hidden" name="change_page" value="north">
                <button type="submit" style="padding: 5px 10px; background: #333; color: white; border: none; cursor: pointer;">Norte ↑</button>
            </form>
        </div>
        <?php endif; ?>

        <div style="display: flex; align-items: center; justify-content: center;">
            <?php if($current_page['west_page_id']): ?>
            <form method="post" style="margin-right: 10px;">
                <input type="hidden" name="change_page" value="west">
                <button type="submit" style="padding: 5px 10px; background: #333; color: white; border: none; cursor: pointer;">← Oeste</button>
            </form>
            <?php endif; ?>

            <div id="mapa-container" style="position: relative; width: 400px; height: 400px; border: 2px solid #333; background: url('_img/mapa_konoha.png') no-repeat center center; background-size: cover;">
                <!-- Grade do mapa -->
                <div id="mapa-grid" style="width: 100%; height: 100%; position: absolute; top: 0; left: 0;">
                    <?php for($y = 0; $y < 20; $y++): ?>
                        <?php for($x = 0; $x < 20; $x++): ?>
                            <div class="grid-cell" 
                                 data-x="<?php echo $x; ?>" 
                                 data-y="<?php echo $y; ?>"
                                 style="position: absolute; 
                                        left: <?php echo ($x * 20); ?>px; 
                                        top: <?php echo ($y * 20); ?>px; 
                                        width: 20px; 
                                        height: 20px; 
                                        height: 20px; 
                                        border: 1px solid rgba(0,0,0,0.1);
                                        cursor: pointer;"
                                 ondblclick="moverPara(<?php echo $x; ?>, <?php echo $y; ?>)">
                            </div>
                        <?php endfor; ?>
                    <?php endfor; ?>

                    <!-- Jogador atual -->
                    <div id="player-self" 
                         style="position: absolute; 
                                left: <?php echo ($position['x'] * 20 + 7); ?>px; 
                                top: <?php echo ($position['y'] * 20 + 7); ?>px; 
                                width: 6px; 
                                height: 6px; 
                                background: blue; 
                                border-radius: 50%;
                                z-index: 10;"
                         title="Você"></div>

                    <!-- Outros jogadores -->
                    <?php foreach($other_players as $player): ?>
                    <div class="other-player" 
                         data-player-id="<?php echo $player['player_id']; ?>"
                         data-player-name="<?php echo htmlspecialchars($player['usuario']); ?>"
                         data-player-avatar="<?php echo $player['avatar'] ? "_img/personagens/" . $player['personagem'] . "/" . $player['avatar'] . ".jpg" : "_img/personagens/no_avatar.jpg"; ?>"
                         data-player-personagem="<?php echo htmlspecialchars($player['personagem']); ?>"
                         data-player-x="<?php echo $player['x']; ?>"
                         data-player-y="<?php echo $player['y']; ?>"
                         style="position: absolute; 
                                left: <?php echo ($player['x'] * 20 + 5); ?>px; 
                                top: <?php echo ($player['y'] * 20 + 5); ?>px; 
                                width: 10px; 
                                height: 10px; 
                                background: red; 
                                border-radius: 50%;
                                z-index: 9;
                                cursor: pointer;"
                         title="<?php echo $player['usuario']; ?>"
                         onclick="mostrarPopupJogador(
                            '<?php echo $player['player_id']; ?>',
                            '<?php echo htmlspecialchars($player['usuario']); ?>',
                            '<?php echo $player['avatar'] ? "_img/personagens/" . $player['personagem'] . "/" . $player['avatar'] . ".jpg" : "_img/personagens/no_avatar.jpg"; ?>',
                            '<?php echo htmlspecialchars($player['personagem']); ?>',
                            '<?php echo htmlspecialchars($player['vila'] ?? ''); ?>', 
                            '<?php echo htmlspecialchars($player['organizacao'] ?? ''); ?>',
                            '<?php echo htmlspecialchars($player['level'] ?? ''); ?>',
                            <?php echo $player['x']; ?>,
                            <?php echo $player['y']; ?>
                         )"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if($current_page['east_page_id']): ?>
            <form method="post" style="margin-left: 10px;">
                <input type="hidden" name="change_page" value="east">
                <button type="submit" style="padding: 5px 10px; background: #333; color: white; border: none; cursor: pointer;">Leste →</button>
            </form>
            <?php endif; ?>
        </div>

        <?php if($current_page['south_page_id']): ?>
        <div style="text-align: center; margin-top: 5px;">
            <form method="post" style="display: inline;">
                <input type="hidden" name="change_page" value="south">
                <button type="submit" style="padding: 5px 10px; background: #333; color: white; border: none; cursor: pointer;">Sul ↓</button>
            </form>
        </div>
        <?php endif; ?>
    </div>



    <div class="sep"></div>
    <div id="position-display" style="text-align: center;">
        <b>Posição:</b> X: <?php echo $position['x']; ?>, Y: <?php echo $position['y']; ?><br>
        <small>Setas: 2 quadrados | Duplo clique: até 2 quadrados</small>
    </div>
</div>
<div class="box_bottom"></div>

<form id="move-form" method="post" style="display: none;">
    <input type="hidden" id="move-x" name="move_x">
    <input type="hidden" id="move-y" name="move_y">
</form>




<script>
let lastMoveTime = 0;
let currentX = <?php echo $position['x']; ?>;
let currentY = <?php echo $position['y']; ?>;
let movendo = false;

function moverPara(x, y) {
    const now = Date.now();
    const distance = Math.abs(x - currentX) + Math.abs(y - currentY);
    if (distance === 2) {
        if(now - lastMoveTime < 1500) {
            alert('Aguarde 1.5 segundos entre movimentos!');
            return;
        }
    }
    if(distance > 2) {
        alert('Você só pode mover até 2 quadrados por vez!');
        return;
    }

    // Fazer movimento via AJAX
    const formData = new FormData();
    formData.append('move_x', x);
    formData.append('move_y', y);

    fetch(window.location.pathname + '?p=mapa', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Erro no movimento: ' + response.status);
        }
        return response.text();
    })
    .then(data => {
        // Atualizar posição atual
        currentX = x;
        currentY = y;

        // Atualizar posição visual do jogador
        const playerElement = document.getElementById('player-self');
        if (playerElement) {
            playerElement.style.left = (x * 20 + 7) + 'px';
            playerElement.style.top = (y * 20 + 7) + 'px';
        }

        // Atualizar display de posição
        const positionDisplay = document.getElementById('position-display');
        if(positionDisplay) {
            positionDisplay.innerHTML = '<b>Posição:</b> X: ' + x + ', Y: ' + y + '<br><small>Setas: 2 quadrados | Duplo clique: até 2 quadrados</small>';
        }

        if (distance === 2) {
          lastMoveTime = now;
        }
    })
    .catch(error => {
        console.error('Erro ao mover:', error);
        alert('Erro ao realizar movimento!');
    });
}

function mostrarPopupJogador(playerId, playerName, playerAvatar, playerPersonagem, playerVila, playerOrganizacao, playerLevel, x, y) {
    console.log('Mostrando popup para:', playerName, 'na posição:', x, y);

    // Parar propagação do evento para evitar fechamento imediato
    event.stopPropagation();

    // Verificar se os dados básicos estão presentes
    if (!playerName || !playerAvatar || !playerPersonagem) {
        console.error('Dados do jogador incompletos:', {playerName, playerAvatar, playerPersonagem});
        return;
    }

    // Remover popup existente se houver
    const existingPopup = document.getElementById('player-popup');
    if (existingPopup) {
        existingPopup.remove();
    }

    // Verificar se o usuário é VIP (placeholder - substituir por lógica real)
    const isVipUser = true; // TODO: Implementar verificação real de VIP do usuário logado

    // Verificar se o personagem visualizado é VIP (placeholder)
    const isTargetVip = Math.random() > 0.5; // TODO: Implementar verificação real

    // Criar novo popup
    const popup = document.createElement('div');
    popup.id = 'player-popup';
    popup.className = 'player-popup-container';

    let popupContent = '';
    const distancia = Math.abs(currentX - x) + Math.abs(currentY - y);
    const canAttack = distancia <= 1;

    if (isVipUser) {
        // Usuário VIP - mostra todas as informações - NOME SEMPRE BRANCO
        popupContent = `
            <div class="box2_top">
                <span class="player-name">${playerName}</span>
            </div>
            <div class="box2_middle">
                <div style="margin-bottom: 10px; text-align: center;">
                    <img src="${playerAvatar}" alt="Avatar" style="width: 60px; height: 60px; border: 2px solid #666; border-radius: 3px; display: block; margin: 0 auto;">
                </div>
                <div style="text-align: left; margin-bottom: 10px;">
                    <p><strong>Personagem:</strong> ${playerPersonagem}</p>
                    <p><strong>Vila:</strong> ${playerVila || 'Oculta'}</p>
                    <p><strong>Organização:</strong> ${playerOrganizacao || 'Oculta'}</p>
                    <p><strong>Level:</strong> ${playerLevel || 'Oculto'}</p>
                </div>
                <div style="text-align: center; margin-top: 12px;">
                    <button onclick="event.stopPropagation(); atacarJogador()" class="botao" id="attack-button" ${!canAttack ? 'disabled style="background: #666; cursor: not-allowed;"' : ''}>
                        ${canAttack ? 'Atacar' : 'Muito longe'}
                    </button>
                    <button onclick="event.stopPropagation(); fecharPopup()" class="botao">Fechar</button>
                </div>
            </div>
            <div class="box2_bottom"></div>
        `;
    } else {
        // Usuário Free - mostra apenas avatar e botões
        popupContent = `
            <div class="box2_top">
                <span class="player-name">Informações Limitadas</span>
            </div>
            <div class="box2_middle">
                <div style="margin-bottom: 10px; text-align: center;">
                    <img src="${playerAvatar}" alt="Avatar" style="width: 70px; height: 70px; border: 2px solid #666; border-radius: 3px; display: block; margin: 0 auto;">
                </div>
                <p style="margin: 8px 0; color: #999; text-align: center;">Seja VIP para ver mais informações</p>
                <div style="text-align: center; margin-top: 12px;">
                    <button onclick="event.stopPropagation(); atacarJogador()" class="botao" id="attack-button" ${!canAttack ? 'disabled style="background: #666; cursor: not-allowed;"' : ''}>
                        ${canAttack ? 'Atacar' : 'Muito longe'}
                    </button>
                    <button onclick="event.stopPropagation(); fecharPopup()" class="botao">Fechar</button>
                </div>
            </div>
            <div class="box2_bottom"></div>
        `;
    }

    popup.innerHTML = popupContent;

    // Adicionar evento para prevenir fechamento ao clicar no popup
    popup.addEventListener('click', function(e) {
        e.stopPropagation();
    });

    // Adicionar popup ao body
    document.body.appendChild(popup);

    // Tornar variáveis acessíveis globalmente para funções
    window.selectedPlayerData = {
        playerId,
        playerName,
        playerAvatar,
        playerPersonagem,
        playerVila,
        playerOrganizacao,
        playerLevel,
        x,
        y
    };
}

function fecharPopup() {
    const popup = document.getElementById('player-popup');
    if (popup) {
        popup.classList.add('closing');
        setTimeout(() => {
            popup.remove();
        }, 150);
    }
}



function atacarJogador() {
    if (window.selectedPlayerData) {
        console.log('Atacando jogador:', window.selectedPlayerData.playerName);
        // TODO: Implementar lógica de ataque
        alert('Função de ataque ainda não implementada');
        fecharPopup();
    }
}

// Fechar popup ao clicar fora dele - com delay para evitar fechamento imediato
let popupClickTimeout;
document.addEventListener('click', function(event) {
    clearTimeout(popupClickTimeout);
    popupClickTimeout = setTimeout(function() {
        const popup = document.getElementById('player-popup');
        if (popup && !popup.contains(event.target)) {
            // Verificar se o clique não foi em um ponto do mapa ou jogador
            const isPlayerOrMap = event.target.classList.contains('other-player') || 
                                event.target.closest('.other-player') ||
                                event.target.classList.contains('grid-cell') ||
                                event.target.closest('#mapa-grid');

            if (!isPlayerOrMap) {
                fecharPopup();
            }
        }
    }, 100);
});

// Fechar popup com ESC
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        fecharPopup();
    }
});

function moverPersonagem(direcao) {
    if (movendo) {
        console.log('Já está se movendo...');
        return;
    }

    movendo = true;

    fetch('ajax_mover.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'direcao=' + encodeURIComponent(direcao)
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            console.log('Movimento realizado com sucesso');
            // Atualizar posição na tela se necessário
            if (data.nova_posicao) {
                // Atualizar interface com nova posição
                console.log('Nova posição:', data.nova_posicao);
            }
        } else {
            console.error('Erro no movimento:', data.erro);
            alert('Erro: ' + data.erro);
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        alert('Erro ao realizar movimento!');
    })
    .finally(() => {
        movendo = false;
    });
}

// Função para atualizar outros jogadores
function atualizarOutrosJogadores() {
    fetch(window.location.pathname + '?p=mapa&ajax=outros_jogadores', {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.players) {
            // Remover jogadores existentes
            document.querySelectorAll('.other-player').forEach(player => player.remove());

            // Adicionar jogadores atualizados
            const mapaGrid = document.getElementById('mapa-grid');
            data.players.forEach(player => {
                const playerElement = document.createElement('div');
                playerElement.className = 'other-player';
                playerElement.setAttribute('data-player-id', player.player_id);
                playerElement.setAttribute('data-player-name', player.usuario);
                playerElement.setAttribute('data-player-avatar', player.avatar ? "_img/personagens/" + player.personagem + "/" + player.avatar + ".jpg" : "_img/personagens/no_avatar.jpg");
                playerElement.setAttribute('data-player-personagem', player.personagem);
                playerElement.setAttribute('data-player-x', player.x);
                playerElement.setAttribute('data-player-y', player.y);
                playerElement.style.cssText = `
                    position: absolute; 
                    left: ${player.x * 20 + 5}px; 
                    top: ${player.y * 20 + 5}px; 
                    width: 10px; 
                    height: 10px; 
                    background: red; 
                    border-radius: 50%;
                    z-index: 9;
                    cursor: pointer;
                `;
                playerElement.title = player.usuario;
                playerElement.onclick = function() {
                    mostrarPopupJogador(
                        player.player_id,
                        player.usuario,
                        player.avatar ? "_img/personagens/" + player.personagem + "/" + player.avatar + ".jpg" : "_img/personagens/no_avatar.jpg",
                        player.personagem,
                        player.vila || '',
                        player.organizacao || '',
                        player.level || '',
                        player.x,
                        player.y
                    );
                };

                mapaGrid.appendChild(playerElement);
            });
        }
    })
    .catch(error => {
        console.error('Erro ao atualizar outros jogadores:', error);
    });
}

// Atualizar outros jogadores a cada 10 segundos
setInterval(atualizarOutrosJogadores, 10000);

// Adicionar controles de teclado para movimentação
document.addEventListener('keydown', function(e) {
    // Verificar se não estamos em um campo de input
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
        return;
    }

    let newX = currentX;
    let newY = currentY;
    let moved = false;
    let distance = 2; // Movement is now 2 squares

    switch(e.keyCode) {
        case 37: // Seta esquerda
            newX = Math.max(0, currentX - distance);
            moved = true;
            break;
        case 38: // Seta para cima
            newY = Math.max(0, currentY - distance);
            moved = true;
            break;
        case 39: // Seta direita
            newX = Math.min(19, currentX + distance);
            moved = true;
            break;
        case 40: // Seta para baixo
            newY = Math.min(19, currentY - distance);
            moved = true;
            break;
    }

    if (moved && (newX !== currentX || newY !== currentY)) {
        e.preventDefault(); // Prevenir scroll da página
        const actualDistance = Math.abs(newX - currentX) + Math.abs(newY - currentY);
         if (actualDistance !== 2) {
            return;
        }

        moverPara(newX, newY);
    }
});

// Garantir que o elemento tenha foco para receber eventos de teclado
document.addEventListener('DOMContentLoaded', function() {
    document.body.tabIndex = 0;
    document.body.focus();
});
</script>