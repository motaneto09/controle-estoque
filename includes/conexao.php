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
    die("<div style='color:red; font-family:sans-serif; padding:20px;'><strong>Erro Crítico de Conexão:</strong> " . $e->getMessage() . "</div>");
}

// =========================================================================
// DEFINE A URL BASE DINAMICAMENTE PARA DESENVOLVIMENTO OU SERVIDOR IIS
// =========================================================================
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    if (!defined('URL_BASE')) {
        define('URL_BASE', '/controle-estoque/');
    }
} else {
    if (!defined('URL_BASE')) {
        define('URL_BASE', '/');
    }
}
?>