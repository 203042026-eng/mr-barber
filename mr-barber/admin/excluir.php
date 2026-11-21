<?php
require_once __DIR__ . '/../includes/auth.php';
exigir_login();
exigir_post_seguro('painel.php');

$id = (int) ($_POST['id'] ?? 0);
$pdo = db();

$stmt = $pdo->prepare('SELECT nome FROM servico WHERE id_servico = ?');
$stmt->execute([$id]);
$servico = $stmt->fetch();

if (!$servico) {
    flash('erro', 'Serviço não encontrado.');
    header('Location: painel.php');
    exit;
}

try {
    $pdo->prepare('DELETE FROM servico WHERE id_servico = ?')->execute([$id]);
    flash('ok', 'Serviço "' . $servico['nome'] . '" excluído.');
} catch (PDOException $ex) {
    // Chave estrangeira: o serviço já tem agendamentos e não pode sumir do histórico
    flash('erro', 'Não é possível excluir "' . $servico['nome']
        . '" porque ele já possui agendamentos. Desmarque "Serviço ativo" para ocultá-lo do site.');
}

header('Location: painel.php');
exit;
