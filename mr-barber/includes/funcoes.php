<?php
// Funções usadas em todo o site
date_default_timezone_set('America/Sao_Paulo');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

require_once __DIR__ . '/../config/conexao.php';

// Horários oferecidos no agendamento (conforme o wireframe)
const HORARIOS = [
    '09:00', '09:30', '10:00', '10:30',
    '11:00', '11:30', '13:00', '13:30',
    '14:00', '14:30', '15:00', '15:30',
];

// Dias em que a barbearia está fechada (0 = domingo)
const DIAS_FECHADOS = [0];

// Escapa texto antes de mostrar na tela (evita injeção de HTML/JS)
function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function preco_br($valor): string
{
    return 'R$ ' . number_format((float) $valor, 2, ',', '.');
}

function data_br(string $ymd): string
{
    $d = DateTime::createFromFormat('!Y-m-d', $ymd);
    return $d ? $d->format('d/m/Y') : '';
}

function hora_curta($hora): string
{
    return substr((string) $hora, 0, 5);
}

// Confere se a string é uma data real no formato AAAA-MM-DD
function data_valida(string $ymd): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $ymd);
    return $d && $d->format('Y-m-d') === $ymd;
}

function data_fechada(string $ymd): bool
{
    $d = DateTime::createFromFormat('!Y-m-d', $ymd);
    return $d && in_array((int) $d->format('w'), DIAS_FECHADOS, true);
}

// Formata telefone com 10 ou 11 dígitos: (11) 91234-5678
function formatar_telefone(string $texto): ?string
{
    $n = preg_replace('/\D/', '', $texto);

    if (strlen($n) === 11) {
        return sprintf('(%s) %s-%s', substr($n, 0, 2), substr($n, 2, 5), substr($n, 7));
    }
    if (strlen($n) === 10) {
        return sprintf('(%s) %s-%s', substr($n, 0, 2), substr($n, 2, 4), substr($n, 6));
    }
    return null;
}

// ----- Proteção CSRF (formulários que alteram dados) -----
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valido(): bool
{
    return isset($_POST['csrf'], $_SESSION['csrf'])
        && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

// ----- Mensagens entre páginas -----
function flash(string $tipo, string $mensagem): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $mensagem];
}

function pegar_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function mostrar_flash(): void
{
    $f = pegar_flash();
    if ($f) {
        $classe = $f['tipo'] === 'ok' ? 'alerta-ok' : 'alerta-erro';
        echo '<div class="alerta ' . $classe . '">' . e($f['msg']) . '</div>';
    }
}

function info_contato(): array
{
    $info = db()->query('SELECT * FROM info_contato ORDER BY id_info LIMIT 1')->fetch();
    return $info ?: [
        'endereco' => '', 'telefone' => '', 'whatsapp' => '',
        'email' => '', 'horario_funcionamento' => '', 'link_mapa' => '',
    ];
}

// Monta os links de paginação: < 1 2 3 >
function paginacao_html(int $atual, int $total, string $arquivo, array $params, string $classe = 'paginacao'): string
{
    if ($total <= 1) {
        return '';
    }

    $link = function (int $p) use ($arquivo, $params) {
        $params['pagina'] = $p;
        return e($arquivo . '?' . http_build_query($params));
    };

    $html = '<div class="' . e($classe) . '">';
    $html .= $atual > 1 ? '<a href="' . $link($atual - 1) . '">&lt;</a>' : '<span>&lt;</span>';
    for ($i = 1; $i <= $total; $i++) {
        $html .= $i === $atual
            ? '<span class="atual">' . $i . '</span>'
            : '<a href="' . $link($i) . '">' . $i . '</a>';
    }
    $html .= $atual < $total ? '<a href="' . $link($atual + 1) . '">&gt;</a>' : '<span>&gt;</span>';
    return $html . '</div>';
}
