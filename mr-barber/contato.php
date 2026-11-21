<?php
require_once __DIR__ . '/includes/funcoes.php';

$info = info_contato();

$titulo = 'Contato | Mr Barber';
$pagina = 'contato';
require __DIR__ . '/includes/topo.php';

$itens = [
    'Endereço'                 => $info['endereco'],
    'Telefone'                 => $info['telefone'],
    'WhatsApp'                 => $info['whatsapp'],
    'E-mail'                   => $info['email'],
    'Horário de funcionamento' => $info['horario_funcionamento'],
];
?>

    <main>
        <!-- 1. Cabeçalho -->
        <section class="titulo-pagina">
            <h1>Contato e Localização</h1>
        </section>

        <div class="contato-layout">
            <!-- 2. Mapa (iframe do Google Maps) -->
            <div class="mapa">
                <?php if (!empty($info['link_mapa'])): ?>
                    <iframe src="<?= e($info['link_mapa']) ?>" title="Mapa de localização da Mr Barber"
                            loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                <?php else: ?>
                    <div class="mapa-vazio">Mapa não cadastrado</div>
                <?php endif; ?>
            </div>

            <!-- 3. Cartão com os dados (tabela info_contato) -->
            <div class="contato-card">
                <h2>Fale com a gente</h2>
                <dl>
                    <?php foreach ($itens as $rotulo => $valor): ?>
                        <?php if ($valor !== null && $valor !== ''): ?>
                            <div class="contato-item">
                                <span class="contato-icone"></span>
                                <div>
                                    <dt><?= e($rotulo) ?></dt>
                                    <dd><?= e($valor) ?></dd>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </dl>
            </div>
        </div>
    </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
