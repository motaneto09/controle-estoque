<?php

include 'includes/auth.php';
include 'includes/conexao.php';
include 'includes/log.php';

$perfil = $_SESSION['usuario_perfil'] ?? '';
$pode_acessar = in_array($perfil, ['Administrador', 'AdmVendas'], true);

if (!$pode_acessar) {
    header('Location: consulta_ativos.php');
    exit;
}

$mensagem = '';
$tipo_mensagem = 'success'; // 'success' ou 'danger'
$cliente = [];
$modo_edicao = false;

function e($valor) {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

// Carrega dados do cliente se um ID válido for passado via GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conexao->prepare('SELECT * FROM clientes WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $cliente = $resultado->fetch_assoc();
        $modo_edicao = true;
    }
}

// Processamento do Formulário (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = !empty($_POST['id']) ? (int) $_POST['id'] : null;

    $nome       = trim($_POST['nome'] ?? '');
    $cnpj       = trim($_POST['cnpj'] ?? '');
    $contato    = trim($_POST['contato'] ?? '');
    $telefone   = trim($_POST['telefone'] ?? '');
    $email      = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $observacao = trim($_POST['observacao'] ?? '');

    // Mantém o estado preenchido temporariamente caso ocorra erro de validação
    $cliente = [
        'id' => $id,
        'nome' => $nome,
        'cnpj' => $cnpj,
        'contato' => $contato,
        'telefone' => $telefone,
        'email' => $_POST['email'] ?? '',
        'observacao' => $observacao
    ];

    if ($nome === '' || $cnpj === '' || $contato === '' || $telefone === '' || !$email) {
        $mensagem = !$email && !empty($_POST['email']) ? 'Por favor, insira um e-mail válido.' : 'Preencha todos os campos obrigatórios.';
        $tipo_mensagem = 'danger';
        $modo_edicao = !empty($id);
    } else {
        // Verifica duplicidade de CNPJ
        if ($id) {
            $stmtVerifica = $conexao->prepare('SELECT id FROM clientes WHERE cnpj = ? AND id != ?');
            $stmtVerifica->bind_param('si', $cnpj, $id);
        } else {
            $stmtVerifica = $conexao->prepare('SELECT id FROM clientes WHERE cnpj = ?');
            $stmtVerifica->bind_param('s', $cnpj);
        }

        $stmtVerifica->execute();
        $resultadoVerifica = $stmtVerifica->get_result();

        if ($resultadoVerifica->num_rows > 0) {
            $mensagem = 'Já existe um cliente cadastrado com esse CNPJ.';
            $tipo_mensagem = 'danger';
            $modo_edicao = !empty($id);
        } elseif ($id) {
            // Executa o UPDATE
            $sql = "UPDATE clientes SET nome = ?, cnpj = ?, contato = ?, telefone = ?, email = ?, observacao = ? WHERE id = ?";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param('ssssssi', $nome, $cnpj, $contato, $telefone, $email, $observacao, $id);

            if ($stmt->execute()) {
                // Redireciona para evitar reenvio de formulário com F5
                header('Location: cadastro_clientes.php?msg=atualizado');
                exit;
            } else {
                $mensagem = 'Erro ao atualizar cliente: ' . $stmt->error;
                $tipo_mensagem = 'danger';
            }
        } else {
            // Executa o INSERT
            $sql = "INSERT INTO clientes (nome, cnpj, contato, telefone, email, observacao) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conexao->prepare($sql);
            $stmt->bind_param('ssssss', $nome, $cnpj, $contato, $telefone, $email, $observacao);

            if ($stmt->execute()) {
                registrarLog($conexao, 'Cadastrou cliente', $stmt->insert_id, 'Cliente: ' . $nome);
                header('Location: cadastro_clientes.php?msg=cadastrado');
                exit;
            } else {
                $mensagem = 'Erro ao cadastrar cliente: ' . $stmt->error;
                $tipo_mensagem = 'danger';
            }
        }
    }
}

// Captura mensagens vindas do redirecionamento de sucesso
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'cadastrado') {
        $mensagem = 'Cliente cadastrado com sucesso!';
        $tipo_mensagem = 'success';
    } elseif ($_GET['msg'] === 'atualizado') {
        $mensagem = 'Cliente atualizado com sucesso!';
        $tipo_mensagem = 'success';
    }
}

// Busca a lista atualizada de clientes
$clientes = [];
$resultadoClientes = $conexao->query('SELECT * FROM clientes ORDER BY nome ASC');
if ($resultadoClientes) {
    while ($linha = $resultadoClientes->fetch_assoc()) {
        $clientes[] = $linha;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Cadastro de Clientes - Controle de Estoque</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="login-container">
    <div class="login-box cadastro-box">

        <h1><?= $modo_edicao ? 'Editar Cliente' : 'Cadastro de Clientes'; ?></h1>

        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?= $tipo_mensagem; ?> mensagem-alerta">
                <?= e($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <input type="hidden" name="id" value="<?= e($cliente['id'] ?? ''); ?>">

            <div class="input-group">
                <label>Nome do Cliente</label>
                <input type="text" name="nome" value="<?= e($cliente['nome'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
                <label>CNPJ</label>
                <input type="text" name="cnpj" value="<?= e($cliente['cnpj'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
                <label>Contato</label>
                <input type="text" name="contato" value="<?= e($cliente['contato'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
                <label>Telefone</label>
                <input type="text" name="telefone" value="<?= e($cliente['telefone'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
                <label>E-mail</label>
                <input type="email" name="email" value="<?= e($cliente['email'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
                <label>Observação</label>
                <textarea name="observacao" rows="3"><?= e($cliente['observacao'] ?? ''); ?></textarea>
            </div>

            <button type="submit" class="btn-login">
                <?= $modo_edicao ? 'Atualizar Cliente' : 'Salvar Cliente'; ?>
            </button>
        </form>

        <button type="button" class="btn-voltar" onclick="window.location.href='pages/dashboard.php'">
            Voltar
        </button>

        <?php if (!empty($clientes)): ?>
            <hr>

            <h2>Clientes Cadastrados</h2>

            <table class="tabela-ativos">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>CNPJ</th>
                        <th>Contato</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $item): ?>
                        <tr>
                            <td><?= e($item['nome'] ?? ''); ?></td>
                            <td><?= e($item['cnpj'] ?? ''); ?></td>
                            <td><?= e($item['contato'] ?? ''); ?></td>
                            <td><?= e($item['telefone'] ?? ''); ?></td>
                            <td><?= e($item['email'] ?? ''); ?></td>
                            <td>
                                <button type="button" class="btn-editar"
                                        onclick="window.location.href='cadastro_clientes.php?id=<?= e($item['id']); ?>'">
                                    Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

    </div>
</div>

<script>
// Fade out automático para alertas de sucesso/erro após 4 segundos
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function () {
        const alerta = document.querySelector('.mensagem-alerta');
        if (alerta) {
            alerta.style.transition = "opacity 0.5s ease";
            alerta.style.opacity = 0;
            setTimeout(() => alerta.remove(), 500);
        }
    }, 4000);
});
</script>

</body>
</html>
