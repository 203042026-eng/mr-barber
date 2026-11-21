<?php
// Controle de acesso da área administrativa
require_once __DIR__ . '/funcoes.php';

function admin_logado(): bool
{
    return !empty($_SESSION['admin_id']);
}

// Chame no topo de toda página restrita
function exigir_login(): void
{
    if (!admin_logado()) {
        header('Location: login.php');
        exit;
    }
}

// Exige POST com token CSRF válido (ações que alteram dados)
function exigir_post_seguro(string $destino): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrf_valido()) {
        flash('erro', 'Requisição inválida. Tente novamente.');
        header('Location: ' . $destino);
        exit;
    }
}
