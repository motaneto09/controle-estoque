<?php
// includes/sidebar.php
?>
<aside class="sidebar">

    <div class="sidebar-header" style="text-align: center; padding: 15px 5px; border-bottom: none !important; text-decoration: none !important; box-shadow: none !important;">
        
        <img src="<?= URL_BASE; ?>assets/img/logo.png" alt="Logo Empresa" class="logo-completa" style="max-width: 85%; height: auto; display: block; margin: 0 auto; border: none !important; outline: none !important; box-shadow: none !important; text-decoration: none !important;">

        <img src="<?= URL_BASE; ?>assets/img/plus-red2.png" alt="Logo Recuada" class="logo-recuada" style="max-width: 55px; width: 100%; height: auto; display: none; margin: 0 auto; border: none !important; outline: none !important; box-shadow: none !important; text-decoration: none !important;">
        
    </div>

    <?php include __DIR__ . '/menu.php'; ?>

</aside>

<style>
.sidebar-header, 
.sidebar-header * {
    border: none !important;
    border-bottom: none !important;
    text-decoration: none !important;
    outline: none !important;
    box-shadow: none !important;
}

/* Quando a sidebar estiver RECUADA (.sidebar-collapsed) */
.sidebar-collapsed .logo-completa {
    display: none !important;
}
.sidebar-collapsed .logo-recuada {
    display: block !important;
}
</style>