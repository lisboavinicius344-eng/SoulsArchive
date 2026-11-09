<?php

require_once __DIR__ . '/includes/auth.php';

if (usuarioAtual() !== null) {
    redirecionar('admin/index.php');
}

$erro = '';
$identificacao = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $identificacao = textoPost('identificacao');
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';

    if (!csrfValido()) {
        $erro = 'Sua sessão expirou. Tente entrar novamente.';
    } else {
        $conta = $identificacao !== '' && $senha !== '' ? autenticar($identificacao, $senha) : null;

        if ($conta !== null) {
            iniciarSessaoUsuario($conta);
            redirecionar('admin/index.php');
        }

        $erro = 'Usuário ou senha inválidos.';
    }
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Login | SoulArchive Wiki</title>

  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/admin.css">
  <link rel="icon" href="img/logos/favicon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body class="pagina-centralizada login-tela">

  <main class="login-cartao">

    <div class="painel-marca">
      <span class="painel-marca-icone"><?= icone('chama') ?></span>
      <span>
        <strong>SoulArchive</strong>
        <small>Painel administrativo</small>
      </span>
    </div>

    <h1>Acesso ao painel</h1>

    <?php exibirMensagens(); ?>

    <?php if (isset($_GET['encerrada'])): ?>
      <p class="alerta alerta-sucesso" role="status">Sessão encerrada.</p>
    <?php endif; ?>

    <?php exibirErros($erro === '' ? [] : [$erro]); ?>

    <form method="post" action="login.php" class="contact-form">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="identificacao">Nome de usuário ou e-mail</label>
        <input type="text" id="identificacao" name="identificacao" value="<?= e($identificacao) ?>" autocomplete="username" required autofocus>
      </div>

      <div class="form-group">
        <label for="senha">Senha</label>
        <input type="password" id="senha" name="senha" autocomplete="current-password" required>
      </div>

      <button type="submit" class="btn-acao">Entrar</button>

    </form>

    <a href="index.php" class="btn-voltar-site">
      <?= icone('site') ?>
      Voltar ao site
    </a>

  </main>

</body>

</html>
