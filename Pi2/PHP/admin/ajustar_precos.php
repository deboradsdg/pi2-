<?php
// Arquivo: Pi2/PHP/admin/ajustar_precos.php
require_once '../conexao.php'; 
session_start();

// 1. VERIFICAÇÃO DE PERMISSÃO (Segurança)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../../Principal.php");
    exit;
}

$mensagem = '';

// 2. LÓGICA DE ATUALIZAÇÃO (Quando o formulário é submetido)
if (isset($_POST['submit_update'])) {
    $id_produto = $_POST['id_produto'];
    $novo_preco = $_POST['preco'];
    $novo_tipo = $_POST['tipo'];
    $novo_ativo = isset($_POST['ativo']) ? 1 : 0; // Se o checkbox 'ativo' estiver marcado, é 1, senão é 0.
    
    // Consulta para atualizar os campos
    $sql_update = "UPDATE produto SET preco = ?, tipo = ?, ativo = ? WHERE id_produto = ?";
    
    try {
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$novo_preco, $novo_tipo, $novo_ativo, $id_produto]);
        $mensagem = "Produto ID #$id_produto atualizado com sucesso!";
    } catch (PDOException $e) {
        $mensagem = "Erro ao atualizar produto: " . $e->getMessage();
    }
}

// 3. BUSCAR TODOS OS PRODUTOS
$sql_produtos = "SELECT id_produto, nome, preco, tipo, ativo FROM produto ORDER BY tipo, nome ASC";

try {
    $stmt_produtos = $pdo->query($sql_produtos);
    $produtos = $stmt_produtos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar lista de produtos: " . $e->getMessage());
}

// Opções de Tipo para o <select>
$tipo_opcoes = ['Pizza', 'Pizza Doce', 'Sobremesa', 'Bebida']; // Use os tipos que você definiu no banco.
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Ajustar Preços e Status</title>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: left; }
        input[type="number"], select { width: 100px; padding: 5px; }
        .submit-cell button { padding: 5px 10px; cursor: pointer; }
    </style>
</head>
<body>
    <header>
        <h1>Ajuste de Preços e Status de Produtos</h1>
        <a href="painel_admin.php">Voltar ao Painel</a> | <a href="../logout.php">Sair</a>
    </header>
    <main>
        
        <?php if ($mensagem): ?>
            <p style="color: green; font-weight: bold;"><?php echo $mensagem; ?></p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome do Produto</th>
                    <th>Tipo</th>
                    <th>Preço (R$)</th>
                    <th>Ativo?</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $produto): ?>
                    <form action="ajustar_precos.php" method="POST">
                        <input type="hidden" name="id_produto" value="<?php echo $produto['id_produto']; ?>">
                        <tr>
                            <td><?php echo $produto['id_produto']; ?></td>
                            <td><?php echo htmlspecialchars($produto['nome']); ?></td>
                            
                            <td>
                                <select name="tipo" required>
                                    <?php foreach ($tipo_opcoes as $tipo): ?>
                                        <option value="<?php echo $tipo; ?>" <?php echo ($tipo == $produto['tipo']) ? 'selected' : ''; ?>>
                                            <?php echo $tipo; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            
                            <td>
                                <input type="number" name="preco" step="0.01" min="0" value="<?php echo number_format($produto['preco'], 2, '.', ''); ?>" required>
                            </td>
                            
                            <td style="text-align: center;">
                                <input type="checkbox" name="ativo" value="1" <?php echo ($produto['ativo'] == 1) ? 'checked' : ''; ?>>
                            </td>
                            
                            <td class="submit-cell">
                                <button type="submit" name="submit_update">Salvar</button>
                            </td>
                        </tr>
                    </form>
                <?php endforeach; ?>
            </tbody>
        </table>

    </main>
</body>
</html>