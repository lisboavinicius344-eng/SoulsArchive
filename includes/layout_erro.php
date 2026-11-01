<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= e($titulo) ?> | SoulArchive Wiki</title>

  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('css/admin.css')) ?>">
  <link rel="icon" href="<?= e(url('img/logos/favicon.png')) ?>">
</head>

<body class="pagina-centralizada">

  <article class="article-card cartao-estreito">

    <h3><?= e($titulo) ?></h3>

    <p><?= e($mensagem) ?></p>

    <p class="acoes-cartao">
      <a href="<?= e(url('index.php')) ?>" class="btn-secondary">Voltar ao início</a>
    </p>

  </article>

</body>

</html>
