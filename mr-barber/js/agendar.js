// Mr Barber - agendar.php
// Resumo em tempo real, horários ocupados (consulta o banco via horarios.php) e validação.
// O PHP confere tudo de novo ao salvar.

(function () {
    var form = document.getElementById('form-agendar');
    var selServico = document.getElementById('servico');
    var inData = document.getElementById('data');
    var inHora = document.getElementById('hora');
    var inNome = document.getElementById('nome');
    var inTel = document.getElementById('telefone');
    var botoes = document.querySelectorAll('.horario');

    // ---- Data mínima = hoje (datas passadas bloqueadas) ----
    var hoje = new Date();
    var hojeStr = hoje.getFullYear() + '-' +
        String(hoje.getMonth() + 1).padStart(2, '0') + '-' +
        String(hoje.getDate()).padStart(2, '0');
    inData.min = hojeStr;

    function erro(id, mostrar, texto) {
        var el = document.getElementById(id);
        if (texto) el.textContent = texto;
        el.classList.toggle('visivel', mostrar);
    }

    // ---- Resumo ----
    function atualizarResumo() {
        var opt = selServico.options[selServico.selectedIndex];
        var tem = selServico.value !== '';
        document.getElementById('r-servico').textContent = tem ? opt.text.trim() : '-';

        var preco = tem ? parseFloat(opt.getAttribute('data-preco')) : 0;
        document.getElementById('r-valor').textContent =
            'R$ ' + preco.toFixed(2).replace('.', ',');

        if (inData.value) {
            var p = inData.value.split('-');
            document.getElementById('r-data').textContent = p[2] + '/' + p[1] + '/' + p[0];
        } else {
            document.getElementById('r-data').textContent = '-';
        }

        document.getElementById('r-hora').textContent = inHora.value || '-';
    }

    // ---- Horários ----
    function marcarSelecionado() {
        botoes.forEach(function (b) {
            b.classList.toggle('selecionado', b.textContent === inHora.value);
        });
    }

    function liberarTodos(bloquear) {
        botoes.forEach(function (b) {
            b.classList.toggle('ocupado', bloquear);
            b.disabled = bloquear;
        });
    }

    function carregarHorarios() {
        erro('erro-data', false);

        if (!inData.value) {
            liberarTodos(false);
            return;
        }

        if (inData.value < hojeStr) {
            erro('erro-data', true, 'Não é possível agendar em datas passadas.');
            liberarTodos(true);
            inHora.value = '';
            marcarSelecionado();
            atualizarResumo();
            return;
        }

        fetch('horarios.php?data=' + encodeURIComponent(inData.value))
            .then(function (r) { return r.json(); })
            .then(function (dados) {
                if (dados.fechado) {
                    erro('erro-data', true, 'A barbearia está fechada neste dia. Escolha outra data.');
                }

                botoes.forEach(function (b) {
                    var indisponivel = dados.indisponiveis.indexOf(b.textContent) !== -1;
                    b.classList.toggle('ocupado', indisponivel);
                    b.disabled = indisponivel;

                    // Se o horário escolhido ficou indisponível, limpa a escolha
                    if (indisponivel && b.textContent === inHora.value) {
                        inHora.value = '';
                    }
                });

                marcarSelecionado();
                atualizarResumo();
            })
            .catch(function () {
                // Se a consulta falhar, o PHP ainda valida ao salvar
                liberarTodos(false);
            });
    }

    botoes.forEach(function (b) {
        b.addEventListener('click', function () {
            if (b.classList.contains('ocupado')) return;
            inHora.value = b.textContent;
            erro('erro-hora', false);
            marcarSelecionado();
            atualizarResumo();
        });
    });

    selServico.addEventListener('change', function () {
        erro('erro-servico', false);
        atualizarResumo();
    });

    inData.addEventListener('change', function () {
        atualizarResumo();
        carregarHorarios();
    });

    // ---- Validação ao enviar ----
    form.addEventListener('submit', function (evento) {
        var valido = true;

        var okServico = selServico.value !== '';
        erro('erro-servico', !okServico);
        if (!okServico) valido = false;

        var okData = inData.value !== '' && inData.value >= hojeStr;
        erro('erro-data', !okData, 'Escolha uma data válida.');
        if (!okData) valido = false;

        var okHora = inHora.value !== '';
        erro('erro-hora', !okHora);
        if (!okHora) valido = false;

        var okNome = inNome.value.trim().length >= 3;
        erro('erro-nome', !okNome);
        if (!okNome) valido = false;

        var digitos = inTel.value.replace(/\D/g, '');
        var okTel = digitos.length === 10 || digitos.length === 11;
        erro('erro-telefone', !okTel);
        if (!okTel) valido = false;

        if (!valido) evento.preventDefault();
    });

    // ---- Estado inicial (também funciona após erro do servidor) ----
    atualizarResumo();
    if (inData.value) {
        carregarHorarios();
    }
    marcarSelecionado();
})();
