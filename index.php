<?php

require_once __DIR__ . '/includes/funcoes.php';

$destaques = conectarBanco()
    ->query('SELECT id, nome, descricao, dificuldade, imagem FROM bosses WHERE destaque = 1 ORDER BY id LIMIT 3')
    ->fetchAll();

$tituloPagina = 'SoulArchive Wiki';
$audio = 'firelink.mp3';
$heroImagem = 'img/hero-bg.jpg.jpg';
$heroTitulo = 'SoulArchive Wiki';
$heroTexto = 'Explore o universo de Dark Souls Remastered em uma enciclopédia inspirada no estilo Fextralife. Descubra bosses, lore, builds, armas, NPCs e os mistérios ocultos de Lordran.';
$heroBotoes = true;
$textoRodape = 'Inspirado em Dark Souls Wiki Fextralife.';

require __DIR__ . '/includes/layout_topo.php';

?>

  <!-- MAIN -->
  <main class="main-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <!-- CATEGORIAS -->
      <div class="sidebar-card">

        <h2>Categorias</h2>

        <ul>

          <li>
            <a href="bosses.php">
              Bosses
            </a>
          </li>

          <li>
            <a href="lore.html">
              Lore
            </a>
          </li>

          <li>
            <a href="builds.php">
              Builds
            </a>
          </li>

          <li>
            <a href="maps.html">
              Mapas
            </a>
          </li>

        </ul>

      </div>

      <!-- DESTAQUE -->
      <div class="sidebar-card">

        <h2>Destaque</h2>

        <img
          src="img/boss-artorias.jpg"
          alt="Knight Artorias"
        >

        <p>
          Knight Artorias permanece como um dos
          personagens mais marcantes da franquia,
          sendo consumido pelo próprio Abismo
          que tentou derrotar.
        </p>

      </div>

    </aside>

    <!-- CONTENT -->
    <section class="content">

      <!-- FEATURED -->
      <article class="featured-card">

        <img
          src="img/featured-darksouls.jpg.jpg"
          alt="Dark Souls Remastered"
        >

        <div class="featured-text">

          <h2>Dark Souls Remastered</h2>

          <p>
            Dark Souls redefiniu os RPGs de ação
            com seu combate estratégico,
            exploração interconectada
            e narrativa fragmentada.
            Cada área de Lordran esconde
            histórias, tragédias e segredos.
          </p>

        </div>

      </article>

      <!-- BOSSES -->
      <section class="section-area">

        <div class="section-header">

          <h2>Bosses em Destaque</h2>

          <a href="bosses.php">
            Ver Todos
          </a>

        </div>

        <?php if ($destaques === []): ?>
          <p>Nenhum boss em destaque no momento.</p>
        <?php else: ?>
          <div class="card-grid">

            <?php foreach ($destaques as $boss): ?>
              <article class="boss-card">

                <a href="bosses.php?id=<?= (int) $boss['id'] ?>">

                  <?php if ($boss['imagem'] !== null): ?>
                    <img src="<?= e(urlImagem($boss['imagem'])) ?>" alt="<?= e($boss['nome']) ?>">
                  <?php endif; ?>

                  <div class="boss-info">

                    <h3><?= e($boss['nome']) ?></h3>

                    <p><?= textoHtml($boss['descricao']) ?></p>

                    <span><?= e($boss['dificuldade']) ?></span>

                  </div>

                </a>

              </article>
            <?php endforeach; ?>

          </div>
        <?php endif; ?>

      </section>

      <!-- ARTICLES -->
      <section class="section-area">

        <div class="section-header">

          <h2>Últimos Artigos</h2>

        </div>

        <!-- ARTICLE -->
        <article class="article-card">

          <h3>Guia Completo de Build Faith</h3>

          <p>
            Descubra os melhores milagres,
            armas e talismãs para builds
            focadas em fé e suporte híbrido.
          </p>

        </article>

        <!-- ARTICLE -->
        <article class="article-card">

          <h3>Lore da Primeira Chama</h3>

          <p>
            Entenda o surgimento da Era do Fogo,
            dos Lord Souls e do eterno ciclo
            entre luz e escuridão.
          </p>

        </article>

        <!-- ARTICLE -->
        <article class="article-card">

          <h3>As regiões mais perigosas de Lordran</h3>

          <p>
            Explore locais brutais como
            New Londo Ruins,
            Tomb of the Giants
            e Lost Izalith.
          </p>

        </article>

      </section>

    </section>

  </main>

<?php require __DIR__ . '/includes/layout_rodape.php'; ?>
