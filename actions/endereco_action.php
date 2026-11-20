<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$destino = '../pages/enderecos.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar($destino);
validarCsrf();
$acao = postString('acao');
$id = filter_var(postString('id'), FILTER_VALIDATE_INT);

try {
    if ($acao === 'excluir' && $id) {
        $stmt = $pdo->prepare('DELETE FROM enderecos WHERE id = :id');
        $stmt->execute(['id' => $id]);
        flash('success', 'Endereço excluído com sucesso.');
    } elseif (in_array($acao, ['criar', 'atualizar'], true)) {
        $cep = preg_replace('/\D/', '', postString('cep'));
        $logradouro = trim(postString('logradouro'));
        $numero = trim(postString('numero'));
        $bairro = trim(postString('bairro'));
        $cidade = trim(postString('cidade'));
        $uf = strtoupper(trim(postString('uf')));
        $referencia = trim(postString('referencia'));
        if (strlen($cep) !== 8 || $logradouro === '' || $numero === '' || $bairro === '' || $cidade === '' || !preg_match('/^[A-Z]{2}$/', $uf)) {
            flash('danger', 'Preencha todos os campos obrigatórios com dados válidos (CEP com 8 dígitos e UF com 2 letras).');
            redirecionar($destino);
        }
        $dados = ['cep' => substr($cep, 0, 5) . '-' . substr($cep, 5), 'logradouro' => $logradouro, 'numero' => $numero, 'bairro' => $bairro, 'cidade' => $cidade, 'uf' => $uf, 'referencia' => $referencia ?: null];
        if ($acao === 'atualizar' && $id) {
            $stmt = $pdo->prepare('UPDATE enderecos SET cep=:cep, logradouro=:logradouro, numero=:numero, bairro=:bairro, cidade=:cidade, uf=:uf, referencia=:referencia WHERE id=:id');
            $stmt->execute($dados + ['id' => $id]);
            flash('success', 'Endereço atualizado com sucesso.');
        } elseif ($acao === 'criar') {
            $stmt = $pdo->prepare('INSERT INTO enderecos (cep, logradouro, numero, bairro, cidade, uf, referencia) VALUES (:cep, :logradouro, :numero, :bairro, :cidade, :uf, :referencia)');
            $stmt->execute($dados);
            flash('success', 'Endereço cadastrado com sucesso.');
        } else {
            flash('danger', 'Endereço inválido para atualização.');
        }
    } else {
        flash('danger', 'Ação inválida.');
    }
} catch (PDOException $exception) {
    error_log('Erro na ação de endereço: ' . $exception->getMessage());
    flash('danger', $acao === 'excluir'
        ? 'Não foi possível excluir. O endereço pode estar vinculado a fretes.'
        : 'Não foi possível salvar o endereço. Confira os dados e tente novamente.');
}
redirecionar($destino);
