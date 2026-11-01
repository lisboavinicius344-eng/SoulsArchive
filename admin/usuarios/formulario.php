<?php

// Cadastro (sem ?id=) e edição (com ?id=) de contas do painel.

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador');
$pdo = conectarBanco();

$id = null;
$conta = ['nome' => '', 'nome_usuario' => '', 'email' => '', 'perfil' => 'editor', 'ativo' => 1];

if (isset($_GET['id'])) {
    $id = idValido($_GET['id']);
    $consulta = $pdo->prepare('SELECT nome, nome_usuario, email, perfil, ativo FROM usuarios WHERE id = ?');
    $consulta->execute([$id ?? 0]);
    $conta = $consulta->fetch();

    if ($conta === false) {
        paginaErro(404, 'O usuário solicitado não foi encontrado.');
    }
}

$erros = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    exigirCsrf();

    $conta = [
        'nome' => textoPost('nome'),
        'nome_usuario' => textoPost('nome_usuario'),
        'email' => textoPost('email'),
        'perfil' => textoPost('perfil'),
        'ativo' => isset($_POST['ativo']) ? 1 : 0,
    ];

    // Na edição, a senha em branco mantém a atual.
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $senhaInformada = $id === null || $senha !== '';

    $erros = validarUsuario($conta['nome'], $conta['nome_usuario'], $conta['email'], $senhaInformada ? $senha : null);

    if (!array_key_exists($conta['perfil'], PERFIS_USUARIO)) {
        $erros[] = 'Selecione um perfil válido.';
    }

    // Quem edita é sempre um administrador ativo, então impedir a alteração da
    // própria conta garante que o sistema nunca fique sem administradores.
    if ($id === (int) $usuario['id'] && ($conta['perfil'] !== 'administrador' || $conta['ativo'] !== 1)) {
        $erros[] = 'Você não pode desativar a própria conta nem remover o próprio perfil de administrador.';
    }

    if ($erros === []) {
        try {
            if ($id === null) {
                $pdo->prepare(
                    'INSERT INTO usuarios (nome, nome_usuario, email, senha_hash, perfil, ativo) VALUES (?, ?, ?, ?, ?, ?)'
                )->execute([
                    $conta['nome'],
                    $conta['nome_usuario'],
                    $conta['email'],
                    password_hash($senha, PASSWORD_DEFAULT),
                    $conta['perfil'],
                    $conta['ativo'],
                ]);
                definirMensagem('sucesso', 'Usuário cadastrado com sucesso.');
            } else {
                $pdo->prepare('UPDATE usuarios SET nome = ?, nome_usuario = ?, email = ?, perfil = ?, ativo = ? WHERE id = ?')
                    ->execute([$conta['nome'], $conta['nome_usuario'], $conta['email'], $conta['perfil'], $conta['ativo'], $id]);

                if ($senhaInformada) {
                    $pdo->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
                        ->execute([password_hash($senha, PASSWORD_DEFAULT), $id]);
                }

                definirMensagem('sucesso', 'Usuário atualizado com sucesso.');
            }

            redirecionar('admin/usuarios/index.php');
        } catch (PDOException $erro) {
            if ($erro->getCode() !== SQLSTATE_RESTRICAO) {
                throw $erro;
            }

            $erros[] = 'O nome de usuário ou o e-mail já está em uso.';
        }
    }
}

$tituloPagina = $id === null ? 'Cadastrar usuário' : 'Editar usuário';
$secaoAtiva = 'usuarios';
$migalhas = [['Usuários', 'admin/usuarios/index.php'], [$tituloPagina, null]];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

    <?php exibirErros($erros); ?>

    <form method="post" class="contact-form form-admin">

      <?= campoCsrf() ?>

      <div class="form-group">
        <label for="nome">Nome *</label>
        <input type="text" id="nome" name="nome" value="<?= e($conta['nome']) ?>" maxlength="120" required>
      </div>

      <div class="form-group">
        <label for="nome_usuario">Nome de usuário *</label>
        <input type="text" id="nome_usuario" name="nome_usuario" value="<?= e($conta['nome_usuario']) ?>" maxlength="50" autocomplete="off" required>
      </div>

      <div class="form-group">
        <label for="email">E-mail *</label>
        <input type="email" id="email" name="email" value="<?= e($conta['email']) ?>" maxlength="190" autocomplete="off" required>
      </div>

      <div class="form-group">
        <label for="senha">Senha <?= $id === null ? '*' : '' ?></label>
        <input type="password" id="senha" name="senha" minlength="8" autocomplete="new-password" <?= $id === null ? 'required' : '' ?>>
        <?php if ($id !== null): ?>
          <small>Deixe em branco para manter a senha atual.</small>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label for="perfil">Perfil *</label>
        <select id="perfil" name="perfil" required>
          <?php foreach (PERFIS_USUARIO as $valor => $rotulo): ?>
            <option value="<?= e($valor) ?>" <?= $valor === $conta['perfil'] ? 'selected' : '' ?>><?= e($rotulo) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <label class="campo-marcar">
        <input type="checkbox" name="ativo" value="1" <?= (int) $conta['ativo'] === 1 ? 'checked' : '' ?>>
        Conta ativa
      </label>

      <div class="form-botoes">
        <button type="submit" class="btn-acao">Salvar</button>
        <a href="<?= e(url('admin/usuarios/index.php')) ?>" class="btn-contorno">Cancelar</a>
      </div>

    </form>

<?php require __DIR__ . '/../../includes/layout_admin_rodape.php'; ?>
