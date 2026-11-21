<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();

const POR_PAGINA = 6;

$pdo = db();

// Filtros
$data = trim((string) ($_GET['data'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$pagina_atual = max(1, (int) ($_GET['pagina'] ?? 1));

if ($data !== '' && !data_valida($data)) {
    $data = '';
}
if (!in_array($status, ['pendente', 'confirmado', 'cancelado'], true)) {
    $status = '';
}

$where = '1 = 1';
$args = [];
if ($data !== '') {
    $where .= ' AND a.data_agendamento = ?';
    $args[] = $data;
}
if ($status !== '') {
    $where .= ' AND a.status = ?';
    $args[] = $status;
}

// 3. Resumo (contagem geral por status)
$contagem = ['pendente' => 0, 'confirmado' => 0, 'cancelado' => 0];
foreach ($pdo->query('SELECT status, COUNT(*) AS total FROM agendamento GROUP BY status') as $linha) {
    $contagem[$linha['status']] = (int) $linha['total'];
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM agendamento a WHERE $where");
$stmt->execute($args);
$total = (int) $stmt->fetchColumn();
$total_paginas = max(1, (int) ceil($total / POR_PAGINA));
$pagina_atual = min($pagina_atual, $total_paginas);
$offset = ($pagina_atual - 1) * POR_PAGINA;

// 4. Tabela (AGENDAMENTO + SERVICO)
$stmt = $pdo->prepare(
    "SELECT a.id_agendamento, a.nome_cliente, a.telefone, a.data_agendamento, a.hora_agendamento,
            a.status, s.nome AS servico
     FROM agendamento a JOIN servico s ON s.id_servico = a.id_servico
     WHERE $where
     ORDER BY a.data_agendamento, a.hora_agendamento
     LIMIT " . POR_PAGINA . " OFFSET " . (int) $offset
);
$stmt->execute($args);
$agendamentos = $stmt->fetchAll();

$primeiro = $total ? $offset + 1 : 0;
$ultimo = $offset + count($agendamentos);
$params_url = array_filter(['data' => $data, 'status' => $status]);

$rotulos = ['pendente' => 'Pendente', 'confirmado' => 'Confirmado', 'cancelado' => 'Cancelado'];

$titulo = 'Agendamentos | Mr Barber Admin';
$menu_ativo = 'agendamentos';
require __DIR__ . '/../includes/admin_topo.php';
?>

            <div class="admin-topo">
                <h1>Gerenciar Agendamentos</h1>
            </div>

            <!-- 2. Filtros por data e por status -->
            <form class="filtros-agenda" action="agendamentos.php" method="get">
                <label for="data" class="sr-only">Data</label>
                <input type="date" id="data" name="data" value="<?= e($data) ?>">

                <label for="status" class="sr-only">Status</label>
                <select id="status" name="status">
                    <option value="">Status: Todos</option>
                    <?php foreach ($rotulos as $chave => $rotulo): ?>
                        <option value="<?= $chave ?>" <?= $status === $chave ? 'selected' : '' ?>><?= $rotulo ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="botao-primario">Filtrar</button>
            </form>

            <!-- 3. Resumo com a quantidade por status -->
            <div class="contadores">
                <div class="contador pendente">Pendentes<strong><?= $contagem['pendente'] ?></strong></div>
                <div class="contador confirmado">Confirmados<strong><?= $contagem['confirmado'] ?></strong></div>
                <div class="contador cancelado">Cancelados<strong><?= $contagem['cancelado'] ?></strong></div>
            </div>

            <table class="tabela zebra-par">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Hora</th>
                        <th>Cliente</th>
                        <th>Telefone</th>
                        <th>Serviço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$agendamentos): ?>
                        <tr><td colspan="7">Nenhum agendamento encontrado.</td></tr>
                    <?php endif; ?>

                    <?php foreach ($agendamentos as $a): ?>
                        <tr>
                            <td><?= e(substr(data_br($a['data_agendamento']), 0, 5)) ?></td>
                            <td><?= e(hora_curta($a['hora_agendamento'])) ?></td>
                            <td><?= e($a['nome_cliente']) ?></td>
                            <td><?= e($a['telefone']) ?></td>
                            <td><?= e($a['servico']) ?></td>
                            <td><span class="status <?= e($a['status']) ?>"><?= e($rotulos[$a['status']] ?? $a['status']) ?></span></td>
                            <td class="acoes">
                                <?php if ($a['status'] === 'cancelado'): ?>
                                    -
                                <?php else: ?>
                                    <!-- 6. Ações: Confirmar (vira confirmado) e Cancelar (libera o horário) -->
                                    <form action="agendamento_status.php" method="post">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="id" value="<?= (int) $a['id_agendamento'] ?>">
                                        <input type="hidden" name="volta" value="<?= e(http_build_query($params_url + ['pagina' => $pagina_atual])) ?>">
                                        <?php if ($a['status'] === 'pendente'): ?>
                                            <button type="submit" name="acao" value="confirmar" class="btn-mini btn-ouro">Confirmar</button>
                                        <?php endif; ?>
                                        <button type="submit" name="acao" value="cancelar" class="btn-mini btn-preto">Cancelar</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p class="rodape-tabela">Exibindo <?= $primeiro ?>-<?= $ultimo ?> de <?= $total ?></p>
            <?= paginacao_html($pagina_atual, $total_paginas, 'agendamentos.php', $params_url, 'paginacao-admin') ?>

<?php require __DIR__ . '/../includes/admin_rodape.php'; ?>
