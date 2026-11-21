<?php
// Barra superior + menu lateral do painel.
// Antes de incluir, defina: $titulo (aba) e $menu_ativo (servicos|agendamentos|categorias|contato)
$itens_menu = [
    'servicos'     => ['painel.php', 'Serviços'],
    'agendamentos' => ['agendamentos.php', 'Agendamentos'],
    'categorias'   => ['categorias.php', 'Categorias'],
    'contato'      => ['dados_contato.php', 'Dados de contato'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body class="admin">

    <!-- Barra superior com usuário logado e botão Sair -->
    <div class="admin-barra">
        <strong>MR BARBER | Painel Admin</strong>
        <div class="usuario">
            Olá, <?= e($_SESSION['admin_nome'] ?? 'Admin') ?>
            <span class="sep">|</span>
            <a href="logout.php">Sair</a>
        </div>
    </div>

    <div class="admin-corpo">
        <!-- Menu lateral -->
        <aside class="admin-menu">
            <?php foreach ($itens_menu as $chave => [$url, $texto]): ?>
                <a href="<?= $url ?>"<?= $chave === $menu_ativo ? ' class="ativo"' : '' ?>><?= $texto ?></a>
            <?php endforeach; ?>
            <a href="../index.php">Ver site</a>
        </aside>

        <main class="admin-principal">
            <?php mostrar_flash(); ?>
