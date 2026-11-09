<?php

require_once __DIR__ . '/includes/funcoes.php';

$resultado = consultarPaginado(conectarBanco(), 'id, nome, titulo, descricao, imagem', 'regioes', [], 'ordem, id', 10);

$tituloPagina = 'Mapas | SoulArchive Wiki';
$audio = 'Dark Fantasy Tiktok Song (slowed + reverb) Yamaha - Dorian concept - ANTI (youtube).mp3';
$heroImagem = 'img/back-lordran.jpg';
$heroTitulo = 'Mapas de Lordran';
$heroTexto = 'Explore as regiões mais perigosas, misteriosas e memoráveis de Dark Souls Remastered.';
$textoRodape = 'Página de Mapas.';

require __DIR__ . '/includes/layout_topo.php';

?>

  <!-- MAIN -->
  <main class="main-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <!-- REGIÕES -->
      <div class="sidebar-card">

        <h2>Regiões</h2>

        <ul>

          <?php foreach ($resultado['itens'] as $regiao): ?>
            <li>
              <a href="#regiao-<?= (int) $regiao['id'] ?>">
                <?= e($regiao['nome']) ?>
              </a>
            </li>
          <?php endforeach; ?>

        </ul>

      </div>

      <!-- INFO -->
      <div class="sidebar-card">

        <h2>Exploração</h2>

        <p>
          O mundo de Dark Souls possui
          um design interconectado,
          permitindo múltiplos caminhos
          e atalhos entre regiões.
        </p>

      </div>

    </aside>

    <!-- CONTENT -->
    <section class="content">

      <!-- FEATURED -->
      <article class="featured-card">

        <img
          src="img/back-mundo.png"
          alt="Lordran"
        >

        <div class="featured-text">

          <h2>O Mundo Interconectado</h2>

          <p>
            Lordran é reconhecida
            por seu level design inteligente,
            conectando áreas de forma orgânica
            através de elevadores,
            atalhos e passagens ocultas.
          </p>

        </div>

      </article>

      <?php if ($resultado['itens'] === []): ?>
        <p>Nenhuma região publicada.</p>
      <?php endif; ?>

      <?php foreach ($resultado['itens'] as $regiao): ?>
        <section class="section-area" id="regiao-<?= (int) $regiao['id'] ?>">

          <div class="section-header">

            <h2><?= e($regiao['nome']) ?></h2>

          </div>

          <article class="article-card">

            <h3><?= e($regiao['titulo']) ?></h3>

            <p><?= textoHtml($regiao['descricao']) ?></p>

            <?php if ($regiao['imagem'] !== null): ?>
              <img src="<?= e(urlImagem($regiao['imagem'])) ?>" alt="<?= e($regiao['nome']) ?>">
            <?php endif; ?>

          </article>

        </section>
      <?php endforeach; ?>

      <?php exibirPaginacao($resultado); ?>

      <!-- EXTRA -->
      <section class="section-area">

        <div class="section-header">

          <h2>Curiosidades</h2>

        </div>

        <article class="article-card">

          <h3>Interconectividade</h3>

          <p>
            O mapa de Dark Souls é considerado
            um dos melhores level designs
            da história dos videogames,
            graças à sensação contínua
            de descoberta e conexão.
          </p>

        </article>

        <article class="article-card">

          <h3>Atmosfera</h3>

          <p>
            Cada região possui identidade própria,
            refletida em iluminação,
            trilha sonora,
            arquitetura e inimigos.
          </p>

        </article>

      </section>

    </section>

  </main>

<?php require __DIR__ . '/includes/layout_rodape.php'; ?>
