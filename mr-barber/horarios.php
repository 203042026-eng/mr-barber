<?php
// Retorna em JSON os horários indisponíveis de uma data (usado pelo agendar.js)
require_once __DIR__ . '/includes/funcoes.php';

header('Content-Type: application/json; charset=utf-8');

$data = (string) ($_GET['data'] ?? '');

if (!data_valida($data)) {
    http_response_code(400);
    echo json_encode(['erro' => 'Data inválida']);
    exit;
}

$resposta = ['fechado' => false, 'passada' => false, 'indisponiveis' => []];

if ($data < date('Y-m-d')) {
    $resposta['passada'] = true;
    $resposta['indisponiveis'] = HORARIOS;
} elseif (data_fechada($data)) {
    $resposta['fechado'] = true;
    $resposta['indisponiveis'] = HORARIOS;
} else {
    // Horários já reservados (não cancelados)
    $stmt = db()->prepare(
        "SELECT hora_agendamento FROM agendamento
         WHERE data_agendamento = ? AND status <> 'cancelado'"
    );
    $stmt->execute([$data]);
    $ocupados = array_map('hora_curta', $stmt->fetchAll(PDO::FETCH_COLUMN));

    // Se for hoje, horários que já passaram também ficam bloqueados
    $passados = [];
    if ($data === date('Y-m-d')) {
        $agora = date('H:i');
        $passados = array_filter(HORARIOS, fn($h) => $h <= $agora);
    }

    $resposta['indisponiveis'] = array_values(array_unique(array_merge($ocupados, $passados)));
}

echo json_encode($resposta);
