    <?php
    include '../includes/auth.php';

    $perfil = $_SESSION['usuario_perfil'] ?? '';

    if ($perfil !== 'Administrador' && $perfil !== 'AdmVendas') {
        header('Location: dashboard.php');
        exit;
    }
    ?>

    <!DOCTYPE html>
    <html lang="pt-br">
    <head>

        <title>Exportar Ativos - Controle de Estoque</title>

        <?php include '../includes/head.php'; ?>

    </head>
    <body>

    <div class="dashboard-container">

        <?php include '../includes/sidebar.php'; ?>

        <main class="dashboard-content">

            <h1>Exportar Ativos</h1>

            <p>Escolha o formato de exportação:</p>

            <div class="export-buttons">
                <a href="exportar_pdf.php" class="btn-export btn-pdf" target="_blank">Exportar PDF</a>
            </div>

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