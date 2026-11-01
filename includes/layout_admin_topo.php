<?php

// Variáveis definidas por cada página: $usuario, $tituloPagina e, opcionalmente,
// $secaoAtiva, $subtituloPagina, $acaoPrincipal e $migalhas.

$secaoAtiva = $secaoAtiva ?? '';
$migalhasCompletas = array_merge([['Painel', 'admin/index.php']], $migalhas ?? []);

// O botão de retorno sempre aponta para o nível anterior da trilha de navegação.
$voltar = count($migalhasCompletas) > 1 ? $migalhasCompletas[count($migalhasCompletas) - 2] : null;

$itensConteudo = [
    ['inicio', 'Início', 'painel', 'admin/index.php'],
    ['bosses', 'Chefes', 'chefes', 'admin/bosses/index.php'],
    ['builds', 'Builds', 'builds', 'admin/builds/index.php'],
];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= e($tituloPagina) ?> | Painel SoulArchive</title>

  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
  <link rel="stylesheet" href="<?= e(url('css/admin.css')) ?>">
  <link rel="icon" href="<?= e(url('img/logos/favicon.png')) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

  <script src="<?= e(url('js/admin.js')) ?>" defer></script>
</head>

<body class="painel-corpo">

  <div class="painel">

    <!-- MENU LATERAL -->
    <aside class="painel-lateral">

      <a href="<?= e(url('admin/index.php')) ?>" class="painel-marca">
        <span class="painel-marca-icone"><?= icone('chama') ?></span>
        <span>
          <strong>SoulArchive</strong>
          <small>Painel administrativo</small>
        </span>
      </a>

      <nav class="painel-menu" aria-label="Menu do painel">

        <p class="painel-menu-grupo">Conteúdo</p>

        <?php foreach ($itensConteudo as [$secao, $rotulo, $iconeItem, $caminho]): ?>
          <a href="<?= e(url($caminho)) ?>" class="painel-menu-item<?= $secaoAtiva === $secao ? ' ativo' : '' ?>"<?= $secaoAtiva === $secao ? ' aria-current="page"' : '' ?>>
            <?= icone($iconeItem) ?>
            <?= e($rotulo) ?>
          </a>
        <?php endforeach; ?>

        <?php if ($usuario['perfil'] === 'administrador'): ?>
          <p class="painel-menu-grupo">Administração</p>

          <a href="<?= e(url('admin/usuarios/index.php')) ?>" class="painel-menu-item<?= $secaoAtiva === 'usuarios' ? ' ativo' : '' ?>"<?= $secaoAtiva === 'usuarios' ? ' aria-current="page"' : '' ?>>
            <?= icone('usuarios') ?>
            Usuários
          </a>
        <?php endif; ?>

      </nav>

      <div class="painel-conta">

        <span class="painel-avatar" aria-hidden="true"><?= e(mb_strtoupper(mb_substr($usuario['nome'], 0, 1))) ?></span>

        <span class="painel-conta-dados">
          <strong><?= e($usuario['nome_usuario']) ?></strong>
          <small><?= e(PERFIS_USUARIO[$usuario['perfil']]) ?></small>
        </span>

        <form action="<?= e(url('logout.php')) ?>" method="post">
          <?= campoCsrf() ?>
          <button type="submit" class="painel-sair" title="Sair" aria-label="Sair">
            <?= icone('sair') ?>
          </button>
        </form>

      </div>

    </aside>

    <div class="painel-principal">

      <!-- BARRA SUPERIOR -->
      <header class="painel-topo">

        <nav class="migalhas" aria-label="Trilha de navegação">
          <?php foreach ($migalhasCompletas as $indice => [$rotulo, $caminho]): ?>
            <?php if ($indice > 0): ?>
              <span class="migalhas-separador" aria-hidden="true">/</span>
            <?php endif; ?>

            <?php if ($indice < count($migalhasCompletas) - 1): ?>
              <a href="<?= e(url($caminho)) ?>"><?= e($rotulo) ?></a>
            <?php else: ?>
              <span aria-current="page"><?= e($rotulo) ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </nav>

        <a href="<?= e(url('index.php')) ?>" class="btn-voltar-site">
          <?= icone('site') ?>
          Voltar ao site
        </a>

      </header>

      <!-- MAIN -->
      <main class="painel-conteudo">

        <div class="cabecalho-pagina">

          <div>
            <?php if ($voltar !== null): ?>
              <a href="<?= e(url($voltar[1])) ?>" class="btn-retorno">
                <?= icone('voltar') ?>
                <?= e($voltar[1] === 'admin/index.php' ? 'Voltar ao painel' : 'Voltar para ' . $voltar[0]) ?>
              </a>
            <?php endif; ?>

            <h1><?= e($tituloPagina) ?></h1>

            <?php if (!empty($subtituloPagina)): ?>
              <p><?= e($subtituloPagina) ?></p>
            <?php endif; ?>
          </div>

          <?php if (!empty($acaoPrincipal)): ?>
            <a href="<?= e(url($acaoPrincipal[1])) ?>" class="btn-acao">
              <?= icone('mais') ?>
              <?= e($acaoPrincipal[0]) ?>
            </a>
          <?php endif; ?>

        </div>

        <?php exibirMensagens(); ?>
