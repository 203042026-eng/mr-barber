<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();
exigir_post_seguro('agendamentos.php');

$id = (int) ($_POST['id'] ?? 0);
$acao = (string) ($_POST['acao'] ?? '');

// Refaz a query string de volta só com os campos permitidos
parse_str((string) ($_POST['volta'] ?? ''), $volta_bruta);
$volta = [];
foreach (['data', 'status', 'pagina'] as $campo) {
    if (isset($volta_bruta[$campo]) && is_string($volta_bruta[$campo]) && $volta_bruta[$campo] !== '') {
        $volta[$campo] = $volta_bruta[$campo];
    }
}
$destino = 'agendamentos.php' . ($volta ? '?' . http_build_query($volta) : '');

$novo_status = ['confirmar' => 'confirmado', 'cancelar' => 'cancelado'][$acao] ?? null;
if ($novo_status === null) {
    flash('erro', 'Ação inválida.');
    header('Location: ' . $destino);
    exit;
}

$pdo = db();
$stmt = $pdo->prepare('SELECT status FROM agendamento WHERE id_agendamento = ?');
$stmt->execute([$id]);
$atual = $stmt->fetchColumn();

if ($atual === false) {
    flash('erro', 'Agendamento não encontrado.');
} elseif ($atual === 'cancelado') {
    flash('erro', 'Este agendamento já está cancelado.');
} elseif ($novo_status === 'confirmado' && $atual !== 'pendente') {
    flash('erro', 'Só é possível confirmar agendamentos pendentes.');
} else {
    $pdo->prepare('UPDATE agendamento SET status = ? WHERE id_agendamento = ?')->execute([$novo_status, $id]);
    flash('ok', $novo_status === 'confirmado' ? 'Agendamento confirmado.' : 'Agendamento cancelado. O horário foi liberado.');
}

header('Location: ' . $destino);
exit;
