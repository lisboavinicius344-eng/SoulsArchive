<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');

$busca = textoGet('q');
$origem = 'regioes';
$parametros = [];

if ($busca !== '') {
    $origem .= ' WHERE nome LIKE ? OR titulo LIKE ?';
    $parametros = [termoLike($busca), termoLike($busca)];
}

$resultado = consultarPaginado(conectarBanco(), 'id, nome, titulo, imagem, ordem', $origem, $parametros, 'ordem, id', 10);

$tituloPagina = 'Regiões';
$secaoAtiva = 'regioes';
$subtituloPagina = 'Gerencie as regiões exibidas na página de Mapas.';
$migalhas = [['Regiões', 'admin/regioes/index.php']];
$acaoPrincipal = ['Cadastrar região', 'admin/regioes/formulario.php'];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

        <section class="cartao cartao-tabela">

          <div class="barra-tabela">

            <form method="get" class="form-pesquisa" role="search">
              <label for="q" class="somente-leitor">Pesquisar regiões</label>
              <span class="campo-pesquisa">
                <?= icone('busca') ?>
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Pesquisar por nome ou título">
              </span>
              <button type="submit" class="btn-contorno">Pesquisar</button>
            </form>

            <span class="contador"><?= (int) $resultado['total'] ?> registro(s)</span>

          </div>

          <?php if ($resultado['itens'] === []): ?>
            <p class="estado-vazio">Nenhuma região encontrada.</p>
          <?php else: ?>
            <div class="tabela-rolagem">
              <table class="tabela-admin">

                <thead>
                  <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Título</th>
                    <th>Ordem</th>
                    <th class="coluna-acoes">Ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($resultado['itens'] as $regiao): ?>
                    <tr>
                      <td>
                        <?php if ($regiao['imagem'] !== null): ?>
                          <img src="<?= e(urlImagem($regiao['imagem'])) ?>" alt="" class="miniatura">
                        <?php else: ?>
                          <span class="miniatura miniatura-vazia"><?= icone('mapa') ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="celula-nome"><?= e($regiao['nome']) ?></td>
                      <td><?= e($regiao['titulo']) ?></td>
                      <td data-rotulo="Ordem"><?= (int) $regiao['ordem'] ?></td>
                      <td class="celula-acoes">
                        <a href="<?= e(url('admin/regioes/formulario.php?id=' . (int) $regiao['id'])) ?>" class="btn-tabela">
                          <?= icone('editar') ?>
                          Editar
                        </a>

                        <form action="<?= e(url('admin/regioes/excluir.php')) ?>" method="post" data-confirmar="Excluir a região <?= e($regiao['nome']) ?>?" data-confirmar-detalhe="Esta ação não pode ser desfeita.">
                          <?= campoCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $regiao['id'] ?>">
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
