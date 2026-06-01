<?php

include 'includes/auth.php';
include 'includes/conexao.php';
include 'includes/log.php';

$perfil = $_SESSION['usuario_perfil'] ?? '';
$pode_editar = in_array($perfil, ['Administrador', 'AdmVendas'], true);
$modo_visualizar = !$pode_editar || (($_GET['modo'] ?? '') === 'visualizar');
$modo_edicao = false;
$mensagem = '';
$ativo = [];

$categorias = [
    'Notebook',
    'Desktop',
    'Monitor',
    'Infraestrutura',
    'Network',
    'Armazenamento',
    'Impressora',
    'Disco',
    'Memoria'
];

function e($valor) {
    return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
}

function selecionado($valorAtual, $valorOption) {
    return ((string) ($valorAtual ?? '') === (string) $valorOption) ? 'selected' : '';
}

$clientes = [];

$sqlClientes = "SELECT nome FROM clientes ORDER BY nome ASC";

$resultadoClientes = $conexao->query($sqlClientes);

if ($resultadoClientes) {

    while ($linhaCliente = $resultadoClientes->fetch_assoc()) {

        $clientes[] = $linhaCliente;
    }
}

function limparCamposPorCategoria(array &$dados) {
    if ($dados['status'] !== 'Alugado') {
        $dados['cliente'] = '';
        $dados['nni'] = '';
    }

    if (!in_array($dados['categoria'], ['Infraestrutura', 'Armazenamento'], true)) {
        $dados['processador'] = '';
        $dados['quantidade_cpu'] = null;
        $dados['memoria_ram'] = '';
        $dados['quantidade_nic'] = null;
        $dados['tipo_nic'] = '';
        $dados['discos_ssd'] = '';
        $dados['discos_nvme'] = '';
        $dados['discos_sas'] = '';
        $dados['discos_nlsas'] = '';
        $dados['discos_sata'] = '';
    }

    if ($dados['categoria'] !== 'Disco') {
        $dados['disco_modelo'] = '';
        $dados['disco_tipo'] = '';
        $dados['disco_tamanho'] = '';
        $dados['disco_velocidade'] = '';
    }

    if ($dados['categoria'] !== 'Memoria') {
        $dados['memoria_tipo'] = '';
        $dados['memoria_capacidade'] = '';
        $dados['memoria_frequencia'] = '';
        $dados['memoria_aplicacao'] = '';
    }

    if (!in_array($dados['categoria'], ['Infraestrutura', 'Network', 'Armazenamento'], true)) {
        $dados['tipo_equipamento'] = '';
    }

    // ADICIONE AQUI
    
}

function dadosPost() {
    $camposTexto = [
        'categoria', 'tipo_equipamento', 'descricao', 'service_tag', 'status', 'observacao',
        'cliente', 'nni', 'modelo', 'processador', 'memoria_ram', 'tipo_nic',
        'discos_ssd', 'discos_nvme', 'discos_sas', 'discos_nlsas', 'discos_sata',
        'disco_modelo', 'disco_tipo', 'disco_tamanho', 'disco_velocidade',
        'memoria_tipo', 'memoria_capacidade', 'memoria_frequencia', 'memoria_aplicacao'
    ];

    $dados = [];

    foreach ($camposTexto as $campo) {
        $dados[$campo] = trim($_POST[$campo] ?? '');
    }

    $dados['id'] = !empty($_POST['id']) ? (int) $_POST['id'] : null;
    $dados['quantidade_cpu'] = ($_POST['quantidade_cpu'] ?? '') !== '' ? (int) $_POST['quantidade_cpu'] : null;
    $dados['quantidade_nic'] = ($_POST['quantidade_nic'] ?? '') !== '' ? (int) $_POST['quantidade_nic'] : null;
    $dados['quantidade'] = ($_POST['quantidade'] ?? '') !== '' ? (int) $_POST['quantidade'] : 1;

    limparCamposPorCategoria($dados);

    return $dados;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conexao->prepare('SELECT * FROM ativos WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $ativo = $resultado->fetch_assoc();
        $modo_edicao = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($modo_visualizar) {
        $mensagem = 'Você não tem permissão para salvar alterações.';
    } else {
        $dados = dadosPost();

if ($dados['categoria'] === 'Memoria') {

    $dados['status'] = 'Estoque';
    $dados['cliente'] = '';
    $dados['nni'] = '';
}

if (
    $dados['categoria'] === 'Disco' ||
    $dados['categoria'] === 'Memoria'
) {

    $resultadoVerifica = false;

} else {

    if ($dados['id']) {

        $stmtVerifica = $conexao->prepare(
            'SELECT id FROM ativos WHERE service_tag = ? AND id != ?'
        );

        $stmtVerifica->bind_param(
            'si',
            $dados['service_tag'],
            $dados['id']
        );

    } else {

        $stmtVerifica = $conexao->prepare(
            'SELECT id FROM ativos WHERE service_tag = ?'
        );

        $stmtVerifica->bind_param(
            's',
            $dados['service_tag']
        );
    }

    $stmtVerifica->execute();
    $resultadoVerifica = $stmtVerifica->get_result();
}

        if (
    $resultadoVerifica &&
    $resultadoVerifica->num_rows > 0
) {
            $mensagem = 'Já existe um ativo com essa Service Tag.';
            $ativo = $dados;
        } elseif ($dados['id']) {
            $sql = "UPDATE ativos SET
                        categoria = ?, tipo_equipamento = ?, descricao = ?, service_tag = ?, status = ?,
                        cliente = ?, nni = ?, modelo = ?, processador = ?, quantidade_cpu = ?,
                        memoria_ram = ?, quantidade_nic = ?, tipo_nic = ?, discos_ssd = ?, discos_nvme = ?,
                        discos_sas = ?, discos_nlsas = ?, discos_sata = ?, disco_modelo = ?, disco_tipo = ?,
                        disco_tamanho = ?, disco_velocidade = ?, memoria_tipo = ?, memoria_capacidade = ?,
                        memoria_frequencia = ?, memoria_aplicacao = ?, quantidade = ?, observacao = ?
                        WHERE id = ?";

            $stmt = $conexao->prepare($sql);

            

$stmt->bind_param(
    'sssssssssisissssssssssssssisi',
    $dados['categoria'],
    $dados['tipo_equipamento'],
    $dados['descricao'],
    $dados['service_tag'],
    $dados['status'],
    $dados['cliente'],
    $dados['nni'],
    $dados['modelo'],
    $dados['processador'],
    $dados['quantidade_cpu'],
    $dados['memoria_ram'],
    $dados['quantidade_nic'],
    $dados['tipo_nic'],
    $dados['discos_ssd'],
    $dados['discos_nvme'],
    $dados['discos_sas'],
    $dados['discos_nlsas'],
    $dados['discos_sata'],
    $dados['disco_modelo'],
    $dados['disco_tipo'],
    $dados['disco_tamanho'],
    $dados['disco_velocidade'],
    $dados['memoria_tipo'],
    $dados['memoria_capacidade'],
    $dados['memoria_frequencia'],
    $dados['memoria_aplicacao'],
    $dados['quantidade'],
    $dados['observacao'],
    $dados['id']
);

            if ($stmt->execute()) {
                $mensagem = 'Ativo atualizado com sucesso!';
                registrarLog($conexao, 'Editou ativo', $dados['id'], 'Service Tag: ' . $dados['service_tag']);
                $ativo = $dados;
                $modo_edicao = true;
            } else {
                $mensagem = 'Erro ao atualizar ativo: ' . $stmt->error;
                $ativo = $dados;
            }
        } else {
            $sql = "INSERT INTO ativos (
    categoria, tipo_equipamento, descricao, service_tag, status,
    cliente, nni, modelo, processador, quantidade_cpu,
    memoria_ram, quantidade_nic, tipo_nic, discos_ssd, discos_nvme,
    discos_sas, discos_nlsas, discos_sata, disco_modelo, disco_tipo,
    disco_tamanho, disco_velocidade, memoria_tipo, memoria_capacidade,
    memoria_frequencia, memoria_aplicacao, quantidade, observacao
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql);
            $stmt->bind_param(
    'sssssssssisissssssssssssssis',
    $dados['categoria'],
    $dados['tipo_equipamento'],
    $dados['descricao'],
    $dados['service_tag'],
    $dados['status'],
    $dados['cliente'],
    $dados['nni'],
    $dados['modelo'],
    $dados['processador'],
    $dados['quantidade_cpu'],
    $dados['memoria_ram'],
    $dados['quantidade_nic'],
    $dados['tipo_nic'],
    $dados['discos_ssd'],
    $dados['discos_nvme'],
    $dados['discos_sas'],
    $dados['discos_nlsas'],
    $dados['discos_sata'],
    $dados['disco_modelo'],
    $dados['disco_tipo'],
    $dados['disco_tamanho'],
    $dados['disco_velocidade'],
    $dados['memoria_tipo'],
    $dados['memoria_capacidade'],
    $dados['memoria_frequencia'],
    $dados['memoria_aplicacao'],
    $dados['quantidade'],
    $dados['observacao']
);

            if ($stmt->execute()) {
                $mensagem = 'Ativo cadastrado com sucesso!';
                registrarLog($conexao, 'Cadastrou ativo', $stmt->insert_id, 'Service Tag: ' . $dados['service_tag']);
                $ativo = [];
            } else {
                $mensagem = 'Erro ao cadastrar ativo: ' . $stmt->error;
                $ativo = $dados;
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <title>Cadastro Ativos - Controle de Estoque</title>
    <?php include 'includes/head.php'; ?>
</head>
<body>

<div class="login-container">
    <div class="login-box cadastro-box">

        <h1><?= $modo_edicao ? 'Editar Ativo' : 'Cadastro de Ativos'; ?></h1>

        <form method="POST">
            <input type="hidden" name="id" value="<?= e($ativo['id'] ?? ''); ?>">

            <div class="input-group">
                <label>Categoria</label>
                <select name="categoria" id="categoria" required>
                    <option value="">Selecione</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= e($cat); ?>" <?= selecionado($ativo['categoria'] ?? '', $cat); ?>><?= e($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="input-group" id="grupo-tipo-equipamento">
                <label>Tipo Equipamento</label>
                <select name="tipo_equipamento" id="tipo_equipamento">
                    <option value="">Selecione</option>
                </select>
            </div>

            <div class="input-group">
                <label>Descrição</label>
                <input type="text" name="descricao" value="<?= e($ativo['descricao'] ?? ''); ?>" required>
            </div>

            <div class="input-group">
    <label>Service Tag / Serial</label>
    <input
        type="text"
        id="service_tag"
        name="service_tag"
        value="<?= e($ativo['service_tag'] ?? ''); ?>">
</div>

            <div class="input-group">
                <label>Status</label>
                <select name="status" id="status" required>
                    <option value="">Selecione</option>
                    <option value="Estoque" <?= selecionado($ativo['status'] ?? '', 'Estoque'); ?>>Estoque</option>
                    <option value="Alugado" <?= selecionado($ativo['status'] ?? '', 'Alugado'); ?>>Alugado</option>
                </select>
            </div>

            <div id="dados-locacao" style="display:none;">
                <div class="input-group">
                    <label>Cliente</label>
                    <select name="cliente">

    <option value="">Selecione o cliente</option>

    <?php foreach ($clientes as $clienteItem): ?>

        <option
            value="<?= e($clienteItem['nome']); ?>"
            <?= selecionado($ativo['cliente'] ?? '', $clienteItem['nome']); ?>>

            <?= e($clienteItem['nome']); ?>

        </option>

    <?php endforeach; ?>

</select>
                </div>
                <div class="input-group">
                    <label>NNI</label>
                    <input type="text" name="nni" value="<?= e($ativo['nni'] ?? ''); ?>">
                </div>
            </div>

            <div id="detalhes-tecnicos" style="display:none;">
                <h2>Detalhes Técnicos</h2>

                <div class="input-group">
                    <label>Modelo</label>
                    <input type="text" name="modelo" value="<?= e($ativo['modelo'] ?? ''); ?>">
                </div>

                <div class="input-group campo-servidor">
                    <label>Processador</label>
                    <input type="text" name="processador" value="<?= e($ativo['processador'] ?? ''); ?>">
                </div>

                <div class="input-group campo-servidor">
                    <label>Quantidade CPU</label>
                    <input type="number" name="quantidade_cpu" value="<?= e($ativo['quantidade_cpu'] ?? ''); ?>">
                </div>

                <div class="input-group campo-servidor">
                    <label>Memória RAM</label>
                    <input type="text" name="memoria_ram" value="<?= e($ativo['memoria_ram'] ?? ''); ?>">
                </div>

                <div class="input-group campo-servidor">
                    <label>Quantidade NIC</label>
                    <input type="number" name="quantidade_nic" value="<?= e($ativo['quantidade_nic'] ?? ''); ?>">
                </div>

                <div class="input-group campo-servidor">
                    <label>Tipo NIC</label>
                    <input type="text" name="tipo_nic" value="<?= e($ativo['tipo_nic'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Discos SSD</label>
                    <input type="text" name="discos_ssd" value="<?= e($ativo['discos_ssd'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Discos NVMe</label>
                    <input type="text" name="discos_nvme" value="<?= e($ativo['discos_nvme'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Discos SAS</label>
                    <input type="text" name="discos_sas" value="<?= e($ativo['discos_sas'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Discos NL-SAS</label>
                    <input type="text" name="discos_nlsas" value="<?= e($ativo['discos_nlsas'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Discos SATA</label>
                    <input type="text" name="discos_sata" value="<?= e($ativo['discos_sata'] ?? ''); ?>">
                </div>
            </div>

            <div id="detalhes-disco" style="display:none;">
                <h2>Detalhes do Disco</h2>

                

                <div class="input-group">
                    <label>Modelo do Disco</label>
                    <input type="text" name="disco_modelo" value="<?= e($ativo['disco_modelo'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Tipo do Disco</label>
                    <select name="disco_tipo">
                        <option value="">Selecione</option>
                        <option value="SSD" <?= selecionado($ativo['disco_tipo'] ?? '', 'SSD'); ?>>SSD</option>
                        <option value="NVMe" <?= selecionado($ativo['disco_tipo'] ?? '', 'NVMe'); ?>>NVMe</option>
                        <option value="SAS" <?= selecionado($ativo['disco_tipo'] ?? '', 'SAS'); ?>>SAS</option>
                        <option value="NL-SAS" <?= selecionado($ativo['disco_tipo'] ?? '', 'NL-SAS'); ?>>NL-SAS</option>
                        <option value="SATA" <?= selecionado($ativo['disco_tipo'] ?? '', 'SATA'); ?>>SATA</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Tamanho</label>
                    <input type="text" name="disco_tamanho" placeholder="Ex: 600GB, 1.2TB, 4TB" value="<?= e($ativo['disco_tamanho'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Velocidade</label>
                    <input type="text" name="disco_velocidade" placeholder="Ex: 7.2K, 10K, 15K, 12Gbps" value="<?= e($ativo['disco_velocidade'] ?? ''); ?>">
                </div>
            </div>

            <div id="detalhes-memoria" style="display:none;">
                <h2>Detalhes da Memória</h2>

                

                <div class="input-group">
                    <label>Tipo da Memória</label>
                    <select name="memoria_tipo">
                        <option value="">Selecione</option>
                        <option value="DDR3" <?= selecionado($ativo['memoria_tipo'] ?? '', 'DDR3'); ?>>DDR3</option>
                        <option value="DDR4" <?= selecionado($ativo['memoria_tipo'] ?? '', 'DDR4'); ?>>DDR4</option>
                        <option value="DDR5" <?= selecionado($ativo['memoria_tipo'] ?? '', 'DDR5'); ?>>DDR5</option>
                        <option value="ECC DDR4" <?= selecionado($ativo['memoria_tipo'] ?? '', 'ECC DDR4'); ?>>ECC DDR4</option>
                        <option value="ECC DDR5" <?= selecionado($ativo['memoria_tipo'] ?? '', 'ECC DDR5'); ?>>ECC DDR5</option>
                        <option value="RDIMM" <?= selecionado($ativo['memoria_tipo'] ?? '', 'RDIMM'); ?>>RDIMM</option>
                        <option value="LRDIMM" <?= selecionado($ativo['memoria_tipo'] ?? '', 'LRDIMM'); ?>>LRDIMM</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Capacidade</label>
                    <input type="text" name="memoria_capacidade" placeholder="Ex: 8GB, 16GB, 32GB, 64GB" value="<?= e($ativo['memoria_capacidade'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Frequência</label>
                    <input type="text" name="memoria_frequencia" placeholder="Ex: 1600MHz, 2400MHz, 2666MHz, 3200MHz" value="<?= e($ativo['memoria_frequencia'] ?? ''); ?>">
                </div>

                <div class="input-group">
                    <label>Aplicação</label>
                    <select name="memoria_aplicacao">
                        <option value="">Selecione</option>
                        <option value="Notebook" <?= selecionado($ativo['memoria_aplicacao'] ?? '', 'Notebook'); ?>>Notebook</option>
                        <option value="Desktop" <?= selecionado($ativo['memoria_aplicacao'] ?? '', 'Desktop'); ?>>Desktop</option>
                        <option value="Servidor" <?= selecionado($ativo['memoria_aplicacao'] ?? '', 'Servidor'); ?>>Servidor</option>
                    </select>
                </div>
            </div>

            <div id="grupo-quantidade" class="input-group" style="display:none;">
    <label>Quantidade</label>
    <input
        type="number"
        name="quantidade"
        min="1"
        value="<?= e($ativo['quantidade'] ?? 1); ?>">
</div>

            <div class="input-group">
                <label>Observação</label>
                <textarea name="observacao"><?= e($ativo['observacao'] ?? ''); ?></textarea>
            </div>

            <?php if (!$modo_visualizar): ?>
                <button type="submit" class="btn-login"><?= $modo_edicao ? 'Atualizar Ativo' : 'Salvar Ativo'; ?></button>
            <?php endif; ?>
        </form>

        <a href="pages/dashboard.php" class="btn-voltar">Voltar</a>
    </div>
</div>

<?php if (!empty($mensagem)): ?>
<script>alert("<?= addslashes($mensagem); ?>");</script>
<?php endif; ?>

<script>
const categoriaSelect = document.getElementById('categoria');
const tipoEquipamentoSelect = document.getElementById('tipo_equipamento');
const grupoTipoEquipamento = document.getElementById('grupo-tipo-equipamento');
const statusSelect = document.getElementById('status');
const dadosLocacao = document.getElementById('dados-locacao');
const detalhesTecnicos = document.getElementById('detalhes-tecnicos');
const detalhesDisco = document.getElementById('detalhes-disco');
const detalhesMemoria = document.getElementById('detalhes-memoria');
const grupoQuantidade =
    document.getElementById('grupo-quantidade');
    const campoServiceTag =
    document.getElementById('service_tag');
const tipoAtual = "<?= addslashes($ativo['tipo_equipamento'] ?? ''); ?>";

const tiposPorCategoria = {
    Infraestrutura: ['Servidor'],
    Network: ['Switch', 'Firewall', 'Roteador', 'Access Point'],
    Armazenamento: ['Storage', 'Tape Library']
};

function mostrar(elemento, condicao) {
    elemento.style.display = condicao ? 'block' : 'none';
}

function carregarTipos() {
    const categoria = categoriaSelect.value;
    const tipos = tiposPorCategoria[categoria] || [];

    tipoEquipamentoSelect.innerHTML = '<option value="">Selecione</option>';
    mostrar(grupoTipoEquipamento, tipos.length > 0);

    tipos.forEach(function(tipo) {
        const option = new Option(tipo, tipo, false, tipo === tipoAtual);
        tipoEquipamentoSelect.add(option);
    });
}

function atualizarTela() {

    const categoria = categoriaSelect.value;
    const tipo = tipoEquipamentoSelect.value;

    const ehServidor =
        categoria === 'Infraestrutura' &&
        tipo === 'Servidor';

    const ehStorageOuTape =
        categoria === 'Armazenamento' &&
        ['Storage', 'Tape Library'].includes(tipo);

        const grupoStatus = statusSelect.closest('.input-group');

if (
    categoria === 'Disco' ||
    categoria === 'Memoria'
) {

    grupoStatus.style.display = 'none';
    statusSelect.value = 'Estoque';

    if (campoServiceTag) {
    campoServiceTag.required = false;
}

} else {

    grupoStatus.style.display = 'block';

    if (campoServiceTag) {
    campoServiceTag.required = true;
}
}

    mostrar(dadosLocacao, statusSelect.value === 'Alugado');

    mostrar(
        detalhesTecnicos,
        ehServidor || ehStorageOuTape
    );

    mostrar(detalhesDisco, categoria === 'Disco');

    mostrar(detalhesMemoria, categoria === 'Memoria');

   grupoQuantidade.style.display = 'block';

    document.querySelectorAll('.campo-servidor').forEach(function(campo) {

        campo.style.display =
            ehServidor
            ? 'block'
            : 'none';
    });
}

categoriaSelect.addEventListener('change', function() {
    carregarTipos();
    atualizarTela();
});

tipoEquipamentoSelect.addEventListener('change', atualizarTela);
statusSelect.addEventListener('change', atualizarTela);

carregarTipos();
atualizarTela();
</script>

</body>
</html>