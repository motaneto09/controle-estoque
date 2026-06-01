<?php

include '../includes/auth.php';
include '../includes/conexao.php';

$perfil = $_SESSION['usuario_perfil'] ?? '';

if ($perfil !== 'Administrador' && $perfil !== 'AdmVendas') {
    header('Location: dashboard.php');
    exit;
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=ativos_exportados.xls");
header("Pragma: no-cache");
header("Expires: 0");

$sql = "SELECT categoria, tipo_equipamento, descricao, status 
        FROM ativos
        ORDER BY categoria, descricao";

$resultado = $conexao->query($sql);

echo "\xEF\xBB\xBF";
?>

<table border="1">
    <thead>
        <tr>
            <th>Categoria</th>
            <th>Tipo</th>
            <th>Descrição</th>
            <th>Status</th>
        </tr>
    </thead>

    <tbody>
        <?php while ($ativo = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($ativo['categoria']); ?></td>
                <td><?= htmlspecialchars($ativo['tipo_equipamento']); ?></td>
                <td><?= htmlspecialchars($ativo['descricao']); ?></td>
                <td><?= htmlspecialchars($ativo['status']); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>