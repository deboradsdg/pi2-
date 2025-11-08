<?php
require_once '../conexao.php'; 
session_start();

if (isset($_POST['submit'])) {
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $tipo = $_POST['tipo'];
    $preco = $_POST['preco'];
    $imagem_url = $_POST['imagem_url']; 

    // CORREÇÃO: Incluir a coluna 'tipo' e um placeholder (?)
    $sql = "INSERT INTO produto (nome, descricao, tipo, preco, imagem_url) VALUES (?, ?, ?, ?, ?)"; 
    
    try {
        $stmt = $pdo->prepare($sql);
        // Ajustar o execute para passar as 5 variáveis na ordem correta
        $stmt->execute([$nome, $descricao, $tipo, $preco, $imagem_url]); 
        echo "Produto **$nome** cadastrado com sucesso!";
    } catch (PDOException $e) {
        echo "Erro ao cadastrar produto: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
     <meta charset="UTF-8">
     <link rel="stylesheet" href="../../CSS/admin.css"> 
     <link rel="shortcut icon" href="../MIDIAS/IMAGENS/PRINCIPAL.png" type="image">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE PIZZA ONE</title>
</head>
<body>
 <h1>Cadastro de Novo Produto</h1> 
 <form action="cadastropizza.php" method="POST">
    <label>Nome do Produto:</label>
    <input type="text" name="nome" required><br><br> 
    <label>Descrição:</label>
    <textarea name="descricao" required></textarea><br><br>
    <label>Tipo:</label>
    <input type="text" name="tipo" required placeholder="Ex: Pizza, Pizza Doce, Bebida"><br><br> 
    
    <label>Preço:</label>
    <input type="number" name="preco" step="0.01" required><br><br>
    
    <label>URL da Imagem:</label>
 <input type="text" name="imagem_url" required placeholder="../MIDIAS/IMAGENS/nome.png"><br><br>
 <input type="submit" name="submit" value="Cadastrar Produto">
</form>
</body>
</html>