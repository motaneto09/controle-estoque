<?php
session_start();

include 'includes/conexao.php';

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (!empty($email) && !empty($senha)) {
        
        // Busca o usuário pelo e-mail utilizando Prepared Statements para total segurança
        $sql = "SELECT id, nome, senha, perfil, is_root FROM usuarios WHERE email = ?";
        
        if ($stmt = $conexao->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows === 1) {
                $usuario = $resultado->fetch_assoc();
                $senha_valida = false;

                // Teste 1: Se a senha já for o hash novo seguro (password_hash)
                if (password_verify($senha, $usuario['senha'])) {
                    $senha_valida = true;
                } 
                // Teste 2: Se a senha ainda for o MD5 antigo que está no seu banco atualmente
                elseif (md5($senha) === $usuario['senha']) {
                    $senha_valida = true;
                    
                    // Opcional: converte e atualiza para o formato novo de forma invisível
                    $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
                    $stmt_up = $conexao->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                    $stmt_up->bind_param("si", $novo_hash, $usuario['id']);
                    $stmt_up->execute();
                    $stmt_up->close();
                }

                if ($senha_valida) {
                    // Define exatamente as sessões que o seu sistema precisa
                    $_SESSION['usuario_id']     = $usuario['id'];
                    $_SESSION['usuario_nome']   = $usuario['nome'];
                    $_SESSION['usuario_perfil'] = $usuario['perfil'];
                    $_SESSION['is_root']        = $usuario['is_root'];

                    header("Location: pages/dashboard.php");
                    exit;
                } else {
                    $erro = "Usuário ou senha inválidos.";
                }
            } else {
                $erro = "Usuário ou senha inválidos.";
            }
            $stmt->close();
        } else {
            $erro = "Erro interno no servidor de autenticação.";
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Login - Controle de Estoque</title>
    <?php include __DIR__ . '/includes/head.php'; ?>
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <img src="assets/img/logo.png" class="logo-login" alt="Logo">

        <h1>Controle de Estoque</h1>

        <?php if (!empty($erro)): ?>
            <div class="alert alert-danger" style="color: #721c24; background-color: #f8d7da; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center;">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group icon-input">
                <i class="bi bi-person-fill"></i>
                <input type="text" 
                       name="email" 
                       placeholder="Usuário" 
                       value="<?= isset($email) ? htmlspecialchars($email, ENT_QUOTES, 'UTF-8') : ''; ?>" 
                       required>
            </div>

            <div class="input-group icon-input">
                <i class="bi bi-lock-fill"></i>
                <input type="password" 
                       name="senha" 
                       placeholder="Senha" 
                       required>
            </div>

            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>
</div>

</body>
</html>