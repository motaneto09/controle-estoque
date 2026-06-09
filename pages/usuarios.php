<?php

include '../includes/auth.php';
include '../includes/conexao.php';
include '../includes/log.php';

$perfil = $_SESSION['usuario_perfil'] ?? '';
$usuarioLogadoId = $_SESSION['usuario_id'] ?? 0;

if ($perfil !== 'Administrador') {
    header("Location: dashboard.php");
    exit;
}

$mensagem = '';
$modo_edicao = false;
$usuarioEditar = [];

// EXCLUIR USUÁRIO
if (isset($_GET['excluir']) && is_numeric($_GET['excluir'])) {

    $idExcluir = (int) $_GET['excluir'];

    // Buscar dados do usuário que será excluído
$sqlCheck = "SELECT is_root FROM usuarios WHERE id = ?";
$stmtCheck = $conexao->prepare($sqlCheck);
$stmtCheck->bind_param("i", $idExcluir);
$stmtCheck->execute();
$resultadoCheck = $stmtCheck->get_result();
$usuarioExcluir = $resultadoCheck->fetch_assoc();

// Buscar dados do usuário logado
$sqlLogado = "SELECT is_root FROM usuarios WHERE id = ?";
$stmtLogado = $conexao->prepare($sqlLogado);
$stmtLogado->bind_param("i", $usuarioLogadoId);
$stmtLogado->execute();
$resultadoLogado = $stmtLogado->get_result();
$usuarioLogado = $resultadoLogado->fetch_assoc();

// 🚫 Não excluir a si mesmo
if ($idExcluir == $usuarioLogadoId) {

    $mensagem = "Você não pode excluir seu próprio usuário.";

// 🚫 Não excluir ROOT
} elseif ($usuarioExcluir['is_root'] == 1) {

    $mensagem = "Usuário ROOT não pode ser excluído!";

// 🚫 Só ROOT pode excluir usuários
} elseif ($usuarioLogado['is_root'] != 1) {

    $mensagem = "Apenas o ROOT pode excluir usuários.";

} else {


        $sqlExcluir = "DELETE FROM usuarios WHERE id = ?";
        $stmtExcluir = $conexao->prepare($sqlExcluir);
        $stmtExcluir->bind_param("i", $idExcluir);

        if ($stmtExcluir->execute()) {
            registrarLog(
    $conexao,
    "Excluiu usuário",
    $idExcluir,
    "Usuário removido do sistema"
);
            header("Location: usuarios.php?msg=excluido");
            exit;
        } else {
            $mensagem = "Erro ao excluir usuário.";
        }
    }
}

// CARREGAR USUÁRIO PARA EDIÇÃO
if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {

    $idEditar = (int) $_GET['editar'];

    $sqlBusca = "SELECT id, nome, email, perfil 
                 FROM usuarios 
                 WHERE id = ?";

    $stmtBusca = $conexao->prepare($sqlBusca);
    $stmtBusca->bind_param("i", $idEditar);
    $stmtBusca->execute();

    $resultadoBusca = $stmtBusca->get_result();

    if ($resultadoBusca->num_rows > 0) {
        $usuarioEditar = $resultadoBusca->fetch_assoc();
        $modo_edicao = true;
    }
}

// SALVAR / ATUALIZAR USUÁRIO
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $id = $_POST['id'] ?? '';

    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $perfilUsuario = trim($_POST['perfil'] ?? '');

    if ($nome === '' || $email === '' || $perfilUsuario === '') {

        $mensagem = "Preencha todos os campos obrigatórios.";

    } else {

        if (!empty($id)) {

            // Verifica duplicidade ignorando o próprio usuário
            $sqlVerifica = "SELECT id FROM usuarios 
                            WHERE email = ? 
                            AND id != ?";

            $stmtVerifica = $conexao->prepare($sqlVerifica);
            $stmtVerifica->bind_param("si", $email, $id);
            $stmtVerifica->execute();

            $resultadoVerifica = $stmtVerifica->get_result();

            if ($resultadoVerifica->num_rows > 0) {

                $mensagem = "Já existe outro usuário com esse login.";

            } else {

                if ($senha !== '') {

                    $senhaCriptografada = md5($senha);

                    $sqlUpdate = "UPDATE usuarios SET 
                                    nome = ?,
                                    email = ?,
                                    senha = ?,
                                    perfil = ?
                                  WHERE id = ?";

                    $stmtUpdate = $conexao->prepare($sqlUpdate);
                    $stmtUpdate->bind_param(
                        "ssssi",
                        $nome,
                        $email,
                        $senhaCriptografada,
                        $perfilUsuario,
                        $id
                    );

                } else {

                    $sqlUpdate = "UPDATE usuarios SET 
                                    nome = ?,
                                    email = ?,
                                    perfil = ?
                                  WHERE id = ?";

                    $stmtUpdate = $conexao->prepare($sqlUpdate);
                    $stmtUpdate->bind_param(
                        "sssi",
                        $nome,
                        $email,
                        $perfilUsuario,
                        $id
                    );
                }

                if ($stmtUpdate->execute()) {
                    registrarLog(
    $conexao,
    "Editou usuário",
    $id,
    "Usuário: " . $email
);
                    header("Location: usuarios.php?msg=atualizado");
                    exit;
                } else {
                    $mensagem = "Erro ao atualizar usuário.";
                }
            }

        } else {

            if ($senha === '') {

                $mensagem = "Informe uma senha para o novo usuário.";

            } else {

                $sqlVerifica = "SELECT id FROM usuarios WHERE email = ?";
                $stmtVerifica = $conexao->prepare($sqlVerifica);
                $stmtVerifica->bind_param("s", $email);
                $stmtVerifica->execute();

                $resultadoVerifica = $stmtVerifica->get_result();

                if ($resultadoVerifica->num_rows > 0) {

                    $mensagem = "Já existe um usuário cadastrado com esse login.";

                } else {

                    $senhaCriptografada = md5($senha);

                    $sqlInsert = "INSERT INTO usuarios 
                                    (nome, email, senha, perfil) 
                                  VALUES (?, ?, ?, ?)";

                    $stmtInsert = $conexao->prepare($sqlInsert);
                    $stmtInsert->bind_param(
                        "ssss",
                        $nome,
                        $email,
                        $senhaCriptografada,
                        $perfilUsuario
                    );

                    if ($stmtInsert->execute()) {
                        registrarLog(
    $conexao,
    "Cadastrou usuário",
    $stmtInsert->insert_id,
    "Usuário: " . $email
);
                        header("Location: usuarios.php?msg=cadastrado");
                        exit;
                    } else {
                        $mensagem = "Erro ao cadastrar usuário.";
                    }
                }
            }
        }
    }
}

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cadastrado') {
        $mensagem = "Usuário cadastrado com sucesso!";
    } elseif ($_GET['msg'] === 'atualizado') {
        $mensagem = "Usuário atualizado com sucesso!";
    } elseif ($_GET['msg'] === 'excluido') {
        $mensagem = "Usuário excluído com sucesso!";
    }
}

$sqlUsuarios = "SELECT id, nome, email, perfil
                FROM usuarios 
                WHERE is_root = 0
                ORDER BY id DESC";
$resultadoUsuarios = $conexao->query($sqlUsuarios);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <title>Cadastro de Usuário - Controle de Estoque</title>

    <?php include '../includes/head.php'; ?>

</head>

<body>

<div class="dashboard-container">

    <?php include '../includes/sidebar.php'; ?>

    <main class="dashboard-content">

        <h1><?= $modo_edicao ? 'Editar Usuário' : 'Cadastro de Usuários'; ?></h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-sucesso"><?= htmlspecialchars($mensagem); ?></p>
        <?php endif; ?>

        <form method="POST" class="form-padrao">

            <input type="hidden"
                   name="id"
                   value="<?= htmlspecialchars($usuarioEditar['id'] ?? ''); ?>">

            <div class="input-group">
                <label>Nome</label>

                <input type="text"
                       name="nome"
                       value="<?= htmlspecialchars($usuarioEditar['nome'] ?? ''); ?>"
                       required>
            </div>

            <div class="input-group">
                <label>Usuário</label>

                <input type="text"
                       name="email"
                       value="<?= htmlspecialchars($usuarioEditar['email'] ?? ''); ?>"
                       required>
            </div>

            <div class="input-group">
                <label>
                    Senha <?= $modo_edicao ? '(preencha apenas se quiser alterar)' : ''; ?>
                </label>

                <input type="password"
                       name="senha"
                       <?= $modo_edicao ? '' : 'required'; ?>>
            </div>

            <div class="input-group">
                <label>Perfil</label>

                <select name="perfil" required>
                    <option value="">Selecione</option>

                    <option value="Administrador"
                        <?= (($usuarioEditar['perfil'] ?? '') === 'Administrador') ? 'selected' : ''; ?>>
                        Administrador
                    </option>

                    <option value="AdmVendas"
                        <?= (($usuarioEditar['perfil'] ?? '') === 'AdmVendas') ? 'selected' : ''; ?>>
                        AdmVendas
                    </option>

                    <option value="Comercial"
                        <?= (($usuarioEditar['perfil'] ?? '') === 'Comercial') ? 'selected' : ''; ?>>
                        Comercial
                    </option>
                </select>
            </div>

            <button type="submit" class="btn-login">
                <?= $modo_edicao ? 'Atualizar Usuário' : 'Cadastrar Usuário'; ?>
            </button>

            <?php if ($modo_edicao): ?>
                <button type="button" class="btn-voltar"
    onclick="window.location.href='usuarios.php'">
    Cancelar edição
</button>
            <?php endif; ?>

        </form>

        <h2 class="section-title">Usuários Cadastrados</h2>

        <table class="tabela-ativos">

            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Usuário</th>
                    <th>Perfil</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($resultadoUsuarios && $resultadoUsuarios->num_rows > 0): ?>

                    <?php while ($usuario = $resultadoUsuarios->fetch_assoc()): ?>

                        <tr>
                            <td><?= htmlspecialchars($usuario['nome']); ?></td>
                            <td><?= htmlspecialchars($usuario['email']); ?></td>
                            <td><?= htmlspecialchars($usuario['perfil']); ?></td>

                            <td>
                                <button type="button" class="btn-editar"
    onclick="window.location.href='usuarios.php?editar=<?= (int)$usuario['id']; ?>'">
    Editar
</button>


                                <?php if ((int) $usuario['id'] !== (int) $usuarioLogadoId): ?>

                                    <button type="button" class="btn-excluir"
    onclick="if(confirm('Tem certeza que deseja excluir este usuário?')) 
        window.location.href='usuarios.php?excluir=<?= (int)$usuario['id']; ?>'">
    Excluir
</button>

                                <?php endif; ?>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="4">Nenhum usuário cadastrado.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </main>

</div>

<script>

const toggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');

if(localStorage.getItem('sidebar') === 'collapsed'){
    sidebar.classList.add('sidebar-collapsed');
}

toggle.addEventListener('click', () => {

    sidebar.classList.toggle('sidebar-collapsed');

    if(sidebar.classList.contains('sidebar-collapsed')){
        localStorage.setItem('sidebar', 'collapsed');
    } else {
        localStorage.setItem('sidebar', 'expanded');
    }

});

</script>

</body>
</html>