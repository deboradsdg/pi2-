<?php
// Arquivo: confirmar_endereco.php
require_once 'conexao.php'; 
session_start();

// 1. Verificação de Login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    header("Location: Cadastro.html");
    exit;
}

$id_cliente = $_SESSION['user_id'];
$dados_cliente = null;
$erro = '';

// 2. Busca o endereço atual do cliente no banco
try {
    $sql = "SELECT cep, cidade, complemento, logradouro, numero FROM cliente WHERE id_cliente = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_cliente]);
    $dados_cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados_cliente) {
        $erro = "Não foi possível carregar os dados de endereço.";
    }
} catch (PDOException $e) {
    $erro = "Erro de banco de dados: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Confirmar Endereço</title>
    </head>
<body>
    <h1>Confirmação de Endereço de Entrega</h1>
    
    <?php if ($erro): ?>
        <p style="color: red;"><?php echo $erro; ?></p>
    <?php else: ?>
        <p>Por favor, confirme se o endereço abaixo está correto ou edite-o:</p>
        
        <form action="processarpedido.php" method="POST"> 
            <label for="logradouro">Logradouro:</label>
            <input type="text" name="logradouro" value="<?php echo htmlspecialchars($dados_cliente['logradouro'] ?? ''); ?>" required><br>

            <label for="numero">Número:</label>
            <input type="text" name="numero" value="<?php echo htmlspecialchars($dados_cliente['numero'] ?? ''); ?>" required><br>
            
            <label for="complemento">Complemento:</label>
            <input type="text" name="complemento" value="<?php echo htmlspecialchars($dados_cliente['complemento'] ?? ''); ?>"><br>
            
            <label for="bairro">Bairro:</label>
            <input type="text" name="bairro" value="<?php echo htmlspecialchars($dados_cliente['bairro'] ?? ''); ?>" required><br>

            <label for="cidade">Cidade:</label>
            <input type="text" name="cidade" value="<?php echo htmlspecialchars($dados_cliente['cidade'] ?? ''); ?>" required><br>

            <label for="cep">CEP:</label>
            <input type="text" name="cep" value="<?php echo htmlspecialchars($dados_cliente['cep'] ?? ''); ?>" required><br>
            
            <button type="submit" name="confirmar">Confirmar e Finalizar Pedido</button>
        </form>

    <?php endif; ?>
    <a href="Principal.php">Voltar ao Cardápio</a>
</body>
</html>