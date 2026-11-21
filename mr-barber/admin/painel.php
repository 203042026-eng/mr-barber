<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();

const POR_PAGINA = 5;

$pdo = db();
$q = trim((string) ($_GET['q'] ?? ''));
$pagina_atual = max(1, (int) ($_GET['pagina'] ?? 1));

$where = '1 = 1';
$args = [];
if ($q !== '') {
    $where = '(s.nome LIKE ? OR c.nome LIKE ?)';
    $args = ['%' . $q . '%', '%' . $q . '%'];
}

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM servico s JOIN categoria c ON c.id_categoria = s.id_categoria WHERE $where"
);
$stmt->execute($args);
$total = (int) $stmt->fetchColumn();
$total_paginas = max(1, (int) ceil($total / POR_PAGINA));
$pagina_atual = min($pagina_atual, $total_paginas);
$offset = ($pagina_atual - 1) * POR_PAGINA;

$stmt = $pdo->prepare(
    "SELECT s.id_servico, s.nome, s.preco, s.ativo, c.nome AS categoria
     FROM servico s JOIN categoria c ON c.id_categoria = s.id_categoria
     WHERE $where
     ORDER BY s.id_servico
     LIMIT " . POR_PAGINA . " OFFSET " . (int) $offset
);
$stmt->execute($args);
$servicos = $stmt->fetchAll();

$primeiro = $total ? $offset + 1 : 0;
$ultimo = $offset + count($servicos);

$titulo = 'Painel Admin | Mr Barber';
$menu_ativo = 'servicos';
$scripts = ['../js/admin.js'];
require __DIR__ . '/../includes/admin_topo.php';
?>

            <!-- 6. Título + botão novo serviço -->
            <div class="admin-topo">
                <h1>Gerenciar Serviços</h1>
                <a href="servico_form.php" class="botao-primario">+ Novo serviço</a>
            </div>

            <form method="get" action="painel.php">
                <label for="q" class="sr-only">Buscar</label>
                <input type="search" id="q" name="q" class="admin-busca" placeholder="Buscar..." value="<?= e($q) ?>">
            </form>

            <!-- 7. Tabela com Editar e Excluir em cada linha (lida do banco) -->
            <table class="tabela zebra-impar">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$servicos): ?>
                        <tr><td colspan="6">Nenhum serviço encontrado.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($servicos as $s): ?>
                        <tr>
                            <td><?= (int) $s['id_servico'] ?></td>
                            <td><?= e($s['nome']) ?></td>
                            <td><?= e($s['categoria']) ?></td>
                            <td><?= e(preco_br($s['preco'])) ?></td>
                            <td><?= $s['ativo'] ? 'Ativo' : 'Inativo' ?></td>
                            <td class="acoes">
                                <a href="servico_form.php?id=<?= (int) $s['id_servico'] ?>" class="btn-mini btn-ouro">Editar</a>
                                <button type="button" class="btn-mini btn-preto js-excluir"
                                        data-id="<?= (int) $s['id_servico'] ?>"
                                        data-nome="<?= e($s['nome']) ?>">Excluir</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="rodape-tabela">Exibindo <?= $primeiro ?>-<?= $ultimo ?> de <?= $total ?></p>
            <?= paginacao_html($pagina_atual, $total_paginas, 'painel.php', array_filter(['q' => $q]), 'paginacao-admin') ?>

            <!-- 6.2 Janela de confirmação antes de executar o DELETE -->
            <div class="modal-fundo" id="modal-excluir" role="dialog" aria-modal="true" aria-labelledby="modal-titulo">
                <div class="modal">
                    <h2 id="modal-titulo">Excluir serviço?</h2>
                    <p>Deseja realmente excluir "<span id="modal-nome"></span>"?</p>
                    <p class="aviso-final">Essa ação não poderá ser desfeita.</p>
                    <div class="modal-botoes">
                        <button type="button" class="botao-escuro" id="modal-cancelar">Cancelar</button>
                        <form action="excluir.php" method="post">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="id" id="modal-id" value="">
                            <button type="submit" class="botao-primario">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>

<?php require __DIR__ . '/../includes/admin_rodape.php'; ?>
