<?php
require_once __DIR__ . '/funcoes.php';
$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$prefixo = str_contains($scriptPath, '/pages/') ? '..' : '.';
$paginaAtiva = $paginaAtiva ?? '';
$tituloPagina = $tituloPagina ?? 'Gerenciador Logístico';
$flashAtual = $_SESSION['mensagem'] ?? null;
unset($_SESSION['mensagem']);
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Sistema local para gestão de produtos, transportadores, fretes e relatórios.">
    <title><?= e($tituloPagina) ?> | Logística</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e($prefixo) ?>/assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
    <div class="container-fluid px-lg-4">
        <a class="navbar-brand fw-semibold" href="<?= e($prefixo) ?>/index.php">
            <i class="bi bi-box-seam me-2"></i>Logística
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Abrir menu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="menuPrincipal">
            <ul class="navbar-nav ms-auto">
                <?php
                $itensMenu = [
                    'dashboard' => ['Dashboard', '/index.php', 'bi-grid-1x2'],
                    'produtos' => ['Produtos', '/pages/produtos.php', 'bi-box'],
                    'enderecos' => ['Endereços', '/pages/enderecos.php', 'bi-geo-alt'],
                    'transportadores' => ['Transportadores', '/pages/transportadores.php', 'bi-truck'],
                    'fretes' => ['Fretes', '/pages/fretes.php', 'bi-send'],
                    'relatorio' => ['Relatórios', '/pages/relatorio.php', 'bi-bar-chart'],
                ];
                foreach ($itensMenu as $chave => [$rotulo, $link, $icone]):
                ?>
                <li class="nav-item">
                    <a class="nav-link <?= $paginaAtiva === $chave ? 'active' : '' ?>" <?= $paginaAtiva === $chave ? 'aria-current="page"' : '' ?> href="<?= e($prefixo . $link) ?>">
                        <i class="bi <?= e($icone) ?> me-1"></i><?= e($rotulo) ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="container-fluid app-container px-3 px-lg-4 py-4">
    <?php if ($flashAtual): ?>
        <div class="alert alert-<?= e($flashAtual['tipo']) ?> alert-dismissible fade show" role="alert">
            <?= e($flashAtual['texto']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
        </div>
    <?php endif; ?>
