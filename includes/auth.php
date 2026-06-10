<?php
// includes/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário NÃO está logado, manda para a tela de login de forma segura
if (!isset($_SESSION['usuario_id'])) {
    // Usamos o caminho relativo correto baseado em onde este arquivo está sendo chamado
    header("Location: /controle-estoque/login.php");
    exit;
}
?>