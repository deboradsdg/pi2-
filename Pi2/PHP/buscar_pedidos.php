<?php
// Arquivo: Pi2/PHP/buscar_pedidos.php

// 1. INICIA O BUFFER DE SAÍDA
ob_start();
session_start();
require_once 'conexao.php'; 
// 2. DESCARREGA E APAGA QUALQUER SAÍDA DO conexao.php (como o echo "Conexão...")
ob_end_clean(); 

// Garante que a resposta será JSON
header('Content-Type: application/json');

// Garante que o cliente está logado
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'cliente') {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado ou usuário não é cliente']);
    exit;
}

$idCliente = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT id_pedido, data_pedido, status, valor_total 
        FROM pedido
        WHERE id_cliente = ? 
        ORDER BY data_pedido DESC
    ");
    $stmt->execute([$idCliente]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($pedidos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar pedidos: ' . $e->getMessage()]);
}
?>
