<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function postString(string $key): string
{
    $value = $_POST[$key] ?? '';
    return is_scalar($value) ? (string) $value : '';
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validarCsrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrfToken(), $token)) {
        http_response_code(419);
        exit('Sessão expirada ou token inválido. Volte à página e tente novamente.');
    }
}

function flash(string $tipo, string $mensagem): void
{
    $_SESSION['mensagem'] = ['tipo' => $tipo, 'texto' => $mensagem];
}

function redirecionar(string $destino): void
{
    header('Location: ' . $destino);
    exit;
}

function enderecoLegivel(array $endereco): string
{
    return trim($endereco['logradouro'] . ', ' . $endereco['numero'] . ' — ' . $endereco['cidade'] . '/' . $endereco['uf']);
}

function validarData(string $data): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $data)) {
        return false;
    }

    $valor = DateTime::createFromFormat('!Y-m-d', $data);
    return $valor !== false && $valor->format('Y-m-d') === $data;
}

function numeroDecimal(mixed $valor): ?float
{
    if (!is_scalar($valor) || !is_numeric((string) $valor)) {
        return null;
    }

    $numero = (float) $valor;
    return is_finite($numero) ? $numero : null;
}
