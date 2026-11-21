<?php
require_once __DIR__ . '/includes/funcoes.php';

$pdo = db();

// ---------- 4.2 Tela de confirmação (depois de salvar) ----------
if (isset($_GET['sucesso'])) {
    $ultimo = $_SESSION['ultimo_agendamento'] ?? null;
    if (!$ultimo) {
        header('Location: agendar.php');
        exit;
    }

    $titulo = 'Agendamento solicitado | Mr Barber';
    $pagina = 'agendar';
    require __DIR__ . '/includes/topo.php';
    ?>
    <main class="confirmacao-fundo">
        <section class="confirmacao">
            <div class="icone-ok">OK</div>
            <h1>Agendamento solicitado!</h1>
            <p><?= e($ultimo['servico']) ?> - <?= e($ultimo['data']) ?> às <?= e($ultimo['hora']) ?>.
                Aguarde a confirmação da barbearia.</p>
            <a href="index.php" class="botao-primario">Voltar para a Home</a>
        </section>
    </main>
    <?php
    require __DIR__ . '/includes/rodape.php';
    exit;
}

// ---------- Dados para o formulário ----------
$servicos = $pdo->query(
    'SELECT id_servico, nome, preco FROM servico WHERE ativo = TRUE ORDER BY nome'
)->fetchAll();

$valores = [
    'servico'  => (int) ($_GET['servico'] ?? 0),
    'data'     => '',
    'hora'     => '',
    'nome'     => '',
    'telefone' => '',
];
$erros = [];

// ---------- Gravação do agendamento ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valores['servico']  = (int) ($_POST['servico'] ?? 0);
    $valores['data']     = trim((string) ($_POST['data'] ?? ''));
    $valores['hora']     = trim((string) ($_POST['hora'] ?? ''));
    $valores['nome']     = trim((string) ($_POST['nome'] ?? ''));
    $valores['telefone'] = trim((string) ($_POST['telefone'] ?? ''));

    // Validação no servidor (o JavaScript já validou, mas aqui conferimos de novo)
    $stmt = $pdo->prepare('SELECT id_servico, nome FROM servico WHERE id_servico = ? AND ativo = TRUE');
    $stmt->execute([$valores['servico']]);
    $servico = $stmt->fetch();
    if (!$servico) {
        $erros[] = 'Selecione um serviço.';
    }

    if (!data_valida($valores['data'])) {
        $erros[] = 'Escolha uma data válida.';
    } elseif ($valores['data'] < date('Y-m-d')) {
        $erros[] = 'Não é possível agendar em datas passadas.';
    } elseif (data_fechada($valores['data'])) {
        $erros[] = 'A barbearia está fechada neste dia. Escolha outra data.';
    }

    if (!in_array($valores['hora'], HORARIOS, true)) {
        $erros[] = 'Escolha um horário.';
    } elseif ($valores['data'] === date('Y-m-d') && $valores['hora'] <= date('H:i')) {
        $erros[] = 'Esse horário já passou. Escolha outro.';
    }

    if (mb_strlen($valores['nome']) < 3 || mb_strlen($valores['nome']) > 100) {
        $erros[] = 'Informe seu nome completo.';
    }

    $telefone = formatar_telefone($valores['telefone']);
    if ($telefone === null) {
        $erros[] = 'Informe um telefone válido com DDD.';
    }

    if (!$erros) {
        $hora_sql = $valores['hora'] . ':00';

        try {
            $pdo->beginTransaction();

            // Evita horário duplicado: confere se já existe outro, na mesma data e hora, não cancelado
            $sql = "SELECT COUNT(*) FROM agendamento
                    WHERE data_agendamento = ? AND hora_agendamento = ? AND status <> 'cancelado'";
            if ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
                $sql .= ' FOR UPDATE';
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$valores['data'], $hora_sql]);

            if ((int) $stmt->fetchColumn() > 0) {
                $pdo->rollBack();
                $erros[] = 'Esse horário acabou de ser reservado. Escolha outro.';
            } else {
                $stmt = $pdo->prepare(
                    "INSERT INTO agendamento (id_servico, nome_cliente, telefone, data_agendamento, hora_agendamento, status)
                     VALUES (?, ?, ?, ?, ?, 'pendente')"
                );
                $stmt->execute([$servico['id_servico'], $valores['nome'], $telefone, $valores['data'], $hora_sql]);
                $pdo->commit();

                $_SESSION['ultimo_agendamento'] = [
                    'servico' => $servico['nome'],
                    'data'    => data_br($valores['data']),
                    'hora'    => $valores['hora'],
                ];
                header('Location: agendar.php?sucesso=1');
                exit;
            }
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $erros[] = 'Não foi possível salvar o agendamento agora. Tente novamente.';
        }
    }
}

$titulo = 'Agendar horário | Mr Barber';
$pagina = 'agendar';
$scripts = ['js/agendar.js'];
require __DIR__ . '/includes/topo.php';
?>

    <main>
        <section class="titulo-pagina">
            <h1>Agende seu horário</h1>
            <p>Escolha o serviço, o dia e o horário que ficam melhor para você</p>
        </section>

        <!-- 4.1 Formulário de agendamento -->
        <div class="agendar-layout">
            <form class="agendar-form" id="form-agendar" action="agendar.php" method="post" novalidate>

                <?php if ($erros): ?>
                    <div class="alerta alerta-erro">
                        <ul>
                            <?php foreach ($erros as $erro): ?>
                                <li>* <?= e($erro) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="campo">
                    <label for="servico">1. Serviço *</label>
                    <select id="servico" name="servico">
                        <option value="" data-preco="0">Selecione o serviço</option>
                        <?php foreach ($servicos as $s): ?>
                            <option value="<?= (int) $s['id_servico'] ?>" data-preco="<?= e($s['preco']) ?>"
                                <?= $valores['servico'] === (int) $s['id_servico'] ? 'selected' : '' ?>>
                                <?= e($s['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="erro" id="erro-servico">Selecione um serviço.</span>
                </div>

                <div class="campo">
                    <label for="data">2. Data *</label>
                    <div class="linha-data">
                        <input type="date" id="data" name="data" value="<?= e($valores['data']) ?>">
                        <span class="dica">Dias fechados e datas passadas ficam bloqueados</span>
                    </div>
                    <span class="erro" id="erro-data">Escolha uma data válida.</span>
                </div>

                <div class="campo">
                    <label>3. Horário *</label>
                    <div class="horarios" id="horarios">
                        <?php foreach (HORARIOS as $h): ?>
                            <button type="button" class="horario"><?= $h ?></button>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" id="hora" name="hora" value="<?= e($valores['hora']) ?>">
                    <div class="legenda">
                        <span class="l-disponivel">Disponível</span>
                        <span class="l-ocupado">Ocupado</span>
                        <span class="l-selecionado">Selecionado</span>
                    </div>
                    <span class="erro" id="erro-hora">Escolha um horário.</span>
                </div>

                <h2>4. Seus dados</h2>

                <div class="campo">
                    <label for="nome">Nome completo *</label>
                    <input type="text" id="nome" name="nome" maxlength="100" placeholder="Digite seu nome"
                           value="<?= e($valores['nome']) ?>">
                    <span class="erro" id="erro-nome">Informe seu nome completo.</span>
                </div>

                <div class="campo">
                    <label for="telefone">Telefone / WhatsApp *</label>
                    <input type="tel" id="telefone" name="telefone" maxlength="20" placeholder="(00) 00000-0000"
                           value="<?= e($valores['telefone']) ?>">
                    <span class="erro" id="erro-telefone">Informe um telefone válido com DDD.</span>
                </div>

                <button type="submit" class="botao-primario">Confirmar agendamento</button>
            </form>

            <!-- 7. Resumo (atualizado pelo JavaScript) -->
            <aside>
                <div class="resumo">
                    <h3>Resumo do agendamento</h3>
                    <dl>
                        <div class="resumo-linha"><dt>Serviço:</dt><dd id="r-servico">-</dd></div>
                        <div class="resumo-linha"><dt>Data:</dt><dd id="r-data">-</dd></div>
                        <div class="resumo-linha"><dt>Horário:</dt><dd id="r-hora">-</dd></div>
                        <div class="resumo-linha"><dt>Valor:</dt><dd id="r-valor">R$ 00,00</dd></div>
                    </dl>
                </div>
                <p class="aviso"><strong>Atenção: o horário fica "pendente"</strong> até a barbearia confirmar o agendamento.</p>
            </aside>
        </div>
    </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
