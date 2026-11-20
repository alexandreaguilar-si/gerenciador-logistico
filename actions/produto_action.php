<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$destino = '../pages/produtos.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirecionar($destino);
}
validarCsrf();

$acao = postString('acao');
$id = filter_var(postString('id'), FILTER_VALIDATE_INT);

try {
    if ($acao === 'excluir' && $id) {
        $stmt = $pdo->prepare('DELETE FROM produtos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        flash('success', 'Produto excluído com sucesso.');
    } elseif (in_array($acao, ['criar', 'atualizar'], true)) {
        $nome = trim(postString('nome'));
        $descricao = trim(postString('descricao'));
        $peso = numeroDecimal(postString('peso_kg'));
        $valor = numeroDecimal(postString('valor_unitario'));

        if ($nome === '' || mb_strlen($nome) > 150 || $peso === null || $peso < 0 || $valor === null || $valor < 0) {
            flash('danger', 'Informe nome, peso e valor válidos. Peso e valor não podem ser negativos.');
            redirecionar($destino);
        }

        if ($acao === 'atualizar' && $id) {
            $stmt = $pdo->prepare('UPDATE produtos SET nome = :nome, descricao = :descricao, peso_kg = :peso, valor_unitario = :valor WHERE id = :id');
            $stmt->execute(['nome' => $nome, 'descricao' => $descricao ?: null, 'peso' => $peso, 'valor' => $valor, 'id' => $id]);
            flash('success', 'Produto atualizado com sucesso.');
        } elseif ($acao === 'criar') {
            $stmt = $pdo->prepare('INSERT INTO produtos (nome, descricao, peso_kg, valor_unitario) VALUES (:nome, :descricao, :peso, :valor)');
            $stmt->execute(['nome' => $nome, 'descricao' => $descricao ?: null, 'peso' => $peso, 'valor' => $valor]);
            flash('success', 'Produto cadastrado com sucesso.');
        } else {
            flash('danger', 'Produto inválido para atualização.');
        }
    } else {
        flash('danger', 'Ação inválida.');
    }
} catch (PDOException $exception) {
    error_log('Erro na ação de produto: ' . $exception->getMessage());
    flash('danger', $acao === 'excluir'
        ? 'Não foi possível excluir. O produto pode estar vinculado a fretes.'
        : 'Não foi possível salvar o produto. Confira os dados e tente novamente.');
}

redirecionar($destino);
