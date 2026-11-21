<?php
require_once __DIR__ . '/includes/funcoes.php';

// 3 serviços em destaque (os primeiros cadastrados e ativos)
$destaques = db()->query(
    'SELECT id_servico, nome, descricao, preco FROM servico WHERE ativo = TRUE ORDER BY id_servico LIMIT 3'
)->fetchAll();

$titulo = 'Mr Barber';
$pagina = 'home';
require __DIR__ . '/includes/topo.php';
?>

    <main>
        <!-- 2. Banner principal -->
        <section class="banner">
            <h1>OS MÍNIMOS DETALHES <br> <span>FAZEM GRANDES MUDANÇAS</span></h1>

            <div class="banner-botoes">
                <a href="agendar.php" class="botao-primario">Agendar horário</a>
                <a href="servicos.php" class="botao-secundario">Ver serviços</a>
            </div>
        </section>

        <!-- 3. Nossos serviços em destaque (vêm do banco de dados) -->
        <section class="destaques">
            <h2>Nossos serviços em destaque</h2>

            <div class="cards">
                <?php foreach ($destaques as $s): ?>
                    <article class="card-servico">
                        <h3><?= e($s['nome']) ?></h3>
                        <p><?= e($s['descricao']) ?></p>
                        <div class="card-rodape">
                            <span class="preco"><?= e(preco_br($s['preco'])) ?></span>
                            <a href="agendar.php?servico=<?= (int) $s['id_servico'] ?>" class="botao-escuro">Agendar</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <a href="servicos.php" class="ver-catalogo">Ver catálogo completo &gt;</a>
        </section>

        <!-- 4. Sobre a Mr Barber -->
        <section class="sobre">
            <div class="sobre-interno">
                <div class="sobre-foto">
                    <!-- Coloque a foto real em img/barbearia.jpg; enquanto não existir, aparece o quadro cinza -->
                    <img src="img/barbearia.jpg" alt="Interior da barbearia Mr Barber"
                         onerror="this.onerror=null;this.src='img/foto-placeholder.svg'">
                </div>
                <div class="sobre-texto">
                    <h2>Sobre a Mr Barber</h2>
                    <p>Na Mr Barber, cada corte e cada barba são feitos com atenção aos mínimos detalhes.
                        Um ambiente sofisticado, atendimento personalizado e o cuidado que o seu visual merece.</p>
                    <a href="servicos.php" class="botao-primario">Nossos serviços</a>
                </div>
            </div>
        </section>

        <!-- 5. Chamada para ação -->
        <section class="cta-contato">
            <div class="cta-interno">
                <p>Venha nos conhecer e transforme o seu visual.</p>
                <a href="contato.php" class="botao-primario">Como chegar</a>
            </div>
        </section>
    </main>

<?php require __DIR__ . '/includes/rodape.php'; ?>
