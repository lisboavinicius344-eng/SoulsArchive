<?php

// Cadastro (sem ?id=) e edição (com ?id=) de chefes.

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');
$pdo = conectarBanco();

$id = null;
$imagemAnterior = null;
$boss = ['nome' => '', 'descricao' => '', 'dificuldade' => '', 'imagem' => '', 'destaque' => 0];

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT nome, descricao, dificuldade, imagem, destaque FROM bosses WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $boss = $consulta->fetch();

    if ($boss === false) {
        paginaErro(404, 'O chefe solicitado não foi encontrado.');
    }

    $imagemAnterior = $boss['imagem'];
}

$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $boss = [
        'nome' => textoPost('nome'),
        'descricao' => textoPost('descricao'),
        'dificuldade' => textoPost('dificuldade'),
        'destaque' => isset($_POST['destaque']) ? 1 : 0,
    ];

    if ($boss['nome'] === '' || mb_strlen($boss['nome']) > 150) {
        $erros[] = 'Informe o nome, com até 150 caracteres.';
    }

    if ($boss['descricao'] === '' || mb_strlen($boss['descricao']) > 5000) {
        $erros[] = 'Informe a descrição, com até 5000 caracteres.';
    }

    if ($boss['dificuldade'] === '' || mb_strlen($boss['dificuldade']) > 60) {
        $erros[] = 'Informe a dificuldade, com até 60 caracteres.';
    }

    [$boss['imagem'], $arquivoImagem] = lerCampoImagem($erros);

    if ($erros === []) {
        if ($arquivoImagem !== null) {
            $boss['imagem'] = salvarImagemEnviada($arquivoImagem);
        }

        $valores = [$boss['nome'], $boss['descricao'], $boss['dificuldade'], $boss['imagem'] ?: null, $boss['destaque']];

        if ($id === null) {
            $pdo->prepare('INSERT INTO bosses (nome, descricao, dificuldade, imagem, destaque) VALUES (?, ?, ?, ?, ?)')
                ->execute($valores);
            definirMensagem('sucesso', 'Chefe cadastrado com sucesso.');
        } else {
            $pdo->prepare('UPDATE bosses SET nome = ?, descricao = ?, dificuldade = ?, imagem = ?, destaque = ? WHERE id = ?')
                ->execute([...$valores, $id]);
            definirMensagem('sucesso', 'Chefe atualizado com sucesso.');
        }

        removerImagemEnviada($pdo, $imagemAnterior);

        redirecionar('admin/bosses/index.php');
    }
}

$tituloPagina = $id === null ? 'Cadastrar chefe' : 'Editar chefe';
$secaoAtiva = 'bosses';
$migalhas = [['Chefes', 'admin/bosses/index.php'], [$tituloPagina, null]];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

    <?php exibirErros($erros); ?>

    <form method="post" enctype="multipart/form-data" class="contact-form form-admin">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" value="<?= e($boss['nome']) ?>" maxlength="150" required>
      </div>

      <div class="form-group">
        <label for="dificuldade">Dificuldade *</label>
        <input type="text" id="dificuldade" name="dificuldade" value="<?= e($boss['dificuldade']) ?>" maxlength="60" required>
      </div>

      <div class="form-group campo-largo">
        <label for="descricao">Descrição *</label>
        <textarea id="descricao" name="descricao" rows="6" maxlength="5000" required><?= e($boss['descricao']) ?></textarea>
      </div>

      <?php $imagemAtual = (string) $boss['imagem']; require __DIR__ . '/../../includes/campo_imagem.php'; ?>

      <label class="campo-marcar">
        <input type="checkbox" name="destaque" value="1" <?= (int) $boss['destaque'] === 1 ? 'checked' : '' ?>>
        Exibir em destaque na página inicial e na lista de bosses principais
      </label>

      <div class="form-botoes">
        <button type="submit" class="btn-acao">Salvar</button>
        <a href="<?= e(url('admin/bosses/index.php')) ?>" class="btn-contorno">Cancelar</a>
      </div>

    </form>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
