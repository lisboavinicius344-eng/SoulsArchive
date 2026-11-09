<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');
$pdo = conectarBanco();

$topicos = $pdo->query('SELECT id, titulo FROM lore_topicos ORDER BY ordem, id')->fetchAll();

$busca = textoGet('q');
$topicoFiltro = idValido($_GET['topico'] ?? null);
$condicoes = [];
$parametros = [];

if ($busca !== '') {
    $condicoes[] = '(a.titulo LIKE ? OR t.titulo LIKE ?)';
    array_push($parametros, termoLike($busca), termoLike($busca));
}

if ($topicoFiltro !== null) {
    $condicoes[] = 'a.topico_id = ?';
    $parametros[] = $topicoFiltro;
}

$origem = 'lore_artigos a
    INNER JOIN lore_topicos t ON t.id = a.topico_id
    LEFT JOIN usuarios u ON u.id = a.criado_por'
    . ($condicoes === [] ? '' : ' WHERE ' . implode(' AND ', $condicoes));

$resultado = consultarPaginado(
    $pdo,
    'a.id, a.titulo, a.ordem, a.imagem, t.titulo AS topico, u.nome_usuario AS autor',
    $origem,
    $parametros,
    't.ordem, t.id, a.ordem, a.id',
    10
);

$tituloPagina = 'Artigos de Lore';
$secaoAtiva = 'lore_artigos';
$subtituloPagina = 'Gerencie os textos publicados na página de Lore.';
$migalhas = [['Artigos de Lore', 'admin/lore_artigos/index.php']];
$acaoPrincipal = ['Cadastrar artigo', 'admin/lore_artigos/formulario.php'];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

        <section class="cartao cartao-tabela">

          <div class="barra-tabela">

            <form method="get" class="form-pesquisa" role="search">
              <label for="q" class="somente-leitor">Pesquisar artigos</label>
              <span class="campo-pesquisa">
                <?= icone('busca') ?>
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Pesquisar por título ou tópico">
              </span>

              <label for="topico" class="somente-leitor">Filtrar por tópico</label>
              <select id="topico" name="topico">
                <option value="">Todos os tópicos</option>
                <?php foreach ($topicos as $opcao): ?>
                  <option value="<?= (int) $opcao['id'] ?>" <?= (int) $opcao['id'] === $topicoFiltro ? 'selected' : '' ?>><?= e($opcao['titulo']) ?></option>
                <?php endforeach; ?>
              </select>

              <button type="submit" class="btn-contorno">Pesquisar</button>
            </form>

            <span class="contador"><?= (int) $resultado['total'] ?> registro(s)</span>

          </div>

          <?php if ($resultado['itens'] === []): ?>
            <p class="estado-vazio">Nenhum artigo encontrado.</p>
          <?php else: ?>
            <div class="tabela-rolagem">
              <table class="tabela-admin">

                <thead>
                  <tr>
                    <th>Imagem</th>
                    <th>Título</th>
                    <th>Tópico</th>
                    <th>Ordem</th>
                    <th>Criado por</th>
                    <th class="coluna-acoes">Ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($resultado['itens'] as $artigo): ?>
                    <tr>
                      <td>
                        <?php if ($artigo['imagem'] !== null): ?>
                          <img src="<?= e(urlImagem($artigo['imagem'])) ?>" alt="" class="miniatura">
                        <?php else: ?>
                          <span class="miniatura miniatura-vazia"><?= icone('livro') ?></span>
                        <?php endif; ?>
                      </td>
                      <td class="celula-nome"><?= e($artigo['titulo']) ?></td>
                      <td><span class="selo selo-ouro"><?= e($artigo['topico']) ?></span></td>
                      <td data-rotulo="Ordem"><?= (int) $artigo['ordem'] ?></td>
                      <td data-rotulo="Criado por"><?= e($artigo['autor'] ?? '—') ?></td>
                      <td class="celula-acoes">
                        <a href="<?= e(url('admin/lore_artigos/formulario.php?id=' . (int) $artigo['id'])) ?>" class="btn-tabela">
                          <?= icone('editar') ?>
                          Editar
                        </a>

                        <form action="<?= e(url('admin/lore_artigos/excluir.php')) ?>" method="post" data-confirmar="Excluir o artigo <?= e($artigo['titulo']) ?>?" data-confirmar-detalhe="Esta ação não pode ser desfeita.">
                          <?= campoCsrf() ?>
                          <input type="hidden" name="id" value="<?= (int) $artigo['id'] ?>">
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
