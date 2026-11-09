<?php

// Cadastro (sem ?id=) e edição (com ?id=) de artigos de lore.

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');
$pdo = conectarBanco();

$topicos = $pdo->query('SELECT id, titulo FROM lore_topicos ORDER BY ordem, id')->fetchAll();

$id = null;
$imagemAnterior = null;
$artigo = ['topico_id' => '', 'titulo' => '', 'conteudo' => '', 'imagem' => '', 'ordem' => 0];

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT topico_id, titulo, conteudo, imagem, ordem FROM lore_artigos WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $artigo = $consulta->fetch();

    if ($artigo === false) {
        paginaErro(404, 'O artigo solicitado não foi encontrado.');
    }

    $imagemAnterior = $artigo['imagem'];
}

$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $artigo = [
        'topico_id' => textoPost('topico_id'),
        'titulo' => textoPost('titulo'),
        'conteudo' => textoPost('conteudo'),
        'ordem' => textoPost('ordem'),
    ];

    if (!in_array((int) $artigo['topico_id'], array_map('intval', array_column($topicos, 'id')), true)) {
        $erros[] = 'Selecione um tópico válido.';
    }

    if ($artigo['titulo'] === '' || mb_strlen($artigo['titulo']) > 150) {
        $erros[] = 'Informe o título, com até 150 caracteres.';
    }

    if ($artigo['conteudo'] === '' || mb_strlen($artigo['conteudo']) > 5000) {
        $erros[] = 'Informe o conteúdo, com até 5000 caracteres.';
    }

    $ordem = validarOrdem($artigo['ordem'], $erros);

    [$artigo['imagem'], $arquivoImagem] = lerCampoImagem($erros);

    if ($erros === []) {
        if ($arquivoImagem !== null) {
            $artigo['imagem'] = salvarImagemEnviada($arquivoImagem);
        }

        $valores = [(int) $artigo['topico_id'], $artigo['titulo'], $artigo['conteudo'], $artigo['imagem'] ?: null, $ordem];

        if ($id === null) {
            $pdo->prepare('INSERT INTO lore_artigos (topico_id, titulo, conteudo, imagem, ordem, criado_por) VALUES (?, ?, ?, ?, ?, ?)')
                ->execute([...$valores, $usuario['id']]);
            definirMensagem('sucesso', 'Artigo cadastrado com sucesso.');
        } else {
            $pdo->prepare('UPDATE lore_artigos SET topico_id = ?, titulo = ?, conteudo = ?, imagem = ?, ordem = ? WHERE id = ?')
                ->execute([...$valores, $id]);
            definirMensagem('sucesso', 'Artigo atualizado com sucesso.');
        }

        removerImagemEnviada($pdo, $imagemAnterior);

        redirecionar('admin/lore_artigos/index.php');
    }
}

$tituloPagina = $id === null ? 'Cadastrar artigo' : 'Editar artigo';
$secaoAtiva = 'lore_artigos';
$migalhas = [['Artigos de Lore', 'admin/lore_artigos/index.php'], [$tituloPagina, null]];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

    <?php exibirErros($erros); ?>

    <?php if ($topicos === []): ?>
      <p class="alerta alerta-info">
        Cadastre um tópico de lore antes de criar artigos.
      </p>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="contact-form form-admin">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" value="<?= e($artigo['titulo']) ?>" maxlength="150" required>
      </div>

      <div class="form-group">
        <label for="topico_id">Tópico *</label>
        <select id="topico_id" name="topico_id" required>
          <option value="">Selecione</option>
          <?php foreach ($topicos as $opcao): ?>
            <option value="<?= (int) $opcao['id'] ?>" <?= (int) $opcao['id'] === (int) $artigo['topico_id'] ? 'selected' : '' ?>><?= e($opcao['titulo']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group campo-largo">
        <label for="conteudo">Conteúdo *</label>
        <textarea id="conteudo" name="conteudo" rows="7" maxlength="5000" required><?= e($artigo['conteudo']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="ordem">Ordem de exibição</label>
        <input type="number" id="ordem" name="ordem" value="<?= e((string) $artigo['ordem']) ?>" min="0" max="9999">
        <small>Números menores aparecem primeiro dentro do tópico.</small>
      </div>

      <?php $imagemAtual = (string) $artigo['imagem']; require __DIR__ . '/../../includes/campo_imagem.php'; ?>

      <div class="form-botoes">
        <button type="submit" class="btn-acao">Salvar</button>
        <a href="<?= e(url('admin/lore_artigos/index.php')) ?>" class="btn-contorno">Cancelar</a>
      </div>

    </form>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
