<?php

session_start();

// Recebe os dados JSON do JavaScript
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (isset($data['itens'])) {
    $_SESSION['carrinho_temp'] = $data['itens'];
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Nenhum item no carrinho.']);
}
?>