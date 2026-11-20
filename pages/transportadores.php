<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$paginaAtiva = 'transportadores';
$tituloPagina = 'Transportadores';
$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
$transportadorEdicao = null;
if ($editarId) {
    $stmt = $pdo->prepare('SELECT * FROM transportadores WHERE id = :id');
    $stmt->execute(['id' => $editarId]);
    $transportadorEdicao = $stmt->fetch() ?: null;
}
$transportadores = $pdo->query('SELECT * FROM transportadores ORDER BY nome_razao')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2"><div><h1 class="h3 mb-1">Transportadores</h1><p class="mb-0">Cadastre motoristas e empresas responsáveis pelas entregas.</p></div><span class="badge text-bg-light border"><?= count($transportadores) ?> cadastrados</span></div>
<section class="panel mb-4">
    <h2 class="h5 mb-3"><?= $transportadorEdicao ? 'Editar transportador' : 'Novo transportador' ?></h2>
    <form action="../actions/transportador_action.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="acao" value="<?= $transportadorEdicao ? 'atualizar' : 'criar' ?>">
        <?php if ($transportadorEdicao): ?><input type="hidden" name="id" value="<?= (int) $transportadorEdicao['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="nome_razao">Nome / Razão social *</label><input class="form-control" id="nome_razao" name="nome_razao" maxlength="180" required value="<?= e($transportadorEdicao['nome_razao'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label" for="documento">CPF ou CNPJ *</label><input class="form-control" id="documento" name="documento" inputmode="numeric" maxlength="18" required value="<?= e($transportadorEdicao['documento'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label" for="telefone">Telefone *</label><input class="form-control" id="telefone" name="telefone" type="tel" maxlength="30" required value="<?= e($transportadorEdicao['telefone'] ?? '') ?>"></div>
            <div class="col-md-8"><label class="form-label" for="veiculo_modelo">Veículo / Modelo *</label><input class="form-control" id="veiculo_modelo" name="veiculo_modelo" maxlength="120" required value="<?= e($transportadorEdicao['veiculo_modelo'] ?? '') ?>"></div>
            <div class="col-md-4"><label class="form-label" for="placa">Placa *</label><input class="form-control text-uppercase" id="placa" name="placa" maxlength="8" placeholder="ABC1D23" required value="<?= e($transportadorEdicao['placa'] ?? '') ?>"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i><?= $transportadorEdicao ? 'Salvar alterações' : 'Cadastrar transportador' ?></button><?php if ($transportadorEdicao): ?><a class="btn btn-outline-secondary" href="transportadores.php">Cancelar</a><?php endif; ?></div>
        </div>
    </form>
</section>
<section class="panel">
    <h2 class="h5 mb-3">Transportadores cadastrados</h2>
    <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Nome / Razão social</th><th>Documento</th><th>Telefone</th><th>Veículo</th><th>Placa</th><th class="text-end">Ações</th></tr></thead><tbody>
    <?php foreach ($transportadores as $transportador): ?>
        <tr><td class="fw-semibold"><?= e($transportador['nome_razao']) ?></td><td><?= e($transportador['documento']) ?></td><td><?= e($transportador['telefone']) ?></td><td><?= e($transportador['veiculo_modelo']) ?></td><td><span class="badge text-bg-light border"><?= e($transportador['placa']) ?></span></td><td><div class="d-flex justify-content-end gap-2"><a class="btn btn-sm btn-outline-primary" href="?editar=<?= (int) $transportador['id'] ?>" aria-label="Editar transportador"><i class="bi bi-pencil"></i></a><form action="../actions/transportador_action.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $transportador['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Excluir este transportador? Registros usados em fretes não podem ser excluídos." aria-label="Excluir transportador"><i class="bi bi-trash"></i></button></form></div></td></tr>
    <?php endforeach; ?>
    <?php if (!$transportadores): ?><tr><td colspan="6" class="text-center text-muted py-4">Nenhum transportador cadastrado.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
