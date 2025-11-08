<?php
// Arquivo: Pi2/PHP/admin/addadmin.php
require_once '../conexao.php'; 
session_start();

// 1. VERIFICAÇÃO DE PERMISSÃO (COMENTADA PARA DEBUG/TESTE)
// if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
//     header("Location: ../../Principal.php");
//     exit;
// }

// Inicializa a variável de mensagem para evitar o Warning
$mensagem = '';
$sucesso = false;

if (isset($_POST['submit'])) {
    // 2. CAPTURA E VALIDAÇÃO DE DADOS
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $confirma_senha = $_POST['confirma_senha'];

    if (empty($nome) || empty($usuario) || empty($email) || empty($senha)) {
        $mensagem = "Todos os campos são obrigatórios.";
    } elseif ($senha !== $confirma_senha) {
        $mensagem = "A senha e a confirmação não coincidem.";
    } else {
        // 3. SEGURANÇA: CRIPTOGRAFIA DA SENHA
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        // 4. INSERÇÃO NO BANCO DE DADOS
        $sql = "INSERT INTO administrador (nome, usuario, email, senha) VALUES (?, ?, ?, ?)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $usuario, $email, $senha_hash]);
            $mensagem = "Administrador **$usuario** cadastrado com sucesso!";
            $sucesso = true;
        } catch (PDOException $e) {
            // Verifica erro de duplicidade (UNIQUE constraint violation)
            if ($e->getCode() == '23000') {
                $mensagem = "Erro: O usuário ou e-mail já estão em uso.";
            } else {
                $mensagem = "Erro ao cadastrar: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Administrador</title>
    </head>
<body>
    <header>
        <a href="painel_admin.php">Voltar ao Painel</a>
    </header>
    <main>
        <h1>Adicionar Novo Administrador</h1>
        
        <?php if ($mensagem): ?>
            <p style="color: <?php echo $sucesso ? 'green' : 'red'; ?>;">
                <?php echo $mensagem; ?>
            </p>
        <?php endif; ?>

        <form action="addadmin.php" method="POST">
            <label>Nome Completo:</label>
            <input type="text" name="nome" required><br><br>
            
            <label>Nome de Usuário (Login):</label>
            <input type="text" name="usuario" required><br><br>

            <label>E-mail:</label>
            <input type="email" name="email" required><br><br>
            
            <label>Senha:</label>
            <input type="password" name="senha" required><br><br>
            
            <label>Confirmar Senha:</label>
            <input type="password" name="confirma_senha" required><br><br>
            
            <input type="submit" name="submit" value="Cadastrar Admin">
        </form>
    </main>
</body>
</html>