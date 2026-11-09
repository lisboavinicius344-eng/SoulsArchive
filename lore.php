<?php

require_once __DIR__ . '/includes/funcoes.php';

$pdo = conectarBanco();

$resultado = consultarPaginado($pdo, 'id, titulo', 'lore_topicos', [], 'ordem, id', 10);

// Agrupa os artigos da página atual por tópico; tópicos ainda sem artigos não são exibidos.
$secoes = [];

if ($resultado['itens'] !== []) {
    $ids = array_column($resultado['itens'], 'id');
    $marcadores = implode(', ', array_fill(0, count($ids), '?'));

    $consulta = $pdo->prepare("SELECT topico_id, titulo, conteudo, imagem FROM lore_artigos WHERE topico_id IN ($marcadores) ORDER BY ordem, id");
    $consulta->execute($ids);

    $artigos = [];
    foreach ($consulta as $artigo) {
        $artigos[$artigo['topico_id']][] = $artigo;
    }

    foreach ($resultado['itens'] as $topico) {
        if (isset($artigos[$topico['id']])) {
            $secoes[] = ['id' => $topico['id'], 'titulo' => $topico['titulo'], 'artigos' => $artigos[$topico['id']]];
        }
    }
}

$tituloPagina = 'Lore | SoulArchive Wiki';
$audio = 'Dark Sun Gwyndolin - Motoi Sakuraba (youtube).mp3';
$heroImagem = 'img/lore-banner.jpg';
$heroTitulo = 'Lore de Dark Souls';
$heroTexto = 'Descubra os mistérios da Primeira Chama, dos Lord Souls e do eterno ciclo entre fogo e escuridão em Lordran.';
$textoRodape = 'Página de Lore.';

require __DIR__ . '/includes/layout_topo.php';

?>

  <!-- MAIN -->
  <main class="main-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <!-- TÓPICOS -->
      <div class="sidebar-card">

        <h2>Tópicos</h2>

        <ul>

          <?php foreach ($secoes as $secao): ?>
            <li>
              <a href="#topico-<?= (int) $secao['id'] ?>">
                <?= e($secao['titulo']) ?>
              </a>
            </li>
          <?php endforeach; ?>

        </ul>

      </div>

      <!-- QUOTE -->
      <div class="sidebar-card">

        <h2>Citação</h2>

        <p>
          “In the Age of Ancients,
          the world was unformed,
          shrouded by fog.”
        </p>

      </div>

    </aside>

    <!-- CONTENT -->
    <section class="content">

      <!-- FEATURED -->
      <article class="featured-card">

        <img
          src="img/hero-bg.jpg.jpg"
          alt="Lore Dark Souls"
        >

        <div class="featured-text">

          <h2>A História de Lordran</h2>

          <p>
            Dark Souls apresenta uma narrativa fragmentada,
            construída através de diálogos,
            descrições de itens,
            ambientes e simbolismos espalhados pelo mundo.
            O jogador reconstrói a história
            explorando as ruínas de Lordran.
          </p>

        </div>

      </article>

      <?php if ($secoes === []): ?>
        <p>Nenhum artigo publicado.</p>
      <?php endif; ?>

      <?php foreach ($secoes as $secao): ?>
        <section class="section-area" id="topico-<?= (int) $secao['id'] ?>">

          <div class="section-header">

            <h2><?= e($secao['titulo']) ?></h2>

          </div>

          <?php if (count($secao['artigos']) > 1): ?>
            <div class="card-grid">

              <?php foreach ($secao['artigos'] as $artigo): ?>
                <article class="boss-card">

                  <?php if ($artigo['imagem'] !== null): ?>
                    <img src="<?= e(urlImagem($artigo['imagem'])) ?>" alt="<?= e($artigo['titulo']) ?>">
                  <?php endif; ?>

                  <div class="boss-info">

                    <h3><?= e($artigo['titulo']) ?></h3>

                    <p><?= textoHtml($artigo['conteudo']) ?></p>

                  </div>

                </article>
              <?php endforeach; ?>

            </div>
          <?php else: ?>
            <article class="article-card">

              <h3><?= e($secao['artigos'][0]['titulo']) ?></h3>

              <p><?= textoHtml($secao['artigos'][0]['conteudo']) ?></p>

              <?php if ($secao['artigos'][0]['imagem'] !== null): ?>
                <img src="<?= e(urlImagem($secao['artigos'][0]['imagem'])) ?>" alt="<?= e($secao['artigos'][0]['titulo']) ?>">
              <?php endif; ?>

            </article>
          <?php endif; ?>

        </section>
      <?php endforeach; ?>

      <?php exibirPaginacao($resultado); ?>

    </section>

  </main>

<?php require __DIR__ . '/includes/layout_rodape.php'; ?>
