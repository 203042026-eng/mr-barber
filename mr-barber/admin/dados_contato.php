<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();

$pdo = db();
$atual = $pdo->query('SELECT * FROM info_contato ORDER BY id_info LIMIT 1')->fetch();

$v = [
    'endereco'  => $atual['endereco'] ?? '',
    'telefone'  => $atual['telefone'] ?? '',
    'whatsapp'  => $atual['whatsapp'] ?? '',
    'email'     => $atual['email'] ?? '',
    'horario'   => $atual['horario_funcionamento'] ?? '',
    'link_mapa' => $atual['link_mapa'] ?? '',
];
$erros = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_valido()) {
        $erros[] = 'Sessão expirada. Tente novamente.';
    }

    foreach ($v as $campo => $_) {
        $v[$campo] = trim((string) ($_POST[$campo] ?? ''));
    }

    if ($v['endereco'] === '' || mb_strlen($v['endereco']) > 200) {
        $erros[] = 'Informe o endereço da barbearia.';
    }
    if ($v['email'] !== '' && !filter_var($v['email'], FILTER_VALIDATE_EMAIL)) {
        $erros[] = 'Informe um e-mail válido.';
    }
    if (mb_strlen($v['telefone']) > 20 || mb_strlen($v['whatsapp']) > 20) {
        $erros[] = 'Telefone e WhatsApp podem ter no máximo 20 caracteres.';
    }
    if (mb_strlen($v['horario']) > 200) {
        $erros[] = 'O horário de funcionamento pode ter no máximo 200 caracteres.';
    }

    // Se colou o código <iframe ...> inteiro, pega só o endereço do src
    if (preg_match('/src="([^"]+)"/i', $v['link_mapa'], $m)) {
        $v['link_mapa'] = html_entity_decode($m[1]);
    }
    if ($v['link_mapa'] !== '') {
        $ok = preg_match('#^https://www\.google\.com/maps(/embed|\?)#', $v['link_mapa']) && mb_strlen($v['link_mapa']) <= 500;
        if (!$ok) {
            $erros[] = 'O link do mapa deve ser o endereço de incorporação do Google Maps (https://www.google.com/maps/embed?...).';
        }
    }

    if (!$erros) {
        $dados = [$v['endereco'], $v['telefone'] ?: null, $v['whatsapp'] ?: null, $v['email'] ?: null,
                  $v['horario'] ?: null, $v['link_mapa'] ?: null, $_SESSION['admin_id']];

        if ($atual) {
            $pdo->prepare(
                'UPDATE info_contato SET endereco = ?, telefone = ?, whatsapp = ?, email = ?,
                 horario_funcionamento = ?, link_mapa = ?, id_admin = ? WHERE id_info = ?'
            )->execute(array_merge($dados, [$atual['id_info']]));
        } else {
            $pdo->prepare(
                'INSERT INTO info_contato (endereco, telefone, whatsapp, email, horario_funcionamento, link_mapa, id_admin)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute($dados);
        }

        flash('ok', 'Dados de contato salvos.');
        header('Location: dados_contato.php');
        exit;
    }
}

$titulo = 'Dados de contato | Mr Barber Admin';
$menu_ativo = 'contato';
require __DIR__ . '/../includes/admin_topo.php';
?>

            <div class="admin-topo">
                <h1>Dados de contato</h1>
            </div>

            <?php if ($erros): ?>
                <div class="alerta alerta-erro">
                    <ul><?php foreach ($erros as $erro): ?><li>* <?= e($erro) ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <form class="form-admin" action="dados_contato.php" method="post">
                <?= csrf_campo() ?>

                <div class="campo">
                    <label for="endereco">Endereço *</label>
                    <input type="text" id="endereco" name="endereco" maxlength="200" value="<?= e($v['endereco']) ?>">
                </div>

                <div class="linha">
                    <div class="campo">
                        <label for="telefone">Telefone</label>
                        <input type="text" id="telefone" name="telefone" maxlength="20" value="<?= e($v['telefone']) ?>">
                    </div>
                    <div class="campo">
                        <label for="whatsapp">WhatsApp</label>
                        <input type="text" id="whatsapp" name="whatsapp" maxlength="20" value="<?= e($v['whatsapp']) ?>">
                    </div>
                </div>

                <div class="campo">
                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" maxlength="120" value="<?= e($v['email']) ?>">
                </div>

                <div class="campo">
                    <label for="horario">Horário de funcionamento</label>
                    <input type="text" id="horario" name="horario" maxlength="200" value="<?= e($v['horario']) ?>">
                </div>

                <div class="campo">
                    <label for="link_mapa">Link do mapa (Google Maps)</label>
                    <textarea id="link_mapa" name="link_mapa"><?= e($v['link_mapa']) ?></textarea>
                    <span class="ajuda">No Google Maps: Compartilhar &gt; Incorporar um mapa &gt; copie o código. Pode colar o código inteiro, o sistema pega só o link.</span>
                </div>

                <div class="form-botoes">
                    <button type="submit" class="botao-primario">Salvar</button>
                    <span class="obrigatorios">* campos obrigatórios</span>
                </div>
            </form>

<?php require __DIR__ . '/../includes/admin_rodape.php'; ?>
