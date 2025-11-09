<?php
// Arquivo: Pi2/PHP/admin/relatorios.php
require_once '../conexao.php'; 
session_start();

// 1. VERIFICAÇÃO DE PERMISSÃO (Segurança)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../../Principal.php");
    exit;
}

$relatorio_selecionado = $_GET['relatorio'] ?? 'diario'; // Padrão: diário
$data_hoje = date('Y-m-d');
$resultados = [];
$titulo = '';
$sql = '';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    switch ($relatorio_selecionado) {
        
        // =================================================================
        // A. RELATÓRIO DE PEDIDOS DIÁRIOS (Tempo Decorrido)
        // =================================================================
        case 'diario':
            $titulo = "Pedidos Diários (Data: " . date('d/m/Y') . ")";
            
            // Busca pedidos do dia de hoje, calcula tempo decorrido
            $sql = "SELECT p.id_pedido, p.valor_total, p.status, p.data_pedido, c.nome AS nome_cliente
                    FROM pedido p
                    JOIN cliente c ON p.id_cliente = c.id_cliente
                    WHERE DATE(p.data_pedido) = ?
                    ORDER BY p.data_pedido DESC";
                    
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$data_hoje]);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        // =================================================================
        // B. RELATÓRIO DE PEDIDOS MENSAIS
        // =================================================================
        case 'mensal':
            $titulo = "Total de Pedidos e Vendas por Mês";
            
            // Agrupa pedidos por mês e ano, somando o valor total.
            $sql = "SELECT 
                        DATE_FORMAT(data_pedido, '%Y-%m') AS mes_ano,
                        COUNT(id_pedido) AS total_pedidos,
                        SUM(valor_total) AS total_vendas
                    FROM pedido
                    WHERE status != 'Cancelado'
                    GROUP BY mes_ano
                    ORDER BY mes_ano DESC";
                    
            $stmt = $pdo->query($sql);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;

        // =================================================================
        // C. RELATÓRIO DE PRODUTOS MAIS PEDIDOS
        // =================================================================
        case 'mais_pedidos':
            $titulo = "Top 10 Produtos Mais Vendidos (Quantidade)";
            
            // Soma a quantidade de cada produto vendido.
            $sql = "SELECT 
                        prod.nome,
                        prod.tipo,
                        SUM(ip.quantidade) AS total_vendido
                    FROM item_pedido ip
                    JOIN produto prod ON ip.id_produto = prod.id_produto
                    GROUP BY prod.nome, prod.tipo
                    ORDER BY total_vendido DESC
                    LIMIT 10";
                    
            $stmt = $pdo->query($sql);
            $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
            break;
            
        default:
            $titulo = "Selecione um relatório válido.";
    }

} catch (PDOException $e) {
    $titulo = "Erro no Banco de Dados: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Relatórios Administrativos</title>
    <link rel="stylesheet" href="../../CSS/admin.css"> 
    <style>
        .relatorio-menu { margin-bottom: 20px; }
        .relatorio-menu a { margin-right: 10px; padding: 8px 15px; border: 1px solid #ccc; text-decoration: none; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
    </style>
</head>
<body>
    <header>
        <h1>Relatórios de Vendas</h1>
        <a href="painel_admin.php">Voltar ao Painel</a> | <a href="../logout.php">Sair</a>
    </header>
    <main>
        
        <h2><?php echo $titulo; ?></h2>

        <div class="relatorio-menu">
            <a href="relatorios.php?relatorio=diario">Pedidos Diários</a>
            <a href="relatorios.php?relatorio=mensal">Pedidos Mensais</a>
            <a href="relatorios.php?relatorio=mais_pedidos">Produtos Mais Pedidos</a>
        </div>
        
        <?php if (!empty($resultados)): ?>
            
            <table>
                <thead>
                    <tr>
                        <?php 
                        // Cabeçalhos dinâmicos
                        foreach (array_keys($resultados[0]) as $coluna) {
                            echo '<th>' . ucwords(str_replace('_', ' ', $coluna)) . '</th>';
                        }
                        // Adicionar a coluna de tempo de preparo se for o relatório diário
                        if ($relatorio_selecionado == 'diario') {
                             echo '<th>Tempo Decorrido (min)</th>';
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resultados as $linha): ?>
                        <tr>
                            <?php foreach ($linha as $coluna => $valor): ?>
                                <td>
                                    <?php 
                                    if ($coluna == 'valor_total' || $coluna == 'total_vendas') {
                                        echo 'R$' . number_format($valor, 2, ',', '.');
                                    } else {
                                        echo htmlspecialchars($valor);
                                    }
                                    ?>
                                </td>
                            <?php endforeach; ?>
                            
                            <?php 
                            // Coluna de TEMPO DECORRIDO (DIÁRIO)
                            if ($relatorio_selecionado == 'diario'):
                                $tempo_decorrido = time() - strtotime($linha['data_pedido']); 
                                echo '<td>' . round($tempo_decorrido / 60) . '</td>';
                            endif;
                            ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        <?php else: ?>
             <p>Nenhum dado encontrado para o relatório selecionado.</p>
        <?php endif; ?>

        <p style="margin-top: 20px;">*Nota: O Tempo Decorrido é o tempo desde o pedido até o momento da visualização, útil para monitoramento.</p>

    </main>
</body>
</html> 