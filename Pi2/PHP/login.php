<?php
// Arquivo: Pi2/PHP/login.php
require_once 'conexao.php'; 
session_start();

// Define a mensagem de erro padrão
$erro_login = "E-mail ou senha incorretos. Tente novamente.";

if (isset($_POST['submit'])) {
    
    // 1. Coleta os dados do formulário
    // Usamos 'identificador' que é o nome do campo no seu HTML, mas o conteúdo é o EMAIL.
    $email_digitado = trim($_POST['identificador']); 
    $senha_digitada = $_POST['senha'];
    
    //mesmo local de login, primeiro ele procura na tabela de admin depois na de cliente
    $sql_admin = "SELECT id_admin, nome, senha, email FROM administrador WHERE email = ?";
    
    $usuario = null;
    $user_type = null;

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt_admin = $pdo->prepare($sql_admin);
        $stmt_admin->execute([$email_digitado]);
        $admin = $stmt_admin->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            $usuario = $admin;
            $user_type = 'admin';
            $id_column = 'id_admin';
            $redirect_url = 'admin/painel_admin.php';
        }

    } catch (PDOException $e) {
        die("Erro de conexão ou busca do Admin: " . $e->getMessage());
    }
    
    // ====================================================================
    // B. SE NÃO FOR ADMIN, TENTA LOGAR COMO CLIENTE (Busca SOMENTE pelo email)
    // ====================================================================
    
    if (!$usuario) {
        $sql_cliente = "SELECT id_cliente, nome, senha, email FROM cliente WHERE email = ?";
        
        try {
            $stmt_cliente = $pdo->prepare($sql_cliente);
            $stmt_cliente->execute([$email_digitado]); 
            $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

            if ($cliente) {
                $usuario = $cliente;
                $user_type = 'cliente';
                $id_column = 'id_cliente';
                $redirect_url = 'Principal.php';
            }
            
        } catch (PDOException $e) {
            die("Erro de conexão ou busca do Cliente: " . $e->getMessage());
        }
    }

    // ====================================================================
    // C. VERIFICAÇÃO FINAL
    // ====================================================================
    if ($usuario) {
        // Verifica a senha (funciona para Admin e Cliente)
        if (password_verify($senha_digitada, $usuario['senha'])) {
            
            // Login BEM-SUCEDIDO
            $_SESSION['user_id'] = $usuario[$id_column];
            $_SESSION['user_name'] = $usuario['nome'];
            $_SESSION['user_type'] = $user_type; 
            
            header("Location: $redirect_url");
            exit;
            
        } else {
            // Senha incorreta
            die($erro_login);
        }
    } else {
        // Usuário (e-mail) não encontrado
        die($erro_login);
    }

} else {
    // Acesso direto sem POST
    header("Location: ../HTML/Cadastro.html");
    exit;
}
?>