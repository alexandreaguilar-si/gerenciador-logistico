<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$paginaAtiva = 'produtos';
$tituloPagina = 'Produtos';
$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
$produtoEdicao = null;
if ($editarId) {
    $stmt = $pdo->prepare('SELECT * FROM produtos WHERE id = :id');
    $stmt->execute(['id' => $editarId]);
    $produtoEdicao = $stmt->fetch() ?: null;
}
$produtos = $pdo->query('SELECT * FROM produtos ORDER BY criado_em DESC, id DESC')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading d-flex flex-column flex-md-row justify-content-between align-items-md-end gap-2">
    <div><h1 class="h3 mb-1">Produtos</h1><p class="mb-0">Cadastre e mantenha os itens transportados.</p></div>
    <span class="badge text-bg-light border"><?= count($produtos) ?> cadastrados</span>
</div>
<section class="panel mb-4">
    <h2 class="h5 mb-3"><?= $produtoEdicao ? 'Editar produto' : 'Novo produto' ?></h2>
    <form action="../actions/produto_action.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
        <input type="hidden" name="acao" value="<?= $produtoEdicao ? 'atualizar' : 'criar' ?>">
        <?php if ($produtoEdicao): ?><input type="hidden" name="id" value="<?= (int) $produtoEdicao['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="nome">Nome *</label><input class="form-control" id="nome" name="nome" maxlength="150" required value="<?= e($produtoEdicao['nome'] ?? '') ?>"></div>
            <div class="col-md-3"><label class="form-label" for="peso_kg">Peso por unidade (kg) *</label><input class="form-control" id="peso_kg" name="peso_kg" type="number" min="0" step="0.001" required value="<?= e($produtoEdicao['peso_kg'] ?? '0.000') ?>"></div>
            <div class="col-md-3"><label class="form-label" for="valor_unitario">Valor unitário (R$) *</label><input class="form-control" id="valor_unitario" name="valor_unitario" type="number" min="0" step="0.01" required value="<?= e($produtoEdicao['valor_unitario'] ?? '0.00') ?>"></div>
            <div class="col-12"><label class="form-label" for="descricao">Descrição</label><textarea class="form-control" id="descricao" name="descricao" rows="2"><?= e($produtoEdicao['descricao'] ?? '') ?></textarea></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i><?= $produtoEdicao ? 'Salvar alterações' : 'Cadastrar produto' ?></button><?php if ($produtoEdicao): ?><a class="btn btn-outline-secondary" href="produtos.php">Cancelar</a><?php endif; ?></div>
        </div>
    </form>
</section>
<section class="panel">
    <h2 class="h5 mb-3">Produtos cadastrados</h2>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Produto</th><th>Peso</th><th>Valor unitário</th><th>Cadastrado em</th><th class="text-end">Ações</th></tr></thead>
            <tbody>
            <?php foreach ($produtos as $produto): ?>
                <tr>
                    <td><div class="fw-semibold"><?= e($produto['nome']) ?></div><small class="text-muted"><?= e($produto['descricao'] ?: 'Sem descrição') ?></small></td>
                    <td><?= number_format((float) $produto['peso_kg'], 3, ',', '.') ?> kg</td>
                    <td>R$ <?= number_format((float) $produto['valor_unitario'], 2, ',', '.') ?></td>
                    <td><?= e(date('d/m/Y', strtotime($produto['criado_em']))) ?></td>
                    <td><div class="d-flex justify-content-end gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="?editar=<?= (int) $produto['id'] ?>" aria-label="Editar <?= e($produto['nome']) ?>"><i class="bi bi-pencil"></i></a>
                        <form action="../actions/produto_action.php" method="post">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $produto['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Excluir este produto? Produtos usados em fretes não podem ser excluídos." aria-label="Excluir <?= e($produto['nome']) ?>"><i class="bi bi-trash"></i></button>
                        </form>
                    </div></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$produtos): ?><tr><td class="text-center text-muted py-4" colspan="5">Nenhum produto cadastrado.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
