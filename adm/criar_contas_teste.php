<?php
require_once('../_inc/conexao.php');

echo "<h2>Criador de Contas de Teste</h2>";

// Verificar se já existem contas de teste
$stmt = $conexao->prepare("SELECT COUNT(*) as total FROM usuarios WHERE usuario LIKE 'teste%'");
$stmt->execute();
$contas_existentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

if($contas_existentes >= 5) {
    echo "<div style='color: orange;'>⚠ Já existem $contas_existentes contas de teste. Máximo permitido: 5</div>";
    echo "<a href='../index.php'>← Voltar ao jogo</a>";
    exit;
}

$contas_criar = min(5 - $contas_existentes, 5);

echo "<p>Criando $contas_criar contas de teste...</p>";

$personagens = ['naruto', 'sasuke', 'sakura', 'kakashi'];
$vilas = [1, 2, 3, 4, 5]; // Folha, Areia, Névoa, Pedra, Nuvem

for($i = 1; $i <= $contas_criar; $i++) {
    $numero_conta = $contas_existentes + $i;
    $usuario = "teste" . $numero_conta;
    $senha = md5("123456");
    $email = "teste{$numero_conta}@narutohit.com";
    $personagem = $personagens[array_rand($personagens)];
    $vila = $vilas[array_rand($vilas)];
    $avatar = rand(1, 5);
    
    try {
        $stmt = $conexao->prepare("
            INSERT INTO usuarios 
            (usuario, senha, email, personagem, avatar, vila, nivel, energia, energiamax, yens, taijutsu, ninjutsu, genjutsu, status, exp, expmax) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'ativo', ?, ?)
        ");
        
        $stmt->execute([
            $usuario,
            $senha,
            $email,
            $personagem,
            $avatar,
            $vila,
            rand(5, 15), // nível
            rand(80, 100), // energia
            100, // energia máxima
            rand(1000, 5000), // yens
            rand(20, 50), // taijutsu
            rand(20, 50), // ninjutsu
            rand(20, 50), // genjutsu
            rand(0, 200), // exp
            rand(100, 300) // exp máxima
        ]);
        
        echo "✅ Conta criada: <strong>$usuario</strong> (Senha: 123456)<br>";
        
    } catch(Exception $e) {
        echo "❌ Erro ao criar conta $usuario: " . $e->getMessage() . "<br>";
    }
}

echo "<br><strong>Contas de teste criadas com sucesso!</strong><br>";
echo "<p>Todas as contas usam a senha: <strong>123456</strong></p>";
echo "<a href='../index.php'>🎮 Ir para o jogo</a> | ";
echo "<a href='verificar_banco.php'>🔍 Verificar banco</a>";
?>
