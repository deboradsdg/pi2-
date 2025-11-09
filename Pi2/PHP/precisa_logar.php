<?php
session_start([
    'cookie_path' => '/',
]);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Você precisa estar logado</title>
    <link rel="stylesheet" href="../CSS/style.css">
    <meta http-equiv="refresh" content="3;url=../Principal.php">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #fafafa;
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .mensagem {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 12px rgba(0,0,0,0.15);
        }
        .mensagem h2 {
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="mensagem">
        <h2>⚠️ Você precisa estar logado!</h2>
        <p>Você será redirecionado para a página principal em alguns segundos...</p>
        <p><a href="Principal.php">Clique aqui se não for redirecionado.</a></p>
    </div>
</body>
</html>
