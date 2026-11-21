<?php
// Cabeçalho comum das páginas públicas.
// Antes de incluir, defina: $titulo (texto da aba) e $pagina (home|servicos|agendar|contato)
$menu = [
    'home'     => ['index.php', 'Home'],
    'servicos' => ['servicos.php', 'Serviços'],
    'agendar'  => ['agendar.php', 'Agendar'],
    'contato'  => ['contato.php', 'Contato'],
    'admin'    => ['admin/login.php', 'Admin'],
];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($titulo) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <header class="topo">
        <a href="index.php" class="logo">
            <span class="logo-simbolo"></span>
            <strong>MR BARBER</strong>
        </a>

        <nav>
            <?php foreach ($menu as $chave => [$url, $texto]): ?>
                <a href="<?= $url ?>"<?= $chave === $pagina ? ' class="ativo"' : '' ?>><?= $texto ?></a>
            <?php endforeach; ?>
        </nav>
    </header>
