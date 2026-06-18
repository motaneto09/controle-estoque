<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário NÃO está logado, manda para o login
if (!isset($_SESSION['usuario_id'])) {
    // Se por acaso a conexao não foi incluída ainda, inclui agora
    if (!defined('URL_BASE')) {
        require_once __DIR__ . '/conexao.php';
    }
    header("Location: " . URL_BASE . "login.php");
    exit;
}
?>