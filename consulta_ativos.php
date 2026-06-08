<?php

session_start();

include 'includes/conexao.php';

$perfil = $_SESSION['usuario_perfil'] ?? '';

$filtro_service_tag = trim($_GET['service_tag'] ?? '');
$filtro_descricao = trim($_GET['descricao'] ?? '');
$filtro_categoria = trim($_GET['categoria'] ?? '');
$filtro_status = $_GET['status'] ?? '';
$filtro_pendencia = $_GET['pendencia'] ?? '';

$pode_editar = in_array($perfil, ['Administrador', 'AdmVendas']);
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

if ($filtro_status === 'estoque') {

    $sql .= " AND status <> 'Alugado'";

} elseif (!empty($filtro_status)) {

    $sql .= " AND status = '$filtro_status'";
}

if ($filtro_pendencia === 'cliente') {
    $sql .= " AND status = 'Alugado' AND (cliente IS NULL OR cliente = '')";
}

if ($filtro_pendencia === 'nni') {
    $sql .= " AND status = 'Alugado' AND (nni IS NULL OR nni = '')";
}

$sql .= " ORDER BY id DESC";

$stmt = $conexao->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$resultado = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <title>Consultar Ativos - Controle de Estoque</title>

    <?php include 'includes/head.php'; ?>

</head>

<body>

<div class="dashboard-container">

    <?php include 'includes/sidebar.php'; ?>

        

    <main class="dashboard-content">

    <?php if (isset($_GET['msg']) && $_GET['msg'] === 'cadastrado'): ?>
    <div class="alert alert-success">
        Ativo cadastrado com sucesso!
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'atualizado'): ?>
    <div class="alert alert-success">
        Ativo atualizado com sucesso!
    </div>
<?php endif; ?>

        <h1>Consulta de Ativos</h1>

        <form method="GET" class="filtros-consulta">

            <input type="text"
                   name="service_tag"
                   placeholder="Service Tag"
                   value="<?= htmlspecialchars($filtro_service_tag); ?>">

            <input type="text"
                   name="descricao"
                   placeholder="Descrição"
                   value="<?= htmlspecialchars($filtro_descricao); ?>">

            <select name="categoria">

                <option value="">Todas as categorias</option>

                <?php foreach ($categorias as $categoria): ?>
                    <option value="<?= $categoria; ?>"
                        <?= ($filtro_categoria === $categoria) ? 'selected' : ''; ?>>
                        <?= $categoria; ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <button type="submit" class="btn-primary">Buscar</button>


            <a href="consulta_ativos.php" class="btn-limpar">Limpar</a>

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

                <?php if ($resultado->num_rows > 0): ?>

                    <?php while ($ativo = $resultado->fetch_assoc()): ?>

                        <tr>
                            <td><?= htmlspecialchars($ativo['categoria']); ?></td>
                            <td><?= htmlspecialchars($ativo['descricao']); ?></td>
                            <td><?= (int)($ativo['quantidade'] ?? 1); ?></td>
                            <td><?= htmlspecialchars($ativo['service_tag']); ?></td>
                            <td><?= htmlspecialchars($ativo['status']); ?></td>
                            <?php if ($pode_visualizar): ?>

<td>

    <a href="cadastro_ativo.php?id=<?= (int)$ativo['id']; ?>&modo=visualizar&service_tag=<?= urlencode($filtro_service_tag); ?>&descricao=<?= urlencode($filtro_descricao); ?>&categoria=<?= urlencode($filtro_categoria); ?>"
   class="btn-visualizar">
    Visualizar
</a>

    <?php if ($pode_editar): ?>

        <a href="cadastro_ativo.php?id=<?= (int)$ativo['id']; ?>&service_tag=<?= urlencode($filtro_service_tag); ?>&descricao=<?= urlencode($filtro_descricao); ?>&categoria=<?= urlencode($filtro_categoria); ?>"
   class="btn-editar">
    Editar
</a>

    <?php endif; ?>

</td>

<?php endif; ?>

                            </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="<?= $pode_editar ? '6' : '5'; ?>">
                            Nenhum ativo encontrado.
                        </td>
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

</body>
</html>