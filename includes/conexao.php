<?php
// Configuração de exibição de erros (mantém ativo para ajudar no deploy)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definição direta da URL_BASE para o ambiente do IIS
if (!defined('URL_BASE')) {
    define('URL_BASE', '/');
}

// Credenciais de conexão com o MariaDB do Servidor
$host    = "localhost"; 
$usuario = "root";  
$senha   = ""; // Vazio, conforme validado no prompt
$banco   = "controle_estoque";

// Criação da conexão utilizando MySQLi
$conexao = mysqli_connect($host, $usuario, $senha, $banco);

// Verifica se houve falha na conexão
if (!$conexao) {
    die("<span style='color:red; font-weight:bold;'>Erro Crítico de Conexão:</span> " . mysqli_connect_error());
}

// Define o charset para evitar problemas com acentuação
mysqli_set_charset($conexao, "utf8mb4");