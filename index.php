<?php
// index.php
// Se a raiz serve apenas para redirecionar para o login ou dashboard:
session_start();

if (isset($_SESSION['usuario_id'])) {
    header("Location: pages/dashboard.php");
} else {
    header("Location: login.php");
}
exit;