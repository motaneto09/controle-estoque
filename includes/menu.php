    <?php
    $pagina_atual = basename($_SERVER['PHP_SELF']);
    $perfil = $_SESSION['usuario_perfil'] ?? '';
    ?>

    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="bi bi-list"></i>
    </div>

    <nav class="sidebar-menu">

        <?php if ($pagina_atual !== 'dashboard.php'): ?>
            <a href="/controle-estoque/pages/dashboard.php">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <?php if ($pagina_atual !== 'consulta_ativos.php'): ?>
            <a href="/controle-estoque/consulta_ativos.php">
                <i class="bi bi-search"></i>
                <span>Consultar Ativos</span>
            </a>
        <?php endif; ?>

        <?php if ($perfil === 'Administrador' && $pagina_atual !== 'cadastro_ativo.php'): ?>
            <a href="/controle-estoque/cadastro_ativo.php">
                <i class="bi bi-plus-circle"></i>
                <span>Incluir Ativo</span>
            </a>
        <?php endif; ?>

        <?php if ($perfil === 'Administrador' && $pagina_atual !== 'excluir.php'): ?>
            <a href="/controle-estoque/pages/excluir.php">
                <i class="bi bi-trash"></i>
                <span>Excluir Ativo</span>
            </a>
        <?php endif; ?>

        <?php if ($perfil === 'Administrador' && $pagina_atual !== 'usuarios.php'): ?>
            <a href="/controle-estoque/pages/usuarios.php">
                <i class="bi bi-people"></i>
                <span>Cadastro de Usuário</span>
            </a>
        <?php endif; ?>

        <?php if (($perfil === 'Administrador' || $perfil === 'AdmVendas') && $pagina_atual !== 'cadastro_clientes.php'): ?>
            <a href="/controle-estoque/cadastro_clientes.php">
                <i class="bi bi-buildings"></i>
                <span>Cadastro de Cliente</span>
            </a>
        <?php endif; ?>

        <?php if ($perfil === 'Administrador' && $pagina_atual !== 'logs.php'): ?>
            <a href="/controle-estoque/pages/logs.php">
                <i class="bi bi-clock-history"></i>
                <span>Log de Atividade</span>
            </a>
        <?php endif; ?>

        <?php if (($perfil === 'Administrador' || $perfil === 'AdmVendas') && $pagina_atual !== 'exportar.php'): ?>
            <a href="/controle-estoque/pages/exportar.php">
                <i class="bi bi-file-earmark-pdf"></i>
                <span>Exportar Relatório</span>
            </a>
        <?php endif; ?>

        <a href="/controle-estoque/logout.php" class="logout">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logoff</span>
        </a>

    </nav>