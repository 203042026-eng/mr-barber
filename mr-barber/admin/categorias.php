<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();

$pdo = db();
$erros = [];
$editando = (int) ($_GET['editar'] ?? 0);
$v = ['id' => 0, 'nome' => '', 'descricao' => '', 'ordem' => '0'];

if ($editando > 0) {
    $stmt = $pdo->prepare('SELECT * FROM categoria WHERE id_categoria = ?');
    $stmt->execute([$editando]);
    if ($c = $stmt->fetch()) {
        $v = ['id' => (int) $c['id_categoria'], 'nome' => $c['nome'],
              'descricao' => (string) $c['descricao'], 'ordem' => (string) $c['ordem_exibicao']];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $erros[] = 'Sessão expirada. Tente novamente.';
    } elseif (($_POST['acao'] ?? '') === 'excluir') {
        // Excluir categoria (só se não tiver serviços)
        $idc = (int) ($_POST['id'] ?? 0);
        try {
            $pdo->prepare('DELETE FROM categoria WHERE id_categoria = ?')->execute([$idc]);
            flash('ok', 'Categoria excluída.');
        } catch (PDOException $ex) {
            flash('erro', 'Não é possível excluir uma categoria que possui serviços.');
        }
        header('Location: categorias.php');
        exit;
    } else {
        // Salvar (nova ou existente)
        $v['id'] = (int) ($_POST['id'] ?? 0);
        $v['nome'] = trim((string) ($_POST['nome'] ?? ''));
        $v['descricao'] = trim((string) ($_POST['descricao'] ?? ''));
        $v['ordem'] = trim((string) ($_POST['ordem'] ?? '0'));

        if ($v['nome'] === '' || mb_strlen($v['nome']) > 60) {
            $erros[] = 'Informe o nome da categoria (até 60 caracteres).';
        }
        if (mb_strlen($v['descricao']) > 255) {
            $erros[] = 'A descrição pode ter no máximo 255 caracteres.';
        }
        if (!ctype_digit($v['ordem'])) {
            $erros[] = 'A ordem deve ser um número inteiro (0 ou mais).';
        }

        if (!$erros) {
            try {
                if ($v['id'] > 0) {
                    $pdo->prepare('UPDATE categoria SET nome = ?, descricao = ?, ordem_exibicao = ? WHERE id_categoria = ?')
                        ->execute([$v['nome'], $v['descricao'] ?: null, (int) $v['ordem'], $v['id']]);
                    flash('ok', 'Categoria atualizada.');
                } else {
                    $pdo->prepare('INSERT INTO categoria (nome, descricao, ordem_exibicao) VALUES (?, ?, ?)')
                        ->execute([$v['nome'], $v['descricao'] ?: null, (int) $v['ordem']]);
                    flash('ok', 'Categoria cadastrada.');
                }
                header('Location: categorias.php');
                exit;
            } catch (PDOException $ex) {
                $erros[] = 'Já existe uma categoria com esse nome.';
            }
        }
    }
}

$categorias = $pdo->query(
    'SELECT c.id_categoria, c.nome, c.descricao, c.ordem_exibicao, COUNT(s.id_servico) AS qtd
     FROM categoria c LEFT JOIN servico s ON s.id_categoria = c.id_categoria
     GROUP BY c.id_categoria, c.nome, c.descricao, c.ordem_exibicao
     ORDER BY c.ordem_exibicao, c.nome'
)->fetchAll();

$titulo = 'Categorias | Mr Barber Admin';
$menu_ativo = 'categorias';
require __DIR__ . '/../includes/admin_topo.php';
?>

            <div class="admin-topo">
                <h1>Categorias</h1>
            </div>

            <table class="tabela zebra-impar">
                <thead>
                    <tr>
                        <th>Ordem</th>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Serviços</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categorias as $c): ?>
                        <tr>
                            <td><?= (int) $c['ordem_exibicao'] ?></td>
                            <td><?= e($c['nome']) ?></td>
                            <td><?= e($c['descricao']) ?></td>
                            <td><?= (int) $c['qtd'] ?></td>
                            <td class="acoes">
                                <a href="categorias.php?editar=<?= (int) $c['id_categoria'] ?>" class="btn-mini btn-ouro">Editar</a>
                                <form action="categorias.php" method="post"
                                      onsubmit="return confirm('Excluir a categoria &quot;<?= e($c['nome']) ?>&quot;? Essa ação não poderá ser desfeita.');">
                                    <?= csrf_campo() ?>
                                    <input type="hidden" name="acao" value="excluir">
                                    <input type="hidden" name="id" value="<?= (int) $c['id_categoria'] ?>">
                                    <button type="submit" class="btn-mini btn-preto">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h1 style="font-size:1rem;margin:28px 0 14px;"><?= $v['id'] ? 'Editar categoria' : 'Nova categoria' ?></h1>

            <?php if ($erros): ?>
                <div class="alerta alerta-erro">
                    <ul><?php foreach ($erros as $erro): ?><li>* <?= e($erro) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form class="form-admin" action="categorias.php" method="post">
                <?= csrf_campo() ?>
                <input type="hidden" name="id" value="<?= (int) $v['id'] ?>">

                <div class="linha">
                    <div class="campo">
                        <label for="nome">Nome *</label>
                        <input type="text" id="nome" name="nome" maxlength="60" value="<?= e($v['nome']) ?>">
                    </div>
                    <div class="campo estreito">
                        <label for="ordem">Ordem no site</label>
                        <input type="number" id="ordem" name="ordem" min="0" value="<?= e($v['ordem']) ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="descricao">Descrição</label>
                    <input type="text" id="descricao" name="descricao" maxlength="255" value="<?= e($v['descricao']) ?>">
                </div>

                <div class="form-botoes">
                    <button type="submit" class="botao-primario">Salvar</button>
                    <?php if ($v['id']): ?>
                        <a href="categorias.php" class="botao-escuro">Cancelar</a>
                    <?php endif; ?>
                    <span class="obrigatorios">* campos obrigatórios</span>
                </div>
            </form>

<?php require __DIR__ . '/../includes/admin_rodape.php'; ?>
