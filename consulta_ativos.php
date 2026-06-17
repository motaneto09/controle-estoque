<?php

session_start();

include __DIR__ . '/includes/conexao.php';

$mensagem = '';

if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cadastrado') {
        $mensagem = "Ativo cadastrado com sucesso!";
    }
    if ($_GET['msg'] === 'atualizado') {
        $mensagem = "Ativo editado com sucesso!";
    }
    if ($_GET['msg'] === 'excluido') {
        $mensagem = "Ativo excluído com sucesso!";
    }
}

$perfil = $_SESSION['usuario_perfil'] ?? '';

$filtro_service_tag = trim($_GET['service_tag'] ?? '');
$filtro_descricao   = trim($_GET['descricao'] ?? '');
$filtro_categoria   = trim($_GET['categoria'] ?? '');
$filtro_status      = trim($_GET['status'] ?? '');
$filtro_pendencia   = trim($_GET['pendencia'] ?? '');

$pode_editar     = in_array($perfil, ['Administrador', 'AdmVendas']);
$pode_visualizar = in_array($perfil, ['Administrador', 'AdmVendas', 'Comercial']);

$categorias = [
    'Notebook',
    'Desktop',
    'Monitor',
    'Infraestrutura',
    'Network',
    'Armazenamento',
    'Impressora',
    'Disco',
    'Memoria'
];

// Construção segura da Query com Prepared Statements
$sql = "SELECT * FROM ativos WHERE 1=1";
$params = [];
$types = "";

if ($filtro_service_tag !== '') {
    $sql .= " AND service_tag LIKE ?";
    $params[] = "%{$filtro_service_tag}%";
    $types .= "s";
}

if ($filtro_descricao !== '') {
    $sql .= " AND descricao LIKE ?";
    $params[] = "%{$filtro_descricao}%";
    $types .= "s";
}

if ($filtro_categoria !== '') {
    $sql .= " AND categoria = ?";
    $params[] = $filtro_categoria;
    $types .= "s";
}

// CORREÇÃO: Filtro de status utilizando Prepared Statements de forma segura
if ($filtro_status === 'estoque') {
    $sql .= " AND status <> 'Alugado'";
} elseif ($filtro_status !== '') {
    $sql .= " AND status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if ($filtro_pendencia === 'cliente') {
    $sql .= " AND status = 'Alugado' AND (cliente IS NULL OR cliente = '')";
} elseif ($filtro_pendencia === 'nni') {
    $sql .= " AND status = 'Alugado' AND (nni IS NULL OR nni = '')";
}

$sql .= " ORDER BY id DESC";

$stmt = $conexao->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();

// Definição da quantidade de colunas da tabela para o colspan correto
$total_colunas = $pode_visualizar ? 6 : 5;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Consultar Ativos - Controle de Estoque</title>
    <?php include __DIR__ . '/includes/head.php'; ?>

</head>
<body>

<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

        
    <main class="dashboard-content">

        <h1>Consulta de Ativos</h1>

        <?php if (!empty($mensagem)): ?>
            <div class="mensagem-sucesso alert alert-success">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <form method="GET" class="filtros-consulta">

            <input type="text"
                   name="service_tag"
                   placeholder="Service Tag"
                   value="<?= htmlspecialchars($filtro_service_tag, ENT_QUOTES, 'UTF-8'); ?>">

            <input type="text"
                   name="descricao"
                   placeholder="Descrição"
                   value="<?= htmlspecialchars($filtro_descricao, ENT_QUOTES, 'UTF-8'); ?>">

            <select name="categoria">
                <option value="">Todas as categorias</option>
                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>"
                        <?= ($filtro_categoria === $categoria) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if ($filtro_status !== ''): ?>
                <input type="hidden" name="status" value="<?= htmlspecialchars($filtro_status, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>
            <?php if ($filtro_pendencia !== ''): ?>
                <input type="hidden" name="pendencia" value="<?= htmlspecialchars($filtro_pendencia, ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>

            <button type="submit" class="btn-primary">Buscar</button>
            <button type="button" class="btn-voltar" onclick="window.location.href='consulta_ativos.php'">Limpar</button>

        </form>

        <table class="tabela-ativos">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Service Tag</th>
                    <th>Status</th>
                    <?php if ($pode_visualizar): ?>
                        <th>Ações</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($resultado && $resultado->num_rows > 0): ?>
                    <?php while ($ativo = $resultado->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($ativo['categoria'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($ativo['descricao'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= (int)($ativo['quantidade'] ?? 1); ?></td>
                            <td><?= htmlspecialchars($ativo['service_tag'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($ativo['status'], ENT_QUOTES, 'UTF-8'); ?></td>
                            
                            <?php if ($pode_visualizar): ?>
                                <td>
                                    <button type="button" class="btn-visualizar"
                                            onclick="window.location.href='cadastro_ativo.php?id=<?= (int)$ativo['id']; ?>&modo=visualizar'">
                                        Visualizar
                                    </button>

                                    <?php if ($pode_editar): ?>
                                        <button type="button" class="btn-editar"
                                                onclick="window.location.href='cadastro_ativo.php?id=<?= (int)$ativo['id']; ?>'">
                                            Editar
                                        </button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= $total_colunas; ?>">
                            Nenhum ativo encontrado.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>

<script>
// Lógica do Sidebar Toggle
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

// Fade out suave do alerta de sucesso
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function () {
        const mensagem = document.querySelector('.mensagem-sucesso');
        if (mensagem) {
            mensagem.style.transition = "opacity 0.5s ease";
            mensagem.style.opacity = 0;
            setTimeout(() => mensagem.remove(), 500);
        }
    }, 3000);
});
</script>

</body>
</html>