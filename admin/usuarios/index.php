<?php

require_once __DIR__ . '/../../includes/auth.php';

$usuario = exigirPerfil('administrador');

$busca = textoGet('q');
$origem = 'usuarios';
$parametros = [];

if ($busca !== '') {
    $origem .= ' WHERE nome LIKE ? OR nome_usuario LIKE ? OR email LIKE ?';
    $parametros = [termoLike($busca), termoLike($busca), termoLike($busca)];
}

$resultado = consultarPaginado(
    conectarBanco(),
    'id, nome, nome_usuario, email, perfil, ativo, ultimo_acesso',
    $origem,
    $parametros,
    'nome, id',
    10
);

$tituloPagina = 'Usuários';
$secaoAtiva = 'usuarios';
$subtituloPagina = 'Gerencie as contas com acesso a este painel.';
$migalhas = [['Usuários', 'admin/usuarios/index.php']];
$acaoPrincipal = ['Cadastrar usuário', 'admin/usuarios/formulario.php'];

require __DIR__ . '/../../includes/layout_admin_topo.php';

?>

        <section class="cartao cartao-tabela">

          <div class="barra-tabela">

            <form method="get" class="form-pesquisa" role="search">
              <label for="q" class="somente-leitor">Pesquisar usuários</label>
              <span class="campo-pesquisa">
                <?= icone('busca') ?>
                <input type="search" id="q" name="q" value="<?= e($busca) ?>" placeholder="Pesquisar por nome, usuário ou e-mail">
              </span>
              <button type="submit" class="btn-contorno">Pesquisar</button>
            </form>

            <span class="contador"><?= (int) $resultado['total'] ?> registro(s)</span>

          </div>

          <?php if ($resultado['itens'] === []): ?>
            <p class="estado-vazio">Nenhum usuário encontrado.</p>
          <?php else: ?>
            <div class="tabela-rolagem">
              <table class="tabela-admin">

                <thead>
                  <tr>
                    <th>Nome</th>
                    <th>Usuário</th>
                    <th>E-mail</th>
                    <th>Perfil</th>
                    <th>Situação</th>
                    <th>Último acesso</th>
                    <th class="coluna-acoes">Ações</th>
                  </tr>
                </thead>

                <tbody>
                  <?php foreach ($resultado['itens'] as $conta): ?>
                    <tr>
                      <td class="celula-nome"><?= e($conta['nome']) ?></td>
                      <td><?= e($conta['nome_usuario']) ?></td>
                      <td><?= e($conta['email']) ?></td>
                      <td>
                        <span class="selo <?= $conta['perfil'] === 'administrador' ? 'selo-ouro' : 'selo-neutro' ?>">
                          <?= e(PERFIS_USUARIO[$conta['perfil']]) ?>
                        </span>
                      </td>
                      <td>
                        <span class="selo <?= (int) $conta['ativo'] === 1 ? 'selo-sucesso' : 'selo-perigo' ?>">
                          <?= (int) $conta['ativo'] === 1 ? 'Ativa' : 'Desativada' ?>
                        </span>
                      </td>
                      <td><?= $conta['ultimo_acesso'] === null ? '—' : e(date('d/m/Y H:i', strtotime($conta['ultimo_acesso']))) ?></td>
                      <td class="celula-acoes">
                        <a href="<?= e(url('admin/usuarios/formulario.php?id=' . (int) $conta['id'])) ?>" class="btn-tabela">
                          <?= icone('editar') ?>
                          Editar
                        </a>

                        <?php if ((int) $conta['id'] === (int) $usuario['id']): ?>
                          <span class="selo selo-neutro">Sua conta</span>
                        <?php else: ?>
                          <form action="<?= e(url('admin/usuarios/excluir.php')) ?>" method="post" data-confirmar="Excluir a conta <?= e($conta['nome_usuario']) ?>?" data-confirmar-detalhe="Esta ação não pode ser desfeita. As builds criadas por esta conta continuam publicadas, sem autor.">
                            <?= campoCsrf() ?>
                            <input type="hidden" name="id" value="<?= (int) $conta['id'] ?>">
                            <button type="submit" class="btn-tabela btn-perigo">
                              <?= icone('excluir') ?>
                              Excluir
                            </button>
                          </form>
                        <?php endif; ?>
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
