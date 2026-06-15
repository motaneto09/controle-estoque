<?php
// includes/conexao.php

$host = "localhost";
$usuario = "root";
$senha = ""; 
$banco = "controle_estoque";

// Força o PHP a nos mostrar o erro real se a conexão falhar
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexao = new mysqli($host, $usuario, $senha, $banco);
    $conexao->set_charset("utf8mb4");
} catch (Exception $e) {
    // Se der erro, ele vai cuspir o motivo real na tela para nós
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>
            <strong>Erro Crítico de Conexão:</strong> " . $e->getMessage() . "
         </div>");
}

// =========================================================================
// DEFINE A URL BASE DINAMICAMENTE PARA DESENVOLVIMENTO OU SERVIDOR IIS
// =========================================================================
if ($_SERVER['HTTP_HOST'] == 'localhost' && strpos($_SERVER['REQUEST_URI'], '/controle-estoque') !== false) {
    define('URL_BASE', 'http://localhost/controle-estoque/');
} else {
    define('URL_BASE', 'http://localhost/');
}
?>