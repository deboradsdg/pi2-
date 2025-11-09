<?php
session_start();
session_destroy();

// Redireciona para a página principal com um "marcador" na URL
header("Location: Principal.php#produtos");
exit;
?>