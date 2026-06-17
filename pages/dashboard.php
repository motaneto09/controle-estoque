<?php
// ENTRAR EXATAMENTE NO INÍCIO DO ARQUIVO PAGES/DASHBOARD.PHP

// 1. Inicia a sessão antes de qualquer validação
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. PRIMEIRO importamos a conexão (isso faz o grifado vermelho sumir)
include __DIR__ . '/../includes/conexao.php';

// 3. SEGUNDO importamos a autenticação
include __DIR__ . '/../includes/auth.php';

// 4. Ativa alertas na tela para sabermos se algo mais travar
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$perfil = $_SESSION['usuario_perfil'] ?? '';

// DAQUI PARA BAIXO NÃO MEXA EM NADA, DEIXE O SEU CÓDIGO ORIGINAL:
// $total_ativos = $conexao->query("SELECT COUNT(*) AS total FROM ativos")->fetch_assoc()['total'];

// Buscar totalizadores de ativos de forma segura
$total_ativos = 0;
$total_alugados = 0;

$res_ativos = $conexao->query("SELECT COUNT(*) AS total FROM ativos");
if ($res_ativos) {
    $total_ativos = $res_ativos->fetch_assoc()['total'];
}

$res_alugados = $conexao->query("SELECT COUNT(*) AS total FROM ativos WHERE status = 'Alugado'");
if ($res_alugados) {
    $total_alugados = $res_alugados->fetch_assoc()['total'];
}

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
    if ($stmt) {
        $stmt->bind_param("s", $categoria);
        $stmt->execute();
        $resultado = $stmt->get_result()->fetch_assoc();
        $resumo_categorias[$categoria] = $resultado['total'] ?? 0;
        $stmt->close();
    } else {
        $resumo_categorias[$categoria] = 0;
    }
}

// Consultas de alertas e pendências
$alugado_sem_cliente = 0;
$res_sem_cliente = $conexao->query("SELECT COUNT(*) AS total FROM ativos WHERE status = 'Alugado' AND (cliente IS NULL OR cliente = '')");
if ($res_sem_cliente) {
    $alugado_sem_cliente = $res_sem_cliente->fetch_assoc()['total'];
}

$alugado_sem_nni = 0;
$res_sem_nni = $conexao->query("SELECT COUNT(*) AS total FROM ativos WHERE status = 'Alugado' AND (nni IS NULL OR nni = '')");
if ($res_sem_nni) {
    $alugado_sem_nni = $res_sem_nni->fetch_assoc()['total'];
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Controle de Estoque</title>
    <?php 
    // Inclui o head tratando o caminho físico absoluto para evitar erros do Apache
    include __DIR__ . '/../includes/head.php'; 
    ?>
</head>
<body>

<div class="dashboard-container">

    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="dashboard-content">

        <div class="welcome-box">
            <h1>Bem-vindo, <?= htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?></h1>
            <p>Gerencie os ativos e acompanhe o status do estoque.</p>
        </div>

        <div class="stats-grid">
            <a href="/consulta_ativos.php?status=estoque" class="stat-card">
                <span class="stat-number"><?= $total_em_estoque; ?></span>
                <span class="stat-title">Em Estoque</span>
            </a>

            <a href="/consulta_ativos.php?status=Alugado" class="stat-card">
                <span class="stat-number"><?= $total_alugados; ?></span>
                <span class="stat-title">Alugados</span>
            </a>
        </div>

        <section class="pendencias-box">
            <h2>Alertas de Pendências</h2>
            <div class="pendencias-grid">
                <a href="/consulta_ativos.php?pendencia=cliente" class="pendencia-card">
                    <strong><?= $alugado_sem_cliente; ?></strong>
                    <span>Alugados sem Cliente</span>
                </a>

                <a href="/consulta_ativos.php?pendencia=nni" class="pendencia-card">
                    <strong><?= $alugado_sem_nni; ?></strong>
                    <span>Alugados sem NNI</span>
                </a>
            </div>
        </section>

        <section class="categorias-box">
            <h2>Resumo por Categoria</h2>
            <div class="categorias-grid">
                <?php foreach ($resumo_categorias as $cat => $total): ?>
                    <a href="/consulta_ativos.php?categoria=<?= urlencode($cat); ?>" class="categoria-card">
                        <strong><?= $total; ?></strong>
                        <span><?= htmlspecialchars($cat, ENT_QUOTES, 'UTF-8'); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>
</div>

<script>
const toggle = document.getElementById('sidebarToggle');
const sidebar = document.querySelector('.sidebar');

if(sidebar && toggle) {
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
}
</script>

</body>
</html>