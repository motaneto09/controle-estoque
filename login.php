<?php
session_start();

include 'includes/conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST['email'];
    $senha = md5($_POST['senha']);

    $sql = "SELECT * FROM usuarios 
            WHERE email = '$email' 
            AND senha = '$senha'";

    $resultado = $conexao->query($sql);

    if ($resultado->num_rows > 0) {

        $usuario = $resultado->fetch_assoc();

        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_perfil'] = $usuario['perfil'];

        header("Location: pages/dashboard.php");
        exit;

    } else {

        $erro = "Usuário ou senha inválidos.";

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

    <title>Login - Controle de Estoque</title>

    <?php include 'includes/head.php'; ?>

</head>

<body>

<div class="login-container">

    <div class="login-box">
        <img src="assets/img/logo.png" class="logo-login" alt="Logo">

        <h1>Controle de Estoque</h1>

        <form method="POST">

    <div class="input-group icon-input">

    <i class="bi bi-person-fill"></i>

    <input type="text"
           name="email"
           placeholder="Usuário"
           required>

    </div>

        <div class="input-group icon-input">

    <i class="bi bi-lock-fill"></i>

    <input type="password"
           name="senha"
           placeholder="Senha"
           required>

        </div>

            <button type="submit" class="btn-login">
                Entrar
            </button>

        </form>
        <div class="footer-text">
           
           <br>
           © 2026 Desenvolvido por deoclecio_mota@iland.com.br
        </div>

        <?php
        if(isset($erro)){
            echo "<p class='erro'>$erro</p>";
        }
        ?>

    </div>

</div>

</body>
</html>