<?php

// 1º: Garante que apenas usuários logados acessem o gerador de PDF
include '../includes/auth.php';

// 2º: Carrega o banco de dados e a constante URL_BASE para o script funcionar
include '../includes/conexao.php';

require '../vendor/autoload.php';

use Dompdf\Dompdf;

$perfil = $_SESSION['usuario_perfil'] ?? '';

if ($perfil !== 'Administrador' && $perfil !== 'AdmVendas') {
    header('Location: dashboard.php');
    exit;
}

$sql = "SELECT
            categoria, 
            tipo_equipamento, 
            descricao, 
            status,
            SUM(quantidade) AS total_quantidade
        FROM ativos
        GROUP BY categoria, tipo_equipamento, descricao, status
        ORDER BY categoria, descricao";

$resultado = $conexao->query($sql);

$html = '
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">

<style>

body{
    font-family: Arial, sans-serif;
    font-size: 12px;
}

h1{
    text-align:center;
    margin-bottom:20px;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#dddddd;
}

th, td{
    border:1px solid #000;
    padding:8px;
    text-align:left;
}

</style>

</head>

<body>

<h1>Relatório de Ativos</h1>

<table>

<thead>
<tr>
    <th>Categoria</th>
    <th>Tipo</th>
    <th>Descrição</th>
    <th>Status</th>
    <th>Qtd</th>
``
</tr>
</thead>

<tbody>
';

while ($ativo = $resultado->fetch_assoc()) {

    $html .= '
    <tr>
        <td>' . htmlspecialchars($ativo['categoria']) . '</td>
        <td>' . htmlspecialchars($ativo['tipo_equipamento']) . '</td>
        <td>' . htmlspecialchars($ativo['descricao']) . '</td>
        <td>' . htmlspecialchars($ativo['status']) . '</td>
        <td>' . htmlspecialchars($ativo['total_quantidade']) . '</td>
    </tr>
    ';
}

$html .= '

</tbody>

</table>

</body>
</html>
';

$dompdf = new Dompdf();

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'landscape');

$dompdf->render();

$dompdf->stream(
    'ativos_exportados.pdf',
    ['Attachment' => true]
);

exit;