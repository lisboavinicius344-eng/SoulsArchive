<?php

require_once __DIR__ . '/../includes/auth.php';

$usuario = exigirLogin();
$ehAdministrador = $usuario['perfil'] === 'administrador';

$pdo = conectarBanco();

$totalBuilds = (int) $pdo->query('SELECT COUNT(*) FROM builds')->fetchColumn();

$indicadores = [
    ['Chefes', (int) $pdo->query('SELECT COUNT(*) FROM bosses')->fetchColumn(), 'Registros publicados', 'chefes', 'admin/bosses/index.php'],
    ['Builds', $totalBuilds, 'Registros publicados', 'builds', 'admin/builds/index.php'],
    ['Destaques', (int) $pdo->query('SELECT COUNT(*) FROM bosses WHERE destaque = 1')->fetchColumn(), 'Chefes em destaque', 'estrela', 'admin/bosses/index.php'],
];

$atalhos = [
    ['Cadastrar chefe', 'Adicione um novo chefe à galeria', 'chefes', 'admin/bosses/formulario.php'],
    ['Cadastrar build', 'Publique uma nova configuração', 'builds', 'admin/builds/formulario.php'],
];

if ($ehAdministrador) {
    $indicadores[] = ['Usuários ativos', (int) $pdo->query('SELECT COUNT(*) FROM usuarios WHERE ativo = 1')->fetchColumn(), 'Contas com acesso', 'usuarios', 'admin/usuarios/index.php'];
    $atalhos[] = ['Cadastrar usuário', 'Conceda acesso a este painel', 'usuarios', 'admin/usuarios/formulario.php'];
}

// Todos os tipos entram na distribuição, inclusive os que ainda não têm builds.
$buildsPorTipo = array_fill_keys(TIPOS_BUILD, 0);
foreach ($pdo->query('SELECT tipo, COUNT(*) AS total FROM builds GROUP BY tipo') as $linha) {
    $buildsPorTipo[$linha['tipo']] = (int) $linha['total'];
}
arsort($buildsPorTipo);
$maiorTotal = max(1, max($buildsPorTipo));

$recentes = $pdo->query(
    "SELECT 'bosses' AS modulo, id, nome, atualizado_em FROM bosses
     UNION ALL
     SELECT 'builds' AS modulo, id, nome, atualizado_em FROM builds
     ORDER BY atualizado_em DESC, id DESC
     LIMIT 6"
)->fetchAll();

$tituloPagina = 'Início';
$secaoAtiva = 'inicio';
$subtituloPagina = 'Resumo do conteúdo publicado no SoulArchive.';

require __DIR__ . '/../includes/layout_admin_topo.php';

?>

        <section class="boas-vindas">
          <h2>Bem-vindo(a), <?= e($usuario['nome']) ?></h2>
          <p>
            Você está conectado(a) com o perfil
            <strong><?= e(PERFIS_USUARIO[$usuario['perfil']]) ?></strong>.
            May the flames guide thee.
          </p>
        </section>

        <?php if (!$ehAdministrador): ?>
          <p class="alerta alerta-info">
            O gerenciamento de usuários é restrito ao perfil administrador.
          </p>
        <?php endif; ?>

        <!-- INDICADORES -->
        <section class="grade-indicadores" aria-label="Indicadores">
          <?php foreach ($indicadores as [$rotulo, $total, $legenda, $iconeItem, $caminho]): ?>
            <a href="<?= e(url($caminho)) ?>" class="indicador">
              <span class="indicador-icone"><?= icone($iconeItem) ?></span>
              <span class="indicador-rotulo"><?= e($rotulo) ?></span>
              <strong class="indicador-valor"><?= $total ?></strong>
              <small><?= e($legenda) ?></small>
            </a>
          <?php endforeach; ?>
        </section>

        <!-- ATALHOS -->
        <section class="cartao">

          <h2 class="cartao-titulo">Ações rápidas</h2>

          <div class="grade-atalhos">
            <?php foreach ($atalhos as [$rotulo, $legenda, $iconeItem, $caminho]): ?>
              <a href="<?= e(url($caminho)) ?>" class="atalho">
                <span class="indicador-icone"><?= icone($iconeItem) ?></span>
                <span>
                  <strong><?= e($rotulo) ?></strong>
                  <small><?= e($legenda) ?></small>
                </span>
              </a>
            <?php endforeach; ?>
          </div>

        </section>

        <div class="grade-dupla">

          <!-- DISTRIBUIÇÃO -->
          <section class="cartao">

            <h2 class="cartao-titulo">Builds por tipo</h2>

            <?php if ($totalBuilds === 0): ?>
              <p class="estado-vazio">Nenhuma build cadastrada.</p>
            <?php else: ?>
              <ul class="grafico-barras">
                <?php foreach ($buildsPorTipo as $tipoBuild => $quantidade): ?>
                  <li>
                    <a
                      href="<?= e(url('admin/builds/index.php?tipo=' . urlencode($tipoBuild))) ?>"
                      title="<?= e($tipoBuild) ?>: <?= $quantidade ?> de <?= $totalBuilds ?> builds"
                    >
                      <span class="grafico-rotulo"><?= e($tipoBuild) ?></span>
                      <span class="grafico-trilha">
                        <?php if ($quantidade > 0): ?>
                          <span class="grafico-barra" style="width: <?= round($quantidade / $maiorTotal * 100, 1) ?>%"></span>
                        <?php endif; ?>
                      </span>
                      <span class="grafico-valor"><?= $quantidade ?></span>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

          </section>

          <!-- ATUALIZAÇÕES -->
          <section class="cartao">

            <h2 class="cartao-titulo">Atualizados recentemente</h2>

            <?php if ($recentes === []): ?>
              <p class="estado-vazio">Nenhum registro cadastrado.</p>
            <?php else: ?>
              <ul class="lista-recentes">
                <?php foreach ($recentes as $recente): ?>
                  <li>
                    <a href="<?= e(url('admin/' . $recente['modulo'] . '/formulario.php?id=' . (int) $recente['id'])) ?>">
                      <span class="selo <?= $recente['modulo'] === 'bosses' ? 'selo-ouro' : 'selo-neutro' ?>">
                        <?= $recente['modulo'] === 'bosses' ? 'Chefe' : 'Build' ?>
                      </span>
                      <strong><?= e($recente['nome']) ?></strong>
                      <time datetime="<?= e(date('c', strtotime($recente['atualizado_em']))) ?>">
                        <?= e(date('d/m/Y H:i', strtotime($recente['atualizado_em']))) ?>
                      </time>
                    </a>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>

          </section>

        </div>

<?php require __DIR__ . '/../includes/layout_admin_rodape.php'; ?>
