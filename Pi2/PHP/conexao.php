<?php

// 1. Definição das Variáveis
$host = "50.116.86.45";
$user = "argqor30_thepizzaone";
$password = "LQKJuVD84Q";
$dbname = "argqor30_thepizzaone"; // RENOMEADA AQUI para $dbname (consistente)

// 2. Definição do DSN fora do try/catch (melhor prática)
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";


try {
    // 3. O DSN já está pronto acima, mas você pode deixar a definição dentro do try também
    // $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4"; 

    $pdo = new PDO($dsn, $user, $password, [
        
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

 
    echo "Conexão com o banco de dados **$dbname** estabelecida com sucesso! 🎉";

} catch (PDOException $erro) {
    
    echo "Falha na Conexão: " . $erro->getMessage();
    exit;
}
?>