<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title><?= e($tituloPagina) ?></title>

  <link rel="stylesheet" href="<?= e(url('css/style.css')) ?>">
  <link rel="icon" href="<?= e(url('img/logos/favicon.png')) ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>
  <audio autoplay loop> <source src="<?= e(url('audio/' . $audio)) ?>" type="audio/mpeg"> </audio>
  <!-- HERO -->
  <header
    class="hero"
    style="
      background:
      linear-gradient(rgba(0,0,0,0.78), rgba(0,0,0,0.92)),
      url('<?= e(url($heroImagem)) ?>');
      background-size: cover;
      background-position: center;
    "
  >

    <div class="hero-overlay"></div>

    <div class="hero-content">

      <h1><?= e($heroTitulo) ?></h1>

      <p><?= e($heroTexto) ?></p>

      <?php if (!empty($heroBotoes)): ?>
        <div class="hero-buttons">

          <a href="<?= e(url('bosses.php')) ?>" class="btn-primary">Explorar Bosses</a>

          <a href="<?= e(url('lore.html')) ?>" class="btn-secondary">Ver Lore</a>

        </div>
      <?php endif; ?>

    </div>

  </header>

  <!-- NAVBAR -->
  <nav class="navbar">

    <div class="nav-container">

      <div class="logo">
        SoulArchive
      </div>

      <ul class="nav-links">

        <li><a href="<?= e(url('index.php')) ?>">Home</a></li>
        <li><a href="<?= e(url('bosses.php')) ?>">Bosses</a></li>
        <li><a href="<?= e(url('lore.html')) ?>">Lore</a></li>
        <li><a href="<?= e(url('builds.php')) ?>">Builds</a></li>
        <li><a href="<?= e(url('maps.html')) ?>">Mapas</a></li>
        <li><a href="<?= e(url('contatos.html')) ?>">Contato</a></li>
        <li><a href="<?= e(url('admin/index.php')) ?>" class="nav-painel">Painel</a></li>

      </ul>

    </div>

  </nav>
