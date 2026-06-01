<?php

// Ativar erros apenas em desenvolvimento
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

include '../includes/auth.php';
include '../includes/conexao.php';
include '../includes/log.php';

$perfil = $_SESSION['usuario_perfil'] ?? '';

if ($perfil !== 'Administrador') {
    header("Location: dashboard.php");
    exit;
}

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {

    $id = (int) $_POST['id'];

    $sql = "DELETE FROM ativos WHERE id = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {

    

    if ($stmt->affected_rows > 0) {

    registrarLog(
        $conexao,
        "Excluiu ativo",
        $id,
        "Ativo removido do sistema"
    );

    header("Location: excluir.php?msg=excluido");
    exit;
}

} else {
    die("Erro SQL ao excluir: " . $stmt->error);
}
}

if (isset($_GET['msg']) && $_GET['msg'] === 'excluido') {
    $mensagem = "Ativo excluído com sucesso!";
}

$sql = "SELECT 
            id,
            categoria,
            tipo_equipamento,
            descricao,
            service_tag,
            status
        FROM ativos
        ORDER BY id DESC";

$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <title>Excluir Ativos - Controle de Estoque</title>

    <?php include '../includes/head.php'; ?>

</head>

<body>

<div class="dashboard-container">

    <?php include '../includes/sidebar.php'; ?>

    <main class="dashboard-content">

        <h1>Excluir Ativos</h1>

        <?php if (!empty($mensagem)): ?>
            <p class="mensagem-sucesso"><?= htmlspecialchars($mensagem); ?></p>
        <?php endif; ?>

        <table class="tabela-ativos">

            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Service Tag</th>
                    <th>Status</th>
                    <th>Ação</th>
                </tr>
            </thead>

            <tbody>

                <?php if ($resultado && $resultado->num_rows > 0): ?>

                    <?php while ($ativo = $resultado->fetch_assoc()): ?>

                        <tr>
                            <td><?= htmlspecialchars($ativo['categoria']); ?></td>
                            <td><?= htmlspecialchars($ativo['tipo_equipamento']); ?></td>
                            <td><?= htmlspecialchars($ativo['descricao']); ?></td>
                            <td><?= htmlspecialchars($ativo['service_tag']); ?></td>
                            <td><?= htmlspecialchars($ativo['status']); ?></td>

                            <td>
                                <form method="POST"
                                      onsubmit="return confirm('Tem certeza que deseja excluir este ativo?');">

                                    <input type="hidden"
                                           name="id"
                                           value="<?= (int) $ativo['id']; ?>">

                                    <button type="submit" class="btn-excluir">
                                        Excluir
                                    </button>

                                </form>
                            </td>
                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5">Nenhum ativo cadastrado.</td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </main>

</div>

<script>
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');

if (toggle && sidebar) {
    if (localStorage.getItem('sidebar') === 'collapsed') {
        sidebar.classList.add('sidebar-collapsed');
    }

    toggle.addEventListener('click', () => {
        sidebar.classList.toggle('sidebar-collapsed');

        if (sidebar.classList.contains('sidebar-collapsed')) {
            localStorage.setItem('sidebar', 'collapsed');
        } else {
            localStorage.setItem('sidebar', 'expanded');
        }
    });
}
</script>

</body>
</html>