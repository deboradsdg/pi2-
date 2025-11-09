<?php
session_start([
    'cookie_path' => '/',
]);

require_once 'conexao.php'; 

session_start();

$is_logged_in = isset($_SESSION['cliente_id']);
$user_name = $is_logged_in ? $_SESSION['cliente_nome'] : '';
$is_admin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == true;


// Define a ordem de tipos desejada para o SQL
$ordem_tipos = "'Pizza', 'Pizza Doce', 'Sobremesa', 'Bebida'";

// Consulta SQL para buscar e ordenar produtos
$sql = "SELECT id_produto, nome, descricao, preco, imagem_url, tipo 
        FROM produto 
        WHERE ativo = 1
        ORDER BY FIELD(tipo, $ordem_tipos), nome ASC"; 

try {
    $stmt = $pdo->query($sql);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar produtos: " . $e->getMessage());
}

// Verifica status do login
$is_logged_in = isset($_SESSION['user_type']);
$user_name = $is_logged_in ? htmlspecialchars($_SESSION['user_name'] ?? 'Cliente') : ''; // Assumindo que você salva o nome na sessão
$user_id = $is_logged_in ? ($_SESSION['user_id'] ?? 'null') : 'null';
$is_admin = $is_logged_in && $_SESSION['user_type'] === 'admin';

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../CSS/Principal.css">
    <link rel="shortcut icon" href="../MIDIAS/IMAGENS/PRINCIPAL.png" type="image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE PIZZA ONE</title>
</head>
<body>
   
    <header>
        <div id="LT">
            <img src="../MIDIAS/IMAGENS/PRINCIPAL.png" alt="Logo The Pizza One">
        </div>

   <div id="status-login">
<?php if ($is_logged_in): ?>
    <span>Olá, <?php echo htmlspecialchars($user_name); ?>!</span>
    <?php if ($is_admin): ?>
        <a href="admin/painel_admin.php" class="btn-status">Painel Admin</a>
    <?php else: ?>
        <a href="meus_pedidos.php" class="btn-status">Meus Pedidos</a>
    <?php endif; ?>
    <a href="logout.php" class="btn-status">Sair</a>
<?php else: ?>
    <a href="../HTML/Cadastro.html" class="btn-status">Entrar / Cadastrar</a>
<?php endif; ?>
</div>

        
    </header>
    <main>
        <section>
            <h2 id="PI">PEÇA AGORA!!!</h2>
            <div class="cardapio">
                
                <?php 
                $tipo_atual = '';
                
                foreach ($produtos as $produto) {
                    
                    
                    if ($produto['tipo'] !== $tipo_atual) {
                        if ($tipo_atual !== '') {
                            echo '<hr>'; 
                        }
                
                    }

                    // Gera o CARD (<div class="pizza ...">)
                    echo '<div class="pizza" 
                                data-id="' . htmlspecialchars($produto['id_produto']) . '"
                                data-nome="' . htmlspecialchars($produto['nome']) . '"
                                data-preco="' . htmlspecialchars(number_format($produto['preco'], 2, '.', '')) . '">';
                    
                    echo '<h3>' . htmlspecialchars($produto['nome']) . '</h3>';
                    echo '<img src="' . htmlspecialchars($produto['imagem_url']) . '" alt="Imagem de ' . htmlspecialchars($produto['nome']) . '">';
                    echo '<p>' . htmlspecialchars($produto['descricao']) . '</p>';
                    echo '<p class="PR">R$' . number_format($produto['preco'], 2, ',', '.') . '</p>';
                    
                    // Botões de Quantidade
                    echo '<aside>';
                    echo '<button class="decr" aria-label="Diminuir quantidade">−</button>';
                    echo '<span class="qtd">0</span>';
                    echo '<button class="incr" aria-label="Aumentar quantidade">+</button>';
                    echo '</aside>';
                    
                    echo '</div>'; // Fecha div.pizza (o card)
                }
                ?>
                
            </div> 
        </section>
    </main>
    
    <section id="popup">
        <h5>🧺 Itens no carrinho:</h5>
        <div id="itens"></div>
        <p id="total">Total: R$0,00</p>
        <button id="fechar">Fechar</button>
        <button id="comprar">Comprar</button>
    </section>
    

<script src="../JS/Principal.js"></script>
    <script>
const clienteLogado = <?php echo isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'cliente' ? 'true' : 'false'; ?>;
const userId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
</script>
</body>
<footer>
        <section id="REC">
            <h4>DESENVOLVIDO POR:</h4>
            <p>Paulo, Débora e Juvenal, Alunos da Fatec de Marilia</p>
        </section>
        <section id="frame">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d923.459924692022!2d-49.9546171860178!3d-22.208197483465927!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94bfd7217b8e873b%3A0xd7de49af9e293d42!2sFatec%20Mar%C3%ADlia%20-%20Faculdade%20de%20Tecnologia%20de%20Mar%C3%ADlia!5e0!3m2!1spt-BR!2sbr!4v1759406870866!5m2!1spt-BR!2sbr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </section>
    </footer>
</html>