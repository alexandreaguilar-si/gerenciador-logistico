<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$destino = '../pages/fretes.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar($destino);
validarCsrf();

$transportadorId = filter_var(postString('transportador_id'), FILTER_VALIDATE_INT);
$origemId = filter_var(postString('endereco_origem_id'), FILTER_VALIDATE_INT);
$destinoId = filter_var(postString('endereco_destino_id'), FILTER_VALIDATE_INT);
$dataFrete = trim(postString('data_frete'));
$valorFrete = numeroDecimal(postString('valor_frete'));
$status = postString('status') ?: 'Pendente';
$observacoes = trim(postString('observacoes'));
$produtoIds = $_POST['produto_id'] ?? [];
$quantidades = $_POST['quantidade'] ?? [];

if (!$transportadorId || !$origemId || !$destinoId || $origemId === $destinoId || !validarData($dataFrete)
    || $valorFrete === null || $valorFrete < 0
    || !in_array($status, ['Pendente', 'Em Trânsito', 'Entregue'], true)
    || !is_array($produtoIds) || !is_array($quantidades) || count($produtoIds) === 0 || count($produtoIds) !== count($quantidades)) {
    flash('danger', 'Confira os dados do frete. Origem e destino devem ser diferentes e é necessário incluir ao menos um produto.');
    redirecionar($destino);
}

$itens = [];
foreach ($produtoIds as $indice => $produtoIdBruto) {
    $produtoId = is_scalar($produtoIdBruto) ? filter_var((string) $produtoIdBruto, FILTER_VALIDATE_INT) : false;
    $quantidadeBruta = $quantidades[$indice] ?? null;
    $quantidade = is_scalar($quantidadeBruta) ? filter_var((string) $quantidadeBruta, FILTER_VALIDATE_INT) : false;
    if (!$produtoId || !$quantidade || $quantidade < 1) {
        flash('danger', 'Cada item da carga precisa ter um produto e uma quantidade inteira maior que zero.');
        redirecionar($destino);
    }
    $itens[] = ['produto_id' => $produtoId, 'quantidade' => $quantidade];
}

try {
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM transportadores WHERE id = :id');
    $stmt->execute(['id' => $transportadorId]);
    $transportadorExiste = (int) $stmt->fetchColumn() === 1;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM enderecos WHERE id IN (:origem, :destino)');
    $stmt->execute(['origem' => $origemId, 'destino' => $destinoId]);
    $enderecosExistem = (int) $stmt->fetchColumn() === 2;
    $stmt = $pdo->prepare('SELECT COUNT(*) FROM produtos WHERE id = :id');
    foreach ($itens as $item) {
        $stmt->execute(['id' => $item['produto_id']]);
        if ((int) $stmt->fetchColumn() !== 1) {
            flash('danger', 'Um dos produtos selecionados não existe mais. Atualize a página e tente novamente.');
            redirecionar($destino);
        }
    }
    if (!$transportadorExiste || !$enderecosExistem) {
        flash('danger', 'Transportador ou endereço inválido. Atualize a página e tente novamente.');
        redirecionar($destino);
    }

    $pdo->beginTransaction();
    $stmt = $pdo->prepare('INSERT INTO fretes (transportador_id, endereco_origem_id, endereco_destino_id, data_frete, status, valor_frete, observacoes) VALUES (:transportador, :origem, :destino, :data, :status, :valor, :observacoes)');
    $stmt->execute([
        'transportador' => $transportadorId,
        'origem' => $origemId,
        'destino' => $destinoId,
        'data' => $dataFrete,
        'status' => $status,
        'valor' => $valorFrete,
        'observacoes' => $observacoes ?: null,
    ]);
    $freteId = (int) $pdo->lastInsertId();
    $stmtItem = $pdo->prepare('INSERT INTO frete_itens (frete_id, produto_id, quantidade) VALUES (:frete_id, :produto_id, :quantidade)');
    foreach ($itens as $item) {
        $stmtItem->execute(['frete_id' => $freteId, 'produto_id' => $item['produto_id'], 'quantidade' => $item['quantidade']]);
    }
    $pdo->commit();
    flash('success', 'Frete #' . $freteId . ' emitido com sucesso e carga registrada.');
} catch (PDOException $exception) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('Erro ao emitir frete: ' . $exception->getMessage());
    flash('danger', 'Não foi possível emitir o frete. Nenhuma parte foi salva; confira os cadastros e tente novamente.');
}
redirecionar($destino);
