<?php
// Arquivo: Pi2/PHP/admin/painel_admin.php
require_once '../conexao.php'; 
session_start();

// 1. VERIFICAÇÃO DE PERMISSÃO (Segurança)
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // Redireciona para a página principal se não for admin
    header("Location: ../../Principal.php");
    exit;
}

// Obtém o nome do administrador logado para uma saudação personalizada
$nome_admin = $_SESSION['user_name'] ?? 'Administrador';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Administração - THE PIZZA ONE</title>
    <link rel="stylesheet" href="../CSS/painel_admin.css">
        
</head>
<body>
    <header>
        <h1>Bem-vindo(a), <?php echo htmlspecialchars($nome_admin); ?>!</h1>
        <a href="../logout.php">Sair</a>
    </header>
    <main>
        <h2>Menu de Controle</h2>
        
        <ul class="admin-menu">
            <li><a href="addadmin.php">🔑 Adicionar Novo Admin</a></li>
            
            <li><a href="cadastropizza.php">🍕 Cadastrar Produtos</a></li>
            <li><a href="ajustar_precos.php">💲 Alterar Preços e Status</a></li>
            
            <li><a href="gerenciar_pedidos.php">📦 Gerenciar Pedidos</a></li>
            
            <li><a href="relatorios.php">📊 Criar Relatórios</a></li>
        </ul>
        
        <div class="section-box">
            <h2>Próximas Ações</h2>
            <p>Utilize o menu acima para navegar. Sugestão: Verifique novos pedidos em **Gerenciar Pedidos**.</p>
        </div>
        
    </main>
</body>
</html>