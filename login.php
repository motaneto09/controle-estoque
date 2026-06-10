<?php
// login.php
include 'includes/conexao.php';
session_start();

// Se já estiver logado, joga direto para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: pages/dashboard.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario']);
    $senha = trim($_POST['senha']);

    if (!empty($usuario) && !empty($senha)) {
        // Buscando a senha hash correspondente ao usuário
        $sql = "SELECT id, senha FROM usuarios WHERE usuario = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $usuario);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($id, $senha_hash);
                $stmt->fetch();

                // Verificação segura com password_verify
                if (password_verify($senha, $senha_hash)) {
                    // Proteção contra Session Fixation
                    session_regenerate_id(true);
                    
                    $_SESSION['usuario_id'] = $id;
                    $_SESSION['usuario'] = $usuario;

                    // Registrar log de sucesso (Opcional, dependendo da sua tabela de logs)
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $log_sql = "INSERT INTO logs (usuario, acao, ip) VALUES (?, 'Login efetuado com sucesso', ?)";
                    if ($log_stmt = $conn->prepare($log_sql)) {
                        $log_stmt->bind_param("ss", $usuario, $ip);
                        $log_stmt->execute();
                        $log_stmt->close();
                    }

                    header("Location: pages/dashboard.php");
                    exit;
                } else {
                    $erro = "Usuário ou senha incorretos.";
                }
            } else {
                $erro = "Usuário ou senha incorretos.";
            }
            $stmt->close();
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/img/favicon.png" type="image/x-icon">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-box">
            <img src="assets/img/logo.png" alt="Logo" class="login-logo">
            <h2>Acessar o Sistema</h2>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="input-group">
                    <label die for="usuario">Usuário</label>
                    <input type="text" id="usuario" name="usuario" required autocomplete="username">
                </div>
                <div class="input-group">
                    <label die for="senha">Senha</label>
                    <input type="password" id="senha" name="senha" required autocomplete="current-senha">
                </div>
                <button type="submit" class="btn-login">Entrar</button>
            </form>
        </div>
    </div>
</body>
</html>