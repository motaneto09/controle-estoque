<?php

include '../includes/auth.php';
include '../includes/conexao.php';

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil = $_SESSION['usuario_perfil'] ?? '';

$total_ativos = $conexao->query("SELECT COUNT(*) AS total FROM ativos")->fetch_assoc()['total'];

$total_alugados = $conexao->query("SELECT COUNT(*) AS total FROM ativos WHERE status = 'Alugado'")->fetch_assoc()['total'];

$total_em_estoque = $total_ativos - $total_alugados;

$categorias = [
    'Notebook',
    'Desktop',
    'Monitor',
    'Infraestrutura',
    'Network',
    'Armazenamento',
    'Impressora'
];

$resumo_categorias = [];

foreach ($categorias as $categoria) {
    $stmt = $conexao->prepare("SELECT COUNT(*) AS total FROM ativos WHERE categoria = ?");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();

    $resumo_categorias[$categoria] = $resultado['total'];
}




$alugado_sem_cliente = $conexao->query("
    SELECT COUNT(*) AS total 
    FROM ativos 
    WHERE status = 'Alugado' 
    AND (cliente IS NULL OR cliente = '')
")->fetch_assoc()['total'];

$alugado_sem_nni = $conexao->query("
    SELECT COUNT(*) AS total 
    FROM ativos 
    WHERE status = 'Alugado' 
    AND (nni IS NULL OR nni = '')
")->fetch_assoc()['total'];

$sem_categoria = $conexao->query("
    SELECT COUNT(*) AS total 
    FROM ativos 
    WHERE categoria IS NULL OR categoria = ''
")->fetch_assoc()['total'];


?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <title>Dashboard - Controle de Estoque</title>

    <?php include '../includes/head.php'; ?>

</head>

<body>

<div class="dashboard-container">

    <?php include '../includes/sidebar.php'; ?>

    <main class="dashboard-content">

    <div class="welcome-box">
    <h1>Bem-vindo, <?= htmlspecialchars($nome); ?></h1>
    <p>Gerencie os ativos e acompanhe o status do estoque.</p>
    </div>

    
    <div class="stats-grid">

        
        <a href="/controle-estoque/consulta_ativos.php?status=estoque" class="stat-card">
            <span class="stat-number"><?= $total_em_estoque; ?></span>
            <span class="stat-title">Em Estoque</span>
        </a>

        <a href="/controle-estoque/consulta_ativos.php?status=Alugado" class="stat-card">
            <span class="stat-number"><?= $total_alugados; ?></span>
            <span class="stat-title">Alugados</span>
        </a>

        
    </div>

    <section class="pendencias-box">

        <h2>Alertas de Pendências</h2>

        <div class="pendencias-grid">

            
            <a href="/controle-estoque/consulta_ativos.php?pendencia=cliente" class="pendencia-card">
                <strong><?= $alugado_sem_cliente; ?></strong>
                <span>Alugados sem Cliente</span>
            </a>

            <a href="/controle-estoque/consulta_ativos.php?pendencia=nni" class="pendencia-card">
                <strong><?= $alugado_sem_nni; ?></strong>
                <span>Alugados sem NNI</span>
            </a>

            
        </div>

    </section>

    <section class="categorias-box">

    <h2>Resumo por Categoria</h2>

    <div class="categorias-grid">

        <?php foreach ($resumo_categorias as $categoria => $total): ?>
            <a href="/controle-estoque/consulta_ativos.php?categoria=<?= urlencode($categoria); ?>" class="categoria-card">
                <strong><?= $total; ?></strong>
                <span><?= htmlspecialchars($categoria); ?></span>
            </a>
        <?php endforeach; ?>

    </div>

</section>

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