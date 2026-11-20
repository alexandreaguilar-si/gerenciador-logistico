<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$paginaAtiva = 'relatorio';
$tituloPagina = 'Relatórios';
$dataBruta = $_GET['data'] ?? date('Y-m-d');
$dataInformada = is_string($dataBruta) ? $dataBruta : '';
$dataValida = validarData($dataInformada);
$dataSelecionada = $dataValida ? $dataInformada : date('Y-m-d');
$stmt = $pdo->prepare("SELECT f.id, f.data_frete, f.status, f.valor_frete, f.observacoes, t.nome_razao AS transportador, t.placa, origem.cidade AS origem_cidade, origem.uf AS origem_uf, destino.cidade AS destino_cidade, destino.uf AS destino_uf, COALESCE(SUM(fi.quantidade * p.peso_kg), 0) AS peso_total, COALESCE(SUM(fi.quantidade), 0) AS unidades, GROUP_CONCAT(CONCAT(p.nome, ' × ', fi.quantidade) ORDER BY p.nome SEPARATOR ' | ') AS produtos FROM fretes f JOIN transportadores t ON t.id=f.transportador_id JOIN enderecos origem ON origem.id=f.endereco_origem_id JOIN enderecos destino ON destino.id=f.endereco_destino_id LEFT JOIN frete_itens fi ON fi.frete_id=f.id LEFT JOIN produtos p ON p.id=fi.produto_id WHERE f.data_frete=:data GROUP BY f.id, f.data_frete, f.status, f.valor_frete, f.observacoes, t.nome_razao, t.placa, origem.cidade, origem.uf, destino.cidade, destino.uf ORDER BY f.id");
$stmt->execute(['data' => $dataSelecionada]);
$fretes = $stmt->fetchAll();
$totalValor = array_sum(array_map(static fn(array $frete): float => (float) $frete['valor_frete'], $fretes));
$totalPeso = array_sum(array_map(static fn(array $frete): float => (float) $frete['peso_total'], $fretes));
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-3">
    <div><h1 class="h3 mb-1">Relatório diário</h1><p class="mb-0">Resumo de fretes, carga transportada e valores por data.</p></div>
    <div class="d-flex gap-2 no-print">
        <form class="d-flex gap-2" method="get" action="relatorio.php"><label class="visually-hidden" for="data">Data do relatório</label><input class="form-control" id="data" name="data" type="date" value="<?= e($dataSelecionada) ?>" required><button class="btn btn-primary" type="submit"><i class="bi bi-search me-1"></i>Filtrar</button></form>
        <a class="btn btn-outline-success" href="../actions/exportar_relatorio.php?data=<?= e($dataSelecionada) ?>"><i class="bi bi-download me-1"></i>Baixar CSV</a>
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()" aria-label="Imprimir relatório"><i class="bi bi-printer"></i></button>
    </div>
</div>
<?php if (!$dataValida): ?><div class="alert alert-warning">A data informada é inválida. Exibindo o relatório de hoje.</div><?php endif; ?>
<div class="mb-3"><h2 class="h5">Relatório de <?= e(date('d/m/Y', strtotime($dataSelecionada))) ?></h2></div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-4"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-send"></i></div><div class="kpi-value"><?= count($fretes) ?></div><div class="text-muted">Fretes no dia</div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-box-seam"></i></div><div class="kpi-value"><?= number_format($totalPeso, 3, ',', '.') ?> <span class="fs-6">kg</span></div><div class="text-muted">Peso total transportado</div></div></div>
    <div class="col-sm-6 col-xl-4"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-currency-dollar"></i></div><div class="kpi-value">R$ <?= number_format($totalValor, 2, ',', '.') ?></div><div class="text-muted">Valor total dos fretes</div></div></div>
</div>
<section class="panel">
    <h2 class="h5 mb-3">Detalhamento dos fretes</h2>
    <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Frete</th><th>Transportador</th><th>Rota</th><th>Produtos</th><th class="text-end">Peso</th><th class="text-end">Valor</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($fretes as $frete): ?>
        <tr><td class="fw-semibold">#<?= (int) $frete['id'] ?></td><td><?= e($frete['transportador']) ?><br><small class="text-muted"><?= e($frete['placa']) ?></small></td><td><?= e($frete['origem_cidade'] . '/' . $frete['origem_uf']) ?> → <?= e($frete['destino_cidade'] . '/' . $frete['destino_uf']) ?></td><td><?= e($frete['produtos'] ?: 'Sem itens') ?><br><small class="text-muted"><?= (int) $frete['unidades'] ?> unidade(s)</small></td><td class="text-end"><?= number_format((float) $frete['peso_total'], 3, ',', '.') ?> kg</td><td class="text-end">R$ <?= number_format((float) $frete['valor_frete'], 2, ',', '.') ?></td><td><?= e($frete['status']) ?></td></tr>
    <?php endforeach; ?>
    <?php if (!$fretes): ?><tr><td colspan="7" class="text-center text-muted py-4">Nenhum frete encontrado para esta data.</td></tr><?php endif; ?>
    </tbody><tfoot><tr class="fw-bold"><td colspan="4">Totais do dia</td><td class="text-end"><?= number_format($totalPeso, 3, ',', '.') ?> kg</td><td class="text-end">R$ <?= number_format($totalValor, 2, ',', '.') ?></td><td></td></tr></tfoot></table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
