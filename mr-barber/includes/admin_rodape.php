        </main>
    </div>

    <?php foreach (($scripts ?? []) as $js): ?>
        <script src="<?= e($js) ?>"></script>
    <?php endforeach; ?>
</body>

</html>
