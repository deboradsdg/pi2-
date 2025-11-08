<?php
// Arquivo: ../PHP/processar_pedido.php
require_once 'conexao.php';
session_start();

// 1. VERIFICAÇÃO DE SESSÃO E DADOS
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    header("Location: ../Cadastro.html");
    exit;
}

// Verifica se o carrinho temporário existe na sessão
if (!isset($_SESSION['carrinho_temp']) || empty($_SESSION['carrinho_temp'])) {
    die("Erro: Não há itens no carrinho para finalizar o pedido.");
}

$id_cliente = $_SESSION['user_id'];
$itens_carrinho = $_SESSION['carrinho_temp'];
$valor_total = 0;
$id_promocao = NULL; // Por enquanto, não há lógica de promoção

// 2. CAPTURA DOS DADOS DO FORMULÁRIO (Endereço e Confirmação)
if (isset($_POST['confirmar'])) {
    
    // Captura os dados do formulário de confirmação de endereço
    $logradouro = $_POST['logradouro'];
    $numero = $_POST['numero'];
    $complemento = $_POST['complemento'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $cep = $_POST['cep'];
    
    // Inicia a transação SQL
    try {
        $pdo->beginTransaction();

        // 3. ATUALIZAÇÃO DO ENDEREÇO DO CLIENTE
        $sql_update_endereco = "UPDATE cliente SET 
                                logradouro = ?, numero = ?, complemento = ?, 
                                bairro = ?, cidade = ?, cep = ? 
                                WHERE id_cliente = ?";
        
        $stmt_update = $pdo->prepare($sql_update_endereco);
        $stmt_update->execute([
            $logradouro, $numero, $complemento, 
            $bairro, $cidade, $cep, 
            $id_cliente
        ]);

        // 4. CALCULA O VALOR TOTAL E INSERE O PEDIDO PRINCIPAL
        foreach ($itens_carrinho as $item) {
            // Garante que o total seja calculado corretamente
            $valor_total += $item['preco_unitario'] * $item['quantidade'];
        }

        $sql_pedido = "INSERT INTO pedido (id_cliente, id_promocao, valor_total, status) 
                       VALUES (?, ?, ?, 'Pedido Recebido')";
                       
        $stmt_pedido = $pdo->prepare($sql_pedido);
        $stmt_pedido->execute([$id_cliente, $id_promocao, $valor_total]);
        
        $id_novo_pedido = $pdo->lastInsertId();

        // 5. INSERE OS ITENS DO PEDIDO
        $sql_item = "INSERT INTO item_pedido (id_pedido, id_produto, quantidade, preco_unitario) 
                     VALUES (?, ?, ?, ?)";
        $stmt_item = $pdo->prepare($sql_item);

        foreach ($itens_carrinho as $item) {
            $stmt_item->execute([
                $id_novo_pedido, 
                $item['id_produto'], 
                $item['quantidade'], 
                $item['preco_unitario']
            ]);
        }

        // 6. FINALIZA A TRANSAÇÃO E LIMPA A SESSÃO
        $pdo->commit();
        unset($_SESSION['carrinho_temp']); // Limpa o carrinho após a finalização
        
        // Redireciona para a página de acompanhamento
        header("Location: ../acompanhamento.php?pedido=$id_novo_pedido");
        exit;

    } catch (PDOException $e) {
        // 7. DESFAZ TUDO EM CASO DE ERRO
        $pdo->rollBack();
        die("Erro ao processar pedido: " . $e->getMessage());
    }

} else {
    // Se a página for acessada sem o POST do formulário
    header("Location: ../Principal.php");
    exit;
}
?>