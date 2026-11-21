<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();

$pdo = db();
$id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
$editando = $id > 0;

$categorias = $pdo->query('SELECT id_categoria, nome FROM categoria ORDER BY ordem_exibicao, nome')->fetchAll();

$v = ['nome' => '', 'categoria' => '', 'preco' => '', 'duracao' => '30', 'descricao' => '', 'ativo' => true];

if ($editando) {
    $stmt = $pdo->prepare('SELECT * FROM servico WHERE id_servico = ?');
    $stmt->execute([$id]);
    $s = $stmt->fetch();

    if (!$s) {
        flash('erro', 'Serviço não encontrado.');
        header('Location: painel.php');
        exit;
    }

    $v = [
        'nome'      => $s['nome'],
        'categoria' => (string) $s['id_categoria'],
        'preco'     => number_format((float) $s['preco'], 2, ',', ''),
        'duracao'   => (string) ($s['duracao_min'] ?? ''),
        'descricao' => (string) $s['descricao'],
        'ativo'     => (bool) $s['ativo'],
    ];
}

$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $erros[] = 'Sessão expirada. Tente novamente.';
    }

    $v['nome']      = trim((string) ($_POST['nome'] ?? ''));
    $v['categoria'] = (string) ($_POST['categoria'] ?? '');
    $v['preco']     = trim((string) ($_POST['preco'] ?? ''));
    $v['duracao']   = trim((string) ($_POST['duracao'] ?? ''));
    $v['descricao'] = trim((string) ($_POST['descricao'] ?? ''));
    $v['ativo']     = isset($_POST['ativo']);

    // Validações (as mesmas mensagens do wireframe)
    if ($v['nome'] === '' || mb_strlen($v['nome']) > 100) {
        $erros[] = 'Informe o nome do serviço.';
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM categoria WHERE id_categoria = ?');
    $stmt->execute([(int) $v['categoria']]);
    if ((int) $stmt->fetchColumn() === 0) {
        $erros[] = 'Selecione uma categoria.';
    }

    // Aceita "12,50", "12.50" e "1.234,50"
    $preco_txt = $v['preco'];
    if (strpos($preco_txt, ',') !== false) {
        $preco_txt = str_replace('.', '', $preco_txt);
        $preco_txt = str_replace(',', '.', $preco_txt);
    }
    $preco = is_numeric($preco_txt) ? round((float) $preco_txt, 2) : 0;
    if ($preco <= 0 || $preco > 99999999) {
        $erros[] = 'O preço deve ser um número maior que zero.';
    }

    $duracao = null;
    if ($v['duracao'] !== '') {
        if (!ctype_digit($v['duracao']) || (int) $v['duracao'] > 1440) {
            $erros[] = 'A duração deve ser um número de minutos.';
        } else {
            $duracao = (int) $v['duracao'];
        }
    }

    if (!$erros) {
        if ($editando) {
            $pdo->prepare(
                'UPDATE servico SET id_categoria = ?, nome = ?, descricao = ?, preco = ?, duracao_min = ?, ativo = ?
                 WHERE id_servico = ?'
            )->execute([(int) $v['categoria'], $v['nome'], $v['descricao'], $preco, $duracao, $v['ativo'] ? 1 : 0, $id]);
            flash('ok', 'Serviço atualizado.');
        } else {
            $pdo->prepare(
                'INSERT INTO servico (id_categoria, id_admin, nome, descricao, preco, duracao_min, ativo)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([(int) $v['categoria'], $_SESSION['admin_id'], $v['nome'], $v['descricao'], $preco, $duracao, $v['ativo'] ? 1 : 0]);
            flash('ok', 'Serviço cadastrado.');
        }

        header('Location: painel.php');
        exit;
    }
}

$titulo = ($editando ? 'Editar serviço' : 'Novo serviço') . ' | Mr Barber Admin';
$menu_ativo = 'servicos';
$scripts = ['../js/admin.js'];
require __DIR__ . '/../includes/admin_topo.php';
?>

            <div class="admin-topo">
                <h1><?= $editando ? 'Editar serviço' : 'Novo serviço' ?></h1>
            </div>

            <form class="form-admin" id="form-servico" action="servico_form.php<?= $editando ? '?id=' . $id : '' ?>"
                  method="post" novalidate>
                <?= csrf_campo() ?>
                <?php if ($editando): ?>
                    <input type="hidden" name="id" value="<?= $id ?>">
                <?php endif; ?>

                <div class="linha">
                    <div class="campo">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" maxlength="100" placeholder="Ex.: Corte infantil"
                               value="<?= e($v['nome']) ?>">
                    </div>

                    <div class="campo">
                        <label for="categoria">Categoria *</label>
                        <select id="categoria" name="categoria">
                            <option value="">Selecione...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= (int) $c['id_categoria'] ?>"
                                    <?= $v['categoria'] === (string) $c['id_categoria'] ? 'selected' : '' ?>><?= e($c['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="linha">
                    <div class="campo estreito">
                        <label for="preco">Preço (R$) *</label>
                        <input type="text" id="preco" name="preco" placeholder="0,00" inputmode="decimal"
                               value="<?= e($v['preco']) ?>">
                    </div>

                    <div class="campo estreito">
                        <label for="duracao">Duração (min)</label>
                        <input type="number" id="duracao" name="duracao" min="0" max="1440" placeholder="30"
                               value="<?= e($v['duracao']) ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao"><?= e($v['descricao']) ?></textarea>
                </div>

                <div class="campo-check">
                    <input type="checkbox" id="ativo" name="ativo" <?= $v['ativo'] ? 'checked' : '' ?>>
                    <label for="ativo">Serviço ativo (visível no site)</label>
                </div>

                <!-- Mensagens de validação (JavaScript no navegador, PHP no servidor) -->
                <div id="caixa-validacao" class="alerta alerta-erro<?= $erros ? ' visivel' : '' ?>">
                    <ul id="lista-validacao">
                        <?php foreach ($erros as $erro): ?>
                            <li>* <?= e($erro) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="form-botoes">
                    <button type="submit" class="botao-primario">Salvar</button>
                    <a href="painel.php" class="botao-escuro">Cancelar</a>
                    <span class="obrigatorios">* campos obrigatórios</span>
                </div>
            </form>

<?php require __DIR__ . '/../includes/admin_rodape.php'; ?>
