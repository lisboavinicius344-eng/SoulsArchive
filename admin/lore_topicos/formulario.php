<?php

// Cadastro (sem ?id=) e edição (com ?id=) de tópicos de lore.

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador', 'editor');
$pdo = conectarBanco();

$id = null;
$topico = ['titulo' => '', 'ordem' => 0];

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT titulo, ordem FROM lore_topicos WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $topico = $consulta->fetch();

    if ($topico === false) {
        paginaErro(404, 'O tópico solicitado não foi encontrado.');
    }
}

$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $topico = ['titulo' => textoPost('titulo'), 'ordem' => textoPost('ordem')];

    if ($topico['titulo'] === '' || mb_strlen($topico['titulo']) > 150) {
        $erros[] = 'Informe o título, com até 150 caracteres.';
    }

    $ordem = validarOrdem($topico['ordem'], $erros);

    if ($erros === []) {
        if ($id === null) {
            $pdo->prepare('INSERT INTO lore_topicos (titulo, ordem) VALUES (?, ?)')
                ->execute([$topico['titulo'], $ordem]);
            definirMensagem('sucesso', 'Tópico cadastrado com sucesso.');
        } else {
            $pdo->prepare('UPDATE lore_topicos SET titulo = ?, ordem = ? WHERE id = ?')
                ->execute([$topico['titulo'], $ordem, $id]);
            definirMensagem('sucesso', 'Tópico atualizado com sucesso.');
        }

        redirecionar('admin/lore_topicos/index.php');
    }
}

$tituloPagina = $id === null ? 'Cadastrar tópico' : 'Editar tópico';
$secaoAtiva = 'lore_topicos';
$migalhas = [['Tópicos de Lore', 'admin/lore_topicos/index.php'], [$tituloPagina, null]];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

    <?php exibirErros($erros); ?>

    <form method="post" class="contact-form form-admin">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="titulo">Título *</label>
        <input type="text" id="titulo" name="titulo" value="<?= e($topico['titulo']) ?>" maxlength="150" required>
      </div>

      <div class="form-group">
        <label for="ordem">Ordem de exibição</label>
        <input type="number" id="ordem" name="ordem" value="<?= e((string) $topico['ordem']) ?>" min="0" max="9999">
        <small>Números menores aparecem primeiro.</small>
      </div>

      <div class="form-botoes">
        <button type="submit" class="btn-acao">Salvar</button>
        <a href="<?= e(url('admin/lore_topicos/index.php')) ?>" class="btn-contorno">Cancelar</a>
      </div>

    </form>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
