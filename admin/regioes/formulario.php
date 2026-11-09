<?php

// Cadastro (sem ?id=) e edição (com ?id=) de regiões do mapa.

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');
$pdo = conectarBanco();

$id = null;
$imagemAnterior = null;
$regiao = ['nome' => '', 'titulo' => '', 'descricao' => '', 'imagem' => '', 'ordem' => 0];

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT nome, titulo, descricao, imagem, ordem FROM regioes WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $regiao = $consulta->fetch();

    if ($regiao === false) {
        paginaErro(404, 'A região solicitada não foi encontrada.');
    }

    $imagemAnterior = $regiao['imagem'];
}

$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $regiao = [
        'nome' => textoPost('nome'),
        'titulo' => textoPost('titulo'),
        'descricao' => textoPost('descricao'),
        'ordem' => textoPost('ordem'),
    ];

    if ($regiao['nome'] === '' || mb_strlen($regiao['nome']) > 150) {
        $erros[] = 'Informe o nome, com até 150 caracteres.';
    }

    if ($regiao['titulo'] === '' || mb_strlen($regiao['titulo']) > 150) {
        $erros[] = 'Informe o título, com até 150 caracteres.';
    }

    if ($regiao['descricao'] === '' || mb_strlen($regiao['descricao']) > 5000) {
        $erros[] = 'Informe a descrição, com até 5000 caracteres.';
    }

    $ordem = validarOrdem($regiao['ordem'], $erros);

    [$regiao['imagem'], $arquivoImagem] = lerCampoImagem($erros);

    if ($erros === []) {
        if ($arquivoImagem !== null) {
            $regiao['imagem'] = salvarImagemEnviada($arquivoImagem);
        }

        $valores = [$regiao['nome'], $regiao['titulo'], $regiao['descricao'], $regiao['imagem'] ?: null, $ordem];

        if ($id === null) {
            $pdo->prepare('INSERT INTO regioes (nome, titulo, descricao, imagem, ordem) VALUES (?, ?, ?, ?, ?)')
                ->execute($valores);
            definirMensagem('sucesso', 'Região cadastrada com sucesso.');
        } else {
            $pdo->prepare('UPDATE regioes SET nome = ?, titulo = ?, descricao = ?, imagem = ?, ordem = ? WHERE id = ?')
                ->execute([...$valores, $id]);
            definirMensagem('sucesso', 'Região atualizada com sucesso.');
        }

        removerImagemEnviada($pdo, $imagemAnterior);

        redirecionar('admin/regioes/index.php');
    }
}

$tituloPagina = $id === null ? 'Cadastrar região' : 'Editar região';
$secaoAtiva = 'regioes';
$migalhas = [['Regiões', 'admin/regioes/index.php'], [$tituloPagina, null]];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

    <?php exibirErros($erros); ?>

    <form method="post" enctype="multipart/form-data" class="contact-form form-admin">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" value="<?= e($regiao['nome']) ?>" maxlength="150" required>
      </div>

      <div class="form-group">
        <label for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" value="<?= e($regiao['titulo']) ?>" maxlength="150" required>
      </div>

      <div class="form-group campo-largo">
        <label for="descricao">Descrição *</label>
        <textarea id="descricao" name="descricao" rows="6" maxlength="5000" required><?= e($regiao['descricao']) ?></textarea>
      </div>

      <div class="form-group">
        <label for="ordem">Ordem de exibição</label>
        <input type="number" id="ordem" name="ordem" value="<?= e((string) $regiao['ordem']) ?>" min="0" max="9999">
        <small>Números menores aparecem primeiro.</small>
      </div>

      <?php $imagemAtual = (string) $regiao['imagem']; require __DIR__ . '/../../includes/campo_imagem.php'; ?>

      <div class="form-botoes">
        <button type="submit" class="btn-acao">Salvar</button>
        <a href="<?= e(url('admin/regioes/index.php')) ?>" class="btn-contorno">Cancelar</a>
      </div>

    </form>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
