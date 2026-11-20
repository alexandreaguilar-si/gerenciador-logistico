<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';
$destino = '../pages/transportadores.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirecionar($destino);
validarCsrf();
$acao = postString('acao');
$id = filter_var(postString('id'), FILTER_VALIDATE_INT);

try {
    if ($acao === 'excluir' && $id) {
        $stmt = $pdo->prepare('DELETE FROM transportadores WHERE id = :id');
        $stmt->execute(['id' => $id]);
        flash('success', 'Transportador excluído com sucesso.');
    } elseif (in_array($acao, ['criar', 'atualizar'], true)) {
        $nome = trim(postString('nome_razao'));
        $documento = preg_replace('/\D/', '', postString('documento'));
        $telefone = trim(postString('telefone'));
        $telefoneDigitos = preg_replace('/\D/', '', $telefone);
        $veiculo = trim(postString('veiculo_modelo'));
        $placa = strtoupper(preg_replace('/[^A-Z0-9]/i', '', postString('placa')));
        $placaValida = preg_match('/^[A-Z]{3}[0-9][A-Z0-9][0-9]{2}$/', $placa);
        if ($nome === '' || !in_array(strlen($documento), [11, 14], true) || strlen($telefoneDigitos) < 8 || strlen($telefoneDigitos) > 15 || $veiculo === '' || !$placaValida) {
            flash('danger', 'Informe nome, CPF/CNPJ válido, telefone, veículo e placa válida.');
            redirecionar($destino);
        }
        $dados = ['nome' => $nome, 'documento' => $documento, 'telefone' => $telefone, 'veiculo' => $veiculo, 'placa' => $placa];
        if ($acao === 'atualizar' && $id) {
            $stmt = $pdo->prepare('UPDATE transportadores SET nome_razao=:nome, documento=:documento, telefone=:telefone, veiculo_modelo=:veiculo, placa=:placa WHERE id=:id');
            $stmt->execute($dados + ['id' => $id]);
            flash('success', 'Transportador atualizado com sucesso.');
        } elseif ($acao === 'criar') {
            $stmt = $pdo->prepare('INSERT INTO transportadores (nome_razao, documento, telefone, veiculo_modelo, placa) VALUES (:nome, :documento, :telefone, :veiculo, :placa)');
            $stmt->execute($dados);
            flash('success', 'Transportador cadastrado com sucesso.');
        } else {
            flash('danger', 'Transportador inválido para atualização.');
        }
    } else {
        flash('danger', 'Ação inválida.');
    }
} catch (PDOException $exception) {
    error_log('Erro na ação de transportador: ' . $exception->getMessage());
    flash('danger', $acao === 'excluir'
        ? 'Não foi possível excluir. O transportador pode estar vinculado a fretes.'
        : 'Não foi possível salvar. O CPF/CNPJ ou a placa já podem estar cadastrados.');
}
redirecionar($destino);
