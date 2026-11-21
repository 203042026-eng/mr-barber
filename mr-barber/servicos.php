<?php
require_once __DIR__ . '/includes/funcoes.php';

const POR_PAGINA = 9;

$pdo = db();
$categorias = $pdo->query('SELECT id_categoria, nome FROM categoria ORDER BY ordem_exibicao, nome')->fetchAll();

// Filtros vindos da URL
$categoria_id = (int) ($_GET['categoria'] ?? 0);
$busca = trim((string) ($_GET['busca'] ?? ''));
$pagina_atual = max(1, (int) ($_GET['pagina'] ?? 1));

$where = 's.ativo = TRUE';
$args = [];
if ($categoria_id > 0) {
    $where .= ' AND s.id_categoria = ?';
    $args[] = $categoria_id;
}
if ($busca !== '') {
    $where .= ' AND s.nome LIKE ?';
    $args[] = '%' . $busca . '%';
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM servico s WHERE $where");
$stmt->execute($args);
$total = (int) $stmt->fetchColumn();
$total_paginas = max(1, (int) ceil($total / POR_PAGINA));
$pagina_atual = min($pagina_atual, $total_paginas);
$offset = ($pagina_atual - 1) * POR_PAGINA;

// Consulta do catálogo (JOIN com categoria, ordenada como no projeto)
$stmt = $pdo->prepare(
    "SELECT s.id_servico, s.nome, s.descricao, s.preco, c.nome AS categoria
     FROM servico s JOIN categoria c ON c.id_categoria = s.id_categoria
     WHERE $where
     ORDER BY c.ordem_exibicao, s.nome
     LIMIT " . POR_PAGINA . " OFFSET " . (int) $offset
);
$stmt->execute($args);
$servicos = $stmt->fetchAll();

$params_url = array_filter(['categoria' => $categoria_id ?: null, 'busca' => $busca ?: null]);

$titulo = 'Serviços e Preços | Mr Barber';
$pagina = 'servicos';
require __DIR__ . '/includes/topo.php';
?>

    <main>
        <!-- 1. Título -->
        <section class="titulo-pagina">
            <h1>Serviços e Preços</h1>
            <p>Todos os serviços com acabamento nos mínimos detalhes</p>
        </section>

        <div class="barra-catalogo">
            <!-- 2. Filtro por categoria (tabela categoria) -->
            <nav class="filtros" aria-label="Filtrar por categoria">
                <a href="servicos.php<?= $busca !== '' ? '?busca=' . urlencode($busca) : '' ?>"
                   class="<?= $categoria_id === 0 ? 'ativo' : '' ?>">Todos</a>
                <?php foreach ($categorias as $c): ?>
                    <a href="servicos.php?<?= e(http_build_query(array_filter(['categoria' => $c['id_categoria'], 'busca' => $busca ?: null]))) ?>"
                       class="<?= $categoria_id === (int) $c['id_categoria'] ? 'ativo' : '' ?>"><?= e($c['nome']) ?></a>
                <?php endforeach; ?>
            </nav>

            <!-- 3. Busca por nome -->
            <form class="busca" action="servicos.php" method="get">
                <?php if ($categoria_id > 0): ?>
                    <input type="hidden" name="categoria" value="<?= $categoria_id ?>">
                <?php endif; ?>
                <label for="busca" class="sr-only">Buscar serviço</label>
                <input type="search" id="busca" name="busca" placeholder="Buscar serviço..." value="<?= e($busca) ?>">
            </form>
        </div>

        <!-- 4. Grade de cartões (lidos do banco de dados) -->
        <section class="grade-servicos">
            <?php if (!$servicos): ?>
                <p class="vazio">Nenhum serviço encontrado.</p>
            <?php endif; ?>

            <?php foreach ($servicos as $s): ?>
                <article class="card-servico">
                    <span class="categoria-tag"><?= e($s['categoria']) ?></span>
                    <h3><?= e($s['nome']) ?></h3>
                    <p><?= e($s['descricao']) ?></p>
                    <div class="card-rodape">
                        <span class="preco"><?= e(preco_br($s['preco'])) ?></span>
                        <a href="agendar.php?servico=<?= (int) $s['id_servico'] ?>" class="botao-escuro">Agendar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>

        <!-- 5. Paginação -->
        <?= paginacao_html($pagina_atual, $total_paginas, 'servicos.php', $params_url) ?>
    </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
