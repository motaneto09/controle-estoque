<?php

include '../includes/auth.php';
include '../includes/conexao.php';

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
    

$perfil = $_SESSION['usuario_perfil'] ?? '';

if ($perfil !== 'Administrador') {
    header("Location: dashboard.php");
    exit;
}

$sql = "SELECT *
        FROM logs
        WHERE 1=1";

if ($data_inicio && $data_fim) {
    $sql .= " AND DATE(data_hora) BETWEEN '$data_inicio' AND '$data_fim'";
}

$sql .= " ORDER BY data_hora DESC LIMIT 30";

$resultado = $conexao->query($sql);

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <title>Logs de Atividade - Controle de Estoque</title>

    <?php include '../includes/head.php'; ?>
</head>

<body>

<div class="dashboard-container">

    <?php include '../includes/sidebar.php'; ?>

    <main class="dashboard-content">

        <h1>Logs do Sistema</h1>

        <form method="GET" style="margin-bottom:15px; display:flex; gap:10px; align-items:center;">


    
    <label>De:</label>
    <input type="date" name="data_inicio" value="<?= htmlspecialchars($data_inicio); ?>">
    
    <label>Até:</label>
    <input type="date" name="data_fim" value="<?= htmlspecialchars($data_fim); ?>">

    <button type="submit">Filtrar</button>

    <button type="button"
        class="btn-login btn-small"
        onclick="exportarLogs()">
    Exportar
    </button>



</form>


        <table class="tabela-ativos">

            <thead>

                <tr>
                    <th>Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>ID Referência</th>
                    <th>Detalhes</th>
                </tr>

            </thead>

            <tbody>

                <?php if ($resultado && $resultado->num_rows > 0): ?>

                    <?php while ($log = $resultado->fetch_assoc()): ?>

                        <tr>

                            <td>
                                <?= date(
                                    'd/m/Y H:i:s',
                                    strtotime($log['data_hora'])
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['usuario_nome']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['acao']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['referencia_id']); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($log['detalhes']); ?>
                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>
                        <td colspan="5">
                            Nenhum log encontrado.
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

<script>
function exportarLogs() {
    const dataInicio = document.querySelector('[name="data_inicio"]').value;
    const dataFim = document.querySelector('[name="data_fim"]').value;

    let url = 'exportar_logs.php?data_inicio=' 
        + encodeURIComponent(dataInicio) 
        + '&data_fim=' 
        + encodeURIComponent(dataFim);

    window.location.href = url;
}
</script>


</body>
</html>