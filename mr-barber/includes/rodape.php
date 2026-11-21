<?php
// Rodapé comum das páginas públicas. Opcional: $scripts = ['js/arquivo.js']
$info_rodape = info_contato();
$linha_rodape = trim($info_rodape['endereco'] . ' | ' . $info_rodape['telefone'], ' |');
?>

    <footer class="rodape">
        <div class="rodape-interno">
            <div>
                <strong>MR BARBER</strong>
                <p class="slogan">"Os mínimos detalhes fazem grandes mudanças"</p>
            </div>
            <div class="rodape-info">
                <?= e($linha_rodape) ?>
            </div>
        </div>
        <small class="copyright">&copy; 2026 Mr Barber</small>
    </footer>

    <?php foreach (($scripts ?? []) as $js): ?>
        <script src="<?= e($js) ?>"></script>
    <?php endforeach; ?>
</body>

</html>
