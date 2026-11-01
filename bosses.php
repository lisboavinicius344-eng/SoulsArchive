<?php

require_once __DIR__ . '/includes/funcoes.php';

$pdo = conectarBanco();

// Registro individual (?id=) ou listagem com pesquisa e paginação.
$boss = null;

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT nome, descricao, dificuldade, imagem FROM bosses WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $boss = $consulta->fetch();

    if ($boss === false) {
        paginaErro(404, 'O boss solicitado não foi encontrado.');
    }
}

$principais = $pdo
    ->query('SELECT id, nome FROM bosses WHERE destaque = 1 ORDER BY id LIMIT 8')
    ->fetchAll();

$busca = textoGet('q');

if ($boss === null) {
    $origem = 'bosses';
    $parametros = [];

    if ($busca !== '') {
        $origem .= ' WHERE nome LIKE ? OR dificuldade LIKE ?';
        $parametros = [termoLike($busca), termoLike($busca)];
    }

    $resultado = consultarPaginado($pdo, 'id, nome, descricao, dificuldade, imagem', $origem, $parametros, 'id', 6);
}

$tituloPagina = ($boss['nome'] ?? 'Bosses') . ' | SoulArchive Wiki';
$audio = 'Soul of Cinder - Yuki Kitamura (youtube).mp3';
$heroImagem = 'img/backgrounds/dark-souls-warrior-uhdpaper.com-4K-6.1611.jpg';
$heroTitulo = $boss['nome'] ?? 'Bosses de Dark Souls';
$heroTexto = 'Conheça os inimigos mais icônicos, brutais e memoráveis de Lordran. Cada boss representa uma peça essencial da lore e do desafio de Dark Souls.';
$textoRodape = 'Página de Bosses.';

require __DIR__ . '/includes/layout_topo.php';

?>

  <!-- MAIN -->
  <main class="main-container">

    <!-- SIDEBAR -->
    <aside class="sidebar">

      <!-- NAVEGAÇÃO -->
      <div class="sidebar-card">

        <h2>Bosses Principais</h2>

        <ul>

          <?php foreach ($principais as $principal): ?>
            <li>
              <a href="bosses.php?id=<?= (int) $principal['id'] ?>">
                <?= e($principal['nome']) ?>
              </a>
            </li>
          <?php endforeach; ?>

        </ul>

      </div>

      <!-- INFO -->
      <div class="sidebar-card">

        <h2>Sobre os Bosses</h2>

        <p>
          Em Dark Souls,
          os bosses não são apenas obstáculos.
          Cada um carrega uma história trágica,
          conectada diretamente ao ciclo do fogo
          e da decadência de Lordran.
        </p>

      </div>

    </aside>

    <!-- CONTENT -->
    <section class="content">

      <?php if ($boss !== null): ?>

        <!-- DETALHE -->
        <article class="featured-card">

          <?php if ($boss['imagem'] !== null): ?>
            <img src="<?= e(urlImagem($boss['imagem'])) ?>" alt="<?= e($boss['nome']) ?>">
          <?php endif; ?>

          <div class="featured-text">

            <h2><?= e($boss['nome']) ?></h2>

            <p><?= textoHtml($boss['descricao']) ?></p>

            <p class="etiqueta"><?= e($boss['dificuldade']) ?></p>

          </div>

        </article>

        <p><a href="bosses.php" class="btn-secondary">Voltar para a galeria</a></p>

      <?php else: ?>

        <!-- FEATURED -->
        <article class="featured-card">

          <img
            src="img/boss-ornstein-smough.jpg"
            alt="Ornstein e Smough"
          >

          <div class="featured-text">

            <h2>Ornstein & Smough</h2>

            <p>
              Considerada uma das batalhas mais famosas
              da história dos videogames,
              Ornstein e Smough representam
              o ápice da dificuldade em Anor Londo,
              exigindo estratégia,
              posicionamento e controle absoluto.
            </p>

          </div>

        </article>

        <!-- BOSSES GRID -->
        <section class="section-area" id="resultados">

          <div class="section-header">

            <h2>Galeria de Bosses</h2>

          </div>

          <form method="get" action="bosses.php#resultados" class="form-busca" role="search">
            <label for="q">Pesquisar bosses</label>
            <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Nome ou dificuldade">
            <button type="submit" class="btn-primary">Pesquisar</button>
          </form>

          <?php if ($resultado['itens'] === []): ?>
            <p>Nenhum boss encontrado.</p>
          <?php else: ?>
            <div class="card-grid">

              <?php foreach ($resultado['itens'] as $item): ?>
                <article class="boss-card">

                  <a href="bosses.php?id=<?= (int) $item['id'] ?>">

                    <?php if ($item['imagem'] !== null): ?>
                      <img src="<?= e(urlImagem($item['imagem'])) ?>" alt="<?= e($item['nome']) ?>">
                    <?php endif; ?>

                    <div class="boss-info">

                      <h3><?= e($item['nome']) ?></h3>

                      <p><?= textoHtml($item['descricao']) ?></p>

                      <span><?= e($item['dificuldade']) ?></span>

                    </div>

                  </a>

                </article>
              <?php endforeach; ?>

            </div>
          <?php endif; ?>

          <?php exibirPaginacao($resultado, 'resultados'); ?>

        </section>

        <!-- EXTRA -->
        <section class="section-area">

          <div class="section-header">

            <h2>Curiosidades</h2>

          </div>

          <!-- ARTICLE -->
          <article class="article-card">

            <h3>Design Narrativo</h3>

            <p>
              Cada boss possui trilha sonora,
              arena e estilo de combate
              projetados para refletir
              sua história e personalidade.
            </p>

          </article>

          <!-- ARTICLE -->
          <article class="article-card">

            <h3>Dificuldade e Aprendizado</h3>

            <p>
              Dark Souls recompensa observação,
              paciência e adaptação.
              A derrota faz parte do processo
              de aprendizado do jogador.
            </p>

          </article>

        </section>

      <?php endif; ?>

    </section>

  </main>

<?php require __DIR__ . '/includes/layout_rodape.php'; ?>
