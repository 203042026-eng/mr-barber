// Mr Barber - área administrativa
// 1) Janela de confirmação antes de excluir (painel.php)
// 2) Validação do formulário de serviço (servico_form.php) - o PHP valida de novo no servidor

(function () {
    // ---- Janela de confirmação de exclusão ----
    var modal = document.getElementById('modal-excluir');

    if (modal) {
        document.querySelectorAll('.js-excluir').forEach(function (botao) {
            botao.addEventListener('click', function () {
                document.getElementById('modal-nome').textContent = botao.getAttribute('data-nome');
                document.getElementById('modal-id').value = botao.getAttribute('data-id');
                modal.classList.add('aberto');
            });
        });

        function fechar() {
            modal.classList.remove('aberto');
        }

        document.getElementById('modal-cancelar').addEventListener('click', fechar);

        // Clicar fora da janela ou apertar Esc também cancela
        modal.addEventListener('click', function (e) {
            if (e.target === modal) fechar();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') fechar();
        });
    }

    // ---- Validação do formulário de serviço ----
    var form = document.getElementById('form-servico');

    if (form) {
        var caixa = document.getElementById('caixa-validacao');
        var lista = document.getElementById('lista-validacao');

        form.addEventListener('submit', function (evento) {
            var mensagens = [];

            var nome = document.getElementById('nome').value.trim();
            var categoria = document.getElementById('categoria').value;
            var txt = document.getElementById('preco').value.trim();

            // Aceita 12,50 | 12.50 | 1.234,50
            if (txt.indexOf(',') !== -1) {
                txt = txt.replace(/\./g, '').replace(',', '.');
            }
            var preco = parseFloat(txt);

            if (nome === '') mensagens.push('Informe o nome do serviço.');
            if (categoria === '') mensagens.push('Selecione uma categoria.');
            if (isNaN(preco) || preco <= 0 || !isFinite(Number(txt))) {
                mensagens.push('O preço deve ser um número maior que zero.');
            }

            if (mensagens.length) {
                evento.preventDefault();
                lista.innerHTML = '';
                mensagens.forEach(function (m) {
                    var li = document.createElement('li');
                    li.textContent = '* ' + m;
                    lista.appendChild(li);
                });
                caixa.classList.add('visivel');
                caixa.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        });
    }
})();
