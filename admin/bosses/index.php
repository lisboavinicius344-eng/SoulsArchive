<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');

$busca = textoGet('q');
$origem = 'bosses';
$parametros = [];

if ($busca !== '') {
    $origem .= ' WHERE nome LIKE ? OR dificuldade LIKE ?';
    $parametros = [termoLike($busca), termoLike($busca)];
}

$resultado = consultarPaginado(conectarBanco(), 'id, nome, dificuldade, imagem, destaque', $origem, $parametros, 'nome, id', 10);

$tituloPagina = 'Chefes';
$secaoAtiva = 'bosses';
$subtituloPagina = 'Gerencie os chefes exibidos na área pública.';
$migalhas = [['Chefes', 'admin/bosses/index.php']];
$acaoPrincipal = ['Cadastrar chefe', 'admin/bosses/formulario.php'];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

        <section class="cartao cartao-tabela">

          <div class="barra-tabela">

            <form method="get" class="form-pesquisa" role="search">
              <label for="q" class="somente-leitor">Pesquisar chefes</label>
              <span class="campo-pesquisa">
                <?= icone('busca') ?>
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Pesquisar por nome ou dificuldade">
              </span>
              <button type="submit" class="btn-contorno">Pesquisar</button>
            </form>

            <span class="contador"><?= (int) $resultado['total'] ?> registro(s)</span>

          </div>

          <?php if ($resultado['itens'] === []): ?>
            <p class="estado-vazio">Nenhum chefe encontrado.</p>
          <?php else: ?>
            <div class="tabela-rolagem">
              <table class="tabela-admin">

                <thead>
                  <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Dificuldade</th>
                    <th>Destaque</th>
                    <th class="coluna-acoes">Ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($resultado['itens'] as $boss): ?>
                    <tr>
                      <td>
                        <?php if ($boss['imagem'] !== null): ?>
                          <img src="<?= e(urlImagem($boss['imagem'])) ?>" alt="" class="miniatura">
                        <?php else: ?>
                          <span class="miniatura miniatura-vazia"><?= icone('chefes') ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="celula-nome"><?= e($boss['nome']) ?></td>
                      <td><?= e($boss['dificuldade']) ?></td>
                      <td data-rotulo="Destaque">
                        <span class="selo <?= (int) $boss['destaque'] === 1 ? 'selo-ouro' : 'selo-neutro' ?>">
                          <?= (int) $boss['destaque'] === 1 ? 'Sim' : 'Não' ?>
                        </span>
                      </td>
                      <td class="celula-acoes">
                        <a href="<?= e(url('admin/bosses/formulario.php?id=' . (int) $boss['id'])) ?>" class="btn-tabela">
                          <?= icone('editar') ?>
                          Editar
                        </a>

                        <form action="<?= e(url('admin/bosses/excluir.php')) ?>" method="post" data-confirmar="Excluir o chefe <?= e($boss['nome']) ?>?" data-confirmar-detalhe="Esta ação não pode ser desfeita.">
                          <?= campoCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $boss['id'] ?>">
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
