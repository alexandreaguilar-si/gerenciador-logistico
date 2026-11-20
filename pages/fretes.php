<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$paginaAtiva = 'fretes';
$tituloPagina = 'Fretes';
$transportadores = $pdo->query('SELECT id, nome_razao, placa FROM transportadores ORDER BY nome_razao')->fetchAll();
$enderecos = $pdo->query('SELECT * FROM enderecos ORDER BY cidade, logradouro')->fetchAll();
$produtos = $pdo->query('SELECT id, nome, peso_kg FROM produtos ORDER BY nome')->fetchAll();
$fretes = $pdo->query('SELECT f.*, t.nome_razao AS transportador, t.placa, origem.cep AS origem_cep, origem.logradouro AS origem_logradouro, origem.numero AS origem_numero, origem.cidade AS origem_cidade, origem.uf AS origem_uf, destino.cep AS destino_cep, destino.logradouro AS destino_logradouro, destino.numero AS destino_numero, destino.cidade AS destino_cidade, destino.uf AS destino_uf FROM fretes f JOIN transportadores t ON t.id=f.transportador_id JOIN enderecos origem ON origem.id=f.endereco_origem_id JOIN enderecos destino ON destino.id=f.endereco_destino_id ORDER BY f.data_frete DESC, f.id DESC')->fetchAll();
$itensFrete = [];
if ($fretes) {
    $stmtItens = $pdo->query('SELECT fi.frete_id, fi.quantidade, p.nome, p.peso_kg FROM frete_itens fi JOIN produtos p ON p.id=fi.produto_id ORDER BY p.nome');
    foreach ($stmtItens as $item) $itensFrete[$item['frete_id']][] = $item;
}
$statusClasses = ['Pendente' => 'warning', 'Em Trânsito' => 'primary', 'Entregue' => 'success'];
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><h1 class="h3 mb-1">Fretes</h1><p class="mb-0">Emita uma operação de transporte e vincule vários produtos à mesma carga.</p></div>
<section class="panel mb-4">
    <h2 class="h5 mb-3">Emitir novo frete</h2>
    <?php if (!$transportadores || count($enderecos) < 2 || !$produtos): ?>
        <div class="alert alert-warning mb-0">Para emitir um frete, cadastre ao menos um transportador, dois endereços e um produto. A carga pode conter múltiplos itens.</div>
    <?php else: ?>
    <form action="../actions/frete_action.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="transportador_id">Transportador *</label><select class="form-select" id="transportador_id" name="transportador_id" required><option value="">Selecione</option><?php foreach ($transportadores as $transportador): ?><option value="<?= (int) $transportador['id'] ?>"><?= e($transportador['nome_razao']) ?> — <?= e($transportador['placa']) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-3"><label class="form-label" for="data_frete">Data do frete *</label><input class="form-control" id="data_frete" name="data_frete" type="date" required value="<?= e(date('Y-m-d')) ?>"></div>
            <div class="col-md-3"><label class="form-label" for="valor_frete">Valor do frete (R$) *</label><input class="form-control" id="valor_frete" name="valor_frete" type="number" min="0" step="0.01" required value="0.00"></div>
            <div class="col-md-6"><label class="form-label" for="endereco_origem_id">Endereço de origem *</label><select class="form-select" id="endereco_origem_id" name="endereco_origem_id" required><option value="">Selecione</option><?php foreach ($enderecos as $endereco): ?><option value="<?= (int) $endereco['id'] ?>"><?= e(enderecoLegivel($endereco)) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label" for="endereco_destino_id">Endereço de destino *</label><select class="form-select" id="endereco_destino_id" name="endereco_destino_id" required><option value="">Selecione</option><?php foreach ($enderecos as $endereco): ?><option value="<?= (int) $endereco['id'] ?>"><?= e(enderecoLegivel($endereco)) ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label" for="status">Status inicial *</label><select class="form-select" id="status" name="status"><option>Pendente</option><option>Em Trânsito</option><option>Entregue</option></select></div>
            <div class="col-12"><label class="form-label" for="observacoes">Observações</label><textarea class="form-control" id="observacoes" name="observacoes" rows="2"></textarea></div>
        </div>
        <hr class="my-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
            <div><h3 class="h6 mb-1">Produtos da Carga</h3><small class="text-muted">Adicione um ou mais produtos e informe as quantidades.</small></div>
            <button type="button" class="btn btn-sm btn-outline-primary" data-adicionar-produto><i class="bi bi-plus-lg me-1"></i>Adicionar Produto</button>
        </div>
        <div class="d-grid gap-2" data-produtos-lista></div>
        <template id="produto-frete-template">
            <div class="item-row row g-2 align-items-end">
                <div class="col-md-8"><label class="form-label">Produto *</label><select class="form-select" name="produto_id[]" required><option value="">Selecione um produto</option><?php foreach ($produtos as $produto): ?><option value="<?= (int) $produto['id'] ?>"><?= e($produto['nome']) ?> · <?= number_format((float) $produto['peso_kg'], 3, ',', '.') ?> kg/un.</option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Quantidade *</label><input class="form-control" name="quantidade[]" type="number" min="1" step="1" required value="1"></div>
                <div class="col-md-1 d-grid"><button class="btn btn-outline-danger" type="button" data-remover-produto aria-label="Remover produto"><i class="bi bi-x-lg"></i></button></div>
            </div>
        </template>
        <div class="mt-3"><button class="btn btn-primary" type="submit"><i class="bi bi-send me-1"></i>Emitir frete</button></div>
    </form>
    <?php endif; ?>
</section>
<section class="panel">
    <h2 class="h5 mb-3">Fretes registrados</h2>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Frete</th><th>Transportador</th><th>Origem → Destino</th><th>Data</th><th>Produtos da carga</th><th>Valor</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($fretes as $frete): ?>
            <tr>
                <td class="fw-semibold">#<?= (int) $frete['id'] ?></td>
                <td><?= e($frete['transportador']) ?><br><small class="text-muted"><?= e($frete['placa']) ?></small></td>
                <td class="frete-details"><small><strong>De:</strong> <?= e($frete['origem_cidade'] . '/' . $frete['origem_uf']) ?> · <?= e($frete['origem_logradouro'] . ', ' . $frete['origem_numero']) ?><br><strong>Para:</strong> <?= e($frete['destino_cidade'] . '/' . $frete['destino_uf']) ?> · <?= e($frete['destino_logradouro'] . ', ' . $frete['destino_numero']) ?></small></td>
                <td><?= e(date('d/m/Y', strtotime($frete['data_frete']))) ?></td>
                <td><details><summary><?= count($itensFrete[$frete['id']] ?? []) ?> item(ns)</summary><ul class="mb-0 ps-3"><?php foreach ($itensFrete[$frete['id']] ?? [] as $item): ?><li><?= e($item['nome']) ?> × <?= (int) $item['quantidade'] ?> (<?= number_format((float) $item['peso_kg'] * (int) $item['quantidade'], 3, ',', '.') ?> kg)</li><?php endforeach; ?></ul></details></td>
                <td>R$ <?= number_format((float) $frete['valor_frete'], 2, ',', '.') ?></td>
                <td><span class="badge text-bg-<?= e($statusClasses[$frete['status']] ?? 'secondary') ?>"><?= e($frete['status']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$fretes): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhum frete registrado.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
