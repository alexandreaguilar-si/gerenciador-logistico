<?php
require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$paginaAtiva = 'enderecos';
$tituloPagina = 'Endereços';
$editarId = filter_input(INPUT_GET, 'editar', FILTER_VALIDATE_INT);
$enderecoEdicao = null;
if ($editarId) {
    $stmt = $pdo->prepare('SELECT * FROM enderecos WHERE id = :id');
    $stmt->execute(['id' => $editarId]);
    $enderecoEdicao = $stmt->fetch() ?: null;
}
$enderecos = $pdo->query('SELECT * FROM enderecos ORDER BY cidade, logradouro, numero')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<div class="page-heading"><h1 class="h3 mb-1">Endereços</h1><p class="mb-0">Gerencie pontos de coleta e entrega. A consulta de CEP usa o serviço ViaCEP.</p></div>
<section class="panel mb-4">
    <h2 class="h5 mb-3"><?= $enderecoEdicao ? 'Editar endereço' : 'Novo endereço' ?></h2>
    <form action="../actions/endereco_action.php" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="acao" value="<?= $enderecoEdicao ? 'atualizar' : 'criar' ?>">
        <?php if ($enderecoEdicao): ?><input type="hidden" name="id" value="<?= (int) $enderecoEdicao['id'] ?>"><?php endif; ?>
        <div class="row g-3">
            <div class="col-sm-4 col-lg-3"><label class="form-label" for="cep">CEP *</label><input class="form-control" id="cep" name="cep" data-viacep inputmode="numeric" autocomplete="postal-code" placeholder="00000-000" maxlength="9" required value="<?= e($enderecoEdicao['cep'] ?? '') ?>"><small class="text-muted" data-viacep-feedback>Ao sair do campo, o endereço será consultado.</small></div>
            <div class="col-sm-8 col-lg-6"><label class="form-label" for="logradouro">Logradouro *</label><input class="form-control" id="logradouro" name="logradouro" maxlength="180" required value="<?= e($enderecoEdicao['logradouro'] ?? '') ?>"></div>
            <div class="col-sm-4 col-lg-3"><label class="form-label" for="numero">Número *</label><input class="form-control" id="numero" name="numero" maxlength="30" required value="<?= e($enderecoEdicao['numero'] ?? '') ?>"></div>
            <div class="col-sm-6 col-lg-4"><label class="form-label" for="bairro">Bairro *</label><input class="form-control" id="bairro" name="bairro" maxlength="120" required value="<?= e($enderecoEdicao['bairro'] ?? '') ?>"></div>
            <div class="col-sm-6 col-lg-5"><label class="form-label" for="cidade">Cidade *</label><input class="form-control" id="cidade" name="cidade" maxlength="120" required value="<?= e($enderecoEdicao['cidade'] ?? '') ?>"></div>
            <div class="col-sm-4 col-lg-3"><label class="form-label" for="uf">UF *</label><input class="form-control text-uppercase" id="uf" name="uf" maxlength="2" minlength="2" required value="<?= e($enderecoEdicao['uf'] ?? '') ?>"></div>
            <div class="col-12"><label class="form-label" for="referencia">Referência</label><input class="form-control" id="referencia" name="referencia" maxlength="255" value="<?= e($enderecoEdicao['referencia'] ?? '') ?>"></div>
            <div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-check2 me-1"></i><?= $enderecoEdicao ? 'Salvar alterações' : 'Cadastrar endereço' ?></button><?php if ($enderecoEdicao): ?><a class="btn btn-outline-secondary" href="enderecos.php">Cancelar</a><?php endif; ?></div>
        </div>
    </form>
</section>
<section class="panel">
    <h2 class="h5 mb-3">Endereços cadastrados</h2>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead><tr><th>Local</th><th>CEP</th><th>Referência</th><th class="text-end">Ações</th></tr></thead>
        <tbody>
        <?php foreach ($enderecos as $endereco): ?>
            <tr><td><div class="fw-semibold"><?= e(enderecoLegivel($endereco)) ?></div><small class="text-muted"><?= e($endereco['bairro']) ?></small></td><td><?= e($endereco['cep']) ?></td><td><?= e($endereco['referencia'] ?: '—') ?></td><td><div class="d-flex justify-content-end gap-2"><a class="btn btn-sm btn-outline-primary" href="?editar=<?= (int) $endereco['id'] ?>" aria-label="Editar endereço"><i class="bi bi-pencil"></i></a><form action="../actions/endereco_action.php" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="acao" value="excluir"><input type="hidden" name="id" value="<?= (int) $endereco['id'] ?>"><button class="btn btn-sm btn-outline-danger" type="submit" data-confirm="Excluir este endereço? Endereços usados em fretes não podem ser excluídos." aria-label="Excluir endereço"><i class="bi bi-trash"></i></button></form></div></td></tr>
        <?php endforeach; ?>
        <?php if (!$enderecos): ?><tr><td colspan="4" class="text-center text-muted py-4">Nenhum endereço cadastrado.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
