<?php

// Cadastro (sem ?id=) e edição (com ?id=) de builds.

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');
$pdo = conectarBanco();

$id = null;
$nomeAtual = null;
$slugAtual = null;
$imagemAnterior = null;
$build = ['nome' => '', 'tipo' => '', 'descricao' => '', 'atributos' => '', 'armas' => '', 'equipamentos' => '', 'imagem' => ''];

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT nome, slug, tipo, descricao, atributos, armas, equipamentos, imagem FROM builds WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $build = $consulta->fetch();

    if ($build === false) {
        paginaErro(404, 'A build solicitada não foi encontrada.');
    }

    $nomeAtual = $build['nome'];
    $slugAtual = $build['slug'];
    $imagemAnterior = $build['imagem'];
}

$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $build = [
        'nome' => textoPost('nome'),
        'tipo' => textoPost('tipo'),
        'descricao' => textoPost('descricao'),
        'atributos' => textoPost('atributos'),
        'armas' => textoPost('armas'),
        'equipamentos' => textoPost('equipamentos'),
    ];

    if ($build['nome'] === '' || mb_strlen($build['nome']) > 150) {
        $erros[] = 'Informe o nome, com até 150 caracteres.';
    }

    if (!in_array($build['tipo'], TIPOS_BUILD, true)) {
        $erros[] = 'Selecione um tipo de build válido.';
    }

    if ($build['descricao'] === '' || mb_strlen($build['descricao']) > 5000) {
        $erros[] = 'Informe a descrição, com até 5000 caracteres.';
    }

    foreach (['atributos' => 'Atributos', 'armas' => 'Armas', 'equipamentos' => 'Equipamentos'] as $campo => $rotulo) {
        if (mb_strlen($build[$campo]) > 5000) {
            $erros[] = "O campo $rotulo aceita até 5000 caracteres.";
        }
    }

    [$build['imagem'], $arquivoImagem] = lerCampoImagem($erros);

    if ($erros === []) {
        if ($arquivoImagem !== null) {
            $build['imagem'] = salvarImagemEnviada($arquivoImagem);
        }

        // O slug só muda junto com o nome, para não invalidar links já publicados.
        $slug = $build['nome'] === $nomeAtual ? $slugAtual : gerarSlugUnico($pdo, $build['nome'], $id);

        $valores = [
            $build['nome'],
            $slug,
            $build['tipo'],
            $build['descricao'],
            $build['atributos'] ?: null,
            $build['armas'] ?: null,
            $build['equipamentos'] ?: null,
            $build['imagem'] ?: null,
        ];

        try {
            if ($id === null) {
                $pdo->prepare(
                    'INSERT INTO builds (nome, slug, tipo, descricao, atributos, armas, equipamentos, imagem, criado_por)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
                )->execute([...$valores, $usuario['id']]);
                definirMensagem('sucesso', 'Build cadastrada com sucesso.');
            } else {
                $pdo->prepare(
                    'UPDATE builds
                     SET nome = ?, slug = ?, tipo = ?, descricao = ?, atributos = ?, armas = ?, equipamentos = ?, imagem = ?
                     WHERE id = ?'
                )->execute([...$valores, $id]);
                definirMensagem('sucesso', 'Build atualizada com sucesso.');
            }

            removerImagemEnviada($pdo, $imagemAnterior);

            redirecionar('admin/builds/index.php');
        } catch (PDOException $erro) {
            if ($erro->getCode() !== SQLSTATE_RESTRICAO) {
                throw $erro;
            }

            if ($arquivoImagem !== null) {
                removerImagemEnviada($pdo, $build['imagem']);
                $build['imagem'] = (string) $imagemAnterior;
            }

            // Outra requisição ocupou o mesmo slug entre a verificação e a gravação.
            $erros[] = 'Já existe uma build com identificador semelhante. Envie o formulário novamente.';
        }
    }
}

$tituloPagina = $id === null ? 'Cadastrar build' : 'Editar build';
$secaoAtiva = 'builds';
$migalhas = [['Builds', 'admin/builds/index.php'], [$tituloPagina, null]];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

    <?php exibirErros($erros); ?>

    <form method="post" enctype="multipart/form-data" class="contact-form form-admin">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" value="<?= e($build['nome']) ?>" maxlength="150" required>
        <?php if ($slugAtual !== null): ?>
          <small>Slug atual: <?= e($slugAtual) ?>. Ele será recalculado se o nome mudar.</small>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="tipo">Tipo *</label>
        <select id="tipo" name="tipo" required>
          <option value="">Selecione</option>
          <?php foreach (TIPOS_BUILD as $opcao): ?>
            <option value="<?= e($opcao) ?>" <?= $opcao === $build['tipo'] ? 'selected' : '' ?>><?= e($opcao) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group campo-largo">
        <label for="descricao">Descrição *</label>
        <textarea id="descricao" name="descricao" rows="5" maxlength="5000" required><?= e($build['descricao']) ?></textarea>
      </div>

      <div class="form-group campo-largo">
        <label for="atributos">Atributos</label>
        <textarea id="atributos" name="atributos" rows="4" maxlength="5000"><?= e($build['atributos']) ?></textarea>
      </div>

      <div class="form-group campo-largo">
        <label for="armas">Armas e magias</label>
        <textarea id="armas" name="armas" rows="4" maxlength="5000"><?= e($build['armas']) ?></textarea>
      </div>

      <div class="form-group campo-largo">
        <label for="equipamentos">Equipamentos</label>
        <textarea id="equipamentos" name="equipamentos" rows="4" maxlength="5000"><?= e($build['equipamentos']) ?></textarea>
      </div>

      <?php $imagemAtual = (string) $build['imagem']; require __DIR__ . '/../../includes/campo_imagem.php'; ?>

      <div class="form-botoes">
        <button type="submit" class="btn-acao">Salvar</button>
        <a href="<?= e(url('admin/builds/index.php')) ?>" class="btn-contorno">Cancelar</a>
      </div>

    </form>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
