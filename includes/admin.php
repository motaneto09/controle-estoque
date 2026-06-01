<?php
include 'auth.php';

if ($_SESSION['usuario_perfil'] !== 'admin') {
    die("Acesso negado.");
}
?>