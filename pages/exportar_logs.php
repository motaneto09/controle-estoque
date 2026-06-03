<?php
include '../includes/conexao.php';

$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';

// Força download CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=logs_' . date('Y-m-d') . '.csv');

$output = fopen('php://output', 'w');

// Cabeçalho
fputcsv($output, ['Data/Hora', 'Usuário', 'Ação', 'ID Referência', 'Detalhes']);

$sql = "SELECT * FROM logs WHERE 1=1";

if ($data_inicio && $data_fim) {
    $sql .= " AND DATE(data_hora) BETWEEN '$data_inicio' AND '$data_fim'";
}

$sql .= " ORDER BY data_hora DESC";

$result = $conexao->query($sql);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['data_hora'],
        $row['usuario_nome'],
        $row['acao'],
        $row['referencia_id'],
        $row['detalhes']
    ]);
}

fclose($output);
exit;
