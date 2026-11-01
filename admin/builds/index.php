<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');

$busca = textoGet('q');
$tipo = in_array(textoGet('tipo'), TIPOS_BUILD, true) ? textoGet('tipo') : '';
$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(b.nome LIKE ? OR b.slug LIKE ? OR b.tipo LIKE ?)';
    array_push($parametros, termoLike($busca), termoLike($busca), termoLike($busca));
}

if ($tipo !== '') {
    $condicoes[] = 'b.tipo = ?';
    $parametros[] = $tipo;
}

$origem = 'builds b LEFT JOIN usuarios u ON u.id = b.criado_por'
    . ($condicoes === [] ? '' : ' WHERE ' . implode(' AND ', $condicoes));

$resultado = consultarPaginado(
    conectarBanco(),
    'b.id, b.nome, b.slug, b.tipo, b.imagem, u.nome_usuario AS autor',
    $origem,
    $parametros,
    'b.nome, b.id',
    10
);

$tituloPagina = 'Builds';
$secaoAtiva = 'builds';
$subtituloPagina = 'Gerencie as configurações de personagem publicadas.';
$migalhas = [['Builds', 'admin/builds/index.php']];
$acaoPrincipal = ['Cadastrar build', 'admin/builds/formulario.php'];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

        <section class="cartao cartao-tabela">

          <div class="barra-tabela">

            <form method="get" class="form-pesquisa" role="search">
              <label for="q" class="somente-leitor">Pesquisar builds</label>
              <span class="campo-pesquisa">
                <?= icone('busca') ?>
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Pesquisar por nome, slug ou tipo">
              </span>

              <label for="tipo" class="somente-leitor">Filtrar por tipo</label>
              <select id="tipo" name="tipo">
                <option value="">Todos os tipos</option>
                <?php foreach (TIPOS_BUILD as $opcao): ?>
                  <option value="<?= e($opcao) ?>" <?= $opcao === $tipo ? 'selected' : '' ?>><?= e($opcao) ?></option>
                <?php endforeach; ?>
              </select>

              <button type="submit" class="btn-contorno">Pesquisar</button>
            </form>

            <span class="contador"><?= (int) $resultado['total'] ?> registro(s)</span>

          </div>

          <?php if ($resultado['itens'] === []): ?>
            <p class="estado-vazio">Nenhuma build encontrada.</p>
          <?php else: ?>
            <div class="tabela-rolagem">
              <table class="tabela-admin">

                <thead>
                  <tr>
                    <th>Imagem</th>
                    <th>Nome</th>
                    <th>Slug</th>
                    <th>Tipo</th>
                    <th>Criada por</th>
                    <th class="coluna-acoes">Ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($resultado['itens'] as $build): ?>
                    <tr>
                      <td>
                        <?php if ($build['imagem'] !== null): ?>
                          <img src="<?= e(urlImagem($build['imagem'])) ?>" alt="" class="miniatura">
                        <?php else: ?>
                          <span class="miniatura miniatura-vazia"><?= icone('builds') ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="celula-nome"><?= e($build['nome']) ?></td>
                      <td><code><?= e($build['slug']) ?></code></td>
                      <td><span class="selo selo-ouro"><?= e($build['tipo']) ?></span></td>
                      <td><?= e($build['autor'] ?? '—') ?></td>
                      <td class="celula-acoes">
                        <a href="<?= e(url('admin/builds/formulario.php?id=' . (int) $build['id'])) ?>" class="btn-tabela">
                          <?= icone('editar') ?>
                          Editar
                        </a>

                        <form action="<?= e(url('admin/builds/excluir.php')) ?>" method="post" data-confirmar="Excluir a build <?= e($build['nome']) ?>?" data-confirmar-detalhe="Esta ação não pode ser desfeita.">
                          <?= campoCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $build['id'] ?>">
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
