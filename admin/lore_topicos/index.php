<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');

$busca = textoGet('q');
$origem = 'lore_topicos t';
$parametros = [];

if ($busca !== '') {
    $origem .= ' WHERE t.titulo LIKE ?';
    $parametros = [termoLike($busca)];
}

$resultado = consultarPaginado(
    conectarBanco(),
    't.id, t.titulo, t.ordem, (SELECT COUNT(*) FROM lore_artigos a WHERE a.topico_id = t.id) AS total_artigos',
    $origem,
    $parametros,
    't.ordem, t.id',
    10
);

$tituloPagina = 'Tópicos de Lore';
$secaoAtiva = 'lore_topicos';
$subtituloPagina = 'Organize as seções exibidas na página de Lore.';
$migalhas = [['Tópicos de Lore', 'admin/lore_topicos/index.php']];
$acaoPrincipal = ['Cadastrar tópico', 'admin/lore_topicos/formulario.php'];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

        <section class="cartao cartao-tabela">

          <div class="barra-tabela">

            <form method="get" class="form-pesquisa" role="search">
              <label for="q" class="somente-leitor">Pesquisar tópicos</label>
              <span class="campo-pesquisa">
                <?= icone('busca') ?>
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Pesquisar por título">
              </span>
              <button type="submit" class="btn-contorno">Pesquisar</button>
            </form>

            <span class="contador"><?= (int) $resultado['total'] ?> registro(s)</span>

          </div>

          <?php if ($resultado['itens'] === []): ?>
            <p class="estado-vazio">Nenhum tópico encontrado.</p>
          <?php else: ?>
            <div class="tabela-rolagem">
              <table class="tabela-admin">

                <thead>
                  <tr>
                    <th>Ordem</th>
                    <th>Título</th>
                    <th>Artigos</th>
                    <th class="coluna-acoes">Ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($resultado['itens'] as $topico): ?>
                    <tr>
                      <td data-rotulo="Ordem"><?= (int) $topico['ordem'] ?></td>
                      <td class="celula-nome"><?= e($topico['titulo']) ?></td>
                      <td data-rotulo="Artigos"><span class="selo selo-neutro"><?= (int) $topico['total_artigos'] ?></span></td>
                      <td class="celula-acoes">
                        <a href="<?= e(url('admin/lore_topicos/formulario.php?id=' . (int) $topico['id'])) ?>" class="btn-tabela">
                          <?= icone('editar') ?>
                          Editar
                        </a>

                        <form action="<?= e(url('admin/lore_topicos/excluir.php')) ?>" method="post" data-confirmar="Excluir o tópico <?= e($topico['titulo']) ?>?" data-confirmar-detalhe="Esta ação não pode ser desfeita. Tópicos com artigos não podem ser excluídos.">
                          <?= campoCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $topico['id'] ?>">
                          <button type="submit" class="btn-tabela btn-perigo">
                            <?= icone('excluir') ?>
                            Excluir
                          </button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>

              </table>
            </div>
          <?php endif; ?>

        </section>

        <?php exibirPaginacao($resultado); ?>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
