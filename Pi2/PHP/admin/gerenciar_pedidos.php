<?php
// Arquivo: Pi2/PHP/admin/gerenciar_pedidos.php
require_once '../conexao.php'; 
session_start();

// 1. VERIFICAÇÃO DE PERMISSÃO (Segurança)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../../Principal.php");
    exit;
}

$mensagem = '';
$pedidos = [];

// 2. LÓGICA DE ATUALIZAÇÃO DE STATUS
if (isset($_POST['atualizar_status'])) {
    $id_pedido = $_POST['id_pedido'];
    $novo_status = $_POST['status'];
    
    // Consulta para atualizar o status do pedido
    $sql_update = "UPDATE pedido SET status = ? WHERE id_pedido = ?";
    
    try {
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$novo_status, $id_pedido]);
        $mensagem = "Status do Pedido #$id_pedido atualizado para: $novo_status!";
    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar status: " . $e->getMessage();
    }
}

// 3. BUSCAR TODOS OS PEDIDOS (Exceto 'Finalizado' para manter a lista de trabalho limpa)
$sql_pedidos = "SELECT p.id_pedido, p.data_pedido, p.valor_total, p.status, c.nome AS nome_cliente, c.logradouro, c.numero
                FROM pedido p
                JOIN cliente c ON p.id_cliente = c.id_cliente
                WHERE p.status != 'Finalizado' AND p.status != 'Cancelado'
                ORDER BY p.data_pedido ASC";

try {
    $stmt_pedidos = $pdo->query($sql_pedidos);
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar lista de pedidos: " . $e->getMessage());
}

// Opções de Status para o <select>
$status_opcoes = ['Pedido Recebido', 'Em Preparação', 'Saiu para Entrega', 'Finalizado', 'Cancelado'];

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Pedidos</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        .status-badge { padding: 5px; border-radius: 3px; font-weight: bold; }
        .status-PedidoRecebido { background-color: #ffde59; }
        .status-EmPreparacao { background-color: #ffa500; }
        .status-SaiuParaEntrega { background-color: #00d870; color: white; }
    </style>
</head>
<body>
    <header>
        <h1>Gerenciamento de Pedidos</h1>
        <a href="painel_admin.php">Voltar ao Painel</a> | <a href="../logout.php">Sair</a>
    </header>
    <main>
        
        <?php if ($mensagem): ?>
            <p style="color: green; font-weight: bold;"><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <h2>Pedidos Ativos (<?php echo count($pedidos); ?>)</h2>

        <?php if (empty($pedidos)): ?>
            <p>Nenhum pedido ativo no momento.</p>
        <?php else: ?>
            <div id="tabela-pedidos-container"> <table>
        <thead>
            </thead>
        <tbody id="lista-pedidos-body"> <?php require_once 'carregar_pedidos.php'; ?>
        </tbody>
    </table>
</div>
           
        <?php endif; ?>

    </main>

    <script src="../../JS/gerenciar_pedidos.js"></script>
</body>
</html>