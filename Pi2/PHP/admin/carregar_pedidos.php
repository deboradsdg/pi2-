<?php
// Arquivo: Pi2/PHP/admin/carregar_pedidos.php
require_once '../conexao.php'; 
session_start();

// Verifica se é admin (Segurança)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    die("Acesso negado.");
}

// LÓGICA DE BUSCA DE PEDIDOS (Igual à do gerenciar_pedidos.php)
$sql_pedidos = "SELECT p.id_pedido, p.data_pedido, p.valor_total, p.status, c.nome AS nome_cliente, c.logradouro, c.numero
                FROM pedido p
                JOIN cliente c ON p.id_cliente = c.id_cliente
                ORDER BY 
                    CASE WHEN p.status = 'Finalizado' OR p.status = 'Cancelado' THEN 1 ELSE 0 END,
                    p.data_pedido ASC";

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt_pedidos = $pdo->query($sql_pedidos);
    $pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<tr><td colspan='8' style='color: red;'>Erro ao carregar dados: " . $e->getMessage() . "</td></tr>";
    exit;
}

// Opções de Status (Para o <select>)
$status_opcoes = ['Pedido Recebido', 'Em Preparação', 'Saiu para Entrega', 'Finalizado', 'Cancelado'];

// GERAÇÃO DO HTML DA TABELA (<tbody>)
if (empty($pedidos)) {
    echo "<tr><td colspan='8'>Nenhum pedido ativo no momento.</td></tr>";
    exit;
}

foreach ($pedidos as $pedido): 
    $timestamp_pedido = strtotime($pedido['data_pedido']);
    $tempo_decorrido_segundos = time() - $timestamp_pedido;
?>
    <tr>
        <td><?php echo $pedido['id_pedido']; ?></td>
        <td><?php echo date('H:i:s', $timestamp_pedido); ?></td>
        
        <td class="tempo-celula">
            <span class="cronometro" 
                  data-start-time="<?php echo $tempo_decorrido_segundos; ?>"
                  data-status="<?php echo htmlspecialchars($pedido['status']); ?>">
                Carregando...
            </span>
        </td>

        <td><?php echo htmlspecialchars($pedido['nome_cliente']); ?></td>
        <td><?php echo htmlspecialchars($pedido['logradouro'] . ', ' . $pedido['numero']); ?></td>
        <td>R$<?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
        <td><span class="status-badge status-<?php echo str_replace(' ', '', $pedido['status']); ?>"><?php echo htmlspecialchars($pedido['status']); ?></span></td>
        
        <td>
            <form action="gerenciar_pedidos.php" method="POST" style="display: flex; gap: 5px;">
                <input type="hidden" name="id_pedido" value="<?php echo $pedido['id_pedido']; ?>">
                <select name="status" required <?php echo ($pedido['status'] == 'Finalizado' || $pedido['status'] == 'Cancelado') ? 'disabled' : ''; ?>>
                    <?php foreach ($status_opcoes as $status): ?>
                        <option value="<?php echo $status; ?>" <?php echo ($status == $pedido['status']) ? 'selected' : ''; ?>>
                            <?php echo $status; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="atualizar_status" <?php echo ($pedido['status'] == 'Finalizado' || $pedido['status'] == 'Cancelado') ? 'disabled' : ''; ?>>Atualizar</button>
            </form>
        </td>
    </tr>
<?php endforeach; ?>