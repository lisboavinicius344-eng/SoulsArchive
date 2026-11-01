<?php

require_once __DIR__ . '/includes/funcoes.php';

$pdo = conectarBanco();

// Registro individual (?slug=) ou listagem com pesquisa, filtro por tipo e paginação.
$build = null;

if (isset($_GET['slug'])) {
    $consulta = $pdo->prepare('SELECT * FROM builds WHERE slug = ?');
    $consulta->execute([textoGet('slug')]);
    $build = $consulta->fetch();

    if ($build === false) {
        paginaErro(404, 'A build solicitada não foi encontrada.');
    }
}

$busca = textoGet('q');
$tipo = in_array(textoGet('tipo'), TIPOS_BUILD, true) ? textoGet('tipo') : '';

if ($build === null) {
    $condicoes = [];
    $parametros = [];

    if ($busca !== '') {
        $condicoes[] = '(nome LIKE ? OR slug LIKE ? OR tipo LIKE ?)';
        array_push($parametros, termoLike($busca), termoLike($busca), termoLike($busca));
    }

    if ($tipo !== '') {
        $condicoes[] = 'tipo = ?';
        $parametros[] = $tipo;
    }

    $origem = 'builds' . ($condicoes === [] ? '' : ' WHERE ' . implode(' AND ', $condicoes));
    $resultado = consultarPaginado($pdo, 'nome, slug, tipo, descricao, armas, imagem', $origem, $parametros, 'id', 5);
}

$tituloPagina = ($build['nome'] ?? 'Builds') . ' | SoulArchive Wiki';
$audio = 'Ornstein & Smough - Motoi Sakuraba (youtube).mp3';
$heroImagem = 'img/builds-banner.jpg';
$heroTitulo = $build['nome'] ?? 'Builds';
$heroTexto = 'Descubra as melhores builds de Dark Souls, focadas em força, destreza, fé, magia e qualidade.';
$textoRodape = 'Página de Builds.';

require __DIR__ . '/includes/layout_topo.php';

?>

  <!-- MAIN -->
  <main class="main-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <!-- NAVEGAÇÃO -->
      <div class="sidebar-card">

        <h2>Tipos de Build</h2>

        <ul>

          <li>
            <a href="builds.php#resultados">
              Todas
            </a>
          </li>

          <?php foreach (TIPOS_BUILD as $opcao): ?>
            <li>
              <a href="builds.php?tipo=<?= e(urlencode($opcao)) ?>#resultados">
                <?= e($opcao) ?>
              </a>
            </li>
          <?php endforeach; ?>

        </ul>

      </div>

      <!-- INFO -->
      <div class="sidebar-card">

        <h2>Dica</h2>

        <p>
          Em Dark Souls,
          sua build define completamente
          o ritmo do combate,
          equipamentos ideais
          e estilo de gameplay.
        </p>

      </div>

    </aside>

    <!-- CONTENT -->
    <section class="content">

      <?php if ($build !== null): ?>

        <!-- DETALHE -->
        <article class="featured-card">

          <?php if ($build['imagem'] !== null): ?>
            <img src="<?= e(urlImagem($build['imagem'])) ?>" alt="<?= e($build['nome']) ?>">
          <?php endif; ?>

          <div class="featured-text">

            <h2><?= e($build['nome']) ?></h2>

            <p><?= textoHtml($build['descricao']) ?></p>

            <p class="etiqueta"><?= e($build['tipo']) ?></p>

          </div>

        </article>

        <?php foreach (['atributos' => 'Atributos', 'armas' => 'Armas e Magias Recomendadas', 'equipamentos' => 'Equipamentos'] as $campo => $rotulo): ?>
          <?php if ($build[$campo] !== null): ?>
            <article class="article-card">

              <h3><?= e($rotulo) ?></h3>

              <p><?= textoHtml($build[$campo]) ?></p>

            </article>
          <?php endif; ?>
        <?php endforeach; ?>

        <p><a href="builds.php" class="btn-secondary">Voltar para as builds</a></p>

      <?php else: ?>

        <!-- FEATURED -->
        <article class="featured-card">

          <img
            src="img/build_banner.jpeg"
            alt="Builds Dark Souls"
          >

          <div class="featured-text">

            <h2>Montando sua Build</h2>

            <p>
              Escolher corretamente seus atributos,
              armas e magias é essencial
              para sobreviver aos desafios de Lordran.
              Cada build oferece vantagens,
              fraquezas e estilos únicos.
            </p>

          </div>

        </article>

        <form method="get" action="builds.php#resultados" id="resultados" class="form-busca" role="search">
          <label for="q">Pesquisar builds</label>
          <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Nome, slug ou tipo">
          <?php if ($tipo !== ''): ?>
            <input type="hidden" name="tipo" value="<?= e($tipo) ?>">
          <?php endif; ?>
          <button type="submit" class="btn-primary">Pesquisar</button>
        </form>

        <?php if ($resultado['itens'] === []): ?>
          <p>Nenhuma build encontrada.</p>
        <?php endif; ?>

        <?php foreach ($resultado['itens'] as $item): ?>
          <section class="section-area">

            <div class="section-header">

              <h2><?= e($item['nome']) ?></h2>

              <a href="builds.php?slug=<?= e(urlencode($item['slug'])) ?>">
                Ver Detalhes
              </a>

            </div>

            <article class="article-card">

              <h3>Foco Principal</h3>

              <p><?= textoHtml($item['descricao']) ?></p>

              <?php if ($item['imagem'] !== null): ?>
                <img src="<?= e(urlImagem($item['imagem'])) ?>" alt="<?= e($item['nome']) ?>">
              <?php endif; ?>

            </article>

            <?php if ($item['armas'] !== null): ?>
              <article class="article-card">

                <h3>Armas e Magias Recomendadas</h3>

                <p><?= textoHtml($item['armas']) ?></p>

              </article>
            <?php endif; ?>

          </section>
        <?php endforeach; ?>

        <?php exibirPaginacao($resultado, 'resultados'); ?>

      <?php endif; ?>

    </section>

  </main>

<?php require __DIR__ . '/includes/layout_rodape.php'; ?>
