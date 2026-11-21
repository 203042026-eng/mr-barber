<?php
require_once __DIR__ . '/../includes/auth.php';

if (admin_logado()) {
    header('Location: painel.php');
    exit;
}

$erro = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim((string) ($_POST['email'] ?? ''));
    $senha = (string) ($_POST['senha'] ?? '');

    if (!csrf_valido()) {
        $erro = 'Sessão expirada. Tente novamente.';
    } else {
        $stmt = db()->prepare('SELECT id_admin, nome, senha_hash FROM administrador WHERE email = ? AND ativo = TRUE');
        $stmt->execute([$email]);
        $admin = $stmt->fetch();

        // password_verify compara a senha digitada com o hash guardado no banco
        if ($admin && password_verify($senha, $admin['senha_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id_admin'];
            $_SESSION['admin_nome'] = $admin['nome'];
            header('Location: painel.php');
            exit;
        }

        $erro = 'Usuário ou senha incorretos.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Mr Barber Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body class="login">

    <main class="login-caixa">
        <!-- 1. Logo e identificação -->
        <span class="logo-simbolo"></span>
        <h1>MR BARBER</h1>
        <p class="subtitulo">Área Administrativa</p>

        <?php if ($erro): ?>
            <div class="alerta alerta-erro"><?= e($erro) ?></div>
        <?php endif; ?>

        <form action="login.php" method="post">
            <?= csrf_campo() ?>

            <!-- 2. Campos usuário/senha -->
            <div class="campo">
                <label for="email">Usuário (e-mail)</label>
                <input type="email" id="email" name="email" placeholder="admin@mrbarber.com.br"
                       value="<?= e($email) ?>" required autofocus>
            </div>

            <div class="campo">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" placeholder="••••••••" required>
            </div>

            <!-- 3. Botão entrar -->
            <button type="submit" class="botao-primario">Entrar</button>
        </form>

        <a href="../index.php" class="voltar">&lt; Voltar ao site</a>
    </main>

</body>

</html>
