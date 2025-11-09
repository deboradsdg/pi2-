<?php
// Arquivo: acompanhamento.php
require_once 'conexao.php'; 
session_start([
    'cookie_path' => '/',
]);

// 1. Verificação de Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    header("Location: Cadastro.html");
    exit;
}

$id_cliente = $_SESSION['user_id'];
$id_pedido = $_GET['pedido'] ?? null;
$pedido_detalhes = null;
$itens_pedido = [];
$erro = '';

if (!$id_pedido || !is_numeric($id_pedido)) {
    $erro = "ID do pedido inválido ou ausente.";
} else {
    try {
        // 2. BUSCA OS DETALHES DO PEDIDO PRINCIPAL (e verifica se é do cliente logado)
        $sql_detalhes = "SELECT 
                            p.data_pedido, p.valor_total, p.status,
                            c.logradouro, c.numero, c.complemento, c.bairro, c.cidade, c.cep
                         FROM pedido p
                         JOIN cliente c ON p.id_cliente = c.id_cliente
                         WHERE p.id_pedido = ? AND p.id_cliente = ?";
                         
        $stmt_detalhes = $pdo->prepare($sql_detalhes);
        $stmt_detalhes->execute([$id_pedido, $id_cliente]);
        $pedido_detalhes = $stmt_detalhes->fetch(PDO::FETCH_ASSOC);

        if (!$pedido_detalhes) {
            $erro = "Pedido não encontrado ou você não tem permissão para visualizá-lo.";
        } else {
            // 3. BUSCA OS ITENS DO PEDIDO
            $sql_itens = "SELECT 
                              ip.quantidade, ip.valor_unitario,
                              prod.nome, prod.descricao, prod.imagem_url
                          FROM item_pedido ip
                          JOIN produto prod ON ip.id_produto = prod.id_produto
                          WHERE ip.id_pedido = ?";
                          
            $stmt_itens = $pdo->prepare($sql_itens);
            $stmt_itens->execute([$id_pedido]);
            $itens_pedido = $stmt_itens->fetchAll(PDO::FETCH_ASSOC);
        }

    } catch (PDOException $e) {
        $erro = "Erro ao carregar detalhes do pedido: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Acompanhamento do Pedido #<?php echo htmlspecialchars($id_pedido); ?></title>
    <link rel="stylesheet" href="../CSS/Principal.css"> 
    <style>
        .status-badge {
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: bold;
            color: #fff;
            display: inline-block;
        }
        .status-PedidoRecebido { background-color: var(--cor2); }
        .status-EmPreparacao { background-color: var(--cor8); color: var(--cor9); }
        .status-SaiuParaEntrega { background-color: var(--cor7); color: var(--cor9); }
        .status-Finalizado { background-color: var(--cor3); }
        .status-Cancelado { background-color: var(--cor6); }
        
        .endereco-box {
            border: 1px solid var(--cor11);
            padding: 15px;
            margin-top: 15px;
            border-radius: 10px;
            background-color: var(--cor10);
            color: var(--cor9);
        }
        .item-list {
            list-style: none;
            padding: 0;
        }
        .item-list li {
            border-bottom: 1px dashed var(--cor11);
            padding: 10px 0;
            display: flex;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <header>
        <div id="LT">
            <img src="../MIDIAS/IMAGENS/PRINCIPAL.png" alt="Logo The Pizza One">
        </div>
        <a href="Principal.php">Voltar ao Cardápio</a>
    </header>
    
    <main>
        <section>
            <h2>Detalhes do Pedido #<?php echo htmlspecialchars($id_pedido); ?></h2>

            <?php if ($erro): ?>
                <p style="color: red;"><?php echo $erro; ?></p>
            <?php elseif ($pedido_detalhes): ?>
                
                <h3>Status Atual</h3>
                <?php 
                    $status_class = 'status-' . str_replace(' ', '', $pedido_detalhes['status']);
                    echo '<span class="status-badge ' . $status_class . '">' . htmlspecialchars($pedido_detalhes['status']) . '</span>';
                ?>
                
                <p>Data do Pedido: <?php echo date('d/m/Y H:i', strtotime($pedido_detalhes['data_pedido'])); ?></p>
                <p>Valor Total: **R$<?php echo number_format($pedido_detalhes['valor_total'], 2, ',', '.'); ?>**</p>

                <hr>

                <h3>Itens Comprados</h3>
                <ul class="item-list">
                    <?php foreach ($itens_pedido as $item): ?>
                        <li>
                            <span>
                                **<?php echo htmlspecialchars($item['nome']); ?>** (x<?php echo htmlspecialchars($item['quantidade']); ?>)
                            </span>
                            <span>
                                R$<?php echo number_format($item['valor_unitario'] * $item['quantidade'], 2, ',', '.'); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <hr>

                <h3>Endereço de Entrega</h3>
                <div class="endereco-box">
                    <p>**Logradouro:** <?php echo htmlspecialchars($pedido_detalhes['logradouro'] . ', ' . $pedido_detalhes['numero']); ?></p>
                    <p>**Complemento:** <?php echo htmlspecialchars($pedido_detalhes['complemento']); ?></p>
                    <p>**Bairro:** <?php echo htmlspecialchars($pedido_detalhes['bairro']); ?></p>
                    <p>**Cidade/CEP:** <?php echo htmlspecialchars($pedido_detalhes['cidade'] . ' - CEP: ' . $pedido_detalhes['cep']); ?></p>
                </div>
                
                <?php endif; ?>
        </section>
    </main>

    <footer>
        </footer>
</body>
</html>