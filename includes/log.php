<?php

function registrarLog($conexao, $acao, $referencia_id = null, $detalhes = '') {

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $usuario_id = $_SESSION['usuario_id'] ?? 0;
    $usuario_nome = $_SESSION['usuario_nome'] ?? 'Sistema';

    if ($referencia_id === null || $referencia_id === '') {
        $referencia_id = 0;
    }

    $sql = "INSERT INTO logs 
            (usuario_id, usuario_nome, acao, referencia_id, detalhes) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conexao->prepare($sql);

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "issis",
        $usuario_id,
        $usuario_nome,
        $acao,
        $referencia_id,
        $detalhes
    );

    return $stmt->execute();
}