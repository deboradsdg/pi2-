<?php

require_once 'conexao.php'; 

if (isset($_POST['submit'])) {
    
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $telefone = $_POST['telefone'];
    
    $logradouro = $_POST['logradouro']; 
    $numero = $_POST['numero'];
    $complemento = $_POST['complemento']; 
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];
    $cep = $_POST['cep'];
    
    $senha = $_POST['senha'];
    $confSenha = $_POST['confSenha'];
    
    if ($senha !== $confSenha) {
        die("As senhas não coincidem. Tente novamente.");
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO cliente (nome, email, telefone, logradouro, numero, complemento, bairro, cidade, uf, cep, senha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"; 
    
   
    try {
        $stmt = $pdo->prepare($sql);
        
    
        $stmt->execute([
            $nome, 
            $email, 
            $telefone, 
            $logradouro, 
            $numero, 
            $complemento, 
            $bairro, 
            $cidade, 
            $uf, 
            $cep, 
            $senha_hash 
        ]);
        
       
        echo "Cadastro completo realizado com sucesso! 🎉 Você será redirecionado.";
        header("Refresh: 3; url=../index.html");
        exit;
        
    } catch (PDOException $e) {
        
        if ($e->getCode() == '23000') {
            echo "Erro: Alguma informação exclusiva (como Usuário ou Email) já está cadastrada.";
        } else {
            echo "Erro ao inserir dados: " . $e->getMessage();
        }
    }
} else {
    echo "Acesso inválido. Por favor, utilize o formulário de cadastro.";
}
?>
