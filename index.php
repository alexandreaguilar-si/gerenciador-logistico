<?php
require_once __DIR__ . '/config/conexao.php';
require_once __DIR__ . '/includes/funcoes.php';
$paginaAtiva = 'dashboard';
$tituloPagina = 'Dashboard';
$totalProdutos = (int) $pdo->query('SELECT COUNT(*) FROM produtos')->fetchColumn();
$totalTransportadores = (int) $pdo->query('SELECT COUNT(*) FROM transportadores')->fetchColumn();
$totalFretes = (int) $pdo->query('SELECT COUNT(*) FROM fretes')->fetchColumn();
$fretesHojeStmt = $pdo->prepare('SELECT COUNT(*) FROM fretes WHERE data_frete = :hoje');
$fretesHojeStmt->execute(['hoje' => date('Y-m-d')]);
$fretesHoje = (int) $fretesHojeStmt->fetchColumn();
$recentes = $pdo->query('SELECT f.id, f.data_frete, f.status, f.valor_frete, t.nome_razao AS transportador, destino.cidade AS destino_cidade, destino.uf AS destino_uf, (SELECT COUNT(*) FROM frete_itens fi WHERE fi.frete_id=f.id) AS total_itens FROM fretes f JOIN transportadores t ON t.id=f.transportador_id JOIN enderecos destino ON destino.id=f.endereco_destino_id ORDER BY f.data_frete DESC, f.id DESC LIMIT 6')->fetchAll();
$statusClasses = ['Pendente' => 'warning', 'Em Trânsito' => 'primary', 'Entregue' => 'success'];
require __DIR__ . '/includes/header.php';
?>
<div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2">
    <div><h1 class="h3 mb-1">Visão geral</h1><p class="mb-0">Acompanhe a operação logística em um só lugar.</p></div>
    <a class="btn btn-primary" href="pages/fretes.php"><i class="bi bi-plus-lg me-1"></i>Novo frete</a>
</div>
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-send"></i></div><div class="kpi-value"><?= number_format($totalFretes, 0, ',', '.') ?></div><div class="text-muted">Fretes registrados</div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-calendar-check"></i></div><div class="kpi-value"><?= number_format($fretesHoje, 0, ',', '.') ?></div><div class="text-muted">Fretes de hoje</div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-box-seam"></i></div><div class="kpi-value"><?= number_format($totalProdutos, 0, ',', '.') ?></div><div class="text-muted">Produtos cadastrados</div></div></div>
    <div class="col-sm-6 col-xl-3"><div class="kpi-card"><div class="kpi-icon"><i class="bi bi-truck"></i></div><div class="kpi-value"><?= number_format($totalTransportadores, 0, ',', '.') ?></div><div class="text-muted">Transportadores cadastrados</div></div></div>
</div>
<section class="panel">
    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Fretes recentes</h2><a class="btn btn-sm btn-outline-primary" href="pages/fretes.php">Ver todos</a></div>
    <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Frete</th><th>Transportador</th><th>Destino</th><th>Data</th><th>Itens</th><th>Valor</th><th>Status</th></tr></thead><tbody>
    <?php foreach ($recentes as $frete): ?>
        <tr><td class="fw-semibold">#<?= (int) $frete['id'] ?></td><td><?= e($frete['transportador']) ?></td><td><?= e($frete['destino_cidade'] . '/' . $frete['destino_uf']) ?></td><td><?= e(date('d/m/Y', strtotime($frete['data_frete']))) ?></td><td><?= (int) $frete['total_itens'] ?></td><td>R$ <?= number_format((float) $frete['valor_frete'], 2, ',', '.') ?></td><td><span class="badge text-bg-<?= e($statusClasses[$frete['status']] ?? 'secondary') ?>"><?= e($frete['status']) ?></span></td></tr>
    <?php endforeach; ?>
    <?php if (!$recentes): ?><tr><td colspan="7" class="text-center text-muted py-4">Ainda não há fretes. Comece cadastrando produtos, endereços e transportadores.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
