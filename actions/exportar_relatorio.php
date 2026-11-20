<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/conexao.php';
require_once __DIR__ . '/../includes/funcoes.php';

$dataBruta = $_GET['data'] ?? '';
$data = is_string($dataBruta) ? $dataBruta : '';
if (!validarData($data)) {
    http_response_code(400);
    exit('Data inválida. Use o formato AAAA-MM-DD.');
}

$stmt = $pdo->prepare("SELECT f.id, f.data_frete, f.status, f.valor_frete, f.observacoes, t.nome_razao AS transportador, t.documento, t.telefone, t.placa, origem.logradouro AS origem_logradouro, origem.numero AS origem_numero, origem.bairro AS origem_bairro, origem.cidade AS origem_cidade, origem.uf AS origem_uf, destino.logradouro AS destino_logradouro, destino.numero AS destino_numero, destino.bairro AS destino_bairro, destino.cidade AS destino_cidade, destino.uf AS destino_uf, COALESCE(SUM(fi.quantidade * p.peso_kg), 0) AS peso_total, COALESCE(SUM(fi.quantidade), 0) AS unidades, GROUP_CONCAT(CONCAT(p.nome, ' (', fi.quantidade, ' un.)') ORDER BY p.nome SEPARATOR ' | ') AS produtos FROM fretes f JOIN transportadores t ON t.id=f.transportador_id JOIN enderecos origem ON origem.id=f.endereco_origem_id JOIN enderecos destino ON destino.id=f.endereco_destino_id LEFT JOIN frete_itens fi ON fi.frete_id=f.id LEFT JOIN produtos p ON p.id=fi.produto_id WHERE f.data_frete=:data GROUP BY f.id, f.data_frete, f.status, f.valor_frete, f.observacoes, t.nome_razao, t.documento, t.telefone, t.placa, origem.logradouro, origem.numero, origem.bairro, origem.cidade, origem.uf, destino.logradouro, destino.numero, destino.bairro, destino.cidade, destino.uf ORDER BY f.id");
$stmt->execute(['data' => $data]);
$fretes = $stmt->fetchAll();

function protegerCelulaCsv(string $valor): string
{
    return preg_match('/^\s*[=+\-@]/u', $valor) ? "'" . $valor : $valor;
}

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio-logistico-' . $data . '.csv"');
header('Cache-Control: no-store, no-cache, must-revalidate');
echo "\xEF\xBB\xBF";
$saida = fopen('php://output', 'wb');
if ($saida === false) {
    http_response_code(500);
    exit('Não foi possível gerar o arquivo CSV.');
}
fputcsv($saida, ['GERENCIADOR LOGÍSTICO — RELATÓRIO DIÁRIO'], ';', '"', '');
fputcsv($saida, ['Data', date('d/m/Y', strtotime($data))], ';', '"', '');
fputcsv($saida, [], ';', '"', '');
fputcsv($saida, ['Frete', 'Status', 'Transportador', 'Documento', 'Telefone', 'Placa', 'Origem', 'Destino', 'Produtos e quantidades', 'Unidades', 'Peso total (kg)', 'Valor do frete (R$)', 'Observações'], ';', '"', '');
$totalPeso = 0.0;
$totalValor = 0.0;
foreach ($fretes as $frete) {
    $totalPeso += (float) $frete['peso_total'];
    $totalValor += (float) $frete['valor_frete'];
    $origem = $frete['origem_logradouro'] . ', ' . $frete['origem_numero'] . ' — ' . $frete['origem_bairro'] . ', ' . $frete['origem_cidade'] . '/' . $frete['origem_uf'];
    $destino = $frete['destino_logradouro'] . ', ' . $frete['destino_numero'] . ' — ' . $frete['destino_bairro'] . ', ' . $frete['destino_cidade'] . '/' . $frete['destino_uf'];
    $linha = [
        (string) $frete['id'], $frete['status'], $frete['transportador'], $frete['documento'],
        $frete['telefone'], $frete['placa'], $origem, $destino, $frete['produtos'] ?: 'Sem itens',
        (string) $frete['unidades'], number_format((float) $frete['peso_total'], 3, ',', '.'),
        number_format((float) $frete['valor_frete'], 2, ',', '.'), $frete['observacoes'] ?? '',
    ];
    fputcsv($saida, array_map(static fn(string $campo): string => protegerCelulaCsv($campo), $linha), ';', '"', '');
}
fputcsv($saida, [], ';', '"', '');
fputcsv($saida, ['Totais', 'Fretes: ' . count($fretes), 'Peso (kg): ' . number_format($totalPeso, 3, ',', '.'), 'Valor (R$): ' . number_format($totalValor, 2, ',', '.')], ';', '"', '');
fclose($saida);
